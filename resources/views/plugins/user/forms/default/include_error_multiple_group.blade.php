{{--
 * まとめ行多重設定エラー表示インクルードテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
--}}
{{-- 項目設定権限ありならエラーメッセージ表示 --}}
@can('buckets.editColumn',[[null, null, null, $frame]])
    <div class="alert alert-danger">
        まとめ行が多重に設定されていおり、表示できません。[ <a href="{{url('/')}}/plugin/forms/editColumn/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}">項目設定</a> ]を見直してください。
    </div>
@endcan
