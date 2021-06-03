<?php

use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CoursePostsController;
use App\Http\Controllers\CourseStudentPostsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\RoleController;
use App\Models\Course;
use App\Models\CourseStudentPosts;
use App\Models\User;
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

Route::get("/", function (Request $request) {
    return 'xa';
});




Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('me', [AuthController::class, 'me']);
});


Route::get('roles', [RoleController::class, 'getRoles']);

Route::group([
    'middleware' => ['api', 'auth'],
    'prefix' => 'courses'
], function () {
    Route::get('/', [CourseController::class, 'getClasses']);
    Route::post('/', [CourseController::class, 'createClass'])->middleware('teacher');
    Route::get('{course_id}/search/{query}', [CourseController::class, 'search']);
    
    // Route::post('/post', [CourseController::class, 'createPost']);

    Route::group([
        'middleware' => 'teacher',
        'prefix' => 'posts'
    ], function () {
        Route::post('/', [CoursePostsController::class, 'createPost']);
    });

    Route::group([
        'middleware' => 'student'
    ], function () {
        Route::post('/studentPosts', [CourseStudentPostsController::class, 'createPost']);
    });

    Route::group([
        'prefix' => 'files'
    ], function () {
        Route::post('/', [FileController::class, 'newFile'])->middleware('teacher');
        Route::get('/{id}', [FileController::class, 'download']);
    });

    Route::group([
        'prefix' => 'assignments'
    ], function () {
        Route::post('/', [AssignmentsController::class, 'newAssignment'])->middleware('teacher');
        Route::get('{id}/file', [AssignmentsController::class, 'download']);
    });
});

Route::group([
    'prefix' => 'user'
], function () {

    Route::get('/email', [UserController::class, 'findByEmail']);
    Route::put('/summary', [UserController::class, 'updateSummary'])->middleware('auth');
    Route::post('/{id}/follow', [UserController::class, 'follow'])->middleware('auth');
    Route::post('/{id}/unfollow', [UserController::class, 'unfollow'])->middleware('auth');
    Route::get('/following/{id}', [UserController::class, 'getFollowStatus'])->middleware('auth');
    Route::get('/following', [UserController::class, 'getUsersFollowing'])->middleware('auth');
    Route::get('/{id?}', [UserController::class, 'findUser'])->middleware('auth');


    Route::group([
        'prefix' => 'blogs',
        'middleware' => 'auth'
    ], function () {
        Route::post('/', [BlogsController::class, 'createBlog']);
        Route::get('/all', [BlogsController::class, 'getBlogs']);
    });
});
