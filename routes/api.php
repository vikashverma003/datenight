<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('Api')->group(function () {
     Route::post('login', 'UserController@login');
     Route::post('signup', 'UserController@signup');
     Route::get('locations','UserController@locations');
     Route::post('check_social_user_exist','UserController@checkSocialUserExist');
     Route::get('countLocation','UserController@countLocation');
     Route::get('term','UserController@term');
     Route::get('privacy','UserController@privacy');
     Route::get('about','UserController@about');
     Route::get('contact','UserController@contact');
     Route::middleware(['auth:api'])->group(function () {
        Route::post('logout', 'UserController@logout');
        Route::post('change-password', 'UserController@changePassword');
        Route::post('on_of_notification','UserController@onOffNotification');
        Route::get('contact_us','UserController@ContactUs');
        Route::get('term_condition','UserController@termCondition');
        Route::get('about_us','UserController@aboutus');
        Route::post('updateNotification','UserController@updateNotification');
        
        
        Route::middleware(['user_auth'])->group(function () {

        Route::post('notificationList', 'UserController@notificationList');
        Route::post('profile/image-upload', 'UserController@imageUpload');
        Route::get('profile/skip-image-upload', 'UserController@skipImageUpload');
        Route::get('businesses','BusinessController@getBusinesses');
        Route::post('getContact','BusinessController@getContact');
        Route::get('single_business/{id}','BusinessController@getBusiness');
        Route::post('mark_favourite','FavouriteController@makeFavourite');
        Route::get('get_user_fav_businesses','FavouriteController@getUserFavouriteRestourent');
        Route::post('create_date_night','DateNightController@createDateNightEvent');
        Route::post('edit_user_profile','UserController@editUserProfile');
        Route::get('get_date_nights','DateNightController@getDateNights');
        Route::post('invitation_action','DateNightController@invitationAction');
        Route::post('get_next_bussiness','DateNightController@getNextBussiness');
        Route::get('notification','BusinessController@notification');
        });
        
        Route::prefix('business')->middleware(['business_auth'])->group(function () {
            Route::post('profile-images', 'BusinessController@businessProfileImages');
            Route::post('create-event','BusinessEventController@createEvent');
            Route::get('my_events','BusinessEventController@getMyEvents');
            Route::post('edit-event','BusinessEventController@editEvent');
            Route::post('delete-event','BusinessEventController@deleteEvent');
            Route::post('edit_business_profile','UserController@editBusinessProfile');
            Route::get('my-business','BusinessController@myBusiness');
            Route::resource('plans','PlanController');
            Route::get('get_my_plan','PlanController@myPlan');
            Route::post('edit_business_timing','BusinessController@editBusinessTiming');
            Route::post('business_image_upload', 'BusinessController@businessImageAdd'); 
            Route::post('business_image_delete', 'BusinessController@businessImageDelete');  
            Route::post('event_asset_add', 'BusinessEventController@eventAssetAdd'); 
            Route::post('event_asset_delete', 'BusinessEventController@eventAssetDelete');
            Route::post('edit-special-event','SpecialEventController@updateEvent');
            Route::resource('special-event','SpecialEventController');
            Route::post('special_event_image_add', 'SpecialEventController@eventAssetAdd'); 
            Route::post('special_event_image_delete', 'SpecialEventController@eventAssetDelete'); 
            
            Route::get('on_off_target', 'BusinessController@onOfTargetAudiance');
            });
        
        Route::post('add_card','PaymentController@addCardToStripe');
        Route::get('get_cards','PaymentController@getCards');
        Route::get('set_default_card','PaymentController@setDefaultCard');
        Route::post('purchase_spot','PaymentController@purchaseSlot');
        Route::get('single_event','BusinessEventController@getEvent');
     
        
        
     });
});
