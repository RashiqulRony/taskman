<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('/projects');
})->middleware('auth');

//Route::get('/', 'WelcomeController@show');

Route::get('/home', function () {
//    return redirect('/thank-you');
     return redirect('/projects');
});

Route::get('/thank-you', function (Request $request) {
    if (isset($request->redirect_with_register) && $request->redirect_with_register == 1)
    {return view('thankYou');} else {return redirect('/home');}
})->name('thankYou');

Route::get('/allcomments', 'HomeController@allComment');
Route::get('/list-pdf-create/{type}/{list_id}', 'MultipleListController@ListPdfCreate');
Route::get('/team/{id}', 'HomeController@updateTeamId');
Route::get('/active-team/{id}', 'HomeController@activeTeam');
/**
 * Test Cron Jobs Routes
 */
Route::get('reminder', 'ReminderSettingsController@sendDueDateEmail');
Route::get('report/daily/{project_id}', 'ReportEmailController@daily');
Route::get('report/weekly/{project_id}', 'ReportEmailController@weekly');
Route::get('report/monthly/{project_id}', 'ReportEmailController@monthly');

Route::get('/{vue_route?}', 'ProjectController@index')->where('vue_route', '(.*)');
