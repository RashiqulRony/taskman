<?php

namespace App\Http\Controllers;

use App\ActionLog;
use App\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;

class ActionLogController extends Controller
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

    public function getSingleTaskLog ($task_id)
    {
        $log = ActionLog::on($this->db_name)->where('task_id', $task_id)->with('user')->orderBy('id', 'desc')->get();
        return response()->json($log);
    }

    public function AllLogs (Request $request, $project_id)
    {
        $per_page = isset($request->per_page) ? $request->per_page : 100;
        $all_logs = ActionLog::on($this->db_name)->select('action_logs.*')
            ->Where('action_logs.project_id', $project_id)
            ->orderBy('action_logs.id', 'desc')
            ->with('user')
            ->paginate(($per_page != null) ? $per_page : 100);

        foreach ($all_logs as $log) {
            if ($log->action_type == 'softdelete' && $log->action_at > Carbon::now()->subDays(1)) {
                $log->undo = 1;
            } else {
                $log->undo = 0;
            }
        }
        return \response()->json(['logs' => $all_logs, 'status' => 'success']);
    }

    public function UndoAction (Request $request)
    {
        isset($request->type) ? $undo_type = $request->type : $undo_type = null;
        isset($request->id) ? $log_id = $request->id : $log_id = null;
        if ($undo_type == 'delete' && $log_id != null) {
            $log = ActionLog::on($this->db_name)->find($log_id);

            if ($log->task_id != null) {
                $task_id = $log->task_id;
                if (Task::on($this->db_name)->where('id', $task_id)->update(['is_deleted' => 0, 'deleted_at' => Carbon::now()])) {
                    ActionLog::on($this->db_name)->where('id', $log_id)->delete();
                }
            }
            return response()->json(['status' => true]);
        }else{
            return response()->json(['status' => false]);
        }

    }

    public function store ($req = [])
    {
        if ($req) {
            ActionLog::on($this->db_name)->create($req);
        }
    }
}
