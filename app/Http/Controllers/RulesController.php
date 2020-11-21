<?php

namespace App\Http\Controllers;

use App\AssignedUser;
use App\Rules;
use App\Project;
use App\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;
use Sabberworm\CSS\Rule\Rule;

class RulesController extends Controller
{
    protected $HomeController;
    protected $db_name;


    public function __construct()
    {
        $this->HomeController = new HomeController;
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $team_id = $this->user->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name =$db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    public function store(Request $request)
    {
        $rule_check_by_name = Rules::on($this->db_name)->where(['name' => $request->name])->count();
        if ($rule_check_by_name > 0){
            return response()->json(['status' => 'exist']);
        }

        $check_move_from = Rules::on($this->db_name)->where('move_from',$request->move_from)->OrWhere('move_to',$request->move_from)->count();
        $check_move_to = Rules::on($this->db_name)->where('move_from',$request->move_to)->count();
        if ($check_move_from > 0 || $check_move_to > 0){
            return response()->json(['status' => 'exist']);
        }

        $data = [
            'name' => $request->name,
            'status' => $request->status,
            'project_id' => (int)$request->project_id,
            'move_from' => $request->move_from,
            'move_to' => $request->move_to,
            'created_by' => Auth::id(),
            'assigned_users' => $request->assign_to
        ];
        // $project = Project::select('id')->where('team_id',Auth::user()->current_team_id)->first();
        $mailData = [
                'subject' => "Rule Added",
                'body'    => "Rule ( ". $request->name." ) is created",
                'email'   => "email_taskUpdated",
                'generalBody' => "Rule ( ". $request->name ." ) is created",
                'user_id' => json_decode($request->assign_to) ,
                'project_id' => (int)$request->project_id
        ];
        if (Rules::on($this->db_name)->create($data)) {
            if ($request->status == 1){
                $this->MoveAllCardAndAssign($request->move_from,$request->move_to,json_decode($request->assign_to));
            }
            $this->HomeController->userMail( (object) $mailData);
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed']);
        }

    }

    public function show($id)
    {
        $rule = Rules::on($this->db_name)->findOrFail($id);
        return response()->json(['rule' => $rule]);
    }

    public function update(Request $request)
    {
        $check_move_from = Rules::on($this->db_name)->where('move_from',$request->move_from)->OrWhere('move_to',$request->move_from)->count();
        $check_move_to = Rules::on($this->db_name)->where('move_from',$request->move_to)->count();
        $rule = Rules::on($this->db_name)->find($request->id);
        if ($check_move_from > 1 || $check_move_to > 1){
            return response()->json(['status' => 'exist']);
        }
        $data = [
            'name' => $request->name,
            'status' => $request->status,
            'move_from' => $request->move_from,
            'move_to' => $request->move_to,
            'created_by' => Auth::id(),
            'assigned_users' => $request->assign_to,
            'updated_at' => now(),
        ];
        $mailData = [
                'subject' => "Rule is updated",
                'body'    => "Rule ( ". $request->name." ) is updated",
                'email'   => "email_taskUpdated",
                'generalBody' => "Rule ( ". $request->name ." ) is updated",
                'user_id' => json_decode($request->assign_to) ,
                'project_id' => $rule->project_id
        ];
        $update = Rules::on($this->db_name)->where('id', $request->id)->update($data);
        if ($request->status == 1){
            $this->MoveAllCardAndAssign($request->move_from,$request->move_to,json_decode($request->assign_to));
        }

        $this->HomeController->userMail( (object) $mailData);
        return response()->json(['status' => 'success', 'data' => $update]);
    }


    public function delete(Request $request)
    {
        $rule = Rules::on($this->db_name)->find($request->id);
        $mailData = [
            'subject' => "Rule is deleted",
            'body'    => "Rule ( ". $rule->name." ) is deleted",
            'email'   => "email_taskUpdated",
            'generalBody' => "Rule ( ". $rule->name ." ) is deleted",
            'user_id' => json_decode($rule->assign_to) ,
            'project_id' => $rule->project_id
    ];
        if (Rules::on($this->db_name)->find($request->id)->delete()) {
            $this->HomeController->userMail( (object) $mailData);
            return response()->json(['status' => 'success']);
        }
    }

    public function MoveAllCardAndAssign($move_from, $move_to,$users)
    {
        if ($move_to !== 0){
            $move_to_column = Task::on($this->db_name)->where('id', $move_to)->first();
            $check = Task::on($this->db_name)->where('board_parent_id', $move_from)
                ->Update([
                        'progress'=>$move_to_column->progress,
                        'board_parent_id' => $move_to,
                        'multiple_board_id' => $move_to_column->multiple_board_id
                    ]
                );
            $all_task_for_rule_update = Task::on($this->db_name)->where('board_parent_id', $move_to)->get();
        }else{
            $all_task_for_rule_update = Task::on($this->db_name)->where('board_parent_id', $move_from)->get();
        }

        if ($all_task_for_rule_update && !empty($users)){
            foreach ($all_task_for_rule_update as $item) {
                AssignedUser::on($this->db_name)->where('task_id',$item->id)->delete();
                foreach ($users as $user) {
                    AssignedUser::on($this->db_name)->create([
                        'task_id'    => $item->id,
                        'user_id'    => $user,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }
        }

        if ($all_task_for_rule_update) {
            return true;
        } else {
            return false;
        }
    }
}
