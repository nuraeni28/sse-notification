<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'tpnsubscription';
    protected $fillable = [
        'endpoint',
        'expires',
        'subscription',
        'useragent',
        'lastupdated',
    ];
}
