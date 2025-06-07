<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSocket extends Model
{
    protected $fillable = [
        'user_id',
        'socket_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'socket_id' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
