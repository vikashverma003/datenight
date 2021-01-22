<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     
    protected $fillable = [
        'title', 'type','message','action_id','profile_image'
    ];


}
