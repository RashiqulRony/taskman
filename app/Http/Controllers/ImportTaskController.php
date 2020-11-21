<?php

namespace App\Http\Controllers;

use App\AssignedUser;
use App\AssignTag;
use App\Tags;
use App\Task;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Spark\Configuration\DBConnection;
use Laravel\Spark\Notification;
use Validator;
use Auth;

class ImportTaskController extends Controller
{

    protected $HomeController;
    public $TaskController;
    public $dont_Forget_tag = 'Dont Forget';
    public $dont_Forget_Section = 'Dont Forget Section';
    protected $db_name;

    public function __construct()
    {
        $this->TaskController = new TaskController();
        $this->HomeController =  new HomeController;

        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $team_id = \Illuminate\Support\Facades\Auth::user()->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    public function importCsv(Request $request)
    {
        try {
            $data = $request->all();
            $validator = Validator::make($data, [
                'csv_file'   => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 500, 'error' => $validator->errors()], 200);
            }

            if ($request->hasFile('csv_file')) {
                $file = $request->file('csv_file');
                if ($file->getClientOriginalExtension() == 'csv'){
                    $name = time().'_csv_file.' .$file->getClientOriginalExtension();
                    $file->move('storage/', $name);
                }else {
                    return response()->json([
                        'status' => 500,
                        'error' => "The csv file must be a file of type: CSV."
                    ], 200);
                }
            }

            $csvFile = public_path('storage/'.$name);
            $csv= file_get_contents($csvFile);
            $array = array_map("str_getcsv", explode("\n", $csv));
            $columns =  [];
            $csvDatas = [];
            if ($data['header_check'] == 'yes'){
                foreach ($array as $key => $values){
                    $info = [];
                    if ($key == 0){
                        foreach ($values as $vKey => $value){
                            $columns[$vKey] = $value;
                            if($value == null) { break;};
                        }
                    }else{
                        foreach ($values as $vKey => $value){
                            $info[$columns[$vKey]] = $value;
                            if($value == null) { break;};
                        }
                        $csvDatas[$key] = $info;
                    }
                }
            }else{
                foreach ($array as $cKey => $countValue){
                    if ($cKey == 0){
                        foreach ($countValue as $vKey => $value){
                            $columns[$vKey] = 'Column '.($vKey+1);
                            if($value == null) { break;};
                        }
                    }
                    foreach ($countValue as $vKey => $value){
                        $info[$columns[$vKey]] = $value;
                        if($value == null) { break;};
                    }
                    $csvDatas[$cKey] = $info;
                }
            }
            if ($csvFile){
                unlink($csvFile);
            }
            $csvDatas = !empty($csvDatas) ? array_values(array_filter($csvDatas)) : array();
            return response()->json(['status'=> 200, 'data'=> $csvDatas, 'columns' => $columns]);
        }catch (\Exception $exception){
            $message = $exception->getMessage();
            return response()->json(['status'=> 500, 'data'=> $message]);
        }
    }

