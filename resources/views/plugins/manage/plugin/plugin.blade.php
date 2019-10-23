{{--
 * プラグイン管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン追加
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<form name="form_plugins" id="form_plugins" class="form-horizontal" method="post" action="/manage/plugin/update">
    {{ csrf_field() }}
    <div class="card table-responsive">
        <table class="table">
        <thead>
            <th nowrap>表示順</th>
            <th nowrap>表示</th>
            <th nowrap>プラグイン名</th>
            <th nowrap>ディレクトリ</th>
            <th nowrap>状況</th>
        </thead>
        <tbody>
            @foreach($plugins as $plugin)
            <tr>
                <td class="table-text col-1 p-1">
                    <input name="plugins[{{$loop->iteration}}][id]" value="{{$plugin->id}}" type="hidden">
                    <div class="form-group mb-0">
                        <input type="text" name="plugins[{{$loop->iteration}}][display_sequence]" value="{{$plugin->display_sequence}}" class="form-control">
                    </div>
                </td>
                <td class="table-text p-1" nowrap>
                    <div class="custom-control custom-checkbox ml-3 mt-1">
                        @if(isset($plugin->display_flag) && $plugin->display_flag == 1)
                            <input name="plugins[{{$loop->iteration}}][display_flag]" value="1" type="checkbox" class="custom-control-input" id="display_flag{{$loop->iteration}}" checked="checked">
                        @else
                            <input name="plugins[{{$loop->iteration}}][display_flag]" value="1" type="checkbox" class="custom-control-input" id="display_flag{{$loop->iteration}}">
                        @endif
                        <label class="custom-control-label" for="display_flag{{$loop->iteration}}"></label>
                    </div>
                </td>
                <td class="table-text p-1 pt-2">
                    {{$plugin->plugin_name_full}}
                    <input type="hidden" name="plugins[{{$loop->iteration}}][plugin_name_full]" value="{{$plugin->plugin_name_full}}">
                </td>
                <td class="table-text p-1 pt-2">
                    {{$plugin->plugin_name}}
                    <input type="hidden" name="plugins[{{$loop->iteration}}][plugin_name]" value="{{$plugin->plugin_name}}">
                </td>
                <td class="table-text p-1 pt-2">
                    @empty ($plugin->id)
                        データベースに登録します。
                    @endempty
                </td>
            </tr>
            @endforeach
        </tbody>
        </table>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </div>

</form>

@endsection
