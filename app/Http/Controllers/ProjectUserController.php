<?php

namespace App\Http\Controllers;

use App\Project;
use App\ProjectUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;

class ProjectUserController extends Controller
{
    protected $db_name;
    public function __construct ()
    {
        $this->middleware(function ($request, $next) {
            $team_id =  Auth::user()->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    public function AllUser (Request $request)
    {
        $project_id = $request->project_id;
        $project = Project::on($this->db_name)->where('id', $project_id)->first();
        $user_ids = ProjectUser::on($this->db_name)->where(['project_id' => $project_id])->pluck('user_id');
        $users = User::whereIn('id', $user_ids)->orWhere('id', $project->created_by)->get();

        return response()->json(['users' => $users, 'owner' => $project->created_by, 'auth_id' => \Auth::id()]);
    }



}