    public function importCsvSave(Request $request)
    {
        try {
            $sort_id_check =  Task::on($this->db_name)->select('sort_id')->where('parent_id', 0)
                ->where('list_id', $request->list_id)
                ->orderBy('sort_id', 'desc')->first();
            if ($sort_id_check){
                $sort_id = $sort_id_check->sort_id;
            }else{
                $sort_id = 0;
            }
            foreach ($request->params as $key => $value){
                if (!isset($value['title'])){
                    return response()->json(['status'=> 500, 'message'=> 'Please select title.']);
                }
                if (!isset($value['priority_label'])){
                    $priorityLabel = null;
                }
                if (!empty($value['priority_label'])){
                    if (trim($value['priority_label']) == 'high' || trim($value['priority_label']) == 3){
                        $priorityLabel = 3;
                    }elseif (trim($value['priority_label']) == 'medium' || trim($value['priority_label']) == 2){
                        $priorityLabel = 2;
                    }elseif (trim($value['priority_label']) == 'low' || trim($value['priority_label']) == 1){
                        $priorityLabel = 1;
                    }else{
                        $priorityLabel = null;
                    }
                }

                $taskData = [
                    'list_id'        => $request->list_id,
                    'sort_id'        => $sort_id,
                    'parent_id'      => 0,
                    'project_id'     => $request->project_id,
                    'title'          => isset($value['title']) ? $value['title']:'',
                    'description'    => isset($value['description']) ? $value['description']:'',
                    'priority_label' => $priorityLabel,
                    'date'           => isset($value['date']) ? date('Y-m-d', strtotime(trim($value['date']))):'0000-00-00',
                    'created_at'     => Carbon::now(),
                    'created_by'     => Auth::id(),
                    'updated_by'     => Auth::id(),
                ];

                $sort_id += 1;
                $task = Task::on($this->db_name)->create($taskData);
                if (!empty($value['assigned_user'])){
                    $this->getCsvEmailsToAssignUser($task, $value['assigned_user']);
                }
                if (!empty($value['tag'])){
                    $this->getCsvTagsToStore($task, $value['tag'], $value['color']);
                }
            }
            return response()->json(['status'=> 200]);
        }catch (\Exception $exception){
            $message = $exception->getMessage();
            return response()->json(['status'=> 500, 'message'=> $message]);
        }
    }

    public function getCsvEmailsToAssignUser($task, $emails)
    {
        try {
            $getEmails = explode(",", $emails);
            if (!empty($getEmails)) {
                foreach ($getEmails as $email) {
                    $user = User::where('email', trim($email))->first();
                    if (!empty($user)){
                        $checkIsUserAssigned = AssignedUser::on($this->db_name)->where([
                            'task_id' => $task->id,
                            'user_id' => $user->id
                        ])->count();
                        if ($checkIsUserAssigned <= 0) {
                            AssignedUser::on($this->db_name)->create([
                                'task_id'    => $task->id,
                                'user_id'    => $user->id,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                'created_by' => \Illuminate\Support\Facades\Auth::id(),
                                'updated_by' => Auth::id(),
                            ]);

                            $mailData = [
                                'subject'     => "Added to a task",
                                'body'        => "You are assigned on a task (" . $task->title . ").",
                                'email'       => "email_whenAddedToTask",
                                'generalBody' => " (" . $user->name . ") is assigned on a task (" . $task->title . ") ",
                                'task_id'     => $task->id,
                            ];
                            $this->HomeController->userMail((object)$mailData);
                            $this->addNotification($task->id, $mailData['generalBody']);
                        }
                    }
                }
                return true;
            }
        }catch (\Exception $exception){
            $message = $exception->getMessage();
            return response()->json(['status'=> 500, 'data'=> $message]);
        }
    }

