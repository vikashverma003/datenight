<?php

/*
 * This is the status codes file where all the app constants are saved.
 */

return [
    'request_status' => [
        'SUCCESS' => 'SUCCESS',
        'ERROR' => 'FAILURE'
    ],
    // ::::::::::: SUCCESS CODES & MESSAGES :::::::::::: //
    'success_codes' => [
        'USER_LOGIN_SUCCESS' => '200',
        'USER_REGISTER_SUCCESS' => '200',
        'USER_IMAGE_UPLOAD_SUCCESS'=>'200',
        'USER_IMAGE_SKIP_UPLOAD_SUCCESS'=>'200',
        'BUSINESS_IMAGE_UPLOAD_SUCCESS'=>'200',
        'BUSINESS_PROFILE_UPDATE_SUCCESS'=>'200',
        'INFORMATION_FETCH_SUCCESS'=>'200',
        'USER_LOGOUT_SUCCESS'=>'200',
        'DATE_NIGHT_CREATE_SUCCESS'=>'200',
        'PASSWORD_UPDATE_SUCCESS'=>'200',
        'PROFILE_EDIT_SUCCESS'=>'200',
        'DATE_NIGHT_INVITATION'=>'200',
        'SPOT_PURCHASE_SUCCESS'=>'200',
        'EVENT_UPDATE_SUCCESS'=>'200',
        'EVENT_DELETE_SUCCESS'=>'200',
        'BUSINESS_PROFILE_UPLOAD_SUCCESS'=>'200',
        'BUSINESS_PROFILE_DELETE_SUCCESS'=>'200',
        'UPDATE_SUCESS'=>'200',
        'TARGET_AUDIANCE_BUTTON_STATUS_CHANGE'=>'200'
        ],
    'success_messages' => [
        'USER_LOGIN_SUCCESS' => 'User login successfully',
        'UPDATE_SUCESS'=>'Notifications has been updated successfully.',
        'USER_REGISTER_SUCCESS' => 'User register successfully',
        'USER_IMAGE_UPLOAD_SUCCESS'=>'User profile image uploaded successfully',
        'USER_IMAGE_SKIP_UPLOAD_SUCCESS'=>'User image upload skipped successfully',
        'BUSINESS_IMAGE_UPLOAD_SUCCESS' => "Business image uploaded successfully",
        'BUSINESS_PROFILE_UPDATE_SUCCESS' => 'Business profile information updated sucessfully',
        'INFORMATION_FETCH_SUCCESS'     => 'Information retrieved successfully',
        'USER_LOGOUT_SUCCESS'       =>  'User logout successfully',
        'DATE_NIGHT_CREATE_SUCCESS'  => 'Date night event created successfully',
        'PASSWORD_UPDATE_SUCCESS'=>'New password updated successfully',
        'PROFILE_EDIT_SUCCESS'=> 'Profile information updated successfully',
        'DATE_NIGHT_INVITATION'=>'Date night invitation performed successfully',
        'SPOT_PURCHASE_SUCCESS'=>'Spot purchased successfully',
        'EVENT_UPDATE_SUCCESS'=>'Business Event update successfully',
        'EVENT_DELETE_SUCCESS'=>'Business Event deleted successfully',
        'BUSINESS_PROFILE_UPLOAD_SUCCESS'=>'Business Profiles image uploaded succesfully',
        'BUSINESS_PROFILE_DELETE_SUCCESS'=>'Business profile image deleted successfully',
        'TARGET_AUDIANCE_BUTTON_STATUS_CHANGE'=>'Target Audiance status update successfully'
        
    ],
    // ---------------------- :::::::::::::::::::: ------------------//
    // ---------------------- END - SUCCESS CODES & MESSAGES --------//
    // ---------------------- :::::::::::::::::::: ------------------//
    // ::::::::::: ERROR CODES & MESSAGES :::::::::::: //
    'error_codes' => [
        'USER_NOT_EXIST' => '404',
        'BAD_REQUEST' => '400',
        'DB_ERROR' => '500',
        'USER_PASSWORD_WRONG'=>'404',
        'ACCESS_ERROR'  => '403',
        'ACCOUNT_NOT_VERIFY'=>'403',
        'OLD_PASSWORD_NOT_MATCH_ERROR'=>'400',
        'DATE_NIGHT_CONTACT_NOT_EXIST'=>'404',
        'USER_CARD_NOT_EXIST'=>'404',
        'STRIPE_ERROR'=>'404',
        'EMAIL_NOT_EXIST'=>'404',
        'SLOT_ALREADY_PURCHASED'=>'404',
        'SOCIAL_USER_NOT_EXIST'=>'404',
        'ADERVERTISER_SLOT_ALREADY_PURCHASE'=>'404',
        'TARGET_AUDIANCE_ALREADY_PURCHASE'=>'404',
        'TARGET_AUDIANCE_SUBSCRIPTION_NOT_PURCHASED'=>'404'
    ],
    'error_messages' => [
        'USER_NOT_EXIST' => 'User does not exist.',
        'BAD_REQUEST' => 'Bad request or data validation failed.',
        'DB_ERROR' => 'Internal Server Error. Please try again.',
        'USER_PASSWORD_WRONG'=>'You have entered wrong password.',
        'ACCESS_ERROR'  => 'You don\'t have permission for this api',
        'ACCOUNT_NOT_VERIFY'=> 'You account is not verify by admin',
        'OLD_PASSWORD_NOT_MATCH_ERROR'=>'Old password did not match',
        'DATE_NIGHT_CONTACT_NOT_EXIST'=>'Date Night Contact does not exist',
        'USER_CARD_NOT_EXIST'=>'Card not exist for this user',
        'STRIPE_ERROR'=>'Stripe error',
        'SLOT_ALREADY_PURCHASED'=>'Slot Already purchased',
        'EMAIL_NOT_EXIST'=>'Email does not exist',
        'SOCIAL_USER_NOT_EXIST'=>'User not register using social',
        'ADERVERTISER_SLOT_ALREADY_PURCHASE'=>'Adervertiser slot already purchased by you',
        'TARGET_AUDIANCE_ALREADY_PURCHASE'=>'Target audiance subscription already purchased by you',
        'TARGET_AUDIANCE_SUBSCRIPTION_NOT_PURCHASED'=>'Target audiance subscription not purchased by you'
    ],
];
