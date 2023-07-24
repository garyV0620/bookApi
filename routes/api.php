<?php

use App\Http\Controllers\Api\Book;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('isValidToken')->resource('book', Book::class)->only(['index', 'store', 'show']);

Route::middleware('isValidToken')->group(function(){
    //added route to a resource controller
    Route::prefix('books')->group(function(){
        Route::get('title/{title}', [Book::class, 'title']);
        Route::get('author/{author}', [Book::class, 'author']);
        Route::get('search/{titleOrAuthor}', [Book::class, 'search']);
    });
   
    Route::resource('books', Book::class)->only(['index', 'store', 'show', 'update']);
});