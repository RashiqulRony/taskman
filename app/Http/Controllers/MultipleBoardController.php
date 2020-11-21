<?php

namespace App\Http\Controllers;

use App\ActionLog;
use App\Multiple_board;
use App\Tags;
use App\Files;
use App\Task;
use App\Rules;
use App\Project;
use App\User;
use App\Comment;
use App\AssignedUser;
use App\AssignTag;
use App\LinkListToColumn;
use App\UserFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;
use DB;
use mysql_xdevapi\Exception;
use Validator;


class MultipleBoardController extends Controller
{
    protected $actionLog;
    protected $HomeController;
    protected $dont_forget_tag;
    protected $totalChild = 0;
    protected $childIds = [];
    protected $parents = [];
    protected $db_name;

    public function __construct()
    {
        date_default_timezone_set('UTC');
        $this->actionLog = new ActionLogController;
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

    public function index(Request $request)
    {
        $user_id = Auth::id();
        $user_name = '';
        $filter = UserFilter::on($this->db_name)->where('user_id', $user_id)->first();
        if ($filter) {
            $felterData = [
                'type'      => $filter->filter_type,
                'users'     => [],
                'tz'        => $request->tz,
                'projectId' => $request->projectId,
                'board_id'  => $request->board_id,
            ];
            return $this->savedFilterData((object)$felterData);
        }
        $tz = $request->tz;
        $boards = [];
        $allTaskIds = [];
        $board = Task::on($this->db_name)->where('board_parent_id', 0)
            ->with(['moveToCol', 'task' => function ($q) {
                $q->with(['List']);
                $q->where('is_deleted', '!=', 1);
                $q->orderBy('board_sort_id', 'asc');
                $q->where(function ($q) {
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                });
            }, 'linkToList'])
            ->where('project_id', $request->projectId)
            ->where('is_deleted', '!=', 1)
            ->where('multiple_board_id', $request->board_id)
            ->orderby('board_sort_id', 'ASC')
            ->orderby('parent_id', 'ASC')
//            ->limit(5)
            ->get();
        $team_id = Auth::user()->current_team_id;
        $allUsers = User::join('team_users', 'team_users.user_id', 'users.id')
            ->where('team_users.team_id', $team_id)->get()->toArray();
        $allTags = Tags::on($this->db_name)->where('team_id', $team_id)->where('title', '!=', $this->dont_forget_tag)->orderBy('title', 'asc')->get()->toArray();
        foreach ($board as $key => $value) {
            $boards[$key]['id'] = $value['id'];
            $boards[$key]['parent_id'] = $value['parent_id'];
            $boards[$key]['column'] = $value['title'];
            $boards[$key]['hidden'] = $value['hidden'];
            $boards[$key]['progress'] = $value['progress'];
            $boards[$key]['linkToList'] = $value['linkToList'];
            $boards[$key]['total_card'] = count($value['task']);
            $boards[$key]['color'] = $value['color'];
            if ($value['moveToCol'] != null) {
                $users = json_decode($value['moveToCol']['assigned_users']);
                if (count($users) > 0) {
                    foreach ($users as $user) {
                        $user = User::where('id', $user)->first();
                        $user_name .= $user->name . ', ';
                    }
                }
                $boards[$key]['users'] = '';
                $boards[$key]['moveToCol'] = true;
                $boards[$key]['ruleName'] = $value['moveToCol']['name'];
                $boards[$key]['status'] = $value['moveToCol']['status'];
                if ($value['moveToCol']['move_to'] != 0) {
                    $boards[$key]['type'] = 'mvCard';
                    $boards[$key]['boardName'] = $value['moveToCol']['moveTo']['multipleBord']['board_title'];
                    $boards[$key]['colName'] = $value['moveToCol']['moveTo']['title'];
                } else {
                    $boards[$key]['moveToCol'] = true;
                    $boards[$key]['type'] = 'asnUser';
                    $boards[$key]['boardName'] = '';
                    $boards[$key]['colName'] = '';
                    $boards[$key]['users'] = $user_name;
                }
            } else {
                $boards[$key]['moveToCol'] = false;
                $boards[$key]['ruleName'] = '';
                $boards[$key]['boardName'] = '';
                $boards[$key]['colName'] = '';
            }
            if (!empty($value['task']) && count($value['task']) > 0) {
                foreach ($value['task'] as $keys => $values) {
                    if ($keys <= 3) {
                        $tagTooltip = '';
                        $allTaskIds[] = $values['id'];
                        $tags = [];
                        if (!empty($values['Assign_tags']) && count($values['Assign_tags']) > 0) {
                            foreach ($values['Assign_tags'] as $tagkey => $tag) {
                                if (!empty($tag->tag)) {
                                    $infoTags = array(
                                        'assign_id' => $tag->id,
                                        'id' => $tag->tag->id,
                                        'board_id' => $tag->task_id,
                                        'text' => $tag->tag->title,
                                        'classes' => '',
                                        'style' => 'background-color: ' . $tag->tag->color,
                                        'color' => $tag->tag->color,
                                    );
                                    $tagTooltip .= '#' . $tag->tag->title . ' ';
                                    $tags[$tagkey] = $infoTags;
                                }
                            }
                        }
                        $boards[$key]['task'][$keys]['assigned_user'] = $this->AssignUser($values['id']);
                        $assigned_user_ids = [];
                        foreach ($boards[$key]['task'][$keys]['assigned_user'] as $k => $id) {
                            $assigned_user_ids[] = $id['id'];
                            $boards[$key]['task'][$keys]['assigned_user'][$k]['photo_url'] = ($id['photo_url']) ?? 'https://www.gravatar.com/avatar/41f92c0a271983ac380e5bb06999e59d.jpg?s=200&d=mm';
                        }
                        $boards[$key]['task'][$keys]['assigned_user_ids'] = $assigned_user_ids;
                        $boards[$key]['task'][$keys]['users'] = $allUsers;
                        $boards[$key]['task'][$keys]['tags'] = $tags;
                        $boards[$key]['task'][$keys]['tagTooltip'] = $tagTooltip;

                        if ($values['childTask'] !== null) {
                            $this->totalChild = 0;
                            $boards[$key]['task'][$keys]['child'] = $this->recurChild($values['childTask']);

                        } else {
                            $boards[$key]['task'][$keys]['child'] = 0;
                        }
                        $boards[$key]['task'][$keys]['userName'] = Auth::user()->name;
                        $boards[$key]['task'][$keys]['comment'] = Comment::on($this->db_name)->where('task_id', $values['id'])->where('user_id', Auth::id())->get();
                        $boards[$key]['task'][$keys]['children'] = $values['childTask'];
                        $boards[$key]['task'][$keys]['parents'] = $values['parents'];
                        $boards[$key]['task'][$keys]['id'] = $values['id'];
                        $boards[$key]['task'][$keys]['parent_id'] = $values['parent_id'];
                        $boards[$key]['task'][$keys]['name'] = $values['title'];
                        $boards[$key]['task'][$keys]['list'] = $values['List'];
                        $boards[$key]['task'][$keys]['cardOpen'] = $values['card_open'];
                        $boards[$key]['task'][$keys]['list_id'] = $values['list_id'];
                        $boards[$key]['task'][$keys]['multiple_board_id'] = $values['multiple_board_id'];
                        $boards[$key]['task'][$keys]['description'] = $values['description'];
                        $boards[$key]['task'][$keys]['textareaShow'] = ($values['title'] !== '') ? false : true;
                        $boards[$key]['task'][$keys]['progress'] = $values['progress'];
                        $boards[$key]['task'][$keys]['created_by'] = User::find($values['created_by']);

                        $boards[$key]['task'][$keys]['priority_label'] = null;
                        if ($values['priority_label'] == 3 || $values['priority_label'] == 'high') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'high';
                        } else if ($values['priority_label'] == 2 || $values['priority_label'] == 'medium') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'medium';
                        } else if ($values['priority_label'] == 1 || $values['priority_label'] == 'low') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'low';
                        }
                        if ($values['list_id'] != '') {
                            $boards[$key]['task'][$keys]['type'] = 'task';
                        } else {
                            $boards[$key]['task'][$keys]['type'] = 'card';
                        }
                        $date = Carbon::parse($values['date'], 'UTC')->setTimezone($tz);
                        $boards[$key]['task'][$keys]['date'] = ($values['date'] == '0000-00-00' || $values['date'] == '0000-00-00 00:00:00') ? null : date('d M Y', strtotime($date));
                        $boards[$key]['task'][$keys]['existing_tags'] = $allTags;
                    }
                }
            } else {
                $boards[$key]['task'] = [];
            }
        }
        $board = Multiple_board::on($this->db_name)->find($request->board_id);
        return response()->json([
            'success' => $boards,
            'allUsers' => $allUsers,
            'allTags' => $allTags,
            'allCardIds' => $allTaskIds,
            'board' => $board
        ]);
    }

    public function GetAllBoardCard(Request $request)
    {
        $tz = $request->tz;
        $user_name = '';
        $boards = [];
        $allTaskIds = [];
        $board = Task::on($this->db_name)->where('board_parent_id', 0)
            ->with(['moveToCol', 'task' => function ($q) {
                $q->with(['List']);
                $q->where('is_deleted', '!=', 1);
                $q->orderBy('board_sort_id', 'asc');
                $q->where(function ($q) {
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                });
            }, 'linkToList'])
            ->where('project_id', $request->projectId)
            ->where('is_deleted', '!=', 1)
            ->where('multiple_board_id', $request->board_id)
            ->orderby('board_sort_id', 'ASC')
            ->orderby('parent_id', 'ASC')
            ->get();
        $team_id = Auth::user()->current_team_id;
        $allUsers = User::join('team_users', 'team_users.user_id', 'users.id')
            ->where('team_users.team_id', $team_id)->get()->toArray();
        $allTags = Tags::on($this->db_name)->where('team_id', $team_id)->where('title', '!=', $this->dont_forget_tag)->orderBy('title', 'asc')->get()->toArray();
        foreach ($board as $key => $value) {
            $boards[$key]['id']         = $value['id'];
            $boards[$key]['parent_id']  = $value['parent_id'];
            $boards[$key]['column']     = $value['title'];
            $boards[$key]['hidden']     = $value['hidden'];
            $boards[$key]['progress']   = $value['progress'];
            $boards[$key]['linkToList'] = $value['linkToList'];
            $boards[$key]['total_card'] = count($value['task']);
            $boards[$key]['color']      = $value['color'];
            if ($value['moveToCol']     != null) {
                $users = json_decode($value['moveToCol']['assigned_users']);
                if (count($users) > 0) {
                    foreach ($users as $user) {
                        $user = User::where('id', $user)->first();
                        $user_name .= $user->name . ', ';
                    }
                }
                $boards[$key]['users']      = '';
                $boards[$key]['moveToCol']  = true;
                $boards[$key]['ruleName']   = $value['moveToCol']['name'];
                $boards[$key]['status']     = $value['moveToCol']['status'];
                if ($value['moveToCol']['move_to'] != 0) {
                    $boards[$key]['type']       = 'mvCard';
                    $boards[$key]['boardName']  = $value['moveToCol']['moveTo']['multipleBord']['board_title'];
                    $boards[$key]['colName']    = $value['moveToCol']['moveTo']['title'];
                } else {
                    $boards[$key]['moveToCol']  = true;
                    $boards[$key]['type']       = 'asnUser';
                    $boards[$key]['boardName']  = '';
                    $boards[$key]['colName']    = '';
                    $boards[$key]['users']      = $user_name;
                }
            } else {
                $boards[$key]['moveToCol']  = false;
                $boards[$key]['ruleName']   = '';
                $boards[$key]['boardName']  = '';
                $boards[$key]['colName']    = '';
            }
            if (!empty($value['task']) && count($value['task']) > 0) {
                foreach ($value['task'] as $keys => $values) {
                    $tagTooltip = '';
                    $allTaskIds[] = $values['id'];
                    $tags = [];
                    if (!empty($values['Assign_tags']) && count($values['Assign_tags']) > 0) {
                        foreach ($values['Assign_tags'] as $tagkey => $tag) {
                            if (!empty($tag->tag)) {
                                $infoTags = array(
                                    'assign_id' => $tag->id,
                                    'id'        => $tag->tag->id,
                                    'board_id'  => $tag->task_id,
                                    'text'      => $tag->tag->title,
                                    'classes'   => '',
                                    'style'     => 'background-color: ' . $tag->tag->color,
                                    'color'     => $tag->tag->color,
                                );
                                $tagTooltip .= '#' . $tag->tag->title . ' ';
                                $tags[$tagkey] = $infoTags;
                            }
                        }
                    }
                    $boards[$key]['task'][$keys]['assigned_user'] = $this->AssignUser($values['id']);
                    $assigned_user_ids = [];
                    foreach ($boards[$key]['task'][$keys]['assigned_user'] as $k => $id) {
                        $assigned_user_ids[] = $id['id'];
                        $boards[$key]['task'][$keys]['assigned_user'][$k]['photo_url'] = ($id['photo_url']) ?? 'https://www.gravatar.com/avatar/41f92c0a271983ac380e5bb06999e59d.jpg?s=200&d=mm';
                    }
                    $boards[$key]['task'][$keys]['assigned_user_ids'] = $assigned_user_ids;
                    $boards[$key]['task'][$keys]['users'] = $allUsers;
                    $boards[$key]['task'][$keys]['tags'] = $tags;
                    $boards[$key]['task'][$keys]['tagTooltip'] = $tagTooltip;

                    if ($values['childTask'] !== null) {
                        $this->totalChild = 0;
                        $boards[$key]['task'][$keys]['child'] = $this->recurChild($values['childTask']);

                    } else {
                        $boards[$key]['task'][$keys]['child'] = 0;
                    }
                    $boards[$key]['task'][$keys]['userName']            = Auth::user()->name;
                    $boards[$key]['task'][$keys]['comment']             = Comment::on($this->db_name)->where('task_id', $values['id'])->where('user_id', Auth::id())->get();
                    $boards[$key]['task'][$keys]['children']            = $values['childTask'];
                    $boards[$key]['task'][$keys]['parents']             = $values['parents'];
                    $boards[$key]['task'][$keys]['id']                  = $values['id'];
                    $boards[$key]['task'][$keys]['parent_id']           = $values['parent_id'];
                    $boards[$key]['task'][$keys]['name']                = $values['title'];
                    $boards[$key]['task'][$keys]['list']                = $values['List'];
                    $boards[$key]['task'][$keys]['cardOpen']            = $values['card_open'];
                    $boards[$key]['task'][$keys]['list_id']             = $values['list_id'];
                    $boards[$key]['task'][$keys]['multiple_board_id']   = $values['multiple_board_id'];
                    $boards[$key]['task'][$keys]['description']         = $values['description'];
                    $boards[$key]['task'][$keys]['textareaShow']        = ($values['title'] !== '') ? false : true;
                    $boards[$key]['task'][$keys]['progress']            = $values['progress'];
                    $boards[$key]['task'][$keys]['created_by']          = User::find($values['created_by']);

                    $boards[$key]['task'][$keys]['priority_label'] = null;
                    if ($values['priority_label'] == 3 || $values['priority_label'] == 'high') {
                        $boards[$key]['task'][$keys]['priority_label'] = 'high';
                    } else if ($values['priority_label'] == 2 || $values['priority_label'] == 'medium') {
                        $boards[$key]['task'][$keys]['priority_label'] = 'medium';
                    } else if ($values['priority_label'] == 1 || $values['priority_label'] == 'low') {
                        $boards[$key]['task'][$keys]['priority_label'] = 'low';
                    }
                    if ($values['list_id'] != '') {
                        $boards[$key]['task'][$keys]['type'] = 'task';
                    } else {
                        $boards[$key]['task'][$keys]['type'] = 'card';
                    }
                    $date = Carbon::parse($values['date'], 'UTC')->setTimezone($tz);
                    $boards[$key]['task'][$keys]['date'] = ($values['date'] == '0000-00-00' || $values['date'] == '0000-00-00 00:00:00') ? null : date('d M Y', strtotime($date));
                    $boards[$key]['task'][$keys]['existing_tags'] = $allTags;

                }
            } else {
                $boards[$key]['task'] = [];
            }
        }
        return response()->json([
            'success' => $boards,
            'allCardIds' => $allTaskIds,
        ]);
    }

    public function AssignUser($task_id)
    {
        $assign_users_ids = AssignedUser::on($this->db_name)->where('task_id', $task_id)->pluck('user_id');
        $user = User::whereIn('id', $assign_users_ids)->get()->toArray();
        return $user;
    }

    public function filter(Request $request)
    {
        $boards = [];
        $allTaskIds = [];
        $user_id = [Auth::user()->id];
        $filter_type = $request->type;
        $tz = $request->tz;
        $checkEmptyColumn = 0;

        if ($filter_type === "all") {
            UserFilter::on($this->db_name)->where(['user_id' => Auth::id()])->delete();
            return $this->index($request);
        }

        if ($filter_type === "my" || $filter_type === "users_task") {
            $user_id = [Auth::user()->id];
            if (count($request->users) > 0) {
                $user_id = $request->users;
            }
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'linkToList', 'taskFilter' => function ($q) use ($user_id) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->whereHas('Assign_user', function ($q) use ($user_id) {
                        $q->whereIn('user_id', $user_id);
                    });
                    $q->where(function ($q) {
                        $q->where('hidden', '!=', 1);
                        $q->orWhereNull('hidden');
                    });
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        } elseif ($filter_type === "not_assign") {
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'linkToList', 'taskFilter' => function ($q) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->whereDoesntHave('Assign_user');
                    $q->where(function ($q) {
                        $q->where('hidden', '!=', 1);
                        $q->orWhereNull('hidden');
                    });
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }])
                ->where('project_id', $request->projectId)
                ->where('multiple_board_id', $request->board_id)
                ->where('is_deleted', '!=', 1)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        } elseif ($filter_type == 'date') {
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('date', 'DESC');
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        } elseif ($filter_type == 'date-asc') {
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('date', 'ASC');
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        } elseif ($filter_type == 'asc' || $filter_type == 'desc') {
            $sorts = $filter_type;
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) use ($sorts) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('id', $sorts);
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        } elseif ($filter_type == 'priority') {
            $sorts = 'desc';
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) use ($sorts) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('priority_label', $sorts);
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        }

        if ($filter_type == 'p_hide') {
            $filter = $request->filter;
            if (in_array('0', $filter)) {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereNotIn('priority_label', $filter);
                        $q->orWhere('priority_label', null);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            } else {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereNotIn('priority_label', $filter);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            }
        }
        if ($filter_type == 'p_show') {
            $filter = $request->filter;
            if (in_array('0', $filter)) {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereIn('priority_label', $filter);
                        $q->orWhere('priority_label', null);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            } else {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereIn('priority_label', $filter);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            }
        }

        $team_id = Auth::user()->current_team_id;
        $allUsers = User::join('team_users', 'team_users.user_id', 'users.id')
            ->where('team_users.team_id', $team_id)->get()->toArray();
        $allTags = Tags::on($this->db_name)->where('team_id', $team_id)->where('title', '!=', $this->dont_forget_tag)->get()->toArray();
        foreach ($board as $key => $value) {
            $keys = -1;
            $boards[$key]['id']         = $value['id'];
            $boards[$key]['parent_id']  = $value['parent_id'];
            $boards[$key]['column']     = $value['title'];
            $boards[$key]['hidden']     = $value['hidden'];
            $boards[$key]['progress']   = $value['progress'];
            $boards[$key]['linkToList'] = $value['linkToList'];
            $boards[$key]['color']      = $value['color'];
            if ($value['moveToCol'] != null) {
                $users = json_decode($value['moveToCol']['assigned_users']);
                if (count($users) > 0) {
                    $user_name = '';
                    foreach ($users as $user) {
                        $user = User::where('id', $user)->first();
                        $user_name .= $user->name . ', ';
                    }
                }
                $boards[$key]['users']      = '';
                $boards[$key]['moveToCol']  = true;
                $boards[$key]['ruleName']   = $value['moveToCol']['name'];
                $boards[$key]['status']     = $value['moveToCol']['status'];
                if ($value['moveToCol']['move_to'] != 0) {
                    $boards[$key]['type']       = 'mvCard';
                    $boards[$key]['boardName']  = $value['moveToCol']['moveTo']['multipleBord']['board_title'];
                    $boards[$key]['colName']    = $value['moveToCol']['moveTo']['title'];
                } else {
                    $boards[$key]['moveToCol']  = true;
                    $boards[$key]['type']       = 'asnUser';
                    $boards[$key]['boardName']  = '';
                    $boards[$key]['colName']    = '';
                    $boards[$key]['users']      = $user_name;
                }
            } else {
                $boards[$key]['moveToCol']  = false;
                $boards[$key]['ruleName']   = '';
                $boards[$key]['boardName']  = '';
                $boards[$key]['colName']    = '';
            }
            if (!empty($value['taskFilter']) && count($value['taskFilter']) > 0) {
                foreach ($value['taskFilter'] as $keys => $values) {
                    if ($values['title'] !== 'Dont Forget Section') {
                        $checkEmptyColumn = 1;
                        $allTaskIds[] = $values['id'];
                        $tagTooltip = '';
                        $tags = [];
                        if (!empty($values['Assign_tags']) && count($values['Assign_tags']) > 0) {
                            foreach ($values['Assign_tags'] as $tagkey => $tag) {
                                if (!empty($tag->tag)) {
                                    $infoTags = array(
                                        'assign_id' => $tag->id,
                                        'id'        => $tag->tag->id,
                                        'board_id'  => $tag->task_id,
                                        'text'      => $tag->tag->title,
                                        'classes'   => '',
                                        'style'     => 'background-color: ' . $tag->tag->color,
                                        'color'     => $tag->tag->color,
                                    );
                                    $tagTooltip .= '#' . $tag->tag->title . ' ';
                                    $tags[$tagkey] = $infoTags;
                                }
                            }
                        }
                        $boards[$key]['task'][$keys]['assigned_user'] = $this->AssignUser($values['id']);
                        $assigned_user_ids = [];
                        foreach ($boards[$key]['task'][$keys]['assigned_user'] as $id) {
                            $assigned_user_ids[] = $id['id'];
                        }

                        $boards[$key]['task'][$keys]['assigned_user_ids'] = $assigned_user_ids;
                        $boards[$key]['task'][$keys]['users'] = $allUsers;


                        $boards[$key]['task'][$keys]['tags'] = $tags;
                        $boards[$key]['task'][$keys]['tagTooltip'] = $tagTooltip;

                        if ($values['childTask'] !== null) {
                            $this->totalChild = 0;
                            $boards[$key]['task'][$keys]['child'] = $this->recurChild($values['childTask']);

                        } else {
                            $boards[$key]['task'][$keys]['child'] = 0;
                        }

                        $boards[$key]['task'][$keys]['userName'] = Auth::user()->name;
                        $boards[$key]['task'][$keys]['comment'] = Comment::on($this->db_name)->where('task_id', $values['id'])->where('user_id', Auth::id())->get();
                        $boards[$key]['task'][$keys]['children'] = $values['childTask'];
                        $boards[$key]['task'][$keys]['parents'] = $values['parents'];
                        $boards[$key]['task'][$keys]['id'] = $values['id'];
                        $boards[$key]['task'][$keys]['parent_id'] = $values['parent_id'];
                        $boards[$key]['task'][$keys]['name'] = $values['title'];
                        $boards[$key]['task'][$keys]['list'] = $values['List'];
                        $boards[$key]['task'][$keys]['cardOpen'] = $values['card_open'];
                        $boards[$key]['task'][$keys]['list_id'] = $values['list_id'];
                        $boards[$key]['task'][$keys]['multiple_board_id'] = $values['multiple_board_id'];
                        $boards[$key]['task'][$keys]['description'] = $values['description'];
                        $boards[$key]['task'][$keys]['textareaShow'] = ($values['title'] !== '') ? false : true;
                        $boards[$key]['task'][$keys]['progress'] = $values['progress'];
                        $boards[$key]['task'][$keys]['created_by'] = User::find($values['created_by']);

                        $boards[$key]['task'][$keys]['priority_label'] = null;
                        if ($values['priority_label'] == 3 || $values['priority_label'] == 'high') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'high';
                        } else if ($values['priority_label'] == 2 || $values['priority_label'] == 'medium') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'medium';
                        } else if ($values['priority_label'] == 1 || $values['priority_label'] == 'low') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'low';
                        }

                        if ($values['list_id'] != '') {
                            $boards[$key]['task'][$keys]['type'] = 'task';
                        } else {
                            $boards[$key]['task'][$keys]['type'] = 'card';
                        }
                        $date = Carbon::parse($values['date'], 'UTC')->setTimezone($tz);
                        $boards[$key]['task'][$keys]['date'] = ($values['date'] == '0000-00-00') ? $date : date('d M Y', strtotime($date));
                        $boards[$key]['task'][$keys]['existing_tags'] = $allTags;
                    }
                }
            } else {
                $boards[$key]['task'] = [];
            }
        }
        if ($filter_type != 'all' && $filter_type != null && $filter_type != 'p_show' && $filter_type != 'p_hide') {
            $this->AddOrUpdateFilter($filter_type);
        }

        return response()->json(['success' => $boards, 'allUsers' => $allUsers, 'allTags' => $allTags, 'allCardIds' => $allTaskIds, 'checkEmptyColumn' => $checkEmptyColumn]);
    }

    public function savedFilterData($request)
    {
        $boards = [];
        $allTaskIds = [];
        $user_id = [Auth::user()->id];
        $filter_type = $request->type;
        $tz = $request->tz;
        $checkEmptyColumn = 0;
        if ($filter_type === "my") {
            $user_id = [Auth::user()->id];
        }
        if (count($request->users) > 0) {
            $user_id = $request->users;
        }
        if ($filter_type === "all") {
            UserFilter::on($this->db_name)->where(['user_id' => Auth::id()])->delete();
        }
        $board = Task::on($this->db_name)->where('board_parent_id', 0)
            ->with(['moveToCol', 'linkToList', 'taskFilter' => function ($q) use ($user_id) {
                $q->with(['List']);
                $q->where('is_deleted', '!=', 1);
                $q->whereHas('Assign_user', function ($q) use ($user_id) {
                    $q->whereIn('user_id', $user_id);
                });
                $q->where(function ($q) {
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                });
                $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
            }])
            ->where('project_id', $request->projectId)
            ->where('is_deleted', '!=', 1)
            ->where('multiple_board_id', $request->board_id)
            ->orderby('board_sort_id', 'ASC')
            ->orderby('parent_id', 'ASC')
            ->get();

        if ($filter_type === "not_assign") {
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'linkToList', 'taskFilter' => function ($q) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->whereDoesntHave('Assign_user');
                    $q->where(function ($q) {
                        $q->where('hidden', '!=', 1);
                        $q->orWhereNull('hidden');
                    });
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }])
                ->where('project_id', $request->projectId)
                ->where('multiple_board_id', $request->board_id)
                ->where('is_deleted', '!=', 1)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        }
        if ($filter_type == 'date') {
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('date', 'DESC');
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        }
        if ($filter_type == 'date-asc') {
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('date', 'ASC');
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        }
        if ($filter_type == 'asc' || $filter_type == 'desc') {
            $sorts = $filter_type;
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) use ($sorts) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('id', $sorts);
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        }
        if ($filter_type == 'priority') {
            $sorts = 'desc';
            $board = Task::on($this->db_name)->where('board_parent_id', 0)
                ->with(['moveToCol', 'taskFilter' => function ($q) use ($sorts) {
                    $q->with(['List']);
                    $q->where('is_deleted', '!=', 1);
                    $q->where('hidden', '!=', 1);
                    $q->orWhereNull('hidden');
                    $q->orderBy('priority_label', $sorts);
                    $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                }, 'linkToList'])
                ->where('project_id', $request->projectId)
                ->where('is_deleted', '!=', 1)
                ->where('multiple_board_id', $request->board_id)
                ->orderby('board_sort_id', 'ASC')
                ->orderby('parent_id', 'ASC')
                ->get();
        }
        if ($filter_type == 'p_hide') {
            $filter = $request->filter;
            if (in_array('0', $filter)) {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereNotIn('priority_label', $filter);
                        $q->orWhere('priority_label', null);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            } else {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereNotIn('priority_label', $filter);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            }
        }
        if ($filter_type == 'p_show') {
            $filter = $request->filter;
            if (in_array('0', $filter)) {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereIn('priority_label', $filter);
                        $q->orWhere('priority_label', null);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            } else {
                $board = Task::on($this->db_name)->where('board_parent_id', 0)
                    ->with(['moveToCol', 'taskFilter' => function ($q) use ($filter) {
                        $q->with(['List']);
                        $q->where('is_deleted', '!=', 1);
                        $q->where('hidden', '!=', 1);
                        $q->whereIn('priority_label', $filter);
                        $q->orWhereNull('hidden');
                        $q->orderBy('board_sort_id', 'ASC')->orderBy('parent_id', 'ASC');
                    }, 'linkToList'])
                    ->where('project_id', $request->projectId)
                    ->where('is_deleted', '!=', 1)
                    ->where('multiple_board_id', $request->board_id)
                    ->orderby('board_sort_id', 'ASC')
                    ->orderby('parent_id', 'ASC')
                    ->get();
            }
        }

        $team = DB::table('team_users')->where('user_id', Auth::id())->first();
        $team_id = Auth::user()->current_team_id;
        $allUsers = User::join('team_users', 'team_users.user_id', 'users.id')
            ->where('team_users.team_id', $team_id)->get()->toArray();
        $allTags = Tags::on($this->db_name)->where('team_id', $team_id)->where('title', '!=', $this->dont_forget_tag)->get()->toArray();
        foreach ($board as $key => $value) {
            $keys = -1;
            $boards[$key]['id'] = $value['id'];
            $boards[$key]['parent_id'] = $value['parent_id'];
            $boards[$key]['column'] = $value['title'];
            $boards[$key]['hidden'] = $value['hidden'];
            $boards[$key]['progress'] = $value['progress'];
            $boards[$key]['linkToList'] = $value['linkToList'];
            $boards[$key]['color'] = $value['color'];
            if ($value['moveToCol'] != null) {
                $users = json_decode($value['moveToCol']['assigned_users']);
                if (count($users) > 0) {
                    $user_name = '';
                    foreach ($users as $user) {
                        $user = User::where('id', $user)->first();
                        $user_name .= $user->name . ', ';
                    }
                }
                $boards[$key]['users'] = '';
                $boards[$key]['moveToCol'] = true;
                $boards[$key]['ruleName'] = $value['moveToCol']['name'];
                $boards[$key]['status'] = $value['moveToCol']['status'];
                if ($value['moveToCol']['move_to'] != 0) {
                    $boards[$key]['type'] = 'mvCard';
                    $boards[$key]['boardName'] = $value['moveToCol']['moveTo']['multipleBord']['board_title'];
                    $boards[$key]['colName'] = $value['moveToCol']['moveTo']['title'];
                } else {
                    $boards[$key]['moveToCol'] = true;
                    $boards[$key]['type'] = 'asnUser';
                    $boards[$key]['boardName'] = '';
                    $boards[$key]['colName'] = '';
                    $boards[$key]['users'] = $user_name;
                }
            } else {
                $boards[$key]['moveToCol'] = false;
                $boards[$key]['ruleName'] = '';
                $boards[$key]['boardName'] = '';
                $boards[$key]['colName'] = '';
            }
            if (!empty($value['taskFilter']) && count($value['taskFilter']) > 0) {
                foreach ($value['taskFilter'] as $keys => $values) {
                    if ($values['title'] !== 'Dont Forget Section') {
                        $checkEmptyColumn = 1;
                        $allTaskIds[] = $values['id'];
                        $tagTooltip = '';
                        $tags = [];
                        if (!empty($values['Assign_tags']) && count($values['Assign_tags']) > 0) {
                            foreach ($values['Assign_tags'] as $tagkey => $tag) {
                                if (!empty($tag->tag)) {
                                    $infoTags = array(
                                        'assign_id' => $tag->id,
                                        'id' => $tag->tag->id,
                                        'board_id' => $tag->task_id,
                                        'text' => $tag->tag->title,
                                        'classes' => '',
                                        'style' => 'background-color: ' . $tag->tag->color,
                                        'color' => $tag->tag->color,
                                    );
                                    $tagTooltip .= '#' . $tag->tag->title . ' ';
                                    $tags[$tagkey] = $infoTags;
                                }
                            }
                        }

                        $boards[$key]['task'][$keys]['assigned_user'] = $this->AssignUser($values['id']);
                        $assigned_user_ids = [];
                        foreach ($boards[$key]['task'][$keys]['assigned_user'] as $id) {
                            $assigned_user_ids[] = $id['id'];
                        }

                        $boards[$key]['task'][$keys]['assigned_user_ids'] = $assigned_user_ids;
                        $boards[$key]['task'][$keys]['users'] = $allUsers;


                        $boards[$key]['task'][$keys]['tags'] = $tags;
                        $boards[$key]['task'][$keys]['tagTooltip'] = $tagTooltip;

                        if ($values['childTask'] !== null) {
                            $this->totalChild = 0;
                            $boards[$key]['task'][$keys]['child'] = $this->recurChild($values['childTask']);

                        } else {
                            $boards[$key]['task'][$keys]['child'] = 0;
                        }

                        $boards[$key]['task'][$keys]['userName'] = Auth::user()->name;
                        $boards[$key]['task'][$keys]['comment'] = Comment::on($this->db_name)->where('task_id', $values['id'])->where('user_id', Auth::id())->get();
                        $boards[$key]['task'][$keys]['children'] = $values['childTask'];
                        $boards[$key]['task'][$keys]['parents'] = $values['parents'];
                        $boards[$key]['task'][$keys]['id'] = $values['id'];
                        $boards[$key]['task'][$keys]['parent_id'] = $values['parent_id'];
                        $boards[$key]['task'][$keys]['name'] = $values['title'];
                        $boards[$key]['task'][$keys]['list'] = $values['List'];
                        $boards[$key]['task'][$keys]['cardOpen'] = $values['card_open'];
                        $boards[$key]['task'][$keys]['list_id'] = $values['list_id'];
                        $boards[$key]['task'][$keys]['multiple_board_id'] = $values['multiple_board_id'];
                        $boards[$key]['task'][$keys]['description'] = $values['description'];
                        $boards[$key]['task'][$keys]['textareaShow'] = ($values['title'] !== '') ? false : true;
                        $boards[$key]['task'][$keys]['progress'] = $values['progress'];
                        $boards[$key]['task'][$keys]['created_by'] = User::find($values['created_by']);

                        $boards[$key]['task'][$keys]['priority_label'] = null;
                        if ($values['priority_label'] == 3 || $values['priority_label'] == 'high') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'high';
                        } else if ($values['priority_label'] == 2 || $values['priority_label'] == 'medium') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'medium';
                        } else if ($values['priority_label'] == 1 || $values['priority_label'] == 'low') {
                            $boards[$key]['task'][$keys]['priority_label'] = 'low';
                        }

                        if ($values['list_id'] != '') {
                            $boards[$key]['task'][$keys]['type'] = 'task';
                        } else {
                            $boards[$key]['task'][$keys]['type'] = 'card';
                        }
                        $date = Carbon::parse($values['date'], 'UTC')->setTimezone($tz);
                        $boards[$key]['task'][$keys]['date'] = ($values['date'] == '0000-00-00') ? $date : date('d M Y', strtotime($date));
                        $boards[$key]['task'][$keys]['existing_tags'] = $allTags;
                    }
                }
            } else {
                $boards[$key]['task'] = [];
            }
        }
        $this->AddOrUpdateFilter($filter_type);
        $board = Multiple_board::on($this->db_name)->find($request->board_id);
        return response()->json([
            'success' => $boards,
            'allUsers' => $allUsers,
            'allTags' => $allTags,
            'allCardIds' => $allTaskIds,
            'checkEmptyColumn' => $checkEmptyColumn,
            'board' => $board
        ]);
    }

    public function recurChild($child)
    {
        $this->totalChild += count($child);
        foreach ($child as $key => $value) {
            if ($value['childTask'] !== null) {
                if (count($value['childTask']) > 0) {
                    $this->recurChild($value['childTask']);
                }
            }
        }
        return $this->totalChild;
    }

    public function create(Request $request)
    {
        $sortNo = Task::on($this->db_name)->where('board_parent_id', 0)->max('board_sort_id');
        $data = Task::on($this->db_name)->create([
            'multiple_board_id' => $request->multiple_board_id,
            'project_id'        => $request->project_id,
            'board_flag'        => 1,
            'title'             => $request->title,
            'color'             => $request->color,
            'progress'          => $request->progress,
            'board_parent_id'   => 0,
            'hidden'            => 0,
            'board_sort_id'     => $sortNo + 1,
            'created_by'        => Auth::id(),
            'updated_by'        => Auth::id(),
            'date'              => Carbon::today(),
            'created_at'        => Carbon::today(),
        ]);
        if ($data) {
            $this->createLog($data->id, 'created', 'Create new Column', 'Board Column Created');
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function store(Request $request)
    {
        $rqsData = $request->all();
        $validator = Validator::make($rqsData, [
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Board title is required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'error' => $validator->errors()
            ], 200);
        }

        $id = Multiple_board::on($this->db_name)->create([
            'project_id' => $request->project_id,
            'nav_id' => $request->nav_id,
            'board_title' => $request->name,
            'description' => $request->description,
            'created_at' => Carbon::today(),
        ]);
        $multiple_board = Project::on($this->db_name)->findOrFail($request->project_id);
        $multiple_board = $multiple_board->multiple_board;
        $log_data = [
            'multiple_board_id' => $id->id,
            'title' => $request->name,
            'log_type' => 'Create board',
            'action_type' => 'created',
            'action_by' => Auth::id(),
            'action_at' => Carbon::now()
        ];
        ActionLog::on($this->db_name)->create($log_data);
        return response()->json(['multiple_board' => $multiple_board, 'id' => $id]);
    }

    public function cardAdd(Request $request)
    {
        $id = $request->id;
        $parent = Task::on($this->db_name)->find($id);
        $sortNo = Task::on($this->db_name)->where('board_parent_id', $parent->id)->max('board_sort_id');
        $data = [
            'title'             => '',
            'board_sort_id'     => 0,
            'board_parent_id'   => $parent->id,
            'project_id'        => $parent->project_id,
            'multiple_board_id' => $parent->multiple_board_id,
            'hidden'            => 0,
            'board_flag'        => 1,
            'progress'          => $parent->progress,
            'date'              => '0000-00-00',
            'created_by'        => Auth::id(),
            'updated_by'        => Auth::id(),
            'created_at'        => Carbon::now()
        ];
        $child = Task::on($this->db_name)->create($data);
        if ($child) {
            $datas = Task::on($this->db_name)->where('board_parent_id', $parent->id)->get();
            foreach ($datas as $key => $value) {
                Task::on($this->db_name)->where('id', $value->id)->update([
                    'board_sort_id' => $value->board_sort_id + 1
                ]);
            }
            return response()->json(['success' => true, 'data' => $child]);
        }
        return response()->json(['success' => false]);
    }

    public function changeParentId(Request $request)
    {
        if (!is_array($request->id)) {
            $request_ids[] = $request->id;
        } else {
            $request_ids = $request->id;
        }
        foreach ($request_ids as $key => $value) {
            $parent      = Task::on($this->db_name)->find($request->board_parent_id);
            $parent_task = Task::on($this->db_name)->find($value);
            $old_parent  = Task::on($this->db_name)->find($parent_task->board_parent_id);
            $data = Task::on($this->db_name)->where('id', $value)
                ->with('childTask')
                ->get();
            $ids[] = $value;
            foreach ($data as $childs) {
                if (count($childs['childTask']) > 0) {
                    $ids = $this->recurChildIds($childs);
                }
            }
            $mailData = [
                'subject' => "A Card is moved to another column",
                'body' => "A Card ( " . $parent_task->title . " ) that you are assigned on is moved to another column ( " . $parent->title . " )",
                'email' => "email_taskUpdated",
                'generalBody' => "A Card  ( " . $parent_task->title . " ) is moved to another column ( " . $parent->title . " )",
                'task_id' => $ids
            ];
            $moveToData = Rules::on($this->db_name)->where('move_from', $request->board_parent_id)->where('status', 1)->with('moveTo')->first();
            if ($moveToData) {
                $assiagnUser = json_decode($moveToData->assigned_users);
                $delete = AssignedUser::on($this->db_name)->whereIn('task_id', $ids)->delete();
                foreach ($ids as $key => $value) {
                    foreach ($assiagnUser as $keys => $values) {
                        AssignedUser::on($this->db_name)->create([
                            'user_id'    => $values,
                            'task_id'    => $value,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id()
                        ]);
                    }
                }
                $updata = [
                    'board_sort_id'     => $parent_task->board_sort_id,
                    'board_parent_id'   => $request->board_parent_id,
                    'progress'          => $parent->progress
                ];
                if ($moveToData->move_to != 0) {
                    $updata['board_parent_id'] = $moveToData->moveTo->id;
                    $updata['multiple_board_id'] = $moveToData->moveTo->multiple_board_id;
                    $updata['progress'] = $moveToData->moveTo->progress;
                }
                $update = Task::on($this->db_name)->where('board_parent_id', "!=", 0)
                    ->whereIn('id', $ids)
                    ->update($updata);
            } else {
                $update = Task::on($this->db_name)->where('board_parent_id', "!=", 0)
                    ->whereIn('id', $ids)
                    ->update([
                        'board_sort_id' => $parent_task->board_sort_id,
                        'board_parent_id' => $request->board_parent_id,
                        'progress' => $parent->progress
                    ]);
            }
            if ($update) {
                $log_msg = $parent_task->title . ' was move from" ' . $old_parent->title . '" to "' . $parent->title . '" By' . Auth::user()->name;
                $this->createLog($value, 'updated', $log_msg, $parent_task->title);
                $this->HomeController->userMail((object)$mailData);
                $users = $this->addNotification($value, $mailData['body']);
            }
        }

        if ($update) {
            return response()->json(['success' => true, 'data' => $update, 'users' => $users]);
        }
        return response()->json(['success' => false]);
    }

    public function recurChildIds($child)
    {
        if ($child['board_parent_id'] != null) {
            $this->childIds[] = $child['id'];
        }
        if (count($child['childTask']) > 0) {
            foreach ($child['childTask'] as $key => $value) {
                $this->recurChildIds($value);
            }
        }
        return $this->childIds;
    }

    public function cardEdit($id, Request $request)
    {
        $data = [];
        $datas = Task::on($this->db_name)->find($id);
        $mailData = [
            'subject' => "A Card title updated",
            'body' => "A Card (" . $datas->title . ") title is updated to ( " . ($request->title ?? '') . " ) that you are assigned on",
            'email' => "email_taskUpdated",
            'generalBody' => "A Card (" . $datas->title . ") title is updated to ( " . ($request->title ?? '') . " )",
            'task_id' => $id
        ];

        foreach ($request->all() as $key => $value) {
            if ($key == 'date') {
                $tz = $request->tz;
                $value = date('Y-m-d H:i:s', strtotime($value));
                $value = Carbon::parse($value, $tz)->setTimezone('UTC');
                $mailData['subject'] = "Card due date updated";
                $mailData['body'] = "Card due date is updated to ( " . $value . " ) that you are assigned on";
                $mailData['generalBody'] = "A Card due date is updates to ( " . $value . " )";
            }
            if (isset($request->description) && $request->description) {
                $mailData['subject'] = "Card description updated";
                $mailData['body'] = "A Card (" . $datas->title . ") description is updated";
                $mailData['generalBody'] = "A Card (" . $datas->title . ") description is updated";
                $mailData['email'] = "email_descriptionUpdated";
            }
            if ($key !== 'tz') {
                $data[$key] = $value;
            }
        }
        $child = Task::on($this->db_name)->where('id', $id)->update($data);

        if ($child) {
            $datas = Task::on($this->db_name)->find($id);
            $log__ = "A Card (" . $datas->title . ") title is updated to ( " . ($request->title ?? '') . " )";
            if (isset($mailData['body'])) {
                $log__ = $mailData['body'];
            }
            $this->createLog($id, 'Card title updated', $log__, $datas->title);
            $this->HomeController->userMail((object)$mailData);
            $users = $this->addNotification($id, $mailData['body']);

            return response()->json(['success' => true, 'data' => $datas, 'users' => $users]);
        }
        return response()->json(['success' => false]);
    }

    public function update(Request $request)
    {
        $data = [
            'title' => $request->name,
            'color' => $request->color,
            'progress' => $request->progress
        ];
        if ($request->boardId || $request->boardId != '') {
            $update = Task::on($this->db_name)->where('id', $request->boardId)->update($data);
            $update = Task::on($this->db_name)->where('board_parent_id', $request->boardId)
                ->update(['progress' => $request->progress]);
            if ($update) {
                $this->createLog($request->boardId, 'updated', 'Column Update', 'Board Column Updated');
                return response()->json(['success' => true, 'data' => $update]);
            }
        }
        return response()->json(['success' => false]);
    }

    public function destroyColumn($id)
    {
        $child = Task::on($this->db_name)->where('board_parent_id', $id)->get();
        foreach ($child as $key => $value) {
            $this->cardDelete($value->id);
        }
        $delete = Task::on($this->db_name)->where('id', $id)
            ->orWhere('board_parent_id', $id)
            ->delete();
        if ($delete) {
            $this->createLog($id, 'deleted', 'Column Deleted', 'Board Column Deleted With All Card');
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function destroy($id)
    {
        $team_id = Auth::user()->current_team_id;
        $this->db_name = DBConnection::$db_name . $team_id;

        $child = Task::on($this->db_name)->where('board_parent_id', $id)->get();
        foreach ($child as $key => $value) {
            $this->cardDelete($value->id);
        }
        $delete = Task::on($this->db_name)->where('id', $id)
            ->orWhere('board_parent_id', $id)
            ->update([
                'is_deleted' => 1,
                'deleted_at' => carbon::now()
            ]);
        if ($delete) {
            $card = Task::on($this->db_name)->find($id);
            $this->createLog($id, 'softdelete', 'Column was Soft Deleted', $card->title);
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function deleteAllBoardWiseCards($id)
    {
        $cards = Task::on($this->db_name)->where('board_parent_id', $id)->get();
        foreach ($cards as $key => $value) {
            $delete = $this->cardDelete($value->id);
        }
        if ($delete) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function parmanentCardDelete($id)
    {
        $cards = Task::on($this->db_name)->where('id', $id)->first();
        if ($cards->list_id === null) {
            $assiagnUser = AssignedUser::on($this->db_name)->where('task_id', $id)->delete();
            $assiagnTag = AssignTag::on($this->db_name)->where('task_id', $id)->delete();
            $delete = Task::on($this->db_name)->where('id', $id)->delete();
            if ($delete) {
                $this->createLog($id, 'deleted', 'Card was Deleted', 'Board Single Card Deleted');
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false]);
            }
        } else {
            $delete = $this->existingTaskDelete($id);
            if ($delete) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false]);
            }
        }
        return response()->json(['success' => false]);
    }

    public function cardDelete($id)
    {
        $cards = Task::on($this->db_name)->where('id', $id)->first();
        if ($cards->list_id === null) {
            $delete = Task::on($this->db_name)->where('id', $id)->update([
                'is_deleted' => 1,
                'deleted_at' => carbon::now()
            ]);
            if ($delete) {
                $card = Task::on($this->db_name)->where('id', $id)->first();
                $this->createLog($id, 'softdelete', 'Card Soft Deleted', $card->title);
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false]);
            }
        } else {
            $delete = $this->existingTaskDelete($id);
            if ($delete) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false]);
            }
        }
        return response()->json(['success' => false]);
    }

    public function existingTaskDelete($id)
    {
        $delete = Task::on($this->db_name)->where('id', $id)->update([
            'board_parent_id' => null,
            'board_flag' => null,
            'task_flag' => 1,
            'progress' => null,
            'multiple_board_id' => null
        ]);
        if ($delete) {
            $task = Task::on($this->db_name)->where('id', $id)->first();
            $this->createLog($id, 'removed', 'Task was removed from Board', $task->title);
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function selectedExistingTaskDelete(Request $request)
    {
        $delete = Task::on($this->db_name)->whereIn('id', $request->id)->update([
            'board_parent_id' => null,
            'board_flag' => null,
            'task_flag' => 1,
            'multiple_board_id' => null
        ]);
        if ($delete) {
            if (is_array($request->id)) {
                foreach ($request->id as $key => $id) {
                    $task = Task::on($this->db_name)->where('id', $id)->first();
                    $this->createLog($id, 'removed', 'Task Remove From Board', $task->title);
                }
            }
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function hideColumn($id, Request $request)
    {
        $hide = Task::on($this->db_name)->where('id', $id)
            ->update([
                'hidden' => $request->hide
            ]);
        if ($hide) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function addExistingTasks(Request $request)
    {
        $board_id = $request->id;
        $parent = Task::on($this->db_name)->find($board_id);
        foreach ($request->tasks as $key => $value) {
            $taskBoardSortId = Task::on($this->db_name)->where('board_parent_id', $board_id)->max('board_sort_id');
            $board_sort_id = ($taskBoardSortId > 0) ? $taskBoardSortId + 1 : 1;
            Task::on($this->db_name)->where('id', $value)
                ->update([
                    'board_parent_id' => $board_id,
                    'board_flag'      => 1,
                    'progress'        => $parent->progress,
                    'task_flag'       => 1,
                    'board_sort_id'   => $board_sort_id,
                    'multiple_board_id' => $parent->multiple_board_id
                ]);
            $task[$key] = Task::on($this->db_name)->where('id', $value)->first();
            $tagTooltip = '';
            $allTags = AssignTag::on($this->db_name)->where('task_id', $task[$key]->id)->with('tag')->get();
            $tags = [];

            if ($allTags->count() > 0) {
                foreach ($allTags as $tagkey => $tag) {
                    $tags[$tagkey]['assign_id'] = $tag->id;
                    $tags[$tagkey]['id'] = $tag->tag->id;
                    $tags[$tagkey]['board_id'] = $tag->task_id;
                    $tags[$tagkey]['text'] = $tag->tag->title;
                    $tags[$tagkey]['classes'] = '';
                    $tags[$tagkey]['style'] = 'background-color: ' . $tag->tag->color;
                    $tags[$tagkey]['color'] = $tag->tag->color;
                    $tagTooltip = '#' . $tag->tag->title . ' ';
                }
            }
            $task[$key]['tag'] = $tags;
            $task[$key]['tagTooltip'] = $tagTooltip;
        }
        $this->createLog($board_id, 'updated', 'Task was added to board from list', 'Board Card Create From Existing Task');
        return response()->json(['success' => true, 'data' => $task]);
    }

    function findTopParent($data, $d, $parents)
    {
        $key = array_search($d['cardId'], array_column($data, 'cardId'));
        $keyP = array_search($d['parent_id'], array_column($data, 'cardId'));
        $keySP = array_search($d['parent_id'], $parents);
        if ($d['parent_id'] !== 0) {
            if ((string)$keySP == '' && (string)$keyP !== '' && $d['sort_id'] > 0) {
                $data[$key]['sort_id'] = $data[$keyP]['sort_id'];
            } elseif ((string)$keySP !== '' && (string)$keyP !== '' && $d['sort_id'] > 0) {
                $keyDP = array_search($parents[$keySP], array_column($data, 'cardId'));
                $data[$key]['sort_id'] = $data[$keyDP]['sort_id'];
            } else {
                $keySP = array_search($data[$key]['cardId'], $parents);
                $data[$key]['sort_id'] = $keySP;
            }
        }
        return $data;
    }

    public function cardSort(Request $request)
    {
        if (!empty($request->children) && count($request->children) > 0) {
            $ids = '';
            $caseString = '';

            $allIds = array_column($request->children, 'cardId');
            $parents = [];
            $allData = $request->children;
            foreach ($request->children as $key => $item) {
                if ($item['parent_id'] == 0) {
                    $parents[] = $item['cardId'];
                } else {
                    $ind = (string)array_search($item['parent_id'], $allIds);
                    if ($ind == "") {
                        $parents[] = $item['cardId'];
                    }
                }
                $allData[$key]['sort_id'] = $key;
            }

            foreach ($allData as $d) {
                $allData = $this->findTopParent($allData, $d, $parents);
            }
            foreach ($allData as $keys => $values) {
                foreach ($allData as $key2 => $value2) {
                    if ($value2['parent_id'] == $values['cardId']) {
                        $allData[$key2]['sort_id'] = $values['sort_id'];
                    }
                }
            }

            foreach ($allData as $cardKey => $item) {
                if ($item['types'] == 'card' || $item['types'] == 'task') {
                    $id = $item['cardId'];
                    $caseString .= " when id = '" . $id . "' then '" . $item['sort_id'] . "'";
                    $ids .= " $id,";

                    $update = Task::on($this->db_name)->where('id', $id)->update([
                        'board_sort_id' => $cardKey
                    ]);
                }
            }
            if ($update) {
                $this->createLog($request->boardId, 'updated', 'Card sort was Updated', 'Board Card sorting');
                return response()->json(['success' => true, 'data' => $update]);
            } else {
                return response()->json(['success' => false, 'data' => $update]);
            }
        }
    }

    public function columnSort(Request $request)
    {
        if (!empty($request->children) && count($request->children) > 0) {
            $ids = '';
            $caseString = '';
            foreach ($request->children as $key => $item) {
                $id = $item['boardId'];
                $caseString .= " when id = '" . $id . "' then '" . $key . "'";
                $ids .= " $id,";
            }
            $ids = trim($ids, ',');
            $update = DB::connection($this->db_name)->update("update task_lists set board_sort_id = CASE $caseString END where id in ($ids) and board_parent_id = 0");
            if ($update) {
                $this->createLog($id, 'updated', 'Column sorting Updated', 'Board Column sorting');
                return response()->json(['success' => true, 'data' => $update]);
            } else {
                return response()->json(['success' => false, 'data' => $update]);
            }
        }
    }

    public function getAllColumnBylist(Request $request)
    {

        $column = Task::on($this->db_name)->where('board_parent_id', 0)->where('multiple_board_id', $request->list_id)->where('is_deleted', 0)->get();
        if ($column) {
            return response()->json(['success' => true, 'data' => $column]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function transferToAnotherBoard(Request $request)
    {
        $sortNo = Task::on($this->db_name)->where('board_parent_id', $request->board_parent_id)->max('board_sort_id');
        foreach ($request->cardId as $key => $cardId) {
            $data = Task::on($this->db_name)->where('id', $cardId)
                ->update([
                    'board_parent_id' => $request->board_parent_id,
                    'board_sort_id' => $sortNo + 1
                ]);
        }
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function transferColumnToAnotherBoard(Request $request)
    {
        $sortNo = Task::on($this->db_name)->where('board_parent_id', 0)->max('board_sort_id');
        $data = Task::on($this->db_name)->where('id', $request->columnId)
            ->update([
                'multiple_board_id' => $request->multiple_board_id,
                'board_sort_id' => $sortNo + 1
            ]);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function linkListToColumn(Request $request)
    {
        $data = [
            'multiple_list_id' => $request->multiple_list,
            'task_list_id' => $request->columnId,
        ];

        $insert = LinkListToColumn::on($this->db_name)->create($data);
        if ($insert) {
            $col = Task::on($this->db_name)->where('id', $request->columnId)->first();
            $update = Task::on($this->db_name)->where('project_id', $request->projectId)
                ->where('list_id', $request->multiple_list)
                ->where('board_parent_id', null)
                ->update([
                    'board_parent_id' => $request->columnId,
                    'progress' => $col->progress,
                    'multiple_board_id' => $col->multiple_board_id,
                ]);
            return response()->json(['success' => true, 'data' => $insert]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function unlinkListToColumn(Request $request)
    {
        $delete = LinkListToColumn::on($this->db_name)->where('id', $request->linkListId)->first();
        if ($delete) {
            $delete->delete();
            return response()->json(['success' => true, 'data' => $delete]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function isLinked(Request $request)
    {
        $isLink = LinkListToColumn::on($this->db_name)->where('multiple_list_id', $request->multiple_list)->get();

        if ($isLink->count() > 0) {
            return response()->json(['success' => true, 'data' => $isLink]);
        }
        return response()->json(['success' => false]);
    }

    function findTopParents($allData, $item, $parents)
    {
        if ($item['parent_id'] == 0) {
            return $item['id'];
        }
        foreach ($allData as $d) {
            if ($d['id'] == $item['parent_id']) {
                $ind = (string)array_search($d['parent_id'], $parents);
                echo $ind . ' ';
                return $d['id'];
            }
        }
        return $item['parent_id'] . ' 55555';
    }

    public function childHide(Request $request)
    {
        $data = Task::on($this->db_name)->where('id', $request->parent_id)
            ->with('childTask')
            ->get();
        $ids[] = $request->id;
        $hidden = 0;
        $open = 0;
        foreach ($data as $childs) {
            if (count($childs['childTask']) > 0) {
                $ids = $this->recurChildIds($childs);
            }
        }
        $delKey = array_search($request->parent_id, $ids);
        unset($ids[$delKey]);
        $tasks = Task::on($this->db_name)->whereIn('id', $ids)->get();
        foreach ($tasks as $key => $value) {
            if ($value->hidden == 1) {
                $hidden = 0;
                $open = 0;
            } else {
                $hidden = 1;
                $open = 1;
            }
            break;
        }
        $hide = Task::on($this->db_name)->whereIn('id', $ids)
            ->where('board_parent_id', $data[0]->board_parent_id)
            ->update(['hidden' => $hidden]);
        if ($hide) {
            Task::on($this->db_name)->where('id', $request->parent_id)
                ->update(['card_open' => $open]);
            return response()->json(['success' => true]);
        }
    }

    public function childrenAndParent(Request $request)
    {
        $childs = Task::on($this->db_name)->where('id', $request->task_id)
            ->with('childTask')
            ->get();
        $parents = Task::on($this->db_name)->where('id', $request->task_id)
            ->with('parents')
            ->get();

        foreach ($parents as $key => $value) {
            if (count($value['parents']) > 0) {
                $this->recurParent($value['parents']);
            }
        }
        if ($this->parents) {
            $childs = Task::on($this->db_name)->where('id', $this->parents->id)
                ->with('childTask')
                ->get();
        }
        return response()->json(['success' => true, 'childs' => $childs, 'parents' => $this->parents]);
    }

    public function recurParent($parent)
    {
        foreach ($parent as $key => $value) {
            $this->parents = $value;
            if (count($value['parents']) > 0) {
                $this->recurParent($value['parents']);
            }
        }
    }

    public function fileUpload(Request $request)
    {
        if ($request->upload) {
            $photo = uniqid() . $_FILES['upload']['name'];
            $path = public_path() . "/storage/details";
            if (!is_dir($path)) {
                if (!is_dir(public_path() . "/storage/")) {
                    mkdir(public_path() . "/storage/");
                }
                mkdir($path);
            }
            $fullPath = $path . "/" . $photo;
            if (move_uploaded_file($_FILES["upload"]["tmp_name"], $fullPath)) {
                echo "/storage/details/" . $photo;
            }
        }
        if (isset($request->file)) {
            $task_id = $request->id;
            $photo = uniqid() . $_FILES['file']['name'];
            $path = public_path() . "/storage/" . $task_id;
            if (!is_dir($path)) {
                if (!is_dir(public_path() . "/storage/")) {
                    mkdir(public_path() . "/storage/");
                }
                mkdir($path);
            }
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $path . "/" . $photo)) {
                $data = [
                    'tasks_id'   => $task_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'file_name'  => $photo,
                    'created_at' => Carbon::now()
                ];

                $insert = Files::on($this->db_name)->create($data);
                if ($insert) {
                    $insert = Files::on($this->db_name)->where('id', $insert->id)->with('user')->first();
                }
                return response()->json(['success' => true, 'files' => $insert]);
            } else {
                return response()->json('failed', 500);
            }
        }
    }

    public function cardFileDelete(Request $request)
    {
        $delete = Files::on($this->db_name)->where('id', $request->id)->first();
        if (unlink(public_path() . '/storage/' . $delete->tasks_id . '/' . $delete->file_name)) {
            $delete = Files::on($this->db_name)->where('id', $request->id)->delete();
            if ($delete) {
                return response()->json(['success' => true]);
            }
        }

    }

    public function getCardFiles(Request $request)
    {
        $files = Files::on($this->db_name)->where('tasks_id', $request->task_id)->with('user')->get();
        return response()->json(['success' => true, 'files' => $files]);
    }

    protected function createLog($task_id, $type, $message, $title)
    {
        $task = Task::on($this->db_name)->where('id', $task_id)->first();
        $logcheck = ActionLog::on($this->db_name)->where(['task_id' => $task_id, 'log_type' => $message])->first();
        if ($logcheck) {
            ActionLog::on($this->db_name)->where('id', $logcheck->id)
                ->update(['title' => $title, 'action_by' => Auth::id(), 'action_at' => Carbon::now()]);
        } else {
            $log_data = [
                'project_id'  => $task->project_id,
                'task_id'     => $task_id,
                'title'       => $title,
                'log_type'    => $message,
                'action_type' => $type,
                'action_by'   => Auth::id(),
                'action_at'   => Carbon::now()
            ];
            ActionLog::on($this->db_name)->create($log_data);
        }
    }

    public function addNotification($task_id, $notification_body, $action_url = null)
    {
        $this->SetDBConnection();
        $all_Assign_users = Task::on($this->db_name)->where('id', $task_id)->with('Assign_user')->first();
        $user_ids = [];
        foreach ($all_Assign_users->Assign_user as $item) {
            if ($item->user_id != Auth::id()) {
                $user_ids[] = $item->user_id;
                Notification::on($this->db_name)->create([
                    'user_id'     => $item->user_id,
                    'created_by'  => Auth::id(),
                    'body'        => $notification_body,
                    'action_text' => 'View',
                    'action_url'  => ($action_url == null) ? '/project-dashboard/' . $all_Assign_users->project_id : $action_url,
                ]);
            }
        }
        return $user_ids;
    }

    public function AddOrUpdateFilter($filter)
    {
        $user_id = Auth::id();
        try {
            UserFilter::on($this->db_name)->updateOrCreate(['user_id' => $user_id], ['user_id' => $user_id, 'filter_type' => $filter]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function CopyCutPast(Request $request)
    {

        if ($request->type == 'cut') {
            foreach ($request->copy_ids as $key => $value) {
                $tasks = Task::on($this->db_name)->where('id', $value)->first();
                $tasks->board_parent_id = $request->target_id;
                $tasks->save();
            }
        } else {
            foreach ($request->copy_ids as $key => $value) {
                $tasks = Task::on($this->db_name)->where('id', $value)->first();
                $tasks->id = null;
                $tasks->title = $tasks->title . " -copy ";
                $tasks->board_parent_id = $request->target_id;
                $task[] = Task::on($this->db_name)->create($tasks->toArray());
            }
        }
        return response()->json(['success' => true]);
    }

    public function cardMoveUpDown(Request $request)
    {
        $data = Task::on($this->db_name)->where('board_parent_id', $request->column)->whereIn('id', $request->ids)->get()->toArray();
        if (count($data) == 2) {
            Task::on($this->db_name)->where('id', $data[0]['id'])
                ->update([
                    'board_sort_id' => $data[1]['board_sort_id']
                ]);
            Task::on($this->db_name)->where('id', $data[1]['id'])
                ->update([
                    'board_sort_id' => $data[0]['board_sort_id']
                ]);
        }
        return response()->json(['success' => true]);
    }

    public function SetDBConnection()
    {
        $team_id = Auth::user()->current_team_id;
        $db_name = DBConnection::$db_name . $team_id;
        $this->db_name = $db_name;
        DBConnection::SetDBConnection($team_id);
    }

}
