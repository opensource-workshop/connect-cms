{{--
 * 編集画面(編集時の表示側画面)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.contents.contents_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
{{-- データ --}}
<p>
    <div class="card">
        <div class="card-body">
            {!! $contents->content_text !!}

            {{-- 続きを読む --}}
            @if ($contents->read_more_flag)
                {{-- 続きを読む & タグありなら、続きを読むとタグの間に余白追加 --}}
                <div id="content2_text_button_{{$frame->id}}_{{$contents->id}}" @isset($post_tags) class="mb-2" @endisset>
                    <button type="button" class="btn btn-light btn-sm border" onclick="$('#content2_text_{{$frame->id}}_{{$contents->id}}').show(); $('#content2_text_button_{{$frame->id}}_{{$contents->id}}').hide();">
                        <i class="fas fa-angle-down"></i> {{$contents->read_more_button}}
                    </button>
                </div>
                <div id="content2_text_{{$frame->id}}_{{$contents->id}}" style="display: none;" @isset($post_tags) class="mb-2" @endisset>
                    {!! $contents->content2_text !!}
                    <button type="button" class="btn btn-light btn-sm border" onclick="$('#content2_text_button_{{$frame->id}}_{{$contents->id}}').show(); $('#content2_text_{{$frame->id}}_{{$contents->id}}').hide();">
                        <i class="fas fa-angle-up"></i> {{$contents->close_more_button}}
                    </button>
                </div>
            @endif
        </div>
    </div>
</p>
@can('posts.delete',[[$contents, 'contents']])
<form action="{{url('/')}}/redirect/plugin/contents/delete/{{$page->id}}/{{$frame_id}}/{{$contents->id}}#frame-{{$frame->id}}" method="POST" class="form-horizontal">
    {{ csrf_field() }}
    <span class="text-danger">
    <p>
    データを削除します。<br />
    元に戻すことはできないため、よく確認して実行してください。
    </p>
    </span>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card border-danger">
                <div class="card-body text-center p-2">
                    <label class="mb-0">
                        <input type="checkbox" name="frame_delete_flag" value="1">フレームも同時に削除します。
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="row form-group mx-auto mt-3">
            <div>
                <button type="button" class="btn btn-default btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}/'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')">
                    <i class="fas fa-check"></i> データ削除
                </button>
            </div>
        </div>
    </div>
</form>
@endcan
@endsection
