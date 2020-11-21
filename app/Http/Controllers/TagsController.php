<?php

namespace App\Http\Controllers;

use App\AssignTag;
use App\Tags;
use App\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;

class TagsController extends Controller
{
    public $TaskController;
    public $dont_Forget_tag = 'Dont Forget';
    public $dont_Forget_Section = 'Dont Forget Section';
    public $HomeController;
    protected $db_name;

    public function __construct()
    {
        $this->TaskController = new TaskController();
        $this->HomeController =  new HomeController;
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
        $tags = Tags::on($this->db_name)->where('team_id', Auth::user()->current_team_id)
            ->groupBy('title')->orderBy('id','DESC')->get();
        return response()->json(['tags' => $tags]);
    }

    public function store(Request $request)
    {
        $team_id = Auth::user()->current_team_id;
        $tags = Tags::on($this->db_name)->where(['title' => $request->tags,'team_id'=>$team_id])->first();
        $task = Task::on($this->db_name)->find($request->id);
        if ($tags) {
            $tag_id = $tags->id;
        } else {
            $tag_data = [
                'team_id' => $team_id,
                'color' => $request->color,
                'title' => $request->tags,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $tags = Tags::on($this->db_name)->create($tag_data);
            $tag_id = $tags->id;
        }
        $check_assign = AssignTag::on($this->db_name)->where(['task_id' => $request->id, 'tag_id' => $tag_id])->count();
        if ($check_assign <= 0) {
            $check_assign = AssignTag::on($this->db_name)->create(['task_id' => $request->id, 'tag_id' => $tag_id]);
            $tags->assign_id = $check_assign->id;
            $tags->board_id = $check_assign->task_id;
        }
        $mailData = [
            'subject'       => "Tag added to a task",
            'body'          => "A tag (" .$tags->title. ") is assigned to ( ".$task->title." ) task.",
            'email'         => "email_taskUpdated",
            'generalBody'   => "A tag (" .$tags->title. ") is assigned to ( ".$task->title." ) task.",
            'task_id'       => $request->id
        ];
        if ($request->tags == $this->dont_Forget_tag) {
            $this->dontForgetTagProcess($request->id,$tag_id);
        }else {
            $this->HomeController->userMail( (object) $mailData);
            $users = $this->addNotification($request->id, $mailData['body']);
            return response()->json(['success' => true, 'data' => $tags, 'users' => $users]);
        }

    }

    public function addTagToMultipleTask(Request $request)
    {
        $team_id = Auth::user()->current_team_id;
        $tags = Tags::on($this->db_name)->where(['title' => $request->tags,'team_id'=>$team_id])->first();
        $ids = $request->ids;
        if ($tags) {
            $tag_id = $tags->id;
        } else {
            $tags = Tags::on($this->db_name)->create([
                'team_id' => $team_id,
                'color' => $request->color,
                'title' => $request->tags,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $tag_id = $tags->id;
        }

        foreach ($ids as $id) {
            $this->dontForgetTagProcess($id,$tag_id);
        }

    }

    public function dontForgetTagProcess($id,$tag_id)
    {
        $check_assign = AssignTag::on($this->db_name)->where(['task_id' => $id, 'tag_id' => $tag_id])->count();
        if ($check_assign <= 0) {
            AssignTag::on($this->db_name)->create(['task_id' => $id, 'tag_id' =>$tag_id]);
        }

        $task = Task::on($this->db_name)->where('id', $id)->first();
        $taskDontForget = Task::on($this->db_name)->where([
            'title' => $this->dont_Forget_Section,
            'project_id' => $task->project_id,
            'list_id' => $task->list_id,
        ])->get();

        if ($taskDontForget->count() <= 0) {
            $data = [
                'sort_id' => -2,
                'parent_id' => 0,
                'project_id' => $task->project_id,
                'list_id' => $task->list_id,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'title' => $this->dont_Forget_Section,
                'date' => $task->date,
                'created_at' => Carbon::now(),
            ];

            $NewTask = Task::on($this->db_name)->create($data);
            $assign = AssignTag::on($this->db_name)->create(['task_id' => $NewTask->id, 'tag_id' => $tag_id]);

            $taskUpdate = Task::on($this->db_name)->where('id', $id)->update(['parent_id' => $NewTask->id]);
            //update task child tag
            $this->TaskController->updateTagWithDataMove($id, $NewTask->id);

            return response()->json(['success' => $taskUpdate]);
        } elseif ($id != $taskDontForget[0]->id) {

            if($taskDontForget[0]->is_deleted == 1 ){
                Task::on($this->db_name)->where([
                    'title' => $this->dont_Forget_Section,
                    'project_id' => $task->project_id,
                    'list_id' => $task->list_id,
                ])->update(['is_deleted'=>0]);
            }
            $parent_assign_dont_Forget_tag = AssignTag::on($this->db_name)->where(['task_id' => $task->parent_id , 'tag_id' => $tag_id])->count();

            if ($parent_assign_dont_Forget_tag <= 0) {
                $sort = Task::on($this->db_name)->where(['parent_id' => $taskDontForget[0]->id])->max('sort_id');
                $taskUpdate = Task::on($this->db_name)->where('id', $id)->update([
                    'parent_id' => $taskDontForget[0]->id,
                    'sort_id' => $sort + 1
                ]);
                //update task child tag
                $this->TaskController->updateTagWithDataMove($id, $taskDontForget[0]->id);
                return response()->json(['success' => $taskUpdate]);
            }
            return response()->json(['success']);
        }

    }

    public function update(Request $request)
    {
        if (isset($request->tag)) {
            Tags::on($this->db_name)->where('id', $request->id)->update(['title' => $request->tag]);
            $tags = $this->getAllTagByTeamId();
            return response()->json(['success' => 1, 'tags' => $tags]);
        } elseif (isset($request->color)) {
            Tags::on($this->db_name)->where('id', $request->id)->update(['color' => $request->color]);
            $tags = $this->getAllTagByTeamId();
            return response()->json(['success' => 1, 'tags' => $tags]);
        }
    }

    public function destroy(Request $request)
    {
        if (isset($request->assign_id)) {
            AssignTag::on($this->db_name)->where('id', $request->assign_id)->delete();
            return response()->json(['success' => 1, 'tags' => []]);
        } elseif (isset($request->id)) {
            Tags::on($this->db_name)->where('id', $request->id)->delete();
            AssignTag::on($this->db_name)->where('tag_id',$request->id)->delete();
            $tags = $this->getAllTagByTeamId();
            return response()->json(['success' => 1, 'tags' => $tags]);
        }
    }

    public function getAllTagByTeamId()
    {
        $tags = Tags::on($this->db_name)->where('team_id', Auth::user()->current_team_id)->groupBy('title')->orderBy('id','DESC')->get();
        return $tags;
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
