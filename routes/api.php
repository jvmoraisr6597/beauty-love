<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomAccessTokenController;

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
//Permissions
Route::put('/clients/forgotPassword', 'UserController@updatePasswordFirstStep')->name('user.updatePasswordFirstStep.api');
Route::post('oauth/token', [CustomAccessTokenController::class, 'login']);
Route::post('/clients', 'UserController@storeApi')->name('user.store.api');
Route::get('/clients', 'UserController@getClientsJson')->name('user.getall.api');
Route::get('/clients/{user}', 'UserController@showApi')->name('user.show.api');
Route::put('/clients/{user}', 'UserController@updateApi')->name('user.update.api');
Route::put('/clients/{user}/updatePassword', 'UserController@updatePasswordApi')->name('user.updatePassword.api');
Route::delete('/clients/{user}', 'UserController@deleteApi')->name('user.delete.api');

Route::get('/products/{product}', 'ProductsController@show')->name('products.show.api');
Route::get('/products', 'ProductsController@getProductsJson')->name('products.index.api');
Route::get('/categories/{category}', 'CategoriesController@show')->name('categories.show.api');
Route::get('/categories', 'CategoriesController@getCategoriesJson')->name('categories.index.api');
Route::get('/brands/{brand}', 'BrandsController@show')->name('brands.show.api');
Route::get('/brands', 'BrandsController@getbrandsJson')->name('brands.index.api');
Route::get('/comments/{comment}', 'CommentsController@show')->name('comment.show.api');
Route::get('/comments', 'CommentsController@getCommentsJson')->name('comment.index.api');
Route::get('/comments/products/{product}', 'CommentsController@getCommentsByProduct')->name('comment.byProduct.api');
Route::get('/comments/user/{user_id}', 'CommentsController@getCommentsByCustomer')->name('comment.byCustomer.api');

Route::middleware('auth:api')->group(function () {
    Route::post('/products', 'ProductsController@store')->name('products.store.api');
    
    Route::put('/products/{product}', 'ProductsController@update')->name('products.update.api');
    Route::delete('/products/{product}', 'ProductsController@delete')->name('products.delete.api');

    Route::post('/categories', 'CategoriesController@store')->name('categories.store.api');
    
    Route::put('/categories/{category}', 'CategoriesController@update')->name('categories.update.api');
    Route::delete('/categories/{category}', 'CategoriesController@delete')->name('categories.delete.api');


    Route::post('/brands', 'BrandsController@store')->name('brands.store.api');
    Route::put('/brands/{brand}', 'BrandsController@update')->name('brands.update.api');
    Route::delete('/brands/{brand}', 'BrandsController@delete')->name('brands.delete.api');
    
    Route::post('/comments', 'CommentsController@store')->name('comment.store.api');
    Route::put('/comments/{comment}', 'CommentsController@update')->name('comment.update.api');
    Route::delete('/comments/{comment}', 'CommentsController@delete')->name('client.delete.api');
});