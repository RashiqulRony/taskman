<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFilter extends Model
{
    protected $fillable = [
        'user_id',
        'filter_type'
    ];
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
