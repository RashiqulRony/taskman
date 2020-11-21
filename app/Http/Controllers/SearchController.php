<?php

namespace App\Http\Controllers;

use App\Tags;
use App\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Spark\Configuration\DBConnection;

class SearchController extends Controller
{
    protected $db_name;
    protected $dont_forget_tag;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $team_id = Auth::user()->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    public function suggestUser(Request $request)
    {
        if (isset($request->user_name) && trim($request->user_name) != '') {
            $team_id = Auth::user()->current_team_id;
            $search_user = User::join('team_users', 'users.id', 'team_users.user_id')->where('team_users.team_id', $team_id)
                ->Where('name', 'like', '%' . $request->user_name . '%')
                ->orWhere('email', 'like', '%' . $request->user_name . '%')
                ->where('team_users.team_id', $team_id)
                ->orderBy('users.name', 'asc')
                ->get();
            return response()->json(['search_user' => $search_user]);
        } else if (isset($request->text) && trim($request->text) != '') {
            $list_id = $request->list_id;
            $type = $request->type;
            if ($type == 'list') {
                $task = Task::on($this->db_name)->where('project_id', (int)$request->project_id)
                    ->where('title', 'like', '%' . $request->text . '%')
                    ->where('list_id', $list_id)
                    ->where('is_deleted', 0)
                    ->orWhere('id', $request->text)->get();
            } elseif ($type == 'board') {
                $task = Task::on($this->db_name)->where('project_id', (int)$request->project_id)
                    ->where('title', 'like', '%' . $request->text . '%')
                    ->where('board_parent_id','!=',0)
                    ->where('multiple_board_id',$list_id)
                    ->where('is_deleted', 0)
                    ->orWhere('id', $request->text)->get();
            } else {
                $task = Task::on($this->db_name)->where('project_id', (int)$request->project_id)
                    ->where('title', 'like', '%' . $request->text . '%')
                    ->where('board_parent_id','!=',0)
                    ->where('is_deleted', 0)
                    ->orWhere('id', $request->text)->get();
            }

            return response()->json(['search_tasks' => $task]);
        } else if (isset($request->user_id) && trim($request->user_id) != '') {
            $list_id = $request->list_id;
            $type = $request->type;
            if ($type == 'list') {
                $task = Task::on($this->db_name)->join('task_assigned_users', 'task_lists.id', 'task_assigned_users.task_id')
                    ->select('task_lists.*')
                    ->where('project_id', (int)$request->p_id)
                    ->where('list_id', $list_id)
                    ->where('task_lists.is_deleted', 0)
                    ->where('task_assigned_users.user_id', $request->user_id)
                    ->get();
            } elseif ($type == 'board') {
                $task = Task::on($this->db_name)->join('task_assigned_users', 'task_lists.id', 'task_assigned_users.task_id')
                    ->select('task_lists.*')
                    ->where('project_id', (int)$request->p_id)
                    ->where('multiple_board_id',$list_id)
                    ->where('task_assigned_users.user_id', $request->user_id)
                    ->where('task_lists.is_deleted', 0)
                    ->get();
            } else {
                $task = Task::on($this->db_name)->join('task_assigned_users', 'task_lists.id', 'task_assigned_users.task_id')
                    ->select('task_lists.*')
                    ->where('project_id', (int)$request->p_id)
                    ->where('task_assigned_users.user_id', $request->user_id)
                    ->where('task_lists.is_deleted', 0)
                    ->get();
            }

            return response()->json(['search_tasks' => $task]);
        }
    }

    public function getAllUser()
    {
        $team_id = Auth::user()->current_team_id;
        $search_user = User::join('team_users', 'team_users.user_id', 'users.id')
            ->where('team_users.team_id', $team_id)
            ->orderBy('users.name', 'asc')
            ->get();
        return response()->json(['search_user' => $search_user]);
    }

    public function tagSearch(Request $request){
        $team_id = Auth::user()->current_team_id;
        $allTags = Tags::on($this->db_name)->where('team_id', $team_id)
            ->where('title', '!=', $this->dont_forget_tag)
            ->where('title', 'LIKE', '%' . $request->title . '%')
            ->orderBy('title', 'asc')
            ->get()->toArray();
        if ($allTags){
            return response()->json(['success' => true, 'allTags' => $allTags ]);
        }else{
            return response()->json(['success' => false, 'allTags' => [] ]);
        }
    }
}