<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'terms',
        'ip_address',
        'user_agent',
        'consented_at',
    ];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
