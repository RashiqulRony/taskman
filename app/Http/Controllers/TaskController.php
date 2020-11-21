<?php

namespace App\Http\Controllers;

use App\ActionLog;
use App\AssignedUser;
use App\AssignTag;
use App\Files;
use App\Multiple_list;
use App\Project;
use App\ProjectUser;
use App\Rules;
use App\Tags;
use App\Task;
use App\LinkListToColumn;
use App\User;
use App\UserDetails;
use App\UserFilter;
use Carbon\Carbon;
//use Http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\File;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;
use mysql_xdevapi\Exception;
use function GuzzleHttp\Promise\all;
use function response;

class TaskController extends Controller
{
    protected $actionLog;
    protected $dont_forget_tag;
    protected $all_ids = [];
    protected $HomeController;
    protected $db_name;

    public function __construct ()
    {
        date_default_timezone_set('UTC');
        $this->actionLog = new ActionLogController;
        $this->HomeController = new HomeController;
        $this->dont_forget_tag = 'Dont Forget';
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $team_id = Auth::user()->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    public function decorateData ($obj, $drag = null, $filter = null, $tz = null)
    {
        $team_id = Auth::user()->current_team_id;
        $db_name = DBConnection::$db_name . $team_id;
        $this->db_name = $db_name;
        $allTeamUsers = [];
        $allTeamTags = [];
        $data = [];
        foreach ($obj as $key => $task) {

            $info = array();
            array_push($this->all_ids, $task->id);
            $info['id'] = $task->id;
            $info['parent_id'] = $task->parent_id;
            $info['sort_id'] = $task->sort_id;
            $info['board_parent_id'] = $task->board_parent_id;
            $info['multiple_board_id'] = $task->multiple_board_id;
            $info['priority_label'] = null;
            if ($task->priority_label == 3 || $task->priority_label == 'high') {
                $info['priority_label'] = 'high';
            } else if ($task->priority_label == 2 || $task->priority_label == 'medium') {
                $info['priority_label'] = 'medium';
            } else if ($task->priority_label == 1 || $task->priority_label == 'low') {
                $info['priority_label'] = 'low';
            }
            $info['list_id'] = $task->list_id;//list_id
            $info['text'] = $task->title;
            if ($task->title == 'Dont Forget Section' || $drag != null) {
                $info['draggable'] = false;
                $info['droppable'] = false;
            } else {
                $info['draggable'] = true;
                $info['droppable'] = true;
            }
            $info['droppable'] = true;
            $info['clicked'] = 0;
            $info['count_child'] = 0;
            if ($task->date != '0000-00-00 00:00:00' && $task->date != '0000-00-00') {
                $date = Carbon::parse($task->date, 'UTC')->setTimezone($tz);
            } else {
                $date = '0000-00-00';
            }
            $info['date'] = $date;
            $info['progress'] = $task->progress;
            $info['open'] = $task->open;
            $allTags = $task->Assign_tags;
            $infoTags = [];
            $tagTooltip = '';
            if (!empty($allTags) && $allTags->count() > 0) {
                foreach ($allTags as $key => $tag) {
                    if (!empty($tag->tag)) {
                        $infoTags[] = array(
                            'assign_id' => $tag->id,
                            'id' => $tag->tag->id,
                            'text' => $tag->tag->title,
                            'classes' => '',
                            'style' => 'background-color: ' . $tag->tag->color,
                            'color' => $tag->tag->color,
                        );
                        $tagTooltip .= '#' . $tag->tag->title . ' ';
                    }
                }
            }
            $info['tags'] = $infoTags;
            $info['tagTooltip'] = $tagTooltip;
            $info['complete_tooltip'] = ($task->column != null) ? '#Board : ' . $task->column->MultipleBord['board_title'] . " #Column : " . $task->column->title : '';
            $info['description'] = $task->description;
            $info['files'] = $task->files;
            $info['assigned_user'] = $this->AssignUser($task->id);
            $assigned_user_ids = [];
            foreach ($info['assigned_user'] as $id) {
                $assigned_user_ids[] = $id['id'];
            }
            $info['assigned_user_ids'] = $assigned_user_ids;
            $info['created_by'] = User::find($task->created_by);


            $info['users'] = $allTeamUsers;
            $info['existing_tags'] = $allTeamTags;
            $info['comment'] = $task->comment;


            if ($filter === null) {
                $childrens = Task::on($this->db_name)->where('parent_id', $task->id)
                    ->where('list_id', $task->list_id)
                    ->where('is_deleted', '!=', 1)
                    ->orderBy('sort_id', 'ASC')
                    ->get();
                if (!empty($childrens)) {
                    $info['children'] = $this->decorateData($childrens, $drag);
                } else {
                    $info['children'] = [];
                }
            } elseif ($filter === 'date') {
                $childrens = Task::on($this->db_name)->where('parent_id', $task->id)
                    ->where('list_id', $task->list_id)
                    ->where('is_deleted', '!=', 1)
                    ->orderBy('date', 'ASC')
                    ->get();
                if (!empty($childrens)) {
                    $info['children'] = $this->decorateData($childrens, $drag);
                } else {
                    $info['children'] = [];
                }
            } else {
                $info['children'] = [];
            }

            $data[] = $info;


        }
        return $data;
    }

    public function AssignUser($task_id){
        $assign_users_ids = AssignedUser::on($this->db_name)->where('task_id', $task_id)->pluck('user_id');
        $user = User::whereIn('id',$assign_users_ids)->get()->toArray();
        return $user;
    }

    public function getAll (Request $request)
    {
        $user = UserDetails::on($this->db_name)->with('filter')->where('user_id',Auth::id())->first();
        if ($user->filter != null) {
            $data = [
                'tz' => $request->tz,
                'id' => $request->id,
                'list_id' => $request->list_id,
                'filter_type' => $user->filter->filter_type,
                'ids' => ($request->ids) ? $request->ids : [],
            ];
            return $this->getFilter((object)$data);
        }

        $tz = $request->tz;
        if ($request->list_id == null) {
            $list = Multiple_list::on($this->db_name)->where('project_id', $request->id)->orderBy('id', 'ASC')->first();
            $list_id = $list->id;
        } else {
            $list_id = $request->list_id;
            $list = Multiple_list::on($this->db_name)->where('id', $list_id)->first();
        }
        $tasks = Task::on($this->db_name)->where('parent_id', 0)
            ->where('project_id', $request->id)
            ->where('is_deleted', '!=', 1)
            ->where('list_id', $list_id)
            ->with('column')
            ->orderBy('sort_id', 'ASC')
            ->with('comment')
            ->get();
        $task = [];
        if ($tasks->count() <= 0) {
            $data = [
                'sort_id' => 0,
                'parent_id' => 0,
                'project_id' => $request->id,
                'list_id' => $list_id,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'title' => '',
                'date' => '0000-00-00',
                'created_at' => Carbon::now(),
            ];
            $task = Task::on($this->db_name)->create($data);
            $tasks = Task::on($this->db_name)->where('parent_id', 0)
                ->where('project_id', $request->id)
                ->where('list_id', $list_id)
                ->with('column')
                ->orderBy('sort_id', 'ASC')->get();

        }
        $this->all_ids = [];
        $data = $this->decorateData($tasks, null, null, $tz);
        $userName = Auth::user();
        $multiple_list = Project::on($this->db_name)->with('multiple_list')->findOrFail($request->id);
        $multiple_list = $multiple_list->multiple_list;

        $team_id = Auth::user()->current_team_id;

        $project = Project::on($this->db_name)->where('id', $request->id)->first();
        $user_ids = ProjectUser::on($this->db_name)->where(['project_id' => $request->id])->pluck('user_id');
        $allTeamUsers = User::join('team_users', 'team_users.user_id', 'users.id')
            ->where('team_users.team_id', $team_id)->get()->toArray();

        $allTeamTags = Tags::on($this->db_name)->where('team_id', $team_id)->where('title', '!=', $this->dont_forget_tag)
            ->get()->toArray();
        return response()->json([
            'task_list' => $data,
            'multiple_list' => $multiple_list,
            'empty_task' => $task,
            'userName' => $userName,
            'allTeamUsers' => $allTeamUsers,
            'allTeamTags' => $allTeamTags,
            'all_ids' => $this->all_ids,
            'list' => $list
        ]);
    }

    public function getAllFilter (Request $request)
    {
        $data = [
            'tz' => $request->tz,
            'id' => $request->id,
            'list_id' => $request->list_id,
            'filter_type' => $request->filter_type,
            'ids' => ($request->ids) ? $request->ids : [],
            'filter' => ($request->filter) ? $request->filter : [],
        ];
        $type = $request->filter_type;
        if ($type != 'all' && $type != null && $type != 'p_show' && $type != 'p_hide' && $type != 'users_task') {
            $this->AddOrUpdateFilter($type);
        } else {
            UserFilter::on($this->db_name)->where(['user_id' => Auth::id()])->delete();
        }
        return $this->getFilter((object)$data);
    }

    public function getFilter ($request)
    {
        $tz = $request->tz;
        if ($request->list_id == null) {
            $list = Multiple_list::on($this->db_name)->where('project_id', $request->id)
                ->orderBy('id', 'ASC')->first();
            $list_id = $list->id;
        } else {
            $list_id = $request->list_id;
            $list = Multiple_list::on($this->db_name)->where('id', $list_id)->first();
        }
        $tasks = Task::on($this->db_name)->where('project_id', $request->id)
            ->where('is_deleted', '!=', 1)
            ->where('list_id', $list_id)
            ->with('column');
        if ($request->filter_type === 'my' || $request->filter_type === 'users_task') {
            if (count($request->ids) > 0) {
                $ids = $request->ids;
            } else {
                $ids = [Auth::id()];
            }
            $tasks = $tasks->whereHas('Assign_user', function ($q) use ($ids) {
                $q->whereIn('user_id', $ids);
            })
                ->orderBy('sort_id', 'ASC')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');
        } elseif ($request->filter_type === 'not_assign') {
            $tasks = $tasks->whereDoesntHave('Assign_user')
                ->orderBy('sort_id', 'ASC')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');
        } elseif ($request->filter_type === 'completed') {
            $tasks = $tasks->where('progress', 100)
                ->orderBy('sort_id', 'ASC')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');
        } elseif ($request->filter_type === 'hide_completed') {
            $tasks = Task::on($this->db_name)->where('project_id', $request->id)
                ->where('is_deleted', '!=', 1)
                ->where('list_id', $list_id)
                ->with('column')
                ->where(function ($q) {
                    $q->where('progress', '!=', 100)
                        ->orWhere('progress', null);
                })
                ->orderBy('sort_id', 'ASC')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter', $tz);
        } elseif ($request->filter_type === 'date') {

            $tasks = $tasks->orderBy('date', 'DESC')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');

        } elseif ($request->filter_type === 'date-asc') {

            $tasks = $tasks->where(function ($q) {
                $q->where('date', '!=', '0000-00-00');
                $q->orWhere('date', '!=', '0000-00-00 00:00:00');
            })->orderBy('date', 'asc')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');

        } elseif ($request->filter_type === 'asc') {

            $tasks = $tasks->orderBy('id', 'ASC')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');
        } elseif ($request->filter_type === 'desc') {

            $tasks = $tasks->orderBy('id', 'desc')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');
        } elseif ($request->filter_type === "priority") {

            $tasks = $tasks->orderBy('priority_label', 'desc')
                ->get();
            $data = $this->decorateData($tasks, 'drag', 'filter');
        } elseif ($request->filter_type === "p_hide") {
            $filter = $request->filter;
            if (count($filter) > 0 && !in_array(0, $filter)) {
                $tasks = $tasks->where(function ($q) use ($filter) {
                    $q->whereNotIn('priority_label', $filter)
                        ->orWhere('priority_label', null);
                })->get();
            } else {
                $tasks = $tasks->where('priority_label', '!=', null)->get();
            }

            $data = $this->decorateData($tasks, 'drag', 'filter');
        } elseif ($request->filter_type === "p_show") {

            $filter = $request->filter;
            if (count($filter) > 0 && !in_array(0, $filter)) {
                $tasks = $tasks->whereIn('priority_label', $filter)->get();
            } else {
                $tasks = $tasks->where(function ($q) use ($filter) {
                    $q->whereIn('priority_label', $filter);
                    $q->orWhere('priority_label', null);
                })->get();
            }

            $data = $this->decorateData($tasks, 'drag', 'filter');
        } else {

            $tasks = Task::on($this->db_name)->where('parent_id', 0)
                ->where('project_id', $request->id)
                ->where('is_deleted', '!=', 1)
                ->where('list_id', $list_id)->with('column')
                ->orderBy('sort_id', 'ASC')
                ->get();
            $data = $this->decorateData($tasks, null);
        }
        $task = [];
        $this->all_ids = [];
        $userName = Auth::user();
        $multiple_list = Project::on($this->db_name)->with('multiple_list')->findOrFail($request->id);
        $multiple_list = $multiple_list->multiple_list;

        $team_id = Auth::user()->current_team_id;
        $project = Project::on($this->db_name)->where('id', $request->id)->first();
        $user_ids = ProjectUser::on($this->db_name)->where(['project_id' => $request->id])->pluck('user_id');
        $allTeamUsers = User::whereIn('id', $user_ids)->orWhere('id', $project->created_by)->get()->toArray();
        $allTeamTags = Tags::on($this->db_name)->where('team_id', $team_id)->where('title', '!=', $this->dont_forget_tag)
            ->get()->toArray();

        return response()->json([
            'task_list' => $data,
            'multiple_list' => $multiple_list,
            'empty_task' => $task,
            'userName' => $userName,
            'allTeamUsers' => $allTeamUsers,
            'allTeamTags' => $allTeamTags,
            'all_ids' => $this->all_ids,
            'list' => $list
        ]);
    }

    public function getSingleTask (Request $request)
    {
        $tz = $request->tz;
        $id = $request->id;
        $project_id = $request->project_id;
        $tasks = Task::on($this->db_name)->where('id', $id)
            ->with('comment')
            ->with('column','childTask','parents')
            ->orderBy('sort_id', 'ASC')->get();
        $parents = $tasks->count() > 0 ? $tasks[0]->parents : [];
        $childTask = $tasks->count() > 0 ? $tasks[0]->childTask : [];
        $data = $this->decorateData($tasks, null, null, $tz);

        $team_id = Auth::user()->current_team_id;
        $project = Project::on($this->db_name)->where('id', $project_id)->first();
        $user_ids = ProjectUser::on($this->db_name)->where(['project_id' => $project_id])->pluck('user_id');
        $allUsers = User::whereIn('id', $user_ids)->orWhere('id', $project->created_by)->get();

        $allTags = Tags::on($this->db_name)->where('team_id', $team_id)->where('title', '!=', $this->dont_forget_tag)->orderBy('title', 'asc')->get()->toArray();
        return response()->json(['status' => 200, 'task' => $data[0],'parents'=> $parents, 'child' => $childTask, 'users' => $allUsers, 'allTags' => $allTags]);

    }

    protected function createLog ($task_id, $type, $message, $title)
    {
        $logcheck = ActionLog::on($this->db_name)->where(['task_id'=> $task_id,'log_type' => $message])->first();
        if($logcheck){
            ActionLog::on($this->db_name)->where('id',$logcheck->id)->update(['action_at' => Carbon::now(), 'title' => $title,'action_by' => Auth::id()]);
        } else {
            $task = Task::on($this->db_name)->where('id', $task_id)->first();
            $log_data = [
                'project_id' => $task->project_id,
                'task_id' => $task_id,
                'title' => $title,
                'log_type' => $message,
                'action_type' => $type,
                'action_by' => Auth::id(),
                'action_at' => Carbon::now()
            ];
            ActionLog::on($this->db_name)->create($log_data);
        }
    }

    public function getAllTask (Request $request)
    {
        if ($request->list_id == null) {
            $list = Multiple_list::on($this->db_name)->where('project_id', $request->id)->orderBy('id', 'ASC')->first();
            $list_id = $list->id;
        } else {
            $list_id = $request->list_id;
        }
        $tasks = Task::on($this->db_name)->where('parent_id', 0)
            ->where('project_id', $request->id)
            ->where('list_id', $list_id)
            ->orderBy('sort_id', 'ASC')
            ->get();
        $data = $this->decorateData($tasks, null);
        $list = Multiple_list::on($this->db_name)->find($list_id);
        return response()->json(['task_list' => $data,'list'=>$list]);
    }

    public function addTask (Request $request)
    {

        $list_id = $request->list_id;
        $etask = Task::on($this->db_name)->where(['id' => $request->id])->first();
        if ($request->text == '') {
            $this->deleteTaskWithChild($request->id);
            $this->createLog($request->id, 'deleted', 'Task was deleted', $etask->title);
            return response()->json(['success' => ['id' => $request->id]]);

        } else {
            if ($etask && $request->text != '') {
                if ($etask->title !== $request->text) {
                    Task::on($this->db_name)->where('id', $request->id)
                        ->update(['title' => $request->text]);
                    $this->createLog($request->id, 'updated', 'Task title was updated', $request->text);
                }

                Task::on($this->db_name)->where([
                    'title' => '',
                    'parent_id' => $request->parent_id,
                    'project_id' => $request->project_id
                ])->where('list_id', $list_id)->delete();

                Task::on($this->db_name)->where('parent_id', $request->parent_id)
                    ->where('sort_id', '>', $request->sort_id)
                    ->where('list_id', $list_id)
                    ->increment('sort_id');
                $sort_id = ($request->sort_id < 0) ? 0 : $request->sort_id + 1;
                $data = [
                    'sort_id' => $sort_id,
                    'parent_id' => $request->parent_id,
                    'project_id' => $request->project_id,
                    'list_id' => $list_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'title' => '',
                    'date' => '0000-00-00',
                    'created_at' => Carbon::now(),
                ];
                $link = LinkListToColumn::on($this->db_name)->where('multiple_list_id', $list_id)->first();
                if ($link) {

                    $data['board_parent_id'] = $link->task_list_id;
                    $progress = Task::on($this->db_name)->where('id', $link->task_list_id)->first();
                    $data['progress'] = $progress->progress;
                    $data['multiple_board_id'] = $progress->multiple_board_id;
                }
                $task = Task::on($this->db_name)->create($data);
                $this->updateTagWithDataMove($task->id, $request->parent_id);

                $multiple_list = Project::on($this->db_name)->with('multiple_list')->findOrFail($task->project_id);
                $multiple_list = $multiple_list->multiple_list;

                $tasks = Task::on($this->db_name)->where('parent_id', 0)
                    ->where('project_id', $task->project_id)
                    ->where('is_deleted', '!=', 1)
                    ->where('list_id', $task->list_id)->with('column')
                    ->orderBy('sort_id', 'ASC')
                    ->with('comment')
                    ->get();
                $data = $this->decorateData($tasks, null, null);
                return response()->json([
                    'success' => $task,
                    'task_list' => $data,
                    'multiple_list' => $multiple_list
                ]);
            }
        }
    }

    public function addChildTask (Request $request)
    {
        UserFilter::on($this->db_name)->where(['user_id' => Auth::id()])->delete();
        $task_id = Task::on($this->db_name)->where('title', '')->where('parent_id', $request->id)->first();
        if ($task_id) {
            Task::on($this->db_name)->where('title', '')->where('parent_id', $request->id)->delete();
        }
        $sort_id = Task::on($this->db_name)->where('parent_id', $request->id)->orderBy('id', 'desc')->first();
        $data = [
            'sort_id' => $sort_id ? $sort_id->sort_id + 1 : 0,
            'parent_id' => $request->id,
            'project_id' => $request->project_id,
            'list_id' => $request->list_id,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'title' => '',
            'date' => '0000-00-00',
            'created_at' => Carbon::now(),
        ];

        $link = LinkListToColumn::on($this->db_name)->where('multiple_list_id', $request->list_id)->first();
        if ($link) {
            $data['board_parent_id'] = $link->task_list_id;
            $progress = Task::on($this->db_name)->where('id', $link->task_list_id)->first();
            $data['progress'] = $progress->progress;
            $data['multiple_board_id'] = $progress->multiple_board_id;
        }

        $task = Task::on($this->db_name)->create($data);
        $this->updateTagWithDataMove($task->id, $request->id);

        return response()->json(['success' => $task]);
    }

    public function makeChild (Request $request)
    {
        if (isset($request->parent_id)) {
            $task = Task::on($this->db_name)->where('parent_id', $request->parent_id)
                ->where('project_id', $request->project_id)
                ->where('list_id', $request->list_id)
                ->where('sort_id', '<', $request->sort_id)
                ->orderBy('sort_id', 'desc')->first();
            if ($task) {
                $taskSortId = Task::on($this->db_name)->where('parent_id', $task->id)->max('sort_id');
                if ($task->board_parent_id !== null){
                    $taskBoardSortId = Task::on($this->db_name)->where('parent_id', $task->id)->max('board_sort_id');
                    $board_sort_id = ($taskBoardSortId > 0) ? $taskBoardSortId + 1 : 1;
                } else{
                    $board_sort_id = 0;
                }
                $sort_id = ($taskSortId > 0) ? $taskSortId + 1 : 1;

                $child = Task::on($this->db_name)->where('id', $request->id)
                    ->update([
                        'parent_id' => $task->id,
                        'sort_id' => $sort_id,
                        'title' => $request->text,
                        'board_sort_id' =>$board_sort_id,
                        ]);

                $this->updateTagWithDataMove($request->id, $task->id);

                $this->createLog($request->id, 'updated', 'Task parent was updated', $request->text);
            }
            return response()->json(['success' => $request->id]);
        }
    }

    public function updateTagWithDataMove ($task_id, $target_parent_id)
    {
        $team_id = Auth::user()->current_team_id;
        $this->db_name = DBConnection::$db_name . $team_id;
        $tag_ids = $tags = Tags::on($this->db_name)->where(['title' => $this->dont_forget_tag, 'team_id' => Auth::user()->current_team_id])->first();
        if ($tag_ids) {

            $check_tag_assign = AssignTag::on($this->db_name)->where(['task_id' => $target_parent_id, 'tag_id' => $tag_ids->id])->count();

            if ($check_tag_assign <= 0) {
                AssignTag::on($this->db_name)->where(['task_id' => $task_id, 'tag_id' => $tag_ids->id])->delete();
                $task_find = Task::on($this->db_name)->where('id', $task_id)->first();
                $taskDontForgetSection = Task::on($this->db_name)->where([
                    'title' => 'Dont Forget Section',
                    'project_id' => $task_find->project_id,
                    'list_id' => $task_find->list_id,
                ])->first();

                if ($taskDontForgetSection) {
                    $childrenOfDontForgetSection = Task::on($this->db_name)->where('parent_id', $taskDontForgetSection->id)
                        ->where('is_deleted', 0)->get()->toArray();
                    if (count($childrenOfDontForgetSection) <= 0) {
                        AssignedUser::on($this->db_name)->where('task_id', $taskDontForgetSection->id)->delete();
                        AssignTag::on($this->db_name)->where(['task_id' => $taskDontForgetSection->id, 'tag_id' => $tag_ids->id])->delete();
                        Task::on($this->db_name)->where('id', $taskDontForgetSection->id)->delete();
                    }
                }

            } else {
                $self = AssignTag::on($this->db_name)->where(['task_id' => $task_id, 'tag_id' => $tag_ids->id])->with('task')->first();
                if (!$self) {
                    AssignTag::on($this->db_name)->create(['task_id' => $task_id, 'tag_id' => $tag_ids->id]);
                    $self = AssignTag::on($this->db_name)->where(['task_id' => $task_id, 'tag_id' => $tag_ids->id])->with('task')->first();
                }
            }
            $children = Task::on($this->db_name)->where('parent_id', $task_id)->get();
            foreach ($children as $child) {
                $this->updateTagWithDataMove($child->id, $task_id);
            }
        }
    }

    public function reverseChild (Request $request)
    {
        if (isset($request->parent_id) && $request->parent_id != 0) {
            $task = Task::on($this->db_name)->where('id', $request->parent_id)->first();
            if ($task) {
                $taskChild = Task::on($this->db_name)->where('parent_id', $task->parent_id)
                    ->where('project_id', $task->project_id)
                    ->where('list_id', $task->list_id)
                    ->where('sort_id', '>', $task->sort_id)
                    ->increment('sort_id');
                $sort_id = ($task->sort_id < 0) ? 1 : $task->sort_id + 1;
                if ($task->board_parent_id !== null){
                    $taskBoardSortId = Task::on($this->db_name)->where('board_parent_id', $task->board_parent_id)->max('board_sort_id');
                    $board_sort_id = ($taskBoardSortId > 0) ? $taskBoardSortId + 1 : 1;
                } else{
                    $board_sort_id = 0;
                }
                Task::on($this->db_name)->where('id', $request->id)
                    ->update([
                        'parent_id' => $task->parent_id,
                        'sort_id' => $sort_id,
                        'title' => $request->text,
                        'board_sort_id' => $board_sort_id,
                    ]);

                $this->updateTagWithDataMove($request->id, $task->parent_id);
                $this->createLog($request->id, 'updated', 'Task parent was updated', $request->text);
            }

            return response()->json(['success' => $request->id]);
        }
    }

    public function CopyCutPast (Request $request)
    {
        if ($request->type == 'copy') {

            $target_id = $request->target_id;
            $copy_ids = $request->copy_ids;

            $targetTask = Task::on($this->db_name)->where('id', $target_id)->first();
            $parent_id = $targetTask->parent_id;
            $tasks = Task::on($this->db_name)->whereIn('id', $copy_ids)->orderBy('parent_id', 'asc')->orderBy('id', 'desc')->get()->toArray();
            foreach ($tasks as $task){
                $check_links = LinkListToColumn::on($this->db_name)->where('multiple_list_id', $targetTask->list_id)->first();
                $link_column = $check_links ? Task::on($this->db_name)->where('id', $check_links->task_list_id)->first() : null;
                $sortId = $task['sort_id'] + 1;
                if($task['parent_id'] == 0){
                    $data = [
                        'sort_id' => $sortId,
                        'parent_id' => $parent_id,
                        'project_id' => $task['project_id'],
                        'list_id' => $task['list_id'],
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'title' => $task['title'] . '-copy',
                        'date' => $task['date'],
                        'board_parent_id' => $check_links && $link_column ? $link_column->board_parent_id : null,
                        'progress' => $check_links && $link_column ? $link_column->progress : null,
                        'copied_from' => $task['id'],
                        'created_at' => Carbon::now(),
                    ];
                    Task::on($this->db_name)->create($data);
                } else {
                    $pTask = Task::on($this->db_name)->where('copied_from', $task['parent_id'])->orderBy('id','desc')->get()->first();
                    if($pTask != null){
                        $parent_id = $pTask->id;
                    }

                    $data = [
                        'sort_id' => $sortId,
                        'parent_id' => $parent_id,
                        'project_id' => $task['project_id'],
                        'list_id' => $task['list_id'],
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'title' => $task['title'] . '-copy',
                        'date' => $task['date'],
                        'board_parent_id' => $check_links && $link_column ? $link_column->board_parent_id : null,
                        'progress' => $check_links && $link_column ? $link_column->progress : null,
                        'copied_from' => $task['id'],
                        'created_at' => Carbon::now(),
                    ];
                    Task::on($this->db_name)->create($data);
                }
            }

            return response()->json(['success' => $target_id, 'list_id' => $targetTask->list_id, 'execute' => []]);

        } else {
            if ($request->type == 'cut') {
                $target = Task::on($this->db_name)->where('id', $request->target_id)->first();
                Task::on($this->db_name)->where('project_id', $target->project_id)
                    ->where('sort_id', '>', $target->sort_id)
                    ->where('parent_id', $target->parent_id)
                    ->increment('sort_id');
                $past = Task::on($this->db_name)->where('id', $request->copy_ids[0])
                    ->update(['parent_id' => $target->parent_id, 'sort_id' => $target->sort_id + 1]);

                $past = Task::on($this->db_name)->where('id', $request->copy_ids[0])->first();
                $this->updateTagWithDataMove($past->id, $target->parent_id);
                //check the target task id in the dont forget section and update tag for necessary
                $this->createLog($past->id, 'cut', 'Cut and past task', $past->title);
                return response()->json(['success' => $past->id, 'list_id' => $target->list_id]);
            }
        }
    }

    public function CopyPastMultipleTaskToAnotherList (Request $request)
    {
        $task_ids = $request->task_ids;
        $target_nav_id = $request->nav_id;
        $target_list_id = $request->list_id;

        $target_list_sort_id = Task::on($this->db_name)->where(['list_id' => $target_list_id])->orderBy('sort_id', 'desc')->first();

        $check_links = LinkListToColumn::on($this->db_name)->where('multiple_list_id', $target_list_id)->first();
        $link_column = $check_links ? Task::on($this->db_name)->where('id', $check_links->task_list_id)->first() : null;
        $parent_id = 0;
        $tasks = Task::on($this->db_name)->whereIn('id', $task_ids)->orderBy('parent_id', 'asc')->orderBy('id', 'desc')->get()->toArray();
        $sort_id = $target_list_sort_id->sort_id;
        foreach ($tasks as $task){
            $past = Task::on($this->db_name)->where('id', $task['id'])->with('List')->first();
            $sort_id = $sort_id + 1;
            if($task['parent_id'] == 0){
                $data = [
                    'sort_id' => $sort_id,
                    'parent_id' => $parent_id,
                    'project_id' => $task['project_id'],
                    'list_id' => $target_list_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'title' => $task['title'] . '-copy',
                    'date' => $task['date'],
                    'board_parent_id' => $check_links && $link_column ? $link_column->board_parent_id : null,
                    'progress' => $check_links && $link_column ? $link_column->progress : null,
                    'copied_from' => $task['id'],
                    'created_at' => Carbon::now(),
                ];
                Task::on($this->db_name)->create($data);
            } else {
                $pTask = Task::on($this->db_name)->where('copied_from', $task['parent_id'])->orderBy('id','desc')->get()->first();
                if($pTask != null){
                    $parent_id = $pTask->id;
                }
                $data = [
                    'sort_id' => $task['sort_id'],
                    'parent_id' => $parent_id,
                    'project_id' => $task['project_id'],
                    'list_id' => $target_list_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'title' => $task['title'] . '-copy',
                    'date' => $task['date'],
                    'board_parent_id' => $check_links && $link_column ? $link_column->board_parent_id : null,
                    'progress' => $check_links && $link_column ? $link_column->progress : null,
                    'copied_from' => $task['id'],
                    'created_at' => Carbon::now(),
                ];
                Task::on($this->db_name)->create($data);
            }
            $task = Task::on($this->db_name)->where('id', $task['id'])->with('List')->first();
            $this->createLog($task->id, 'copy', 'Copy task from ' . $past->List->list_title . ' to ' . $task->List->list_title, $task->title);
        }
        return response()->json([$task]);

    }

    function CopayPast ($target_id, $copy_id, $ids, $execute = [])
    {
        if (!in_array($copy_id, $execute)) {
            $parent_id = Task::on($this->db_name)->find($copy_id);
            if (in_array($parent_id, $ids)) {
                $task_copy_from_parent = Task::on($this->db_name)->where('copied_from', $parent_id)->orderBy('id', 'desc')->first();

                if ($task_copy_from_parent) {
                    $past = Task::on($this->db_name)->where('id', $copy_id)->first();
                    $target = Task::on($this->db_name)->where('id', $task_copy_from_parent)->first();
                    $check_links = LinkListToColumn::on($this->db_name)->where('multiple_list_id', $task_copy_from_parent->list_id)->first();
                    $link_column = $check_links ? Task::on($this->db_name)->where('id', $task_copy_from_parent->task_list_id)->first() : null;
                    $data = [
                        'sort_id' => 0,
                        'parent_id' => $task_copy_from_parent->id,
                        'project_id' => $past->project_id,
                        'list_id' => $past->list_id,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'title' => $past->title . ' -copy',
                        'date' => $past->date,
                        'board_parent_id' => $check_links && $link_column ? $link_column->board_parent_id : null,
                        'progress' => $check_links && $link_column ? $link_column->progress : null,
                        'created_at' => Carbon::now(),
                        'copied_from' => $past->id,
                    ];
                }

            } else {
                $target = Task::on($this->db_name)->where('id', $target_id)->first();
                $past = Task::on($this->db_name)->where('id', $copy_id)->first();
                $check_links = LinkListToColumn::on($this->db_name)->where('multiple_list_id', $target->list_id)->first();
                $link_column = $check_links ? Task::on($this->db_name)->where('id', $check_links->task_list_id)->first() : null;

                Task::on($this->db_name)->where('parent_id', $target->parent_id)
                    ->where('project_id', $target->project_id)
                    ->where('list_id', $target->list_id)
                    ->where('sort_id', '>', $target->sort_id)
                    ->increment('sort_id');
                $data = [
                    'sort_id' => $target->sort_id + 1,
                    'parent_id' => $target->parent_id,
                    'project_id' => $past->project_id,
                    'list_id' => $past->list_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'title' => $past->title . ' -copy',
                    'date' => $past->date,
                    'board_parent_id' => $check_links && $link_column ? $link_column->board_parent_id : null,
                    'progress' => $check_links && $link_column ? $link_column->progress : null,
                    'created_at' => Carbon::now(),
                    'copied_from' => $past->id,
                ];

            }

            $task = Task::on($this->db_name)->create($data);
            $this->createLog($task->id, 'copy', 'Task was copied', $task->title);
            $this->updateTagWithDataMove($task->id, $target->parent_id);
            array_push($execute, $copy_id);
            return $execute;
        }
    }

    public function parmanentDeleteTask (Request $request)
    {
        if (isset($request->ids)) {
            $ids = $request->ids;
            foreach ($ids as $id) {
                $this->deleteTaskWithChild($id);
            }
            return response()->json(['success' => 1]);
        } else {
            $this->deleteTaskWithChild($request->id);
            return response()->json(['success' => 1]);
        }

    }

    public function softDeleteChild ($id)
    {
        $delete = Task::on($this->db_name)->where('id', $id)->update([
            'is_deleted' => 1,
            'deleted_at' => carbon::now()
        ]);
        $task = Task::on($this->db_name)->find($id);
        $this->createLog($id, 'softdelete', 'Task was Softdeleted', $task->title);
        $childs = Task::on($this->db_name)->where('parent_id', $id)->pluck('id');
        if (!empty($childs)) {
            foreach ($childs as $child) {
                $this->softDeleteChild($child);
            }
        }
    }

    public function deleteTask (Request $request)
    {
        if (isset($request->ids)) {
            $ids = $request->ids;
            foreach ($ids as $id) {
                $this->softDeleteChild($id);
            }
        } else {
            $this->softDeleteChild($request->id);
        }

        return response()->json(['success' => 1]);

    }

    public function ActionSelectedTask (Request $request)
    {
        if (isset($request->type) && $request->type == 'user') {
            $ids = $request->ids;
            foreach ($ids as $id) {
                $checkIsUserAssigned = AssignedUser::on($this->db_name)->where([
                    'task_id' => $id,
                    'user_id' => $request->value
                ])->count();
                if ($checkIsUserAssigned <= 0) {
                    AssignedUser::on($this->db_name)->create([
                        'task_id' => $id,
                        'user_id' => $request->value,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }
            if ($request->value != Auth::id()) {
                Notification::on($this->db_name)->create([
                    'user_id' => $request->value,
                    'created_by' => Auth::id(),
                    'body' => Auth::user()->name . " assign on a task.",
                    'action_text' => 'View',
                ]);
            }
            return response()->json(['success' => 1]);
        } else if (isset($request->type) && $request->type == 'tag') {
            $ids = $request->ids;
            $tag_id = $request->value;
            foreach ($ids as $id) {
                $check_assign = AssignTag::on($this->db_name)->where(['task_id' => $id, 'tag_id' => $tag_id])->get();
                if ($check_assign->count() <= 0) {
                    $assign = AssignTag::on($this->db_name)->create(['task_id' => $id, 'tag_id' => $tag_id]);
                }
            }
            return response()->json(['success' => 1]);
        } else if (isset($request->type) && $request->type == 'date') {
            $ids = $request->ids;
            foreach ($ids as $id) {
                Task::on($this->db_name)->where('id', $id)->update(['date' => $request->value]);
            }
            return response()->json(['success' => "Date Update Success"]);
        }
    }

    public function deleteTaskWithChild ($id)
    {
        $team_id = Auth::user()->current_team_id;
        $this->db_name = DBConnection::$db_name . $team_id;

        $check_dontForgetSection = Task::on($this->db_name)->where('id', $id)->first();
        if ($check_dontForgetSection == null) {
            return true;
        } else {
            $taskDontForget = Task::on($this->db_name)->where([
                'title' => 'Dont Forget Section',
                'project_id' => $check_dontForgetSection->project_id,
                'list_id' => $check_dontForgetSection->list_id
            ])->first();
            $check_number_of_task = Task::on($this->db_name)->where([
                'project_id' => $check_dontForgetSection->project_id,
                'is_deleted' => 0,
                'list_id' => $check_dontForgetSection->list_id
            ])->count();
            if ($check_number_of_task <= 1) {
                return true;
            }
            if ($taskDontForget) {
                $check_child = Task::on($this->db_name)->where('parent_id', $taskDontForget->id)->count();
                if ($check_child <= 0) {
                    Task::on($this->db_name)->findOrFail($taskDontForget->id)->delete();
                }
            }
        }

        $childrens = Task::on($this->db_name)->where('parent_id', $id)->get();
        if ($childrens->count() > 0) {
            foreach ($childrens as $children) {
                $this->deleteTaskWithChild($children->id);
            }
        }
    }

    public function deleteImg (Request $request)
    {
        if (isset($request->img) && isset($request->id)) {
            $task_id = $request->id;//we need a check to make sure this is set.
            $delete = Files::on($this->db_name)->where('file_name', $request->img)->delete();
            if ($delete) {
                $image_path = public_path() . "/storage/" . $task_id . "/" . $request->img;  // Value is not URL but directory file path
                if (file_exists($image_path)) {
                    $del = unlink($image_path);
                    $task = Task::on($this->db_name)->find($task_id);
                    return response()->json(['success' => $del, 'task' => $task , 'user' =>  Auth::id()]);
                }
            }
        } else {
            return response()->json(['success' => 0]);
        }
    }

    public function moveTask (Request $request)
    {
        if ($request->type == 'up') {
            if ($request->sort_id <= 0) {
                return false;
            }
            $pre_task = Task::on($this->db_name)->where(['parent_id' => $request->parent_id])
                ->where('sort_id', '<', $request->sort_id)
                ->where('project_id', $request->project_id)
                ->where('list_id', $request->list_id)
                ->orderBy('sort_id', 'desc')->first();

            if (!empty($pre_task)) {
                $pre_sort_id = $pre_task->sort_id;
                Task::on($this->db_name)->where('id', $pre_task->id)->update(['sort_id' => $request->sort_id]);
                Task::on($this->db_name)->where('id', $request->id)->update(['sort_id' => $pre_sort_id]);
            }
        } else {
            if ($request->type == 'down') {
                if ($request->sort_id < 0) {
                    return false;
                }
                $pre_task = Task::on($this->db_name)->where(['parent_id' => $request->parent_id])
                    ->where('sort_id', '>', $request->sort_id)
                    ->where('project_id', $request->project_id)
                    ->where('list_id', $request->list_id)
                    ->orderBy('sort_id', 'asc')->first();
                if (!empty($pre_task)) {
                    $pre_sort_id = $pre_task->sort_id;
                    Task::on($this->db_name)->where('id', $request->id)->update(['sort_id' => $pre_sort_id]);
                    Task::on($this->db_name)->where('id', $pre_task->id)->update(['sort_id' => $request->sort_id]);
                }
            }
        }

        return response()->json($pre_task);
    }

    public function taskDragDrop (Request $request)
    {
        $id = $request->id;
        $parent_id = $request->parent_id;
        $pre_sort = (isset($request->pre_sort) && $request->pre_sort > 0) ? $request->pre_sort : 0;
        $child_length = Task::on($this->db_name)->where('parent_id', $parent_id)->count();
        if ($child_length > 0) {
            Task::on($this->db_name)->where('parent_id', $parent_id)->where('sort_id', '>', $pre_sort)->increment('sort_id');
        }
        Task::on($this->db_name)->where('id', $id)->update(['parent_id' => $parent_id, 'sort_id' => $pre_sort + 1]);
        $this->updateTagWithDataMove($id, $parent_id);
        return response()->json(['success' => 1]);
    }

    public function deleteEmptyTask (Request $request)
    {
        $response  = Task::on($this->db_name)->where('id', $request->id)->delete();
        if ($response) {
            return response()->json(['success' => 0, 'id' => $request->id]);
        } else {
            return response()->json(['success' => 1, 'id' => $request->id]);
        }
    }

    public function update (Request $request)
    {
        $task_id = $request->id;
        if (isset($request->details)) {
            if (Task::on($this->db_name)->where('id', $request->id)->update(['description' => $request->details])) {
                $users = $this->addNotification($request->id, 'Update a task description you are assigned !');
                return response()->json(['status' => 'success', 'users' => $users]);
            }
        } elseif (isset($request->complete)) {
            $find = Task::on($this->db_name)->find($request->id);
            if ($find->board_parent_id != null) {
                $find2 = Task::on($this->db_name)->find($find->board_parent_id);
                $board_parent = Task::on($this->db_name)->where(['board_parent_id' => 0, 'progress' => 100, 'multiple_board_id' => $find2->multiple_board_id])->first();
                if ($board_parent) {
                    $moveToData = Rules::on($this->db_name)->where('move_from', $board_parent->id)->where('status', 1)->with('moveTo')->first();
                    if ($moveToData) {
                        if ($moveToData->move_to != 0) {
                            $dataUpdate = [
                                'progress' => $moveToData->moveTo->progress,
                                'board_parent_id' => $moveToData->moveTo->id,
                                'multiple_board_id' => $moveToData->moveTo->multiple_board_id
                            ];
                        }
                        $assiagnUser = json_decode($moveToData->assigned_users);
                        if (count($assiagnUser) > 0) {
                            foreach ($assiagnUser as $keys => $_user) {
                                AssignedUser::on($this->db_name)->create([
                                    'user_id' => $_user,
                                    'task_id' => $task_id,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id()
                                ]);
                            }
                        }

                    } else {
                        $dataUpdate = [
                            'progress' => $board_parent->progress,
                            'board_parent_id' => $board_parent->id,
                            'multiple_board_id' => $board_parent->multiple_board_id
                        ];
                    }
                    Task::on($this->db_name)->where('id', $request->id)->update($dataUpdate);
                    $this->UpdateTaskWithChild($request->id,100,$board_parent->id);
                    $users = $this->addNotification($request->id, 'Update a task to complete that you are assigned on!');
                    return response()->json(['status' => 1, 'users' => $users]);
                } else {
                    return response()->json(['status' => 2]);
                }
            } else {
                if ($find->progress == 100) {
                    Task::on($this->db_name)->where('id', $request->id)->update(['progress' => null]);
                    $this->UpdateTaskWithChild($request->id);
                    return response()->json(['status' => 0]);
                } else {
                    Task::on($this->db_name)->where('id', $request->id)->update(['progress' => 100]);
                    $this->UpdateTaskWithChild($request->id,100);
                    return response()->json(['status' => 3]);
                }
            }
        } elseif (isset($request->date)) {
            $d = $request->date;
            $tz = $request->tz;

            $date = Carbon::parse($d, $tz)->setTimezone('UTC');
            if (Task::on($this->db_name)->where('id', $request->id)->update(['date' => $date])) {
                $task = Task::on($this->db_name)->where('id', $request->id)->first();
                $mailData = [
                    'subject' => "A task date updated",
                    'body' => "A task (" . $task->title . ") date is updated to ( " . ($date ?? '') . " ), that you are assigned on",
                    'email' => "email_taskUpdated",
                    'generalBody' => "A task (" . $task->title . ") date is updated to ( " . ($date ?? '') . " ).",
                    'task_id' => $request->id
                ];
                $this->HomeController->userMail((object)$mailData);
                $users = $this->addNotification($request->id, 'Update a dua date on a task that you are assigned on!');
                return response()->json(['status' => 'success', 'users' => $users]);
            }
        } elseif (isset($request->text)) {
            $check_is_empty = Task::on($this->db_name)->where('id', $request->id)->first();
            if (empty($check_is_empty->title)) {
                $empty = true;
            } else {
                $empty = false;
            }
            ($request->text == null) ? $title = '' : $title = $request->text;

            if ($check_is_empty->title == $request->text) {
                return response()->json(['success' => 20, 'empty' => 'not change']);
            }

            if (Task::on($this->db_name)->where('id', $request->id)->update(['title' => $title])) {
                $users = [];
                if ($empty) {
                    $this->createLog($request->id, 'updated', 'Update From empty task', $title);
                } else {
                    $this->createLog($request->id, 'updated', 'Update task', $title);
                    $users = $this->addNotification($request->id, 'Update a task title on a task that you are assigned on!');
                }
                $mailData = [
                    'subject' => "A task title updated",
                    'body' => "A task (" . $title . ") title is updated to ( " . ($title ?? '') . " ) that you are assigned on",
                    'email' => "email_taskUpdated",
                    'generalBody' => "A task (" . $title . ") title is updated to ( " . ($title ?? '') . " )",
                    'task_id' => $request->id
                ];
                $this->HomeController->userMail((object)$mailData);
                return response()->json(['success' => 1, 'empty' => $empty, 'users' => $users]);
            } else {
                return response()->json(['success' => 0, 'empty' => $empty]);
            }
        } elseif (isset($request->open)) {
            if (Task::on($this->db_name)->where('id', $request->id)->update(['open' => $request->open])) {
                return response()->json('success', 200);
            }
        } elseif (isset($request->file)) {
            $task_id = $request->id;
            $photo = $_FILES['file']['name'];
            $path = public_path() . "/storage/" . $task_id;
            if (!is_dir($path)) {
                if (!is_dir(public_path() . "/storage")) {
                    mkdir(public_path() . "/storage/");
                }
                mkdir($path);
            }
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $path . "/" . $_FILES['file']['name'])) {
                $file = [
                    'file_name' => $photo,
                    'tasks_id' => $task_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => Carbon::now()
                ];
                Files::on($this->db_name)->create($file);
                $users = $this->addNotification($request->id, 'Added file on a task that you are assigned on!');
                return response()->json(['status' => 'success', 'users' => $users]);
            } else {
                return response()->json('failed', 500);
            }
        }
    }

    public function addNotification ($task_id, $notification_body, $action_url = null)
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

    public function AddOrUpdateFilter ($filter)
    {
        $user_id = Auth::id();
        try {
            UserFilter::on($this->db_name)->updateOrCreate(['user_id' => $user_id], ['user_id' => $user_id, 'filter_type' => $filter]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function UpdateTaskWithChild ($id,$proress = null,$board_parent = null)
    {
        $team_id = Auth::user()->current_team_id;
        $this->db_name = DBConnection::$db_name . $team_id;
        if ($board_parent != null) {
            $data = [
                'board_parent_id' => $board_parent,
                'progress' => $proress
            ];
        } else {
            $data = [
                'progress' => $proress
            ];
        }
        Task::on($this->db_name)->where(['id' => $id, 'is_deleted' => 0])->update($data);

        $childrens = Task::on($this->db_name)->where(['parent_id' => $id, 'is_deleted' => 0])->get();
        if ($childrens->count() > 0) {
            foreach ($childrens as $children) {
                $this->UpdateTaskWithChild($children->id,$proress,$board_parent);
            }
        }
    }

}
