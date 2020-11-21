<?php

namespace App\Http\Controllers;

use App\ActionLog;
use App\Task;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PriorityController extends Controller
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

    public function AddPriority (Request $request)
    {

        $ids = $request->ids;
        $priority = $request->priority;
        foreach ($ids as $id) {
                $pr = '';
                Task::on($this->db_name)->where('id', $id)->update(['priority_label' => $priority]);
                if ( $priority == '1') {
                    $pr = 'Low';
                }
                if ( $priority == '2') {
                    $pr = 'Medium';
                }
                if ( $priority == '3') {
                    $pr = 'High';
                }
                $task = Task::on($this->db_name)->find($id);
                $mailData = [
                    'subject'       => "Priority added to a task",
                    'body'          => "Priority ( ".$pr." ) added to a task (".$task->title.").",
                    'email'         => "email_whenAddedToTask",
                    'generalBody'   => "Priority ( ".$pr." ) added to a task (".$task->title.") ",
                    'task_id'       => $id
                ];
                if ($priority == null) {
                    $mailData['subject'] = "Priority removed from a task";
                    $mailData['body'] = "Priority removed from a task (".$task->title.").";
                    $mailData['generalBody'] = "Priority removed from a task (".$task->title.").";
                }
                $users = $this->addNotification($id, $mailData['body']);
                $log_data = [
                    'project_id'=>$task->project_id,
                    'task_id' => $id,
                    'title' => $task->title,
                    'log_type' => ($priority !== null) ? 'Add '.$priority.' Priority' : 'Remove Priority',
                    'action_type' => 'updated',
                    'action_by' => Auth::id(),
                    'action_at' => Carbon::now()
                ];
                ActionLog::on($this->db_name)->create($log_data);
        }
        return response()->json(['status' => 'success', 'users' => $users]);
    }

    public function addNotification ($task_id,$notification_body,$action_url = null)
    {
        $all_Assign_users = Task::on($this->db_name)->where('id', $task_id)->with('Assign_user')->first();
        $user_ids = [];
        if ($all_Assign_users->Assign_user->count() > 0){
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
        }

        return $user_ids;
    }
}
