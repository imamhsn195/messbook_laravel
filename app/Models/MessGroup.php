<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessGroup extends Model
{
    use HasFactory;
    protected $fillable = ['name','fixed_cost', 'start_date', 'end_date', 'total_days'];
    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_mess_group', 'mess_group_id', 'member_id')
            ->withPivot('shopping', 'deposits', 'balance')
            ->withTimestamps();
    }

    public function expenses() {
        return $this->hasMany(Expense::class);
    }
}