    public function getCsvTagsToStore($task, $csvTags, $csvColor = null)
    {
        try {
            $getTags = explode(",", $csvTags);
            if (!empty($getTags)) {
                foreach ($getTags as $tag) {
                    $tags = Tags::on($this->db_name)->where(['title' => trim($tag)])->first();
                    $task = Task::on($this->db_name)->find($task->id);
                    if ($tags) {
                        $tag_id = $tags->id;
                    } else {
                        $tag_data = [
                            'color'      => isset($csvColor) ? $csvColor:$this->randColor(),
                            'title'      => trim($tag),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                        $tags = Tags::on($this->db_name)->create($tag_data);
                        $tag_id = $tags->id;
                    }
                    $check_assign = AssignTag::on($this->db_name)->where(['task_id' => $task->id, 'tag_id' => $tag_id])->count();
                    if ($check_assign <= 0) {
                        $check_assign    = AssignTag::on($this->db_name)->create(['task_id' => $task->id, 'tag_id' => $tag_id]);
                        $tags->assign_id = $check_assign->id;
                        $tags->board_id  = $check_assign->task_id;
                    }
                    $mailData = [
                        'subject'       => "Tag added to a task",
                        'body'          => "A tag (" .$tags->title. ") is assigned to ( ".$task->title." ) task.",
                        'email'         => "email_taskUpdated",
                        'generalBody'   => "A tag (" .$tags->title. ") is assigned to ( ".$task->title." ) task.",
                        'task_id'       => $task->id
                    ];
                    if ($tag == $this->dont_Forget_tag) {
                        $this->dontForgetTagProcess($task->id,$tag_id);
                    }else {
                        $this->HomeController->userMail( (object) $mailData);
                        $this->addNotification($task->id, $mailData['body']);
                    }
                }
                return true;
            }
        }catch (\Exception $exception){
            $message = $exception->getMessage();
            return response()->json(['status'=> 500, 'data'=> $message]);
        }
    }

    public function dontForgetTagProcess($id,$tag_id)
    {
        $check_assign = AssignTag::on($this->db_name)->where(['task_id' => $id, 'tag_id' => $tag_id])->count();
        if ($check_assign <= 0) {
            AssignTag::on($this->db_name)->create(['task_id' => $id, 'tag_id' =>$tag_id]);
        }

        $task = Task::on($this->db_name)->where('id', $id)->first();
        $taskDontForget  = Task::on($this->db_name)->where([
            'title'      => $this->dont_Forget_Section,
            'project_id' => $task->project_id,
            'list_id'    => $task->list_id,
        ])->get();

        if ($taskDontForget->count() <= 0) {
            $data = [
                'sort_id'       => -2,
                'parent_id'     => 0,
                'project_id'    => $task->project_id,
                'list_id'       => $task->list_id,
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
                'title'         => $this->dont_Forget_Section,
                'date'          => $task->date,
                'created_at'    => Carbon::now(),
            ];
            $NewTask = Task::on($this->db_name)->create($data);
            AssignTag::on($this->db_name)->create(['task_id' => $NewTask->id, 'tag_id' => $tag_id]);

            $taskUpdate = Task::on($this->db_name)->where('id', $id)->update(['parent_id' => $NewTask->id]);
            //update task child tag
            $this->TaskController->updateTagWithDataMove($id, $NewTask->id);
            return response()->json(['success' => $taskUpdate]);
        } elseif ($id != $taskDontForget[0]->id) {
            if($taskDontForget[0]->is_deleted == 1 ){
                Task::on($this->db_name)->where([
                    'title'      => $this->dont_Forget_Section,
                    'project_id' => $task->project_id,
                    'list_id'    => $task->list_id,
                ])->update(['is_deleted'=>0]);
            }
            $parent_assign_dont_Forget_tag = AssignTag::on($this->db_name)->where(['task_id' => $task->parent_id , 'tag_id' => $tag_id])->count();

            if ($parent_assign_dont_Forget_tag <= 0) {
                $sort = Task::on($this->db_name)->where(['parent_id' => $taskDontForget[0]->id])->max('sort_id');
                $taskUpdate = Task::on($this->db_name)->where('id', $id)->update([
                    'parent_id' => $taskDontForget[0]->id,
                    'sort_id'   => $sort + 1
                ]);
                //update task child tag
                $this->TaskController->updateTagWithDataMove($id, $taskDontForget[0]->id);
                return response()->json(['success' => $taskUpdate]);
            }
            return response()->json(['success']);
        }

    }

    public function addNotification ($task_id,$notification_body,$action_url = null)
    {
        $all_Assign_users = Task::on($this->db_name)->where('id', $task_id)->with('Assign_user')->first();
        $user_ids = [];
        foreach ($all_Assign_users->Assign_user as $item) {
            if ($item->user_id != Auth::id()){
                $user_ids[] = $item->user_id;
                Notification::on($this->db_name)->create([
                    'user_id'       => $item->user_id,
                    'created_by'    => Auth::id(),
                    'body'          => $notification_body,
                    'action_text'   => 'View',
                    'action_url'    => ($action_url == null ) ? '/project-dashboard/'.$all_Assign_users->project_id : $action_url,
                ]);
            }
        }
        return $user_ids;
    }

    function randColor()
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

}
