<?php

namespace App\Http\Controllers;

use App\AssignedUser;
use App\AssignTag;
use App\Comment;
use App\Files;
use App\Mail\AddMemberMail;
use App\Multiple_list;
use App\Project;
use App\ProjectNavItems;
use App\ProjectUser;
use App\Task;
use App\TeamUser;
use App\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;
use Mail;


class OverviewController extends Controller
{
    protected $TaskController;
    protected $ProjectNavItems;
    protected $Files;
    protected $db_name;

    public function __construct()
    {
        $this->middleware('auth');
        $this->TaskController = new TaskController();
        $this->ProjectNavItems = new ProjectNavItems();
        $this->Files = new Files();

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $team_id = $this->user->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            $this->ProjectNavItems->setConnection($db_name);
            return $next($request);
        });
    }

    public function index($project_id)
    {
        $all_lists = Multiple_list::on($this->db_name)
            ->where(['project_id' => $project_id])
            ->orderBy('id', 'desc')
            ->orderBy('sort_id', 'asc')
            ->limit(5)
            ->get();
        foreach ($all_lists as $key => $item) {
            $item->tasks = $this->TaskController->decorateData($item->tasks_list, 'drag');
        }
        return response()->json(['lists' => $all_lists]);
    }

    public function GetAllList($project_id)
    {
        $all_lists = Multiple_list::on($this->db_name)
            ->where(['project_id' => $project_id])
            ->orderBy('id', 'desc')
            ->orderBy('sort_id', 'asc')
            ->get();
        foreach ($all_lists as $key => $item) {
            $item->tasks = $this->TaskController->decorateData($item->tasks_list, 'drag');
        }
        return response()->json(['lists' => $all_lists]);
    }

    public function ListSort(Request $request)
    {
        if (!isset($request->data)) {
            return \response()->json(['status' => 'failed']);
        }
        $data = $request->data;
        foreach ($data as $key => $item) {
            Multiple_list::on($this->db_name)->where('id', (int)$item)->update(['sort_id' => $key]);
        }
        return response()->json(['status' => 'success']);

    }

    public function ListToggle(Request $request)
    {
        if (!isset($request->list_id)) {
            return \response()->json(['status' => 'failed']);
        }
        $list_check = Multiple_list::on($this->db_name)->where('id', $request->list_id)->first();
        $open = ($list_check->open) ? 0 : 1;
        $update_toggle = Multiple_list::on($this->db_name)->where('id', $request->list_id)->update(['open' => $open]);
        if ($update_toggle) {
            return \response()->json(['status' => 'success']);
        } else {
            return \response()->json(['status' => 'failed']);
        }
    }

    public function AllFiles($project_id)
    {
        $all_files = $this->Files->on($this->db_name)->select('task_files.*', 'task_lists.title', 'task_lists.list_id')
            ->join('task_lists', 'task_files.tasks_id', 'task_lists.id')
            ->where('task_lists.project_id', $project_id)
            ->orderBy('task_files.created_at', 'desc')
            ->with('user')
            ->paginate(18);

        return \response()->json(['files' => $all_files, 'status' => 'success']);
    }

    public function AllComments($project_id)
    {
        $all_comments = Comment::on($this->db_name)
            ->select('comments.*', 'task_lists.title', 'task_lists.list_id', 'task_lists.project_id')
            ->join('task_lists', 'comments.task_id', 'task_lists.id')->orderBy('comments.created_at', 'desc')
            ->where('task_lists.project_id', $project_id)->with('user', 'task')->get();
        return \response()->json(['comments' => $all_comments, 'status' => 'success']);
    }

    public function projectAssignCheck(Request $request)
    {
        $user_id = (int)$request->user_id;
        $project_id = (int)$request->project_id;
        $assignUsTask_id = AssignedUser::on($this->db_name)->where('user_id', $user_id)
            ->with(['task' => function ($q) use ($project_id) {
                $q->where('project_id', $project_id);
            }])->whereHas('task', function ($q) use ($project_id) {
                $q->where('project_id', $project_id);
            })->pluck('task_id');

        if ($assignUsTask_id->count() > 0) {
            return response()->json(['assign' => 1]);
        } else {
            ProjectUser::on($this->db_name)->where(['project_id' => $project_id, 'user_id' => $user_id])->delete();
            $project = Project::on($this->db_name)->where('id', $project_id)->first();
            $user = User::where('id', $user_id)->first();
            $data['subject'] = "You remove from Project " . $project->name;
            $data['body'] = '<b>' . \Auth::user()->name . '</b> remove you from <b>' . $project->name . "</b> Project";
            $this->addNotification($user_id, $data['body'], $project_id);
            $mails[] = Mail::to($user->email)->queue(new AddMemberMail($data));
            return response()->json(['assign' => 0, 'users' => [$user_id]]);
        }
    }

    public function AddTeamMember(Request $request)
    {
        $team_id = \Auth::user()->current_team_id;
        $email = $request->email;
        $project_id = $request->project_id;
        $check_user = User::where(['email' => $email])->first();
        if ($check_user) {
            $check_team_user = TeamUser::where(['team_id' => $team_id, 'user_id' => $check_user->id])->first();
            if ($check_team_user) {
                $checkProjectUser = ProjectUser::on($this->db_name)->where(['user_id' => $check_user->id, 'project_id' => $project_id])->first();
                if ($checkProjectUser) {
                    return response()->json(['success' => 1]);
                } else {
                    ProjectUser::on($this->db_name)->create(['user_id' => $check_user->id, 'project_id' => $project_id]);
                    $project = Project::on($this->db_name)->where('id', $project_id)->first();
                    $data['subject'] = "You added to " . $project->name . "Project";
                    $data['body'] = '<b>' . \Auth::user()->name . '</b> Added You to <b>' . $project->name . "</b> Project";
                    $mails[] = Mail::to($email)->queue(new AddMemberMail($data));
                    $this->addNotification($check_user->id, $data['body'], $project_id);
                    return response()->json(['success' => 2, 'users' => [$check_user->id]]);
                }
            } else {
                return response()->json(['success' => 3, 'users' => []]);
            }
        } else {
            return \response()->json(['success' => 0, 'users' => []]);
        }
    }

    public function RemoveTeamUser(Request $request)
    {
        $team_id = \Auth::user()->current_team_id;
        $option = $request->option;
        $project_id = $request->project_id;
        $remove_member_id = $request->remove_member_id;
        $status = 0;
        if ($option == 2) {
            $email = $request->email;
            $check_user = User::where(['email' => $email])->first();
            $is_member = TeamUser::where(['team_id' => $team_id, 'user_id' => $check_user->id])->first();
            if ($check_user && $is_member) {
                AssignedUser::on($this->db_name)->where('user_id', $remove_member_id)
                    ->with(['task' => function ($q) use ($project_id) {
                        $q->where('project_id', $project_id);
                    }])->whereHas('task', function ($q) use ($project_id) {
                        $q->where('project_id', $project_id);
                    })->update(['user_id' => $check_user->id]);
                ProjectUser::on($this->db_name)->where(['project_id' => $project_id, 'user_id' => $remove_member_id])->delete();
                $status = 1;
            } else {
                $status = 0;
            }
        } else if ($option == 1) {
            AssignedUser::on($this->db_name)->where('user_id', $remove_member_id)
                ->with(['task' => function ($q) use ($project_id) {
                    $q->where('project_id', $project_id);
                }])->whereHas('task', function ($q) use ($project_id) {
                    $q->where('project_id', $project_id);
                })->delete();
            ProjectUser::on($this->db_name)->where(['project_id' => $project_id, 'user_id' => $remove_member_id])->delete();
            $status = 2;
        }
        $project = Project::on($this->db_name)->where('id', $project_id)->first();
        $user = User::where(['id' => $remove_member_id])->first();
        $data['subject'] = "You remove from Project " . $project->name;
        $data['body'] = '<b>' . \Auth::user()->name . '</b> remove you from <b>' . $project->name . "</b> Project";
        $mails[] = Mail::to($user->email)->queue(new AddMemberMail($data));
        $this->addNotification($remove_member_id, $data['body'], $project_id);
        return \response()->json(['success' => $status, 'users' => [$remove_member_id]]);
    }

    public function addNotification($user_id, $notification_body, $project_id, $action_url = null)
    {
        Notification::on($this->db_name)->create([
            'user_id' => $user_id,
            'created_by' => \Auth::id(),
            'body' => $notification_body,
            'action_text' => 'View',
            'action_url' => ($action_url == null) ? '/project-dashboard/' . $project_id : $action_url,
        ]);
        return true;
    }

}
