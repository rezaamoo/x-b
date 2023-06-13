<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $users = Http::retry(10, 1000)
        ->post(config('v2board.base_url') . "/server/UniProxy/user", [
            "token" => "nvFDdx8MvBXK4SduouKQEZ4xZD",
            "node_type" => "v2ray",
            "node_id" => "74"
        ])
        ->json()['users'];

    return $users;
});
