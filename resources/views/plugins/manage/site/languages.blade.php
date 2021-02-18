{{--
 * サイト管理（言語）のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.site.site_manage_tab')

</div>
<div class="card-body">

    {{-- エラーメッセージ --}}
    @if ($errors)
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">
                    @foreach($errors->all() as $error)
                    <i class="fas fa-exclamation-triangle"></i> {{$error}}<br />
                    @endforeach
                </span>
{{--
                <span class="text-secondary">
                    @if ($errors->has('add_display_sequence') || $errors->has('add_category') || $errors->has('color') || $errors->has('background_color'))
                    <i class="fas fa-exclamation-circle"></i> 追加行を入力する場合は、すべての項目を入力してください。
                    @endif
                </span>
--}}
            </div>
        </div>
    @endif

    {{-- 削除ボタンのアクション --}}
    <script type="text/javascript">
        function form_delete(id) {
            if (confirm('言語設定を削除します。\nよろしいですか？')) {
                form_delete_language.action = "{{url('/manage/site/deleteLanguages')}}/" + id;
                form_delete_language.submit();
            }
        }
    </script>

    <form action="" method="POST" name="form_delete_language" class="">
        {{ csrf_field() }}
    </form>

    <form action="{{url('/')}}/manage/site/saveLanguages" method="POST" class="">
        {{ csrf_field() }}

        {{-- ヘッダーの表示指定 --}}
        <div class="form-group">
            <label class="col-form-label font-weight-bold">多言語設定の使用</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($language_multi_on == "0")
                            <input type="radio" value="0" id="language_multi_on_off" name="language_multi_on" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="language_multi_on_off" name="language_multi_on" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="language_multi_on_off" id="label_language_multi_on_off">使用しない</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($language_multi_on == "1")
                            <input type="radio" value="1" id="language_multi_on_on" name="language_multi_on" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="language_multi_on_on" name="language_multi_on" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="language_multi_on_on" id="label_language_multi_on_on">使用する</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group table-responsive">
            <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th nowrap class="col-3">言語</th>
                    <th nowrap class="col-3">URL</th>
                    <th nowrap><i class="fas fa-trash-alt"></i></th>
                </tr>
            </thead>
            <tbody>
            @foreach($languages as $language)
                <tr>
                    <td nowrap>
                        <input type="hidden" value="{{$language->id}}" name="languages_id[{{$language->id}}]">
                        <input type="text" value="{{old('language.'.$language->id, $language->value)}}" name="language[{{$language->id}}]" class="form-control">
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('url.'.$language->id, $language->additional1)}}" name="url[{{$language->id}}]" class="form-control">
                    </td>
                    <td nowrap>
                        <a href="javascript:form_delete('{{$language->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                    </td>
                </tr>
            @endforeach
            @if ($create_flag)
                <tr>
                    <td nowrap>
                        <input type="text" value="" name="add_language" class="form-control">
                    </td>
                    <td nowrap>
                        <input type="text" value="" name="add_url" class="form-control">
                    </td>
                    <td nowrap>
                    </td>
                </tr>
            @else
                <tr>
                    <td nowrap>
                        <input type="text" value="{{old('add_language', '')}}" name="add_language" class="form-control">
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('add_url', '')}}" name="add_url" class="form-control">
                    </td>
                    <td nowrap>
                    </td>
                </tr>
            @endif
            </tbody>
            </table>
        </div>

        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/site/languages')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
        </div>
    </form>
</div>
</div>

@endsection
