<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\EpicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect('/projects') : view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('projects.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Board
    Route::get('/projects/{project}/board', [BoardController::class, 'show'])->name('projects.board');

    // Columns
    Route::post('/projects/{project}/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::put('/projects/{project}/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::delete('/projects/{project}/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->name('columns.reorder');

    // Tickets
    Route::post('/projects/{project}/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/projects/{project}/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/projects/{project}/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/projects/{project}/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::patch('/projects/{project}/tickets/{ticket}/move', [TicketController::class, 'move'])->name('tickets.move');

    // Sprints
    Route::post('/projects/{project}/sprints', [SprintController::class, 'store'])->name('sprints.store');
    Route::put('/projects/{project}/sprints/{sprint}', [SprintController::class, 'update'])->name('sprints.update');
    Route::delete('/projects/{project}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('sprints.destroy');

    // Epics
    Route::get('/projects/{project}/epics', [EpicController::class, 'index'])->name('epics.index');
    Route::post('/projects/{project}/epics', [EpicController::class, 'store'])->name('epics.store');
    Route::get('/projects/{project}/epics/{epic}', [EpicController::class, 'show'])->name('epics.show');
    Route::put('/projects/{project}/epics/{epic}', [EpicController::class, 'update'])->name('epics.update');
    Route::delete('/projects/{project}/epics/{epic}', [EpicController::class, 'destroy'])->name('epics.destroy');
    Route::post('/projects/{project}/epics/{epic}/tickets', [EpicController::class, 'createTicket'])->name('epics.tickets.store');
});

require __DIR__.'/auth.php';
