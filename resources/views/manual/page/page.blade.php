@extends("manual.common.main_left_main")

<style>
.modal-middle {		//モーダルウィンドウの縦表示位置を調整
	margin: 5% auto;
}

.modal-img_footer {	//表示予定のテキストとボタンを中央揃え
	padding: .5em;
	text-align: center;
}
</style>

@section('content_main')
    @if ($method->method_name == 'index')
        <div class="card mb-3">
            <div class="card-header text-white bg-primary">{{$method->plugin_title}}</div>
            <div class="card-body">
                <p>{!!nl2br($method->plugin_desc)!!}</p>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header text-white bg-primary">{{$method->plugin_title}} - {{$method->method_title}}</div>
        <div class="card-body">
            <p>{!!nl2br($method->method_desc)!!}</p>
        </div>
    </div>

    @if ($method->img_paths)
    <div class="card mt-3 mb-3">
        <div class="card-header text-white bg-primary">画面</div>
        <div class="card-body">
            @foreach($method->getImgPathArray() as $img_path)
                <p>
                    @if(count($method->getImgPathArray()) > 1) 【画像：{{$loop->iteration}}】<br />@endif
                    <img src="./images/{{basename($img_path)}}.png" class="img-fluid img-thumbnail">
                </p>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header text-white bg-primary">詳細説明</div>
        <div class="card-body">
            <p>{!!nl2br($method->method_detail)!!}</p>
        </div>
    </div>
@endsection
