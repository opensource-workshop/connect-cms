{{--
 * バケツ選択画面のキーワード検索フォーム共通部品。
 *
 * @param $action_url フォーム送信先URL
 * @param $clear_url 条件クリアURL
 * @param $frame フレーム
 * @param $keyword 検索キーワード
 * @param $hidden_inputs array 維持するGETパラメータ (任意)
 * @param $placeholder プレースホルダ (任意)
--}}
@php
    $keyword = $keyword ?? '';
    $hidden_inputs = $hidden_inputs ?? [];
    $placeholder = $placeholder ?? 'キーワード';
    $keyword_id = "{$frame->plugin_name}-bucket-keyword-{$frame->id}";
@endphp

<form action="{{$action_url}}" method="GET" class="form-inline mb-3">
    @foreach ($hidden_inputs as $name => $value)
        @if (!is_null($value) && $value !== '')
            <input type="hidden" name="{{$name}}" value="{{$value}}">
        @endif
    @endforeach
    <label class="sr-only" for="{{$keyword_id}}">キーワード</label>
    <input type="text" name="keyword" id="{{$keyword_id}}" value="{{$keyword}}" class="form-control mr-sm-2 mb-2 mb-sm-0" placeholder="{{$placeholder}}">
    <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> 検索</button>
    @if (!empty($keyword))
        <a class="btn btn-secondary" href="{{$clear_url}}">
            <i class="fas fa-times"></i> クリア
        </a>
    @endif
</form>
