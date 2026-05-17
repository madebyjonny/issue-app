<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\DirectMessageController;
use App\Http\Controllers\EpicController;
use App\Http\Controllers\HuddleController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\DocFolderController;
use App\Http\Controllers\WhiteboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectResourceController;
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

    // Members
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

    // Resources
    Route::post('/projects/{project}/resources', [ProjectResourceController::class, 'store'])->name('projects.resources.store');
    Route::put('/projects/{project}/resources/{resource}', [ProjectResourceController::class, 'update'])->name('projects.resources.update');
    Route::delete('/projects/{project}/resources/{resource}', [ProjectResourceController::class, 'destroy'])->name('projects.resources.destroy');
    Route::post('/projects/{project}/resources/{resource}/tickets', [ProjectResourceController::class, 'attachTicket'])->name('projects.resources.tickets.attach');
    Route::delete('/projects/{project}/resources/{resource}/tickets/{ticket}', [ProjectResourceController::class, 'detachTicket'])->name('projects.resources.tickets.detach');

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

    // Messaging – channels
    Route::get('/projects/{project}/channels', [ChannelController::class, 'index'])->name('channels.index');
    Route::post('/projects/{project}/channels', [ChannelController::class, 'store'])->name('channels.store');
    Route::get('/projects/{project}/channels/{channel}', [ChannelController::class, 'show'])->name('channels.show');
    Route::delete('/projects/{project}/channels/{channel}', [ChannelController::class, 'destroy'])->name('channels.destroy');

    // Messaging – messages
    Route::post('/projects/{project}/channels/{channel}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::delete('/projects/{project}/channels/{channel}/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('/projects/{project}/channels/{channel}/messages/{message}/thread', [MessageController::class, 'thread'])->name('messages.thread');

    // Messaging – direct messages
    Route::get('/projects/{project}/dm/{user}', [DirectMessageController::class, 'show'])->name('dm.show');
    Route::post('/projects/{project}/dm/{user}/messages', [DirectMessageController::class, 'store'])->name('dm.store');

    // Board presence (cursor broadcast)
    Route::post('/projects/{project}/presence/cursor', [PresenceController::class, 'cursor'])->name('presence.cursor');

    // Huddle
    Route::post('/projects/{project}/huddle/start', [HuddleController::class, 'start'])->name('huddle.start');
    Route::post('/projects/{project}/huddle/{huddle}/leave', [HuddleController::class, 'leave'])->name('huddle.leave');
    Route::post('/projects/{project}/huddle/signal', [HuddleController::class, 'signal'])->name('huddle.signal');

    // AI
    Route::post('/projects/{project}/ai/summarise', [AiController::class, 'summarise'])->name('ai.summarise');
    Route::post('/projects/{project}/ai/ticket', [AiController::class, 'quickCreateTicket'])->name('ai.ticket.create');
    Route::post('/projects/{project}/ai/doc', [AiController::class, 'startDoc'])->name('ai.doc.create');

    // Docs
    Route::get('/projects/{project}/docs/search', [DocController::class, 'search'])->name('docs.search');
    Route::get('/projects/{project}/docs', [DocController::class, 'index'])->name('docs.index');
    Route::get('/projects/{project}/docs/new', [DocController::class, 'create'])->name('docs.create');
    Route::post('/projects/{project}/docs', [DocController::class, 'store'])->name('docs.store');
    Route::get('/projects/{project}/docs/{doc}', [DocController::class, 'show'])->name('docs.show');
    Route::get('/projects/{project}/docs/{doc}/edit', [DocController::class, 'edit'])->name('docs.edit');
    Route::put('/projects/{project}/docs/{doc}', [DocController::class, 'update'])->name('docs.update');
    Route::delete('/projects/{project}/docs/{doc}', [DocController::class, 'destroy'])->name('docs.destroy');

    // Whiteboard sync
    Route::post('/projects/{project}/docs/{doc}/whiteboard/sync', [WhiteboardController::class, 'sync'])->name('whiteboard.sync');

    // Doc folders
    Route::post('/projects/{project}/doc-folders', [DocFolderController::class, 'store'])->name('docs.folders.store');
    Route::patch('/projects/{project}/doc-folders/{folder}', [DocFolderController::class, 'update'])->name('docs.folders.update');
    Route::delete('/projects/{project}/doc-folders/{folder}', [DocFolderController::class, 'destroy'])->name('docs.folders.destroy');
});

require __DIR__.'/auth.php';
