<?php

namespace Laravel\Spark\Http\Controllers\Settings\Teams;

use App\Files;
use App\Task;
use App\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\UpdateTeamPhoto;
use Laravel\Spark\Http\Requests\Settings\Teams\UpdateTeamPhotoRequest;

class TeamPhotoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Update the given team's photo.
     *
     * @param  \Laravel\Spark\Http\Requests\Settings\Teams\UpdateTeamPhotoRequest  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTeamPhotoRequest $request, $team)
    {
        if (isset($_FILES['photo'])) {
            $photo = $_FILES['photo']['name'];
            $path = public_path() . "/storage/profiles";
            if (!is_dir($path)) {
                mkdir($path);
            }
            $team_check = Team::find($team->id);
            $image_path = public_path() . $team_check->photo_url ;  // Value is not URL but directory file path
            if (file_exists($image_path)) {
                $del = unlink($image_path);
            }

            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $path . "/" . $photo)) {
                $update_team = [
                    'photo_url' => "/storage/profiles/" . $photo
                ];

                Team::where('id',$team->id)->update($update_team);

                return response()->json(['status' => 'success']);
            } else {
                return response()->json('failed', 500);
            }
        }
//        $this->interaction(
//            $request, UpdateTeamPhoto::class,
//            [$team, $request->all()]
//        );
    }
}
