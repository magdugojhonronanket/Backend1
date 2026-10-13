<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

$baseSpaces = [
    ["id" => 1, "name" => "Slot A1", "location" => "Building A1"],
    ["id" => 2, "name" => "Slot A2", "location" => "Building A2"],
    ["id" => 3, "name" => "Slot B1", "location" => "Building B1"],
];

$getReservations = fn () => Cache::get('reservations', [
    [
        'id'               => 1,
        'parking_space_id' => 1,
        'name'             => 'charles',
        'date'             => '2026-10-06',
        'time'             => '10:00',
        'status'           => 'confirmed',
    ],
]);

// status = reserved if naay reservation, otherwise available
$getSpaces = function () use ($baseSpaces, $getReservations) {
    $reservedIds = collect($getReservations())->pluck('parking_space_id')->all();

    return array_map(
        fn ($s) => [...$s, 'status' => in_array($s['id'], $reservedIds) ? 'reserved' : 'available'],
        $baseSpaces
    );
};

// #1 GET all parking spaces
Route::get('/parking-spaces', fn () => $getSpaces());

// #2 GET one parking space
Route::get('/parking-spaces/{id}', function ($id) use ($getSpaces) {
    $space = collect($getSpaces())->firstWhere('id', (int) $id);
    return $space ?? response()->json(['message' => 'Parking space not found'], 404);
});

// #4 GET all reservations
Route::get('/reservations', fn () => array_values($getReservations()));

Route::get('/reservations/{id}', function ($id) use ($getReservations) {
    $r = collect($getReservations())->firstWhere('id', (int) $id);
    return $r ?? response()->json(['message' => 'Reservation not found'], 404);
});

// #3 POST create reservation
Route::post('/reservations', function (Request $request) use ($getSpaces, $getReservations) {
    $data = $request->validate([
        'parking_space_id' => 'required|integer',
        'name'             => 'required|string',
        'date'             => 'required|string',
        'time'             => 'required|string',
    ]);

    $space = collect($getSpaces())->firstWhere('id', (int) $data['parking_space_id']);

    if (! $space) {
        return response()->json(['message' => 'Parking space not found'], 404);
    }
    if ($space['status'] === 'reserved') {
        return response()->json(['message' => 'Parking space is already reserved'], 409);
    }

    $reservations = $getReservations();

    $reservation = [
        'id'               => (collect($reservations)->max('id') ?? 0) + 1,
        'parking_space_id' => (int) $data['parking_space_id'],
        'name'             => $data['name'],
        'date'             => $data['date'],
        'time'             => $data['time'],
        'status'           => 'confirmed',
    ];

    $reservations[] = $reservation;
    Cache::forever('reservations', $reservations);

    return response()->json([
        'message'     => 'Reservation created successfully',
        'reservation' => $reservation,
    ], 201);
});

// #5 DELETE cancel reservation
Route::delete('/reservations/{id}', function ($id) use ($getReservations) {
    $remaining = collect($getReservations())
        ->reject(fn ($r) => $r['id'] === (int) $id)
        ->values()
        ->all();

    Cache::forever('reservations', $remaining);

    return [
        'message' => 'Reservation cancelled successfully',
        'id'      => (int) $id,
    ];
});
});
