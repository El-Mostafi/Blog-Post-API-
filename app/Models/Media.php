<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'file_path',
        'file_type',
        'post_id'
    ];
    public function post(){
        return $this->belongsTo(Post::class);
    }
}
