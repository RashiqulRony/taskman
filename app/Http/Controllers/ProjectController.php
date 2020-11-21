<?php

namespace App\Http\Controllers;

use App\ActionLog;
use App\AssignedUser;
use App\Multiple_board;
use App\Multiple_list;
use App\Project;
use App\ProjectNavItems;
use App\ProjectUser;
use App\Rules;
use App\Task;
use App\Team;
use App\TeamUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Configuration\ProjectTemplateVariable;
use Sabberworm\CSS\Rule\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    protected $db_name;

    public function __construct ()
    {
        $this->middleware('auth');
        date_default_timezone_set('UTC');
        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            $team_id = $this->user->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            DBConnection::SetDBConnection($team_id);//set New DB
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB

            return $next($request);
        });
    }
    public function getAll (Request $request)
    {
        $team_id = Auth::user()->current_team_id;
        $auth_user_id = Auth::id();
        $project_ids_ = ProjectUser::on($this->db_name)->where(['user_id' => $auth_user_id])->pluck('project_id');
        $Projects = Project::on($this->db_name)->where('team_id', $team_id)
            ->whereIn('id', $project_ids_)
            ->orWhere('created_by', Auth::id())->get();

        if (isset($request->render)) {
            $project = view('vendor.spark.layouts.leftmenuProjects', ['projects' => $Projects])->render();
            return $project;
        } else {
            return \response()->json(['status' => 'success', 'Projects' => $Projects, 'user_id' => $auth_user_id]);
        }
    }

    public function success ($items = null, $status = 200)
    {
        $data = ['status' => 'success'];
        if ($items instanceof Arrayable) {
            $items = $items->toArray();
        }
        if ($items) {
            foreach ($items as $key => $item) {
                $data[$key] = $item;
            }
        }
        return response()->json($data, $status)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function index ()
    {
        return view('projects');
    }

    public function store (Request $request)
    {

        $template_type = $request->template;
        $member_option = $request->member_option;
        $team_id = Auth::user()->current_team_id;
        $db_name = DBConnection::$db_name . $team_id;

        $data = [
            'team_id' => $team_id,
            'name' => $request->title,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        $check_project = Project::on($this->db_name)->Where(['team_id' => $team_id, 'name' => $request->title])->count();
        if ($check_project <= 0) {
            $project = Project::on($this->db_name)->create($data);
            if ($project) {
                if ($member_option === 1) {
                    $team_user = TeamUser::where(['team_id' => $team_id])->pluck('user_id');
                    foreach ($team_user as $id) {
                        ProjectUser::on($this->db_name)->create(['project_id' => $project->id, 'user_id' => $id]);
                    }
                }
                if ($template_type == 'software') {
                    $template = ProjectTemplateVariable::softwareTemplate();
                    $this->TemplateCreate($project->id, $template);
                } elseif ($template_type == 'design') {
                    $template = ProjectTemplateVariable::DesignProjectTemplate();
                    $this->TemplateCreate($project->id, $template);
                } elseif ($template_type == 'basic') {
                    $template = ProjectTemplateVariable::BasicProjectTemplate();
                    $this->TemplateCreate($project->id, $template);
                } elseif ($template_type == 'writers') {
                    $template = ProjectTemplateVariable::WritersProjectTemplate();
                    $this->TemplateCreate($project->id, $template);
                }
                $log_data = [
                    'project_id' => $project->id,
                    'multiple_list_id' => null,
                    'task_id' => null,
                    'multiple_board_id' => null,
                    'board_id' => null,
                    'title' => $request->title,
                    'log_type' => 'Create project',
                    'action_type' => 'created',
                    'action_by' => Auth::id(),
                    'action_at' => Carbon::now()
                ];
                ActionLog::on($this->db_name)->create($log_data);
                return response()->json(['success' => 1]);
            } else {
                return response()->json(['success' => 0]);
            }
        }

    }

    public function show (Request $request)
    {
        $team_id = Auth::user()->current_team_id;
        $project = Project::on($this->db_name)->where('team_id', $team_id)->findOrFail($request->id);
        $multiple_list = Project::on($this->db_name)->with('multiple_list')->findOrFail($request->id);
        $multiple_list = $multiple_list->multiple_list;

        return response()->json(['success' => 1, 'project' => $project, 'multiple_list' => $multiple_list]);
    }

    public function UpdateUserCurrentTeam (Request $request)
    {
        if (isset($request->team_id)) {
            User::where('id', Auth::id())->update(['current_team_id' => $request->team_id]);
            return \response()->json(['status' => 'success', 'data' => 1]);
        }
    }

    public function update (Request $request)
    {
        $team_id = Auth::user()->current_team_id;
        $check_project = Project::on($this->db_name)->Where(['team_id' => $team_id, 'name' => $request->title])->count();

        if ($check_project <= 1) {
            Project::on($this->db_name)->where('id', $request->id)->update(['name' => $request->title, 'description' => $request->description]);
            return \response()->json(['status' => 'success', 'data' => $request->all()]);
        } else {
            return \response()->json(['status' => 0, 'data' => $request->all()]);
        }

    }

    public function DeleteProject ($id)
    {
        Project::on($this->db_name)->where('id', $id)->delete();
        return \response()->json(['success' => 1]);
    }

    public function TemplateCreate ($project_id, $template_data)
    {
        $nav_lists = $template_data['nav_list'];
        $nav_boards = $template_data['nav_board'];
        foreach ($nav_lists as $key => $nav_list) {
            $data = [
                'project_id' => $project_id,
                'title' => $nav_list['name'],
                'type' => 'list',
                'sort_id' => $key,
                'created_at' => Carbon::now(),
            ];
            $nav_list_new = ProjectNavItems::on($this->db_name)->create($data);
            $this->createLog($project_id, 'created', 'List Navbar Create', $nav_list_new->title, $project_id, 'project_id');
            foreach ($nav_list['list'] as $item) {
                $List = Multiple_list::on($this->db_name)->create([
                    'project_id' => $project_id,
                    'nav_id' => $nav_list_new->id,
                    'list_title' => $item,
                    'description' => '',
                    'created_at' => Carbon::today(),
                ]);
                $this->createLog($nav_list_new->id, 'created', 'Create list', $nav_list_new->title, $project_id, 'multiple_list_id');
            }
        }

        foreach ($nav_boards as $key => $nav_board) {
            $data = [
                'project_id' => $project_id,
                'title' => $nav_board['name'],
                'type' => 'board',
                'sort_id' => $key + 2,
                'created_at' => Carbon::now(),
            ];
            $nav_Board_new = ProjectNavItems::on($this->db_name)->create($data);
            $this->createLog($project_id, 'created', 'Board Navbar Create', $nav_Board_new->title, $project_id, 'project_id');
            foreach ($nav_board['board'] as $item) {
                $board_id = Multiple_board::on($this->db_name)->create([
                    'project_id' => $project_id,
                    'nav_id' => $nav_Board_new->id,
                    'board_title' => $item['name'],
                    'description' => '',
                    'created_at' => Carbon::today(),
                ]);
                $this->createLog($board_id->id, 'created', 'Create Multiple Board', $item['name'], $project_id, 'multiple_board_id');

                foreach ($item['column'] as $key => $col) {
                    $data = Task::on($this->db_name)->create([
                        'multiple_board_id' => $board_id->id,
                        'project_id' => $project_id,
                        'board_flag' => 1,
                        'title' => $col['name'],
                        'color' => '',
                        'progress' => $col['progress'],
                        'board_parent_id' => 0,
                        'hidden' => 0,
                        'board_sort_id' => $key,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'date' => Carbon::today(),
                        'created_at' => Carbon::today(),
                    ]);
                }
            }
            foreach ($nav_board['rules'] as $rule) {
                $from_board = Multiple_board::on($this->db_name)->where(['project_id' => $project_id, 'board_title' => $rule['from_board']])->orderBy('id', 'desc')->first();
                $to_board = Multiple_board::on($this->db_name)->where(['project_id' => $project_id, 'board_title' => $rule['to_board']])->orderBy('id', 'desc')->first();
                if ($from_board && $to_board) {
                    $from_column = Task::on($this->db_name)->select('id')->where(['project_id' => $project_id, 'multiple_board_id' => $from_board->id, 'board_parent_id' => 0, 'title' => $rule['from_column']])->first();
                    $to_column = Task::on($this->db_name)->select('id')->where(['project_id' => $project_id, 'multiple_board_id' => $to_board->id, 'board_parent_id' => 0, 'title' => $rule['to_column']])->first();
                    if ($from_column && $to_column) {
                        Rules::on($this->db_name)->create([
                            'name' => $rule['name'],
                            'status' => 1,
                            'project_id' => $project_id,
                            'move_from' => $from_column->id,
                            'move_to' => $to_column->id,
                            'created_by' => Auth::id(),
                            'assigned_users' => '[]'
                        ]);
                    }
                }
            }
        }
    }

    public function createLog ($nav_id, $type, $message, $title, $project_id, $feild)
    {
        $log_data = [
            $feild => $project_id,
            'nav_id' => $nav_id,
            'title' => $title,
            'log_type' => $message,
            'action_type' => $type,
            'action_by' => Auth::id(),
            'action_at' => Carbon::now()
        ];
        ActionLog::on($this->db_name)->create($log_data);
    }


}
