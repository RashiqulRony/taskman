<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Comment;
use App\ActionLog;
use Carbon\Carbon;
use App\Task;
use App\User;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\File;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;
use function Faker\Provider\pt_BR\check_digit;


class CommentController extends Controller
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

    public function getCardComment(Request $request)
    {
        $comment = Task::on($this->db_name)->where('id', $request->task_id)
            ->with(['comment' => function ($q) {
                $q->orderBy('id', 'DESC');
            }])
            ->first();
        return response()->json(['success' => true, 'comment' => $comment]);
    }

    public function addComment(Request $request)
    {
        // return $request->mailUsers;
        $data = [
            'task_id' => $request->task_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'created_at' => Carbon::now()
        ];
        $all_Assign_users = Task::on($this->db_name)->where('id', $request->task_id)->with('Assign_user')->first();
        $mailData = [
            'subject' => "A comment added to a task",
            'body' => "A comment ( " . $request->comment . " ) is added to a task ( " . $all_Assign_users->title . " ).",
            'email' => "email_commentLeft",
            'generalBody' => "A comment ( " . $request->comment . " ) is added to a task ( " . $all_Assign_users->title . " ).",
            'task_id' => $request->task_id
        ];
        if (count($request->mailUsers) > 0) {
            if (in_array(Auth::id(), $request->mailUsers)) {
                $mailData['body'] .= ' And you are mentioned on that reply.';
            }
            $usernames = "";
            foreach ($request->mailUsers as $key => $value) {
                $user = User::find($value);
                if ($key < count($request->mailUsers) - 1) {
                    $usernames .= $user->name . ", ";
                } else {
                    $usernames .= $user->name;
                }
            }
            $mailData['generalBody'] .= 'And ( ' . $usernames . ' ) are mentioned on that comment';
        }

        $insert = Comment::on($this->db_name)->create($data);
        if ($insert) {
            $user_ids = [];
            $insert = Comment::on($this->db_name)->where('id', $insert->id)->with('user')->first();
            $this->HomeController->userMail((object)$mailData);
            foreach ($all_Assign_users->Assign_user as $item) {
                $user_ids[] = $item->user_id;
                if ($item->user_id != Auth::id()) {
                    Notification::on($this->db_name)->create([
                        'user_id' => $item->user_id,
                        'created_by' => Auth::id(),
                        'body' => 'Someone Comments on a task you are assigned!',
                        'action_text' => 'View',
                        'action_url' => '/project-dashboard/' . $all_Assign_users->project_id,
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'Data' => $insert, 'users' => $user_ids]);
    }

    public function fileUpload(Request $request)
    {
        if (isset($request->file)) {
            $task_id = $request->id;
            $photo = ($_FILES['file']['name'] !== null && $_FILES['file']['name'] !== 'blob') ? $_FILES['file']['name'] : md5(uniqid(rand(), true)) . str_shuffle('BLOBImage') . '.png';
            $path = public_path() . "/storage/" . $task_id . "/comment";
            if (!is_dir($path)) {
                if (!is_dir(public_path() . "/storage/")) {
                    mkdir(public_path() . "/storage/");
                }
                if (!is_dir(public_path() . "/storage/" . $task_id)) {
                    mkdir(public_path() . "/storage/" . $task_id);
                }
                mkdir($path);
            }
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $path . "/" . $photo)) {
                $data = [
                    'task_id' => $task_id,
                    'user_id' => Auth::id(),
                    'comment' => '',
                    'attatchment' => $photo,
                    'created_at' => Carbon::now()
                ];

                $insert = Comment::on($this->db_name)->create($data);
                if ($insert) {
                    $insert = Comment::on($this->db_name)->where('id', $insert->id)->with('user')->first();
                }
                return response()->json(['success' => true, 'Data' => $insert]);
            } else {
                return response()->json('failed', 500);
            }
        }
    }

    public function cardCommentDelete(Request $request)
    {
        $delete = Comment::on($this->db_name)->where('id', $request->id)->orwhere('parent_id', $request->id)->delete();
        if ($delete) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function saveCommentReply(Request $request)
    {
        // return $request->all();
        $comment = Comment::on($this->db_name)->find($request->parent_id);
        $task = Task::on($this->db_name)->find($request->parent_id);
        $data = [
            'task_id' => $request->task_id,
            'parent_id' => $request->parent_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'created_at' => Carbon::now()
        ];
        $mailData = [
            'subject' => "A reply added to a comment",
            'body' => "A reply ( " . $request->comment . " ) is added to a comment ( " . $comment->comment . " ) on ( " . $task->title . " ) task.",
            'email' => "email_commentLeft",
            'generalBody' => "A reply ( " . $request->comment . " ) is added to a comment ( " . $comment->comment . " ) on ( " . $task->title . " ) task.",
            'task_id' => $request->task_id
        ];
        if (count($request->mailUsers) > 0) {
            if (in_array(Auth::id(), $request->mailUsers)) {
                $mailData['body'] .= ' And you are mentioned on that reply.';
            }
            $usernames = "";
            foreach ($request->mailUsers as $key => $value) {
                $user = User::find($value);
                if ($key < count($request->mailUsers) - 1) {
                    $usernames .= $user->name . ", ";
                } else {
                    $usernames .= $user->name;
                }
            }
            $mailData['generalBody'] .= 'And ( ' . $usernames . ' ) are mentioned on that reply';
        }
        $insert = Comment::on($this->db_name)->create($data);
        if ($insert) {
            $this->HomeController->userMail((object)$mailData);
            $users = $this->addNotification($request->task_id, $mailData['generalBody']);
            $insert = Comment::on($this->db_name)->where('id', $insert->id)->with('user')->first();
        }
        return response()->json(['success' => true, 'Data' => $insert, 'users' => $users]);
    }

    public function getCommentCount(Request $request)
    {
        $project_ids = Project::on($this->db_name)->pluck('id');
        $comment = Comment::on($this->db_name)->orderBy('id', 'DESC')
            ->with(['user', 'task' => function ($q) use ($project_ids) {
                $q->whereIn('project_id', $project_ids);
            }])->whereHas('task', function ($q) use ($project_ids) {
                $q->whereIn('project_id', $project_ids);
            })->get();
        Comment::on($this->db_name)->orderBy('id', 'DESC')
            ->with(['user', 'task' => function ($q) use ($project_ids) {
                $q->whereIn('project_id', $project_ids);
            }])->whereHas('task', function ($q) use ($project_ids) {
                $q->whereIn('project_id', $project_ids);
            })->update(['is_seen' => 1]);


        return response()->json(['comments' => $comment]);
    }

    public function getUnseenCommentCount(Request $request)
    {
        $check_is_seen = Schema::connection($this->db_name)->hasColumn('comments','is_seen');
        if (!$check_is_seen){
            Schema::connection($this->db_name)->table('comments', function(Blueprint $table){
                $table->tinyInteger('is_seen')->after('attatchment')->default(0)->comment('0 = unseen, 1 = seen');
            });
        }
        $project_ids = Project::on($this->db_name)->pluck('id');
        $comment = Comment::on($this->db_name)->orderBy('id', 'DESC')->where('is_seen',0)
            ->with(['user', 'task' => function ($q) use ($project_ids) {
                $q->whereIn('project_id', $project_ids);
            }])->whereHas('task', function ($q) use ($project_ids) {
                $q->whereIn('project_id', $project_ids);
            })->count();
        return response()->json(['comments' => $comment]);
    }



    public function updateComment(Request $request)
    {
        $oldComment = Comment::on($this->db_name)->where('id', $request->id)->first();
        $data = Comment::on($this->db_name)->where('id', $request->id)->update(['comment' => $request->comment]);
        if ($data) {
            $logcheck = ActionLog::on($this->db_name)->where(['task_id' => $oldComment->task_id, 'log_type' => 'Comment update'])->first();
            if ($logcheck) {
                ActionLog::on($this->db_name)->where('id', $logcheck->id)
                    ->update([
                        'title' => $oldComment->comment,
                        'action_by' => Auth::id(),
                        'action_at' => Carbon::now()
                    ]);
            } else {
                $log = [
                    'task_id' => $oldComment->task_id,
                    // 'task_id' => Comment::id(),
                    'title' => $oldComment->comment,
                    'log_type' => 'Comment update',
                    'action_type' => 'Updated',
                    'action_by' => Auth::id(),
                    'action_at' => Carbon::now()
                ];
                $insert = ActionLog::on($this->db_name)->create($log);
            }
        }
        return response()->json(['success' => true, 'update' => $oldComment]);
    }

    public function addNotification($task_id, $notification_body, $action_url = null)
    {
        $all_Assign_users = Task::on($this->db_name)->where('id', $task_id)->with('Assign_user')->first();
        $user_ids = [];
        foreach ($all_Assign_users->Assign_user as $item) {
            if ($item->user_id != Auth::id()) {
                $user_ids[] = $item->user_id;
                Notification::on($this->db_name)->create([
                    'user_id' => $item->user_id,
                    'created_by' => Auth::id(),
                    'body' => $notification_body,
                    'action_text' => 'View',
                    'action_url' => ($action_url == null) ? '/project-dashboard/' . $all_Assign_users->project_id : $action_url,
                ]);
            }
        }
        return $user_ids;
    }
}
