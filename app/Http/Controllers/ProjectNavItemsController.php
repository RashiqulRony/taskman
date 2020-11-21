<?php

namespace App\Http\Controllers;

use App\ActionLog;
use App\AssignedUser;
use App\AssignTag;
use App\Comment;
use App\Files;
use App\Multiple_board;
use App\Multiple_list;
use App\ProjectNavItems;
use App\Project;
use App\ProjectUser;
use App\Rules;
use App\Tags;
use App\Task;
use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;
use Validator;

class ProjectNavItemsController extends Controller
{
    protected $actionLog;
    protected $dont_forget_tag;
    protected $ProjectNavItems;
    protected $Multiple_list;
    protected $Multiple_board;
    protected $Task;
    protected $Rules;
    protected $Project;
    protected $ProjectUser;
    protected $db_name;

    public function __construct()
    {
        $this->dont_forget_tag = 'Dont Forget';

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $team_id = $this->user->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB

            return $next($request);
        });

        $this->middleware('auth');
    }

    public function index($project_id)
    {
        $navItem = [];
        $team_id = Auth::user()->current_team_id;
        $db_name = DBConnection::$db_name . $team_id;
        $this->db_name = $db_name;
        $nav = ProjectNavItems::on($db_name)->with(['project'])->where('project_id', $project_id)->orderBy('sort_id', 'asc')->get();
        foreach ($nav as $item) {
            $nav_ = $this->getList($project_id, $item->id, $item->type);
            $item->lists = $nav_[0];
            $navItem[] = $item;
        }
        $projects = Project::on($this->db_name)->where(['id' => $project_id, 'created_by' => Auth::id()])->first();
        $project_user = ProjectUser::on($this->db_name)->where(['project_id' => $project_id, 'user_id' => Auth::id()])->first();

        if ($projects || $project_user) {
            return response()->json(['success' => $navItem, 'rules' => [], 'redierct' => false]);
        } else {
            return response()->json(['success' => $navItem, 'rules' => [], 'redierct' => true]);
        }
    }

    public function GetBoardsAndColumn($project_id)
    {
        $boards = [];
        $navs = ProjectNavItems::on($this->db_name)->where(['project_id' => $project_id, 'type' => 'board'])->orderBy('sort_id', 'asc')->get();
        if ($navs->count() > 0) {
            foreach ($navs as $item) {
                $nav = Multiple_board::on($this->db_name)->where(['project_id' => (int)$project_id, 'nav_id' => $item->id])->get()->toArray();
                foreach ($nav as $item1) {
                    $boards[] = $item1;
                }
            }
            foreach ($boards as $key => $board) {
                $boards[$key]['columns'] = Task::on($this->db_name)->where('board_parent_id', 0)->where('multiple_board_id', $board['id'])->get();
            }
        }
        $team_id = Auth::user()->current_team_id;

        $project = Project::on($this->db_name)->where('id', $project_id)->first();
        $user_ids = ProjectUser::on($this->db_name)->where(['project_id' => $project_id])->pluck('user_id');
        $allProjectUsers = User::whereIn('id', $user_ids)->orWhere('id', $project->created_by)->get()->toArray();

        $rules = Rules::on($this->db_name)->where('project_id', (int)$project_id)->with('move_from')->with('move_to')->get();
        foreach ($rules as $rule) {
            $us = [];
            foreach (json_decode($rule->assigned_users) as $item) {
                if ((int)$item !== 0 && !empty($allProjectUsers)) {
                    foreach ($allProjectUsers as $item1) {
                        if ((int)$item == $item1['id']) {
                            $uin['id'] = $item1['id'];
                            $uin['name'] = $item1['name'];
                            $us[] = $uin;
                        }
                    }
                }
            }
            $rule->assigned_users = $us;
        }
        return response()->json(['status' => 'success', 'data' => $boards, 'users' => $allProjectUsers, 'rules' => $rules]);
    }

    public function getList($project_id, $nav_id, $type)
    {
        $team_id = Auth::user()->current_team_id;
        $this->db_name = DBConnection::$db_name . $team_id;
        $nav = ProjectNavItems::on($this->db_name)->where('id', $nav_id)->first();
        $type = $nav->type;
        if ($type == 'list') {
            $list = Multiple_list::on($this->db_name)->where(['project_id' => (int)$project_id, 'nav_id' => $nav_id])->get();
            $list->type = $type;
            return [$list, $type];
        } else {
            $board = Multiple_board::on($this->db_name)->where(['project_id' => (int)$project_id, 'nav_id' => $nav_id])->get();
            $board->type = $type;
            return [$board, $type];
        }
    }

    public function store(Request $request)
    {
        $rqsData = $request->all();
        $validator = Validator::make($rqsData, [
            'title' => ['required', 'string', 'max:255'],
            'type'  => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'error' => $validator->errors()
            ], 200);
        }

        $data = [
            'project_id' => $request->project_id,
            'title' => $request->title,
            'type' => $request->type,
            'sort_id' => $request->sort_id,
            'created_at' => Carbon::now(),
        ];
        $check = ProjectNavItems::on($this->db_name)->where('title', $request->title)->where('project_id', $request->project_id)->count();
        if ($check <= 0) {
            $nav = ProjectNavItems::on($this->db_name)->create($data);
            $this->createLog($nav->id, 'created', 'Navbar Create', $request->title);
            return response()->json(['success' => $nav, 'status' => 'create']);
        } else {
            return response()->json(['success' => 'This title is already taken !', 'status' => 'exists']);
        }

    }

    public function createLog($nav_id, $type, $message, $title)
    {
        $nav = ProjectNavItems::on($this->db_name)->where('id', $nav_id)->first();
        $log_data = [
            'project_id' => $nav->project_id,
            'nav_id' => $nav_id,
            'title' => $title,
            'log_type' => $message,
            'action_type' => $type,
            'action_by' => Auth::id(),
            'action_at' => Carbon::now()
        ];
        ActionLog::on($this->db_name)->create($log_data);
    }

    public function navList(Request $request)
    {
        // return $request->all();
        $nav = Multiple_list::on($this->db_name)->where('project_id', $request->projectId)->where('nav_id', $request->navId)->where('is_delete', 0)->get();

        return response()->json(['success' => $nav]);
    }

    public function boardList(Request $request)
    {
        // return $request->all();
        $nav = Multiple_board::on($this->db_name)->where('project_id', $request->projectId)->where('nav_id', $request->boardId)->get();

        return response()->json(['success' => $nav]);
    }

    public function multipleList(Request $request)
    {
        $nav = $this->getList($request->projectId, $request->listId, $request->type);
        return response()->json(['success' => $nav[0], 'type' => $nav[1]]);
    }

    public function edit(Request $request)
    {
        $rqsData = $request->all();
        $validator = Validator::make($rqsData, [
            'title' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'error' => $validator->errors()
            ], 200);
        }

        $check = ProjectNavItems::on($this->db_name)->where('title', $request->title)->where('project_id', $request->project_id)->count();
        if ($check <= 1) {
            $nab = ProjectNavItems::on($this->db_name)->findOrFail($request->nav_id);

            if ($nab->sort_id > $request->sort_id) {
                ProjectNavItems::on($this->db_name)->where('project_id', $request->project_id)
                    ->where('sort_id', '<', $nab->sort_id)
                    ->where('sort_id', '>=', $request->sort_id)
                    ->increment('sort_id');
            } else {
                if ($nab->sort_id < $request->sort_id) {
                    ProjectNavItems::on($this->db_name)->where('project_id', $request->project_id)
                        ->where('sort_id', '>', $nab->sort_id)
                        ->where('sort_id', '<=', $request->sort_id)
                        ->decrement('sort_id');
                }
            }
            $data = [
                'title' => $request->title,
                'sort_id' => $request->sort_id,
                'updated_at' => Carbon::now(),
            ];
            $nav = ProjectNavItems::on($this->db_name)->where('id', $request->nav_id)->update($data);
            $this->createLog($request->nav_id, 'updated', 'Navbar updated', $request->title);
            return response()->json(['status' => 200, 'success' => $request->nav_id]);
        } else {
            return response()->json(['status' => 404, 'error' => 'This title is already taken !']);
        }
    }

    public function Delete(Request $request)
    {
        $check = $this->DeleteNavWithAllData($request->nav_id);
        if ($check) {
            return response()->json(['status' => 200, 'type' => 'list']);
        } else {
            return response()->json(['status' => 404, 'error' => 'This title is already taken !']);
        }
    }

    public function boardListMove(Request $request)
    {
        $id = (int)$request->id;
        $type = $request->type;
        $nav_id = $request->target;

        if ($type == 'list') {
            Multiple_list::on($this->db_name)->where('id', $id)->update(['nav_id' => $nav_id]);
            $list = Multiple_list::on($this->db_name)->where('id', $id)->first();
        } else {
            Multiple_board::on($this->db_name)->where('id', $id)->update(['nav_id' => $nav_id]);
            $list = Multiple_board::on($this->db_name)->where('id', $id)->first();
        }

        return response()->json(['success' => 1, 'data' => $list]);

    }

    public function moveSelectedTask(Request $request)
    {
        $ids = $request->ids;

        $task_find = Task::on($this->db_name)->where('id', $ids[0])->first();
        $taskDontForgetSection = Task::on($this->db_name)->where([
            'title' => 'Dont Forget Section',
            'project_id' => $task_find->project_id,
            'list_id' => $task_find->list_id,
        ])->first();
        $tag_ids = $tags = Tags::on($this->db_name)->where(['title' => $this->dont_forget_tag, 'team_id' => Auth::user()->current_team_id])->first();

        $target_nav_id = $request->nav;
        $target_listOrBoard = $request->target;
        $nav = ProjectNavItems::on($this->db_name)->where('id', $target_nav_id)->first();
        if ($nav->type == 'board') {

            foreach ($ids as $id) {
                $task_child = Task::on($this->db_name)->where(['multiple_board_id' => $target_listOrBoard, 'board_parent_id' => $request->column_id])->orderBy('board_sort_id', 'desc')->first();
                $column = Task::on($this->db_name)->where(['id' => $request->column_id])->first();

                if ($task_child) {
                    $board_sort_id = $task_child->board_sort_id;
                } else {
                    $board_sort_id = 0;
                }
                Task::on($this->db_name)->where('id', $id)
                    ->update(['multiple_board_id' => $target_listOrBoard, 'board_parent_id' => $request->column_id, 'board_sort_id' => $board_sort_id + 1, 'progress' => $column->progress]);
                $this->applyRulesWithMove($request->column_id, $id);
            }
        } elseif ($nav->type == 'list') {
            $task = Task::on($this->db_name)->where(['list_id' => $target_listOrBoard, 'parent_id' => 0])->orderBy('sort_id', 'desc')->first();
            $sort_id = $task->sort_id + 1;
            foreach ($ids as $id) {
                $updateList = Task::on($this->db_name)->where('id', $id)->first();
                if ($updateList->parent_id == 0) {
                    Task::on($this->db_name)->where('id', $id)->update(['list_id' => $target_listOrBoard, 'parent_id' => 0, 'sort_id' => $sort_id]);
                } else {
                    Task::on($this->db_name)->where('id', $id)->update(['list_id' => $target_listOrBoard, 'sort_id' => $sort_id]);
                }

                $childs = Task::on($this->db_name)->where('parent_id', $id)->get();
                $this->moveWithChild($childs, $target_listOrBoard);
                $sort_id++;
            }
        }

        if ($taskDontForgetSection) {
            $childrenOfDontForgetSection = Task::on($this->db_name)->where('parent_id', $taskDontForgetSection->id)
                ->where('is_deleted', 0)->get()->toArray();

            if (count($childrenOfDontForgetSection) <= 0) {
                AssignedUser::on($this->db_name)->where('task_id', $taskDontForgetSection->id)->delete();
                AssignTag::on($this->db_name)->where(['task_id' => $taskDontForgetSection->id, 'tag_id' => $tag_ids->id])->delete();
                Task::on($this->db_name)->where('id', $taskDontForgetSection->id)->delete();
            }
        }

        return response()->json(['status' => 'success', $nav->type]);

    }

    public function moveWithChild($childs, $list_id)
    {
        foreach ($childs as $child) {
            Task::on($this->db_name)->where('id', $child->id)->update(['list_id' => $list_id]);
            $childrens = Task::on($this->db_name)->where('parent_id', $child->id)->get();
            if ($childrens->count() > 0) {
                $this->moveWithChild($childrens, $list_id);
            }
        }
    }

    public function DeleteNavWithAllData($nav_id)
    {
        $check = ProjectNavItems::on($this->db_name)->where(['id' => $nav_id])->first();
        if ($check) {
            if ($check->type == "list") {
                $all_list = Multiple_list::on($this->db_name)->where('nav_id', $nav_id)->pluck('id');
                $all_taskId = Task::on($this->db_name)->whereIn('list_id', $all_list)->pluck('id');
                DB::beginTransaction();
                try {
                    Files::on($this->db_name)->whereIn('tasks_id', $all_taskId)->delete();
                    AssignedUser::on($this->db_name)->whereIn('task_id', $all_taskId)->delete();
                    Comment::on($this->db_name)->whereIn('task_id', $all_taskId)->delete();
                    AssignTag::on($this->db_name)->whereIn('task_id', $all_taskId)->delete();
                    Task::on($this->db_name)->whereIn('list_id', $all_list)->delete();
                    Multiple_list::on($this->db_name)->where('nav_id', $nav_id)->delete();
                    ProjectNavItems::on($this->db_name)->where(['id' => $nav_id])->delete();
                    DB::commit();
                    return true;
                } catch (\Exception $e) {
                    DB::rollback();
                }
            } elseif ($check->type == "board") {
                $all_list = Multiple_board::on($this->db_name)->where('nav_id', $nav_id)->pluck('id');
                $all_taskId = Task::on($this->db_name)->whereIn('multiple_board_id', $all_list)->pluck('id');
                DB::beginTransaction();
                try {
                    AssignedUser::on($this->db_name)->whereIn('task_id', $all_taskId)->delete();
                    Files::on($this->db_name)->whereIn('tasks_id', $all_taskId)->delete();
                    Comment::on($this->db_name)->whereIn('task_id', $all_taskId)->delete();
                    AssignTag::on($this->db_name)->whereIn('task_id', $all_taskId)->delete();
                    Task::on($this->db_name)->whereIn('multiple_board_id', $all_list)->delete();
                    Multiple_board::on($this->db_name)->where('nav_id', $nav_id)->delete();
                    ProjectNavItems::on($this->db_name)->where(['id' => $nav_id])->delete();
                    DB::commit();
                    return true;
                } catch (\Exception $e) {
                    DB::rollback();
                }
            }
        } else {
            return false;
        }
    }

    public function applyRulesWithMove($target_column, $task_id)
    {
        $moveToData = Rules::on($this->db_name)->where('move_from', $target_column)->where('status', 1)->with('moveTo')->first();
        if ($moveToData) {
            if ($moveToData->move_to != 0) {
                $dataUpdate = [
                    'progress' => $moveToData->moveTo->progress,
                    'board_parent_id' => $moveToData->moveTo->id,
                    'multiple_board_id' => $moveToData->moveTo->multiple_board_id
                ];
            }
            Task::on($this->db_name)->where('id', $task_id)->update($dataUpdate);
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
        }
    }
}
