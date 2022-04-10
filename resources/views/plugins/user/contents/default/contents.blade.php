{{--
 * 表示画面テンプレート。データのみ。HTMLは解釈する。
 *
 * フレームが作られた直後の状態では、$contents が存在せずにnull の場合があるので、チェックして切り替えている。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($contents)
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

    @can('role_update_or_approval',[[$contents, 'contents', $buckets]])
    <div class="row">
        <div class="col-12 text-right mb-1">
            {{-- 変更画面へのリンク --}}
            @if ($frame->page_id == $page->id)

                @if ($contents->status == 2)
                    {{-- バッジ（承認待ち） --}}
                    @can('role_update_or_approval',[[$contents, 'contents', $buckets]])
                        <span class="badge badge-warning align-bottom">承認待ち</span>
                    @endcan
                    @can('posts.approval',[[$contents, 'contents', $buckets]])
                        <form action="{{url('/')}}/redirect/plugin/contents/approval/{{$page->id}}/{{$frame_id}}/{{$contents->id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline">
                            {{ csrf_field() }}
                            {{-- 承認ボタン --}}
                            <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                            </button>
                        </form>
                    @endcan
                @endif

                @can('posts.update',[[$contents, 'contents', $buckets]])
                    {{-- バッジ（一時保存） --}}
                    @if ($contents->status == 1)
                        <span class="badge badge-warning align-bottom">一時保存</span>
                    @endif
                    {{-- 編集ボタン --}}
                    <a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame_id}}/{{$contents->id}}#frame-{{$frame_id}}">
                        <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs" id="{{$frame->plugin_name}}-{{$frame->id}}-edit-button">編集</span></span>
                    </a>
                @endcan
            @endif
        </div>
    </div>
    @endcan
@else
    @can('posts.update',[[$contents, 'contents', $buckets]])
    <p class="text-right">
        {{-- 編集ボタン --}}
        <a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
            <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs" id="{{$frame->plugin_name}}-{{$frame->id}}-edit-button">編集</span></span>
        </a>
    </p>
    @endcan
@endif
@endsection
