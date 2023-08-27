<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\GenericUser;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

//Broadcast::channel('{id}', function (){
//    return $customer;
//});

Route::post('/custom/broadcasting/auth', function () {
    $user = new GenericUser(['id' => microtime()]);

    request()->setUserResolver(function () use ($user) {
        return $user;
    });

    return Broadcast::auth(request());
});

Broadcast::channel('{uuid}', function ($user, $uuid) {

    return $user;
    return array('name' => $user->id);
});
