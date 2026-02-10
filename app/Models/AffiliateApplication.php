<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_data',
        'status',
        'admin_feedback',
        'setup_token',
        'setup_token_expires_at',
        'credentials_set',
    ];

    protected $casts = [
        'application_data' => 'array', // Decodes JSON to array automatically
        'setup_token_expires_at' => 'datetime',
    ];

    /**
     * The user who submitted the application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}