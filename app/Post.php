<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function user()
    {
        return $this->belongsTo("App\User");
    }
    public function tags()
    {
        return $this->belongsToMany("App\Tag")->withPivot("confirmed");
    }
    public function comments()
    {
        return $this->hasMany("App\Comment");
    }

    public function images(){
        return $this->hasMany("App\Image");
    }
}
