<?php 
return [
    'role' =>   [
        'ADMIN' => 'admin',
        'USER' => 'user',
        'BUSINESS'=>'business',
        'ADVERTISER'=>'advertiser'
    ],
    'account_status'=>[
        'IN_ACTIVE'=>0,
        'ACTIVE'=>1,
        'BLOCK'=>2
    ],
    'registration_step'=>[
        'FIRST'  =>  1,
        'SECOND' =>  2,
        'THIRD'  =>   3
    ],
    'LOG_TYPE'=>[
        'INFO'=>1,
        'ERROR'=>2
    ],
    'event_recurring_type'=>[
     'WEEKLY'=>1,
     'MONTHLY'=>2
    ],
    'business_event_type'=>[
        'ONGOING'=>1,
        'UPCOMMING'=>2,
        'PAST'=>3
    ],
    'datenight_action'=>[
        'PENDING'=>'0',
        'DECLINED'=>'2',
        'CONFIRMED'=>'1',
    ],
    'DATE_NIGHT_CUSTOM_COST'=>'1',
    'social_type'=>[
        'GOOGLE'=>1,
        'INSTAGRAM'=>2,
        'APPLE_LOGIN'=>3,
    ],
    'event_delete_type'=>[
        'PERMANENT_DELETE'=>0,
        'DELETE_FOR_NOW'  =>1
    ]
    ];
  