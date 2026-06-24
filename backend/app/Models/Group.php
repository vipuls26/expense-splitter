<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name','description','created_by'])]
class Group extends Model
{
    public function creator()
    {
        return $this->belongsToMany(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(
            User::class,'group_members'
        )->withPivot('role')->withTimestamps();
    }
}
