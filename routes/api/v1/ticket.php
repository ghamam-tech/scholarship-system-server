<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/student/tickets', [TicketController::class, 'studentIndex']);
    Route::post('/student/tickets', [TicketController::class, 'studentStore']);
    Route::get('/student/tickets/{ticketId}', [TicketController::class, 'studentShow'])
        ->whereNumber('ticketId');
    Route::post('/student/tickets/{ticketId}/reply', [TicketController::class, 'studentReply'])
        ->whereNumber('ticketId');
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/tickets', [TicketController::class, 'adminIndex']);
    Route::get('/admin/tickets/{ticketId}', [TicketController::class, 'adminShow'])
        ->whereNumber('ticketId');
    Route::post('/admin/tickets/{ticketId}/reply', [TicketController::class, 'adminReply'])
        ->whereNumber('ticketId');
    Route::patch('/admin/tickets/{ticketId}/status', [TicketController::class, 'adminUpdateStatus'])
        ->whereNumber('ticketId');
});
