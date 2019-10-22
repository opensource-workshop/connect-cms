<?php

namespace App\Http\Controllers\Core;

use App\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Core\ConnectController;

class UserController extends ConnectController
{
    /**
     * 指定ユーザーのプロフィール表示
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}
