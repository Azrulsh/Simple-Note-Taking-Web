<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RemarkController;

// Protect the routes with authentication
Route::middleware('auth')->group(function () {    
//Manage Note
Route::get('/manage-note', [NoteController::class, 'index'])->name('manage-note.IndexNote');
Route::get('/manage-note/CreateNote', [NoteController::class, 'create'])->name('manage-note.CreateNote');
Route::post('/manage-note/CreateNote', [NoteController::class, 'store'])->name('manage-note.StoreNote');
Route::get('/manage-note/{id}/EditNote', [NoteController::class, 'edit'])->name('manage-note.EditNote');
Route::put('/manage-note/{note}', [NoteController::class, 'update'])->name('manage-note.UpdateNote');
Route::get('/manage-note/{id}', [NoteController::class, 'show'])->name('manage-note.ListAllNote');
Route::delete('/manage-note/{id}', [NoteController::class, 'destroy'])->name('manage-note.DeleteNote');

//Manage Remark
Route::get('/manage-remark', [RemarkController::class, 'index'])->name('manage-remark.IndexRemark');
Route::get('/manage-remark/ListRemark', [RemarkController::class, 'getremark'])->name('manage-remark.ListRemark');
Route::post('/manage-remark/SaveRemark', [RemarkController::class, 'saveremark'])->name('manage-remark.SaveRemark');
Route::post('/manage-remark/UpdateRemark', [RemarkController::class, 'updateremark'])->name('manage-remark.UpdateRemark');
Route::post('/manage-remark/DeleteRemark', [RemarkController::class, 'deleteremark'])->name('manage-remark.DeleteRemark');
Route::post('/manage-remark/check-edit', [RemarkController::class, 'checkEditRemark'])->name('manage-remark.CheckEditRemark');
Route::post('/manage-remark/check-delete', [RemarkController::class, 'checkDeleteRemark'])->name('manage-remark.CheckDeleteRemark');
});

//Manage Login
Route::get('/', [LoginController::class, 'login'])->name('login');
Route::get('/manage-login/Login', [LoginController::class, 'login'])->name('login');
Route::post('/manage-login/Login', [LoginController::class, 'postlogin'])->name('manage-login.PostLogin');
Route::get('/manage-login/Registration',[LoginController::class, 'registration'])->name('manage-login.Registration');
Route::post('/manage-login/Registration', [LoginController::class, 'postregistration'])->name('manage-login.PostRegistration');
Route::get('/manage-login/Logout', [LoginController::class, 'logout'])->name('manage-login.Logout');


