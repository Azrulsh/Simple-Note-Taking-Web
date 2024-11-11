<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;

//Manage Note
Route::get('/', [NoteController::class, 'index'])->name('home');
Route::get('/manage-note', [NoteController::class, 'index'])->name('manage-note.IndexNote');
Route::get('/manage-note/CreateNote', [NoteController::class, 'create'])->name('manage-note.CreateNote');
Route::post('/manage-note/CreateNote', [NoteController::class, 'store'])->name('manage-note.StoreNote');
Route::get('/manage-note/{id}/EditNote', [NoteController::class, 'edit'])->name('manage-note.EditNote');
Route::put('/manage-note/{note}', [NoteController::class, 'update'])->name('manage-note.UpdateNote');
Route::get('/manage-note/{id}', [NoteController::class, 'show'])->name('manage-note.ListAllNote');
Route::delete('/manage-note/{id}', [NoteController::class, 'destroy'])->name('manage-note.DeleteNote');
