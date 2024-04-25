<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $table = 'forms';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'limit_one_response',
        'creator_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function allowedDomains()
    {
        return $this->hasMany(AllowedDomain::class);
    }
    
}

