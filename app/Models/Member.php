<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function messGroups()
    {
        return $this->belongsToMany(MessGroup::class, 'member_mess_group', 'member_id', 'mess_group_id')
            ->withPivot('shopping', 'deposits', 'balance')
            ->withTimestamps();
    }
}
