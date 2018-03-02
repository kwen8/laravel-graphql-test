<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'name', 'description',
    ];

    public function user()
    {
        return $this->belongsTo("App\Models\Job");
    }
}
