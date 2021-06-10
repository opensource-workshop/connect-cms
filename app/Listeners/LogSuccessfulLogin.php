<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\Core\UsersLoginHistories;

use Carbon\Carbon;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // ログイン履歴を残します
        $model = new UsersLoginHistories;
        $model->users_id = \Auth::user()->id;
        $model->userid = \Auth::user()->userid;   // ログインID
        $model->logged_in_at = new Carbon();
        $model->ip_address = request()->ip();
        $model->user_agent = request()->header('User-Agent');
        $model->save();
    }
}
