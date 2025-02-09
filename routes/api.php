<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MessGroupController;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

// Include the Sanctum-specific routes

require base_path('routes/sanctum.php');

// Mess Groups
Route::apiResource('mess-groups', MessGroupController::class);
Route::post('/mess-groups/{messGroup}/members', [MessGroupController::class, 'addMember']);
Route::delete('/mess-groups/{messGroupId}/members/{memberId}', [MessGroupController::class, 'removeMember']);
Route::get('/mess-groups/{messGroup}/members', [MessGroupController::class, 'getMembers']);

// Members
Route::apiResource('members', MemberController::class);

// Expenses
Route::apiResource('expenses', ExpenseController::class);
// Ignore it for now
//Route::post('/mess-groups/{messGroup}/expenses/import', [ExpenseController::class, 'import']);

// Balance Calculation
Route::post('/mess-groups/{messGroup}/calculate-balances', [MessGroupController::class, 'calculateBalances']);
