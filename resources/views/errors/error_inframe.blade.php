{{--
 * その他エラー
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 --}}
<div class="container">
    <div class="alert alert-danger mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <span class="sr-only">Error:</span>
        @if ($e)
            {{$e->getStatusCode()}}. 
        @endif
        @if (isset($message))
            {{$message}}
        @endif 
        <br />
        @if (Config::get('app.debug'))
            <div class="card mt-3">
                <div class="card-header">
                    Debug message
                </div>
                <div class="card-body">
                    <p class="card-text">{{$debug_message}}</p>
                </div>
            </div>
        @endif
    </div>
</div>