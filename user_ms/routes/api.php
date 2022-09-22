<?php

use App\Http\Controllers\UsersController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("v1/users",[UsersController::class,'getUser']);
Route::post("setup",function(){
    $data = request()->all();
    if($data["consumer"]==="authms"){
        User::truncate();
        User::create([
            "name"=>"yhiamdan",
            "email"=>"yhiamdan@gmail.com",
            "password"=>'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
        ]);
    }
    return response()->json();
});