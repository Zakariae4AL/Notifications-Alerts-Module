<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'trip_started',
        'trip_delayed',
        'trip_completed',
        'passenger_boarded',
        'passenger_absent',
        'vehicle_maintenance',
        'document_expiration'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
