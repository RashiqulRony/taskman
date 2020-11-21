<?php

namespace Laravel\Spark\Configuration;

use App\UserDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;

trait DBConnection
{
    public static $db_name = 'compltit_team_';

    public static function SetDBConnection ($team_id)
    {
        $db_name =  DBConnection::$db_name. $team_id;
        Config::set("database.connections." . $db_name, [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $db_name,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
        return 1;
    }

    public static function CreateTeamDB ($team_id)
    {
        $db_name =  DBConnection::$db_name. $team_id;
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
        $db = DB::select($query, [$db_name]);
        if (empty($db)) {
            if (DB::statement("CREATE DATABASE {$db_name}")) {
                DBConnection::SetDBConnection($team_id);
                Schema::connection($db_name)->create('users_details', function ($table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('user_id')->index()->unsigned();
                    $table->string('name');
                    $table->string('email')->unique();
                    $table->timestamp('email_verified_at')->nullable();
                    $table->text('photo_url')->nullable();
                    $table->string('country_code', 10)->nullable();
                    $table->string('phone', 25)->nullable();
                    $table->timestamp('last_read_announcements_at')->nullable();
                    $table->timestamps();
                });
                Schema::connection($db_name)->create('invitations', function ($table) {
                    $table->string('id')->primary();
                    $table->unsignedInteger('team_id')->index();
                    $table->unsignedInteger('user_id')->nullable()->index();
                    $table->string('role')->nullable();
                    $table->string('email');
                    $table->string('token', 40)->unique();
                    $table->timestamps();
                });
                Schema::connection($db_name)->create('notifications', function ($table) {
                    $table->increments('id');
                    $table->unsignedInteger('user_id');
                    $table->unsignedInteger('created_by')->nullable();
                    $table->string('icon', 50)->nullable();
                    $table->text('body');
                    $table->string('action_text')->nullable();
                    $table->text('action_url')->nullable();
                    $table->tinyInteger('read')->default(0);
                    $table->timestamps();

                    $table->index(['user_id', 'created_at']);
                });
//                Schema::connection($db_name)->create('notification_user', function ($table) {
//                    $table->bigInteger('user_id');
//                    $table->bigInteger('notification_id');
//                    $table->timestamps();
//                });
                Schema::connection($db_name)->create('projects', function ($table) {
                    $table->increments('id');
                    $table->integer('team_id');
                    $table->string('name');
                    $table->string('description')->nullable();
                    $table->unsignedInteger('created_by');
                    $table->unsignedInteger('updated_by');
                    $table->timestamps();
                });
                Schema::connection($db_name)->create('project_nav_items', function ($table) {
                    $table->bigIncrements('id');
                    $table->integer('project_id')->unsigned();
                    $table->string('title');
                    $table->string('type');
                    $table->integer('sort_id');
                    $table->timestamps();

                    $table->foreign('project_id')
                        ->references('id')->on('projects')
                        ->onDelete('cascade');

                });
                Schema::connection($db_name)->create('rules', function ($table) {
                    $table->bigIncrements('id');
                    $table->string('name');
                    $table->tinyInteger('status')->default(0);
                    $table->integer('project_id')->unsigned();
                    $table->bigInteger('move_from');//column id (from task)
                    $table->bigInteger('move_to')->nullable();//column id (from task)
                    $table->bigInteger('created_by');//user id (from task)
                    $table->string('assigned_users')->nullable();//user assign (user)
                    $table->timestamps();

                    $table->foreign('project_id')
                        ->references('id')->on('projects')
                        ->onDelete('cascade');

                });
                Schema::connection($db_name)->create('multiple_boards', function ($table) {
                    $table->bigIncrements('id');
                    $table->integer('project_id')->unsigned();
                    $table->bigInteger('nav_id');
                    $table->string('board_title');
                    $table->text('description')->nullable();
                    $table->timestamps();

                    $table->foreign('project_id')
                        ->references('id')->on('projects')
                        ->onDelete('cascade');

                });
                Schema::connection($db_name)->create('multiple_lists', function ($table) {
                    $table->bigIncrements('id');
                    $table->integer('project_id')->unsigned();
                    $table->bigInteger('nav_id');
                    $table->string('list_title');
                    $table->text('description')->nullable();
                    $table->tinyInteger('open')->default(1);
                    $table->tinyInteger('sort_id')->default(0);
                    $table->tinyInteger('is_delete')->default(0);
                    $table->timestamps();

                    $table->foreign('project_id')
                        ->references('id')->on('projects')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('task_lists', function ($table) {
                    $table->bigIncrements('id');
                    $table->unsignedBigInteger('parent_id')->default(0)->nullable();
                    $table->unsignedInteger('project_id');
                    $table->bigInteger('board_parent_id')->nullable();
                    $table->integer('list_id')->nullable();
                    $table->Integer('multiple_board_id')->nullable();
                    $table->text('priority_label')->nullable();
                    $table->Integer('task_flag')->nullable();
                    $table->Integer('board_flag')->nullable();
                    $table->bigInteger('hidden')->nullable();
                    $table->bigInteger('sort_id')->default(0);
                    $table->Biginteger('board_sort_id')->default(0);//
                    $table->unsignedInteger('created_by');
                    $table->unsignedInteger('updated_by');
                    $table->tinyInteger('open')->default(1);
                    $table->tinyInteger('card_open')->default(0)->comment('0 = open, 1 = close');
                    $table->text('title');
                    $table->text('description')->nullable('');//
                    $table->text('color')->nullable();
                    $table->text('progress')->nullable();
                    $table->dateTime('date');
                    $table->tinyInteger('is_complete')->default(0);
                    $table->tinyInteger('is_deleted')->default(0);
                    $table->bigInteger('copied_from')->nullable();
                    $table->dateTime('deleted_at')->nullable();
                    $table->timestamps();

                    $table->foreign('project_id')
                        ->references('id')->on('projects')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('tags', function ($table) {
                    $table->bigIncrements('id');
                    $table->tinyInteger('team_id');
                    $table->string('title')->nullable();
                    $table->string('color')->nullable();
                    $table->timestamps();
                });
                Schema::connection($db_name)->create('action_logs', function ($table) {
                    $table->bigIncrements('id');
                    $table->integer('project_id')->nullable();
                    $table->bigInteger('multiple_list_id')->nullable();
                    $table->bigInteger('task_id')->nullable()->unsigned();
                    $table->bigInteger('multiple_board_id')->nullable();
                    $table->bigInteger('board_id')->nullable();
                    $table->text('title')->nullable();
                    $table->string('log_type')->nullable();
                    $table->string('action_type')->nullable();
                    $table->integer('action_by');
                    $table->dateTime('action_at');

                    $table->foreign('task_id')
                        ->references('id')->on('task_lists')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('user_filters', function ($table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('user_id')->unsigned();
                    $table->string('filter_type')->nullable();
                    $table->timestamps();

                    $table->foreign('user_id')
                        ->references('user_id')->on('users_details')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('project_users', function ($table) {
                    $table->bigIncrements('id');
                    $table->integer('project_id')->unsigned()->index();
                    $table->integer('user_id')->unsigned()->index();
                    $table->tinyInteger('status')->default(0);
                    $table->timestamps();

                    $table->foreign('project_id')
                        ->references('id')->on('projects')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('assign_tags', function ($table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('task_id')->unsigned();
                    $table->bigInteger('tag_id')->unsigned();
                    $table->timestamps();

                    $table->foreign('task_id')
                        ->references('id')->on('task_lists')
                        ->onDelete('cascade');

                    $table->foreign('tag_id')
                        ->references('id')->on('tags')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('comments', function ($table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('task_id')->unsigned();
                    $table->bigInteger('user_id');
                    $table->bigInteger('parent_id')->nullable();
                    $table->text('comment');
                    $table->text('attatchment')->nullable();
                    $table->tinyInteger('is_seen')->default(0)->comment('0 = unseen, 1 = seen');
                    $table->timestamps();

                    $table->foreign('task_id')
                        ->references('id')->on('task_lists')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('task_files', function ($table) {
                    $table->bigIncrements('id');
                    $table->string('file_name');
                    $table->unsignedBigInteger('tasks_id');
                    $table->unsignedInteger('created_by');
                    $table->unsignedInteger('updated_by');
                    $table->timestamps();

                    $table->foreign('tasks_id')
                        ->references('id')->on('task_lists')
                        ->onDelete('cascade');
                });
                Schema::connection($db_name)->create('task_assigned_users', function ($table) {
                    $table->bigIncrements('id');
                    $table->unsignedInteger('user_id');
                    $table->unsignedBigInteger('task_id');
                    $table->unsignedInteger('created_by');
                    $table->unsignedInteger('updated_by');
                    $table->timestamps();

                    $table->foreign('task_id')
                        ->references('id')->on('task_lists')
                        ->onDelete('cascade');

                });
                Schema::connection($db_name)->create('link_list_to_columns', function ($table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('multiple_list_id')->unsigned();
                    $table->bigInteger('task_list_id')->unsigned();
                    $table->timestamps();

                    $table->foreign('multiple_list_id')
                        ->references('id')->on('multiple_lists')
                        ->onDelete('cascade');

                    $table->foreign('task_list_id')
                        ->references('id')->on('task_lists')
                        ->onDelete('cascade');
                });

                UserDetails::on($db_name)->create([
                    'user_id' => Auth::id(),
                    'name'=>Auth::user()->name,
                    'email'=>Auth::user()->email,
                    'email_verified_at'=>Auth::user()->email_verified_at,
                    'photo_url'=>Auth::user()->photo_url,
                    'country_code'=>Auth::user()->country_code,
                    'phone'=>Auth::user()->phone,
                    'last_read_announcements_at'=>Auth::user()->last_read_announcements_at,
                    'created_at'=>Auth::user()->created_at,
                    'updated_at'=>Auth::user()->created_at
                ]);
                return true;
            }
        }

    }

    public static function TeamId(){
        return Auth::user()->current_team_id;
    }

}
