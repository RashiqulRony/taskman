<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Spark\Configuration\DBConnection;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    protected $db_name;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $team_id = \Illuminate\Support\Facades\Auth::user()->current_team_id;
            $db_name = DBConnection::$db_name . $team_id;
            $this->db_name = $db_name;
            DBConnection::SetDBConnection($team_id);//set New DB
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $profile = User::select('id', 'name', 'phone')->find(Auth::id())->makeVisible('phone');
        return $profile;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showCardInfo()
    {
        $cardInfo = User::select('id', 'card_brand', 'card_last_four', 'card_country')->find(Auth::id())
            ->makeVisible(['card_brand', 'card_last_four', 'card_country']);
        return response()->json(['success' => true, 'data' => $cardInfo, 'status' => 200], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showBillingInfo()
    {
        $billingInfo = User::select('id', 'billing_address', 'billing_address_line_2', 'billing_city', 'billing_state', 'billing_zip', 'billing_country', 'vat_id', 'extra_billing_information')->find(Auth::id())
            ->makeVisible(['billing_address', 'billing_address_line_2', 'billing_city', 'billing_zip', 'billing_country', 'extra_billing_information']);
        return response()->json(['success' => true, 'data' => $billingInfo, 'status' => 200], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'phone' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors(), 'status' => 400], Response::HTTP_OK);
        }

        $user = User::find(Auth::id());

        $updated = $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);
        if ($updated) {
            return response()->json(['success' => true, 'data' => $updated, 'status' => 200], Response::HTTP_OK);
        } else {
            return response()->json(['success' => false, 'data' => 'Profile Not Updated', 'status' => 401], Response::HTTP_OK);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateCardInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_brand' => 'required|max:255',
            'card_last_four' => 'required|numeric',
            'card_country' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors(), 'status' => 400], Response::HTTP_OK);
        }

        $user = User::find(Auth::id());

        $updated = $user->update([
            'card_brand' => $request->card_brand,
            'card_last_four' => $request->card_last_four,
            'card_country' => $request->card_country,
        ]);
        if ($updated) {
            return response()->json(['success' => true, 'data' => $updated, 'status' => 200], Response::HTTP_OK);
        } else {
            return response()->json(['success' => false, 'data' => 'Card Info Not Updated', 'status' => 401], Response::HTTP_OK);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateBillingInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'billing_address' => 'required|max:255',
            'billing_city' => 'required|max:255',
            'billing_state' => 'required|max:255',
            'billing_zip' => 'required|numeric',
            'billing_country' => 'required|max:255',
            'vat_id' => 'required|max:255',
            'extra_billing_information' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors(), 'status' => 400], Response::HTTP_OK);
        }

        $user = User::find(Auth::id());

        $updated = $user->update([
            'billing_address' => $request->billing_address,
            'billing_address_line_2' => $request->billing_address_line_2,
            'billing_city' => $request->billing_city,
            'billing_state' => $request->billing_state,
            'billing_zip' => $request->billing_zip,
            'billing_country' => $request->billing_country,
            'vat_id' => $request->vat_id,
            'extra_billing_information' => $request->extra_billing_information,
        ]);
        if ($updated) {
            return response()->json(['success' => true, 'data' => $updated, 'status' => 200], Response::HTTP_OK);
        } else {
            return response()->json(['success' => false, 'data' => 'Card Info Not Updated', 'status' => 401], Response::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function weeklyDueTask()
    {
        $now = Carbon::now();
        $start = $now->startOfDay()->format('Y-m-d H:i:s');
        $end = $now->addWeek()->endOfDay()->format('Y-m-d H:i:s');
        $projects = Project::on($this->db_name)->where('team_id', Auth::user()->current_team_id)
            ->with(['tasks' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
                $q->where('sort_id', '>=', 0);
                $q->with(['Assign_user' => function ($q) {
                    $q->where('user_id', Auth::id());
                }]);
                $q->has('Assign_user');
            }])
            ->whereHas('tasks', function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
                $q->where('sort_id', '>=', 0);
                $q->has('Assign_user');
            })->get();
        if ($projects) {
            return response()->json(['success' => true, 'data' => $projects, 'status' => 200], Response::HTTP_OK);
        } else {
            return response()->json(['success' => false, 'data' => 'Whoops! Data Not Find!', 'status' => 401], Response::HTTP_OK);
        }
    }

    public function monthlyPriorityTask()
    {
        $now = Carbon::now();
        $start = $now->startOfDay()->format('Y-m-d H:i:s');
        $end = $now->addDays(30)->endOfDay()->format('Y-m-d H:i:s');
        $projects = Project::on($this->db_name)->where('team_id', Auth::user()->current_team_id)
            ->with(['tasks' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
                $q->where('sort_id', '>=', 0);
                $q->whereNotNull('priority_label');
                $q->orderBy('priority_label', 'desc');
                $q->with(['Assign_user' => function ($q) {
                    $q->where('user_id', Auth::id());
                }]);
                $q->has('Assign_user');
            }])
            ->whereHas('tasks', function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
                $q->where('sort_id', '>=', 0);
                $q->whereNotNull('priority_label');
                $q->has('Assign_user');
            })->get();
        if ($projects) {
            return response()->json(['success' => true, 'data' => $projects, 'status' => 200], Response::HTTP_OK);
        } else {
            return response()->json(['success' => false, 'data' => 'Whoops! Data Not Find!', 'status' => 401], Response::HTTP_OK);
        }
    }

    public function commentsForUser()
    {
        $all_comments = Comment::on($this->db_name)->with(['task' => function ($q) {
        }, 'user'])->whereHas('task.Assign_user', function ($q) {
            $q->where('user_id', Auth::id());
        })->orderBy('created_at', 'desc')->get();
        if ($all_comments) {
            return response()->json(['success' => true, 'comments' => $all_comments, 'status' => 200], Response::HTTP_OK);
        } else {
            return response()->json(['success' => false, 'data' => 'Whoops! Data Not Find!', 'status' => 401], Response::HTTP_OK);
        }
    }
}
