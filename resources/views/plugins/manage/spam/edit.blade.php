{{--
 * スパム管理の編集テンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
--}}
@php
use App\Enums\SpamBlockType;
@endphp
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.spam.spam_tab')
    </div>

    <div class="card-body">

        @include('plugins.common.errors_form_line')

        <form action="{{url('/')}}/manage/spam/update/{{ $spam->id }}" method="POST">
            {{ csrf_field() }}

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">種別</label>
                <div class="col-md-10">
                    <div class="form-control-plaintext">
                        {{ SpamBlockType::getDescription($spam->block_type) }}
                    </div>
                    <small class="text-muted">種別は変更できません。</small>
                </div>
            </div>

            <div class="form-group row" id="block_value_group">
                <label class="col-md-2 col-form-label text-md-right">値 <span class="badge badge-danger" id="block_value_required_badge">必須</span></label>
                <div class="col-md-10">
                    <input type="text" name="block_value" id="block_value_input" value="{{ old('block_value', $spam->block_value) }}" class="form-control">
                    @include('plugins.common.errors_inline', ['name' => 'block_value'])
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">適用範囲</label>
                <div class="col-md-10">
                    <div class="custom-control custom-radio">
                        <input type="radio" value="global" id="scope_type_global" name="scope_type" class="custom-control-input" @if(old('scope_type', is_null($spam->target_id) ? 'global' : 'form') == 'global') checked @endif>
                        <label class="custom-control-label" for="scope_type_global">全体</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" value="form" id="scope_type_form" name="scope_type" class="custom-control-input" @if(old('scope_type', is_null($spam->target_id) ? 'global' : 'form') == 'form') checked @endif>
                        <label class="custom-control-label" for="scope_type_form">特定フォーム</label>
                    </div>
                    <select name="target_forms_id" class="form-control mt-2" style="max-width: 300px;">
                        <option value="">-- フォームを選択 --</option>
                        @foreach($forms as $form)
                            <option value="{{ $form->id }}" @if(old('target_forms_id', $spam->target_id) == $form->id) selected @endif>{{ $form->forms_name }}</option>
                        @endforeach
                    </select>
                    @include('plugins.common.errors_inline', ['name' => 'target_forms_id'])
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">メモ</label>
                <div class="col-md-10">
                    <input type="text" name="memo" value="{{ old('memo', $spam->memo) }}" class="form-control">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">登録日時</label>
                <div class="col-md-10">
                    <div class="form-control-plaintext">
                        {{ $spam->created_at->format('Y/m/d H:i:s') }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-10 offset-md-2">
                    <a href="{{url('/')}}/manage/spam" class="btn btn-secondary mr-2">
                        <i class="fas fa-times"></i> キャンセル
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> 更新
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
$(function() {
    // ハニーポットの場合は値フィールドを非表示
    var blockType = '{{ $spam->block_type }}';
    if (blockType === '{{ SpamBlockType::honeypot }}') {
        $('#block_value_group').hide();
    }
});
</script>

@endsection
