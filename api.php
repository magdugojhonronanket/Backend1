<?php

use Illuminate\Support\Facades\Route;

Route::get('/parking-spaces', function () {
    return [
        ["id" => 1, "name" => "Slot A1", "location" => "Building A1", "status" => "available"],
        ["id" => 2, "name" => "Slot A2", "location" => "Building A2", "status" => "reserved"],
        ["id" => 3, "name" => "Slot B1", "location" => "Building B1", "status" => "available"],
    ];
});

Route::get('/parking-spaces/{id}', function ($id) {
    return [
        'id' => $id,
        'name' => 'Slot A1',
        'location' => 'Building A',
        'status' => 'available',
    ];
});

Route::get('/reservations', function () {
    return [
        [
            'id' => 1,
            'parking_space_id' => 1,
            'name' => 'charles',
            'date' => '2026-10-06',
            'time' => '10:00',
            'status' => 'confirmed',
        ],
    ];
});

Route::get('/reservations/{id}', function ($id) {
    return [
        'id' => (int) $id,
        'parking_space_id' => 1,
        'name' => 'charles',
        'date' => '2026-10-06',
        'time' => '10:00',
        'status' => 'confirmed',
    ];
});

Route::post('/reservations', function () {
    return ['message' => 'Reservation created successfully'];
});

Route::delete('/reservations/{id}', function ($id) {
    return [
        'message' => 'Reservation cancelled successfully',
        'id' => $id,
    ];
});