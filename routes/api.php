<?php

use App\Http\Controllers\CheckListController;
use App\Http\Controllers\CheckListItemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::group(['prefix' => 'checklist'], function () {
//        endpoint for checklist
        Route::get('/', [ChecklistController::class, 'index']);
        Route::post('/', [ChecklistController::class, 'store']);
        Route::delete('/{checklistId}', [ChecklistController::class, 'destroy']);

//        endpoint for checklist item
        Route::get('{checklistId}/item', [CheckListItemController::class, 'index']);
        Route::post('{checklistId}/item', [CheckListItemController::class, 'store']);
        Route::get('{checklistId}/item/{checklistItemId}', [CheckListItemController::class, 'getItem']);
        Route::put('{checklistId}/item/{checklistItemId}', [CheckListItemController::class, 'updateStatus']);
        Route::delete('{checklistId}/item/{checklistItemId}', [CheckListItemController::class, 'deleteItem']);
        Route::put('{checklistId}/item/rename/{checklistItemId}', [CheckListItemController::class, 'renameItem']);
    });

    Route::post('logout', [AuthController::class, 'logout']);
});
