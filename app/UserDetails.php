<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{

    protected $table = 'users_details';

    protected $guarded = ['id'];

    public function notifications()
    {
        return $this->belongsToMany(EmailAndNotification::class, 'notification_user', 'user_id', 'notification_id')->withTimestamps();
    }

    public function user_teams()
    {
        return $this->belongsToMany(Team::class, 'team_users', 'user_id', 'team_id');
    }

    public function assign_users()
    {
        return $this->hasMany(AssignedUser::class, 'user_id', 'user_id');
    }

    /**
     * Get Tasks Assigned to user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_assigned_users', 'user_id', 'task_id')->withTimestamps();
    }

    public function filter(){
        return $this->hasOne(UserFilter::class,'user_id','user_id');
    }
}
