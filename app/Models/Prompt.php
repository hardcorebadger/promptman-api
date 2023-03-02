<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'project_id',
        'payload',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected $attributes = [
        'payload' => '',
        'settings' => '{"model":"text-davinci-003","tempurature":0.5,"max_tokens":150,"frequency_penalty":0,"presence_penalty":0.6}',
    ];

    public function user() {
        return $this->belongsTo(User::class, "user_id");
    }

    public function project() {
        return $this->belongsTo(Project::class, "project_id");
    }
}
