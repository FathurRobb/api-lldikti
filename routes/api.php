<?php

use App\Http\Controllers\API\ActivityController;
use App\Http\Controllers\API\NewsCategoryController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\TestimoniController;
use App\Http\Controllers\API\GalleryCategoryController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\CarouselController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function () {
    /**
     * Route News Category
     */
    Route::get('newscategory', [NewsCategoryController::class, 'get']);
    Route::post('newscategory', [NewsCategoryController::class, 'store']);
    Route::post('newscategory/retrieve', [NewsCategoryController::class, 'retrieve']);
    Route::put('newscategory', [NewsCategoryController::class, 'update']);
    Route::delete('newscategory', [NewsCategoryController::class, 'delete']);

    /**
     * Route Activity
     */
    Route::get('activity', [ActivityController::class, 'get']);
    Route::post('activity', [ActivityController::class, 'store']);
    Route::post('activity/retrieve', [ActivityController::class, 'retrieve']);
    Route::put('activity', [ActivityController::class, 'update']);
    Route::delete('activity', [ActivityController::class, 'delete']);
    Route::post('activity/upload', [ActivityController::class, 'uploadDoc']);

    /**
     * Route News
     */
    Route::get('news', [NewsController::class, 'get']);
    Route::post('news', [NewsController::class, 'store']);
    Route::post('news/retrieve', [NewsController::class, 'retrieve']);
    Route::put('news', [NewsController::class, 'update']);
    Route::delete('news', [NewsController::class, 'delete']);
    Route::post('news/upload', [NewsController::class, 'uploadThumbnail']);
    
    // Route Settings
    Route::get('setting', [SettingController::class, 'get']);
    Route::get('setting/{id}', [SettingController::class, 'show']);
    Route::post('setting', [SettingController::class, 'store']);
    Route::post('setting/{id}', [SettingController::class, 'update']);
    Route::delete('setting/{id}', [SettingController::class, 'destroy']);

    // Route Testimonials
    Route::get('testimoni', [TestimoniController::class, 'get']);
    Route::get('testimoni/{id}', [TestimoniController::class, 'show']);
    Route::post('testimoni', [TestimoniController::class, 'store']);
    Route::post('testimoni/{id}', [TestimoniController::class, 'update']);
    Route::delete('testimoni/{id}', [TestimoniController::class, 'destroy']);

    // Route Gallery Categories
    Route::get('gallery-category', [GalleryCategoryController::class, 'get']);
    Route::get('gallery-category/{id}', [GalleryCategoryController::class, 'show']);
    Route::post('gallery-category', [GalleryCategoryController::class, 'store']);
    Route::post('gallery-category/{id}', [GalleryCategoryController::class, 'update']);
    Route::delete('gallery-category/{id}', [GalleryCategoryController::class, 'destroy']);

    // Route Gallery Categories
    Route::get('gallery', [GalleryController::class, 'get']);
    Route::get('gallery/{id}', [GalleryController::class, 'show']);
    Route::post('gallery', [GalleryController::class, 'store']);
    Route::post('gallery/{id}', [GalleryController::class, 'update']);
    Route::delete('gallery/{id}', [GalleryController::class, 'destroy']);

    // Route Carousel
    Route::get('carousel', [CarouselController::class, 'get']);
    Route::get('carousel/{id}', [CarouselController::class, 'show']);
    Route::post('carousel', [CarouselController::class, 'store']);
    Route::post('carousel/{id}', [CarouselController::class, 'update']);
    Route::delete('carousel/{id}', [CarouselController::class, 'destroy']);

});