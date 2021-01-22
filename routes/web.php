<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});
Route::namespace('Admin')->prefix('admin')->group(function () {
    Route::get('login','UserController@index')->name('login');
    Route::post('check_user','UserController@login');

    Route::middleware(['auth'])->group(function () {
        Route::get('dashboard','DashboardController@index');

        Route::get('logout','UserController@logout');
        Route::post('editCity','BusinessController@editCity');
        Route::get('addLocation','BusinessController@addLocation');
        Route::post('createCity','BusinessController@createCity');

        Route::resource('businesses','BusinessController');
        
        Route::get('term','UserController@term');
        Route::get('about','UserController@about');
        Route::get('contact','UserController@contact');
        
        Route::get('policy','UserController@policy');
        Route::post('updatePrivacy','UserController@updatePrivacy');
        Route::post('updateTerm','UserController@updateTerm');
        Route::post('updateAbout','UserController@updateAbout');
        Route::post('updateContact','UserController@updateContact');
        Route::resource('users','UsersController');
        Route::get('advertiser','BusinessController@advertiser');
        Route::get('city','BusinessController@city');
        Route::resource('setting','SettingController');
        Route::get('/viewUser/{id}',['as'=>'viewUser', 'uses' => 'BusinessController@viewUser']);
        Route::get('/viewUsers/{id}',['as'=>'viewUsers', 'uses' => 'BusinessController@viewUsers']);
        Route::get('/viewApproved/{id}',['as'=>'viewApproved', 'uses' => 'BusinessController@viewApproved']);
        Route::get('/viewApproveds/{id}',['as'=>'viewApproveds', 'uses' => 'BusinessController@viewApproveds']);

         Route::get('/updateCity/{id}',['as'=>'updateCity', 'uses' => 'BusinessController@updateCity']);

          Route::get('/delete/{id}',['as'=>'delete', 'uses' => 'UserController@delete']);
          Route::get('/deleteUser/{id}',['as'=>'deleteUser', 'uses' => 'UserController@deleteUser']);
          Route::get('/deleteAdv/{id}',['as'=>'deleteAdv', 'uses' => 'UserController@deleteAdv']);
        
    });
});
