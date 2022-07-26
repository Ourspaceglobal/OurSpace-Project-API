<?php

use App\Http\Controllers\Generic\AmenityController;
use App\Http\Controllers\Generic\ApartmentController;
use App\Http\Controllers\Generic\ApartmentDurationController;
use App\Http\Controllers\Generic\CategoryController;
use App\Http\Controllers\Generic\CityController;
use App\Http\Controllers\Generic\CommentController;
use App\Http\Controllers\Generic\DatatypeController;
use App\Http\Controllers\Generic\LandlordUserController;
use App\Http\Controllers\Generic\LocalGovernmentController;
use App\Http\Controllers\Generic\PaystackController;
use App\Http\Controllers\Generic\PostController;
use App\Http\Controllers\Generic\StateController;
use App\Http\Controllers\Generic\SubCategoryController;
use App\Http\Controllers\Generic\SystemDataController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/paystack', [PaystackController::class, 'webhook']);

Route::get('datatypes', [DatatypeController::class, 'index']);
Route::apiResource('posts', PostController::class)->only([
    'index',
    'show',
]);
Route::get('comments/{comment}/replies', [CommentController::class, 'index']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}/apartments', [CategoryController::class, 'apartments']);
Route::get('sub-categories', [SubCategoryController::class, 'index']);
Route::get('sub-categories/{category}/apartments', [SubCategoryController::class, 'apartments']);
Route::get('apartment-durations', [ApartmentDurationController::class, 'index']);
Route::get('amenities', [AmenityController::class, 'index']);
Route::get('states', [StateController::class, 'index']);
Route::get('cities', [CityController::class, 'index']);
Route::get('local-governments', [LocalGovernmentController::class, 'index']);
Route::get('users/{user}', [LandlordUserController::class, 'show']);

Route::get('apartments', [ApartmentController::class, 'index']);
Route::get('apartments/{apartment}', [ApartmentController::class, 'show']);

Route::get('system-data/{systemData}', [SystemDataController::class, 'show']);
