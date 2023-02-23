<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'type',
        'content_id'
    ];

    public function project() {
        return $this->belongsTo(Project::class, "project_id");
    }

    public function parent() {
        return $this->belongsTo(FileNode::class, "parent_id");
    }
}
