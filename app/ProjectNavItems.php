<?php

namespace App;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Laravel\Spark\Configuration\DBConnection;

class ProjectNavItems extends Model
{
    protected $table = 'project_nav_items';

    protected $fillable = [
        'project_id',
        'title',
        'type',
        'sort_id',
        'created_at',
        'updated_at'
    ];

    public function All_list(){
        return $this->hasMany('App\Multiple_list', 'nav_id', 'id')->with('tasks_list');
    }

    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'project_id');//->where('team_id', Auth::user()->current_team_id);
    }

}
