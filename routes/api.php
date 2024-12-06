<?php

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

// // User Routes
// Route::get('/users', [UserController::class, 'index']);
// Route::get('/users/{id}', [UserController::class, 'show']);
// Route::post('/users', [UserController::class, 'store']);
// Route::put('/users/{id}', [UserController::class, 'update']);
// Route::delete('/users/{id}', [UserController::class, 'destroy']);

// // Post Routes
// Route::get('/posts', [PostController::class, 'index']);
// Route::get('/posts/{id}', [PostController::class, 'show']);
// Route::post('/posts', [PostController::class, 'store']);
// Route::put('/posts/{id}', [PostController::class, 'update']);
// Route::delete('/posts/{id}', [PostController::class, 'destroy']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::delete('/delete-user', [UserController::class, 'deleteUser']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::put('/profile/password', [UserController::class, 'changePassword']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/users', [UserController::class, 'index']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/search', [PostController::class, 'search']);
    Route::get('/posts/{postId}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{postId}', [PostController::class, 'update']);
    Route::delete('/posts/{postId}', [PostController::class, 'destroy']);

    Route::get('/posts/{postId}/comments', [PostController::class, 'showComments']);
    Route::get('/posts/{postId}/comments/{commentId}', [PostController::class, 'showComment']);
    Route::post('/posts/{postId}/comments', [PostController::class, 'storeComment']);
    Route::put('/posts/{postId}/comments/{commentId}', [PostController::class, 'updateComment']);
    Route::delete('/posts/{postId}/comments/{commentId}', [PostController::class, 'destroyComment']);

    Route::get('/posts/{postId}/likes', [PostController::class, 'getPostLikes']);
    Route::post('/posts/{postId}/toggle-like', [PostController::class, 'toggleLike']);
    Route::get('/posts/{postId}/likes/count', [PostController::class, 'countPostLikes']);


    
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
