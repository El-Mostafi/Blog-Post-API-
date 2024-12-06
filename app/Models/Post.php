<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'view_count',
        'user_id'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }
    public function postLikes(){
        return $this->hasMany(PostLikes::class);
        
    }
    public function media()
    {
        return $this->hasMany(Media::class);
    }
}
