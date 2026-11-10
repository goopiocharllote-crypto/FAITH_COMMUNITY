<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'announcement_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'announcement_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
