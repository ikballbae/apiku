<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'date' => 'datetime',
    ];   

    protected $fillable = [
        'form_id',
        'user_id',
        'answers',
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    // Alternative: hasMany relationship with Answer model
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
