<?php

namespace App\Http\Controllers;

use App\Project;
use App\ProjectUser;
use App\Team;
use App\User;
use App\AssignedUser;
use App\UserDetails;
use App\UserFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Spark\Configuration\DBConnection;
use Mail;
use App\Mail\UserMail;
use mysql_xdevapi\Exception;
use PDO;
use function GuzzleHttp\Promise\task;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $emailNotification;

     protected $db_name;

    public function __construct ()
    {
        $this->middleware('auth');
        $this->emailNotification = new EmailNotificationController();
        $this->middleware(function ($request, $next) {
            $team_id = Auth::user()->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function SetDBConnection(){
        $team_id = Auth::user()->current_team_id;
        $db_name = DBConnection::$db_name . $team_id;
        $this->db_name = $db_name;
        DBConnection::SetDBConnection($team_id);//set New DB
    }

    public function show ()
    {
        $team_id = Auth::user()->current_team_id;
        $Projects = Project::where('team_id', $team_id)->get();

        return view('home', ['projects' => $Projects]);
    }

    public function allComment ()
    {
        $task_comment = AssignedUser::where('user_id', Auth::user()->id)->with('taskComment')->get();
        $commentsArr = [];
        foreach ($task_comment as $key => $assignuser) {
            $j = 0;
            if (count($assignuser['taskComment']) > 0) {
                foreach ($assignuser['taskComment'] as $keys => $comments) {
                    $commentsArr[] = $comments;
                }
            }
        }
        return response()->json(['success' => true, 'data' => $commentsArr]);
    }

    public function AuthUser ()
    {
        $filter = UserDetails::on($this->db_name)->with('filter')->where('user_id',Auth::id())->first();
        return response()->json(['user' => $filter]);
    }

    public function userMail ($request)
    {
        return true;

        /*======================================
                    return true;
        ======================================*/

        $team_id = Auth::user()->current_team_id;
        $db_name = DBConnection::$db_name . $team_id;
        $this->db_name = $db_name;

        $this->SetDBConnection();
        $emails = [];
        $userIds = [];
        $mails = [];
        $emailCond = $request->email;
        if (isset($request->task_id) && $request->task_id !== '') {

            $users = $this->AssignUser($request->task_id);    // AssignedUser::where('task_id', $request->task_id)->with(['users'])->get();
            foreach ($users as $key => $value) {
                if (!in_array($value->users->email, $emails)) {
                    $emails[$value->users->id] = $value->users->email;
                }
                if (!in_array($value->users->id, $userIds)) {
                    $userIds[] = $value->users->id;
                }
            }
        }
        if (isset($request->user_id) && $request->user_id !== '') {
            if (is_array($request->user_id)) {
                foreach ($request->user_id as $keys => $mailuser) {
                    $user = User::where('id', $mailuser)->first();
                    if (!in_array($user->email, $emails)) {
                        $emails[$user->id] = $user->email;
                    }
                    if (!in_array($user->id, $userIds)) {
                        $userIds[] = $user->id;
                    }
                }
            } else {
                if ($request->user_id !== 0) {
                    $user = User::where('id', $request->user_id)->first();
                    if (!in_array($user->email, $emails)) {
                        $emails[$user->id] = $user->email;
                    }
                    if (!in_array($request->user_id, $userIds)) {
                        $userIds[] = $request->user_id;
                    }
                }
            }
        }
        if (isset($request->project_id) && $request->project_id !== '') {
            $teamUsers = Project::on($this->db_name)->where('id', $request->project_id)
                ->with(['team', 'team.team_users' => function ($q) {
                    $q->select(['id', 'email']);
                }
                ])->first();
            foreach ($teamUsers->team->team_users as $uKey => $userValue) {
                if (!in_array($userValue->id, $userIds)) {
                    $userIds[] = $userValue->id;
                }
                if (!in_array($userValue->email, $emails)) {
                    $emails[$userValue->id] = $userValue->email;
                }
            }
        }
        foreach ($userIds as $keys => $ids) {
            $data = $this->emailNotification->getNotificationsByUser($ids);

            if ($data->original['email_IAmOn'] == 1) {
                if ($emails[$ids] !== '') {
                    $comment['subject'] = $request->subject;
                    $comment['body'] = $request->body;
                    $mails[] = Mail::to($emails[$ids])->queue(new UserMail($comment));
                }
            } else {
                if ($data->original[$emailCond] == 1) {
                    if ($emails[$ids] !== '') {
                        $comment['subject'] = $request->subject;
                        $comment['body'] = $request->body;
                        $mails[] = Mail::to($emails[$ids])->queue(new UserMail($comment));
                    }
                }
            }
        }
        return $this->sendAllToAllUser($userIds, $request);

    }

    public function AssignUser($task_id){
        $assign_users = AssignedUser::on($this->db_name)->where('task_id', $task_id)->get();
        foreach ($assign_users as $assign_user) {
            $user = User::find($assign_user->user_id);
            $assign_user->user = $user;
        }
        return $assign_users;
    }

    /**
     * Get Logged in User Profile.
     *
     * @return JsonResponse
     */
    public function profile ()
    {
        $makeVisible = array(
            'country_code',
            'phone',
            'card_brand',
            'card_last_four',
            'card_country',
            'billing_address',
            'billing_address_line_2',
            'billing_city',
            'billing_zip',
            'billing_country',
            'extra_billing_information',
        );
        $user = Auth::user()->makeVisible($makeVisible);
        return response()->json(['user' => $user]);
    }

    public function sendAllToAllUser ($userIds, $request)
    {
        $comment = [];
        $mails = [];
        $team_id = Auth::user()->current_team_id;
        return true;
        $data = Team::with(['team_users.notifications', 'team_users' => function ($q) {
            $q->whereHas('notifications', function ($q) {
                $q->where('unique_id', 'email_everything');
            });
        }])->find($team_id);

        foreach ($data->team_users as $key => $value) {
            $emails = $value->email;
            $comment['subject'] = $request->subject;
            $comment['body'] = $request->generalBody;
            $mails[] = Mail::to($emails)->queue(new UserMail($comment));
        }
    }

    public function projectUser (Request $request)
    {

        $name = $request->name;
        $project_id = $request->project_id;

        $project = Project::where('id', $project_id)->first();
        $user_ids = ProjectUser::where(['project_id' => $project_id])->pluck('user_id');


        if ($request->name) {
            $users = User::where(function ($q) use ($user_ids, $project) {
                $q->whereIn('id', $user_ids);
                $q->orWhere('id', $project->created_by);
            })
                ->where(function ($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%');
                    $q->orWhere('email', 'like', '%' . $name . '%');
                })->get();
        } else {
            $users = User::whereIn('id', $user_ids)->orWhere('id', $project->created_by)->get();
        }

        return response()->json(['users' => $users, 'owner' => ($project->created_by == Auth::id()) ? 1 : 0]);
    }

    public function teamUser (Request $request)
    {
        $team_id = Auth::user()->current_team_id;
        $name = $request->name;
        $users = Team::where('id', $team_id);
        if (isset($request->name)) {
            $users = $users->with(['team_users' => function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
                $q->orWhere('email', 'like', '%' . $name . '%');
            }
            ])->first();
        } else {
            $users = $users->with('team_users')->first();
        }
        return response()->json(['users' => $users->team_users]);
    }

    public function updateTeamId($teamId){
        $user = User::find(Auth::id());
        if (!empty($user)){
            $user->current_team_id = $teamId;
            $user->save();
            return redirect('/');
        }else{
            return redirect()->back();
        }
    }

    public function activeTeam($teamId){
        $user = User::find(Auth::id());
        if (!empty($user)){
            $user->current_team_id = $teamId;
            $user->save();
            return redirect('settings#/teams');
        }else{
            return redirect()->back();
        }
    }

}
