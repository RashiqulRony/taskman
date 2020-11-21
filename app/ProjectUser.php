<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Configuration\DBConnection;

class ProjectUser extends Model
{

    protected $guarded = ['id'];
}
