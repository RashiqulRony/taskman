<?php

namespace App\Http\Controllers;

use App\AssignedUser;
use App\User;
use App\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;
use Illuminate\Support\Facades\Auth;

class AssignedUserController extends Controller
{
    protected $HomeController;
    protected $db_name;

    public function __construct()
    {
        $this->HomeController = new HomeController;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $team_id = Auth::user()->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $task = Task::on($this->db_name)->find($request->task_id);
        $user = User::find($request->user_id);

        $checkIsUserAssigned = AssignedUser::on($this->db_name)->where([
            'task_id' => $request->task_id,
            'user_id' => $request->user_id
        ])->count();
        if ($checkIsUserAssigned <= 0) {
            $assignedUser = AssignedUser::on($this->db_name)->create([
                'task_id'    => $request->task_id,
                'user_id'    => $request->user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $mailData = [
                'subject'       => "Added to a task",
                'body'          => "You are assigned on a task (".$task->title.").",
                'email'         => "email_whenAddedToTask",
                'generalBody'   => " (".$user->name.") is assigned on a task (".$task->title.") ",
                'task_id'       => $request->task_id
            ];
            $this->HomeController->userMail( (object) $mailData);
            $users = $this->addNotification($request->task_id, $mailData['generalBody']);

            $data = $this->AssignUser($request->task_id); // AssignedUser::on($this->db_name)->join('users', 'task_assigned_users.user_id','users.id')->where('task_id',  $request->task_id)->get()->toArray();


            return response()->json(['success' => 'success', 'data' => $data, 'users' => $users]);
        } else {
            return response()->json('already added');
        }

    }

    public function AssignUser($task_id){
        $assign_users_ids = AssignedUser::on($this->db_name)->where('task_id', $task_id)->pluck('user_id');
        $user = User::whereIn('id',$assign_users_ids)->get()->toArray();
        return $user;
    }

    public function delete(Request $request)
    {
        $task = Task::on($this->db_name)->find($request->task_id);
        $user = User::find($request->user_id);
        $mailData = [
            'subject'       => "Removed from a card",
            'body'          => "You are removed from a card (".$task->title.") that you were assigned on.",
            'email'         => "email_whenRemovedFromTask",
            'generalBody'   => " (".$user->name.") is removed from a card (".$task->title.") ",
            'task_id'       => $request->task_id
        ];

        AssignedUser::on($this->db_name)->where($request->all())->delete();
        $this->HomeController->userMail( (object) $mailData);
        $users = $this->addNotification($request->task_id, $mailData['generalBody']);

        return response()->json('success');
    }


    public function edit(AssignedUser $assignedUser)
    {
        //
    }

    public function update(Request $request, AssignedUser $assignedUser)
    {
        //
    }

    public function destroy(AssignedUser $assignedUser)
    {
        //
    }

    public function addNotification ($task_id,$notification_body,$action_url = null)
    {
        $all_Assign_users = Task::on($this->db_name)->where('id', $task_id)->with('Assign_user')->first();
        $user_ids = [];
        foreach ($all_Assign_users->Assign_user as $item) {
            if ($item->user_id != Auth::id()){
                $user_ids[] = $item->user_id;
                Notification::on($this->db_name)->create([
                    'user_id' => $item->user_id,
                    'created_by' => Auth::id(),
                    'body' => $notification_body,
                    'action_text' => 'View',
                    'action_url' => ($action_url == null ) ? '/project-dashboard/'.$all_Assign_users->project_id : $action_url,
                ]);
            }
        }
        return $user_ids;
    }
}
