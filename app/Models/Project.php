<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'openai_api_key'
    ];

    public function user() {
        return $this->belongsTo(User::class, "user_id");
    }
}
