<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Broadcast;
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

Route::get('/token', function () {
    return csrf_token();
});
Route::get('/listen', function () {
    return view('listen');
});
Route::get('/event', function () {
    $newMessage = new \App\Models\Message();
    $newMessage->content_type = 'text';
    $newMessage->content = 'message';
    $newMessage->customer_id = 1;
    $newMessage->beauty_center_id = 2;
    $newMessage->sender_type = 'customer';
    $newMessage->save();
    event(new \App\Events\Messaging($newMessage));
});


