<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'form_id',
        'name',
        'choice_type',
        'choices',
        'is_required',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
