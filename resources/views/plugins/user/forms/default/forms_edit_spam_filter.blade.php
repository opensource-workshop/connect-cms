{{--
 * スパムフィルタリング設定画面テンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
--}}
@php
use App\Enums\SpamBlockType;
@endphp
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message')

@include('plugins.common.errors_form_line')

<div class="alert alert-info mt-2">
    <i class="fas fa-exclamation-circle"></i> スパムフィルタリングの設定を行います。
</div>

{{-- スパムフィルタリング設定フォーム --}}
<form action="{{url('/')}}/redirect/plugin/forms/saveSpamFilter/{{$page->id}}/{{$frame_id}}/{{$form->id}}#frame-{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/forms/editSpamFilter/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">スパムフィルタリング</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="use_spam_filter_flag" value="0">
                <input type="checkbox" name="use_spam_filter_flag" value="1" class="custom-control-input" id="use_spam_filter_flag" @if(old('use_spam_filter_flag', $form->use_spam_filter_flag)) checked @endif>
                <label class="custom-control-label" for="use_spam_filter_flag">スパムフィルタリングを使用する</label>
            </div>
            <small class="form-text text-muted">
                <i class="fas fa-info-circle"></i> 本機能を有効にすると、スパムフィルタリングのために送信元のIPアドレスをフォーム投稿時に収集します。サイトのプライバシーポリシーにその旨を記載されることを推奨いたします。
            </small>
        </div>
    </div>

    <div id="spam_filter_settings" @if(!old('use_spam_filter_flag', $form->use_spam_filter_flag)) style="display: none;" @endif>
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">ブロック時メッセージ</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <textarea name="spam_filter_message" class="form-control" rows="3" placeholder="入力されたメールアドレス、または、IPアドレスからの送信は現在制限されています。">{{ old('spam_filter_message', $form->spam_filter_message) }}</textarea>
                <small class="text-muted">※ 未入力の場合、デフォルトメッセージ「入力されたメールアドレス、または、IPアドレスからの送信は現在制限されています。」が表示されます。</small>
            </div>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
    </div>
</form>

<div id="spam_list_section" @if(!old('use_spam_filter_flag', $form->use_spam_filter_flag)) style="display: none;" @endif>
<hr>

{{-- 適用されるブロックリスト --}}
<div class="font-weight-bold mb-2">適用されるブロックリスト</div>

<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead class="thead-light">
            <tr>
                <th nowrap>種別</th>
                <th nowrap>値</th>
                <th nowrap>適用範囲</th>
                <th nowrap>メモ</th>
                <th nowrap>操作</th>
            </tr>
        </thead>
        <tbody>
        @forelse($spam_lists as $spam)
            <tr>
                <td nowrap>
                    @include('plugins.common.spam_block_type_badge', ['block_type' => $spam->block_type])
                </td>
                <td>{{ $spam->block_value }}</td>
                <td nowrap>
                    @if ($spam->isGlobalScope())
                        <span class="badge badge-primary">全体</span>
                    @else
                        <span class="badge badge-secondary">このフォーム</span>
                    @endif
                </td>
                <td>{{ $spam->memo }}</td>
                <td nowrap>
                    @if ($spam->isGlobalScope())
                        <span class="text-muted">－</span>
                    @else
                        <form action="{{url('/')}}/redirect/plugin/forms/deleteSpamList/{{$page->id}}/{{$frame_id}}/{{$spam->id}}#frame-{{$frame_id}}" method="POST" class="d-inline" onsubmit="return confirm('削除してよろしいですか？');">
                            {{ csrf_field() }}
                            <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/forms/editSpamFilter/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> 削除
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">ブロックリストは登録されていません。</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<small class="text-muted">※ 適用範囲が「全体」のブロックリストは<a href="{{url('/')}}/manage/spam" target="_blank">スパム管理</a>から編集できます。</small>

<hr>

{{-- ブロックリスト追加フォーム --}}
<div class="font-weight-bold mb-2">ブロックリストへ追加（このフォーム用）</div>

<form action="{{url('/')}}/redirect/plugin/forms/addSpamList/{{$page->id}}/{{$frame_id}}/{{$form->id}}#frame-{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/forms/editSpamFilter/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">種別 <span class="badge badge-danger">必須</span></label>
        <div class="{{$frame->getSettingInputClass()}}">
            @foreach (SpamBlockType::getMembers() as $key => $value)
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="{{ $key }}" id="block_type_{{ $key }}" name="block_type" class="custom-control-input" @if(old('block_type', SpamBlockType::email) == $key) checked @endif>
                    <label class="custom-control-label" for="block_type_{{ $key }}">{{ $value }}</label>
                </div>
            @endforeach
            @include('plugins.common.errors_inline', ['name' => 'block_type'])
            <small class="form-text text-muted">
                ※ メールアドレス：完全一致でブロックします。<br>
                ※ ドメイン：メールアドレスの@以降と一致する場合にブロックします。<br>
                ※ メールアドレス・ドメインはフォームに「メールアドレス」型項目がある場合に有効です。<br>
                ※ IPアドレス：送信元IPアドレスと一致する場合にブロックします。<br>
                ※ ハニーポット：ボット対策用の隠しフィールドを設置します。値の入力は不要です。
            </small>
        </div>
    </div>

    <div class="form-group row" id="block_value_group">
        <label class="{{$frame->getSettingLabelClass()}}">値 <span class="badge badge-danger" id="block_value_required_badge">必須</span></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="block_value" id="block_value_input" value="{{ old('block_value') }}" class="form-control" placeholder="例: spam@example.com, spam-domain.com, 192.168.1.100">
            @include('plugins.common.errors_inline', ['name' => 'block_value'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">メモ</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="memo" value="{{ old('memo') }}" class="form-control" placeholder="例: スパム業者からの投稿">
        </div>
    </div>

    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> 追加</button>
    </div>
</form>
</div>

<script>
$(function() {
    // スパムフィルタリングチェックボックスの状態変更時
    $('#use_spam_filter_flag').on('change', function() {
        if ($(this).is(':checked')) {
            $('#spam_filter_settings').slideDown();
            $('#spam_list_section').slideDown();
        } else {
            $('#spam_filter_settings').slideUp();
            $('#spam_list_section').slideUp();
        }
    });

    // 種別選択時の値フィールド表示/非表示
    $('input[name="block_type"]').on('change', function() {
        if ($(this).val() === '{{ SpamBlockType::honeypot }}') {
            $('#block_value_group').slideUp();
            $('#block_value_input').val('');
        } else {
            $('#block_value_group').slideDown();
        }
    });

    // 初期表示時のチェック
    if ($('input[name="block_type"]:checked').val() === '{{ SpamBlockType::honeypot }}') {
        $('#block_value_group').hide();
    }
});
</script>

@endsection
