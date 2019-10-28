<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Closure;

class QueryDebugMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Config::get('app.debug') === false) {
            return $next($request);
        }
        $this->readyOutputSqlLog();
        $response = $next($request);
        $this->outputSqlLog();
        return $response;
    }

    private function readyOutputSqlLog()
    {
        if (!Auth::check()) {
            return;
        }
        foreach (\Config::get('database.connections') as $db_name => $settings) {
            $query_org = \DB::connection($db_name)->enableQueryLog();
        }
    }

    private function outputSqlLog()
    {
        if (!Auth::check()) {
            return;
        }
        foreach (\Config::get('database.connections') as $db_name => $settings) {

            $queries = \DB::connection($db_name)->getQueryLog();
            if (empty($queries)) {
                continue;
            }
            Log::debug('Query log, '. $db_name);

            foreach ($queries as $query) {
                Log::debug($query);
            }
        }
    }
}
