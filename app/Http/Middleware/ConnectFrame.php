<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Core\FrameConfig;

class ConnectFrame
{
    /**
     * Handle an incoming request.
     *
     * ・requestにセット
     *   ・frame_configs
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');

        // フレーム設定の共有
        $frame_configs = FrameConfig::get();
        $request->attributes->add(['frame_configs' => $frame_configs]);

        return $next($request);
    }
}
