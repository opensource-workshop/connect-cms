{{--
 * スパム管理のメインテンプレート
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

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        @include('plugins.common.errors_form_line')

        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle"></i> サイト全体で適用されるスパムリストを管理します。
        </div>

        {{-- 検索フォーム --}}
        <form action="{{url('/')}}/manage/spam" method="GET" class="mb-3">
            <div class="form-row align-items-end">
                <div class="col-auto">
                    <label class="small mb-0">種別</label>
                    <select name="search_block_type" class="form-control form-control-sm">
                        <option value="">-- すべて --</option>
                        @foreach (SpamBlockType::getMembers() as $key => $value)
                            <option value="{{ $key }}" @if($search_block_type == $key) selected @endif>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="small mb-0">値</label>
                    <input type="text" name="search_block_value" value="{{ $search_block_value }}" class="form-control form-control-sm" placeholder="部分一致">
                </div>
                <div class="col-auto">
                    <label class="small mb-0">適用範囲</label>
                    <select name="search_scope_type" class="form-control form-control-sm">
                        <option value="">-- すべて --</option>
                        <option value="global" @if($search_scope_type == 'global') selected @endif>全体</option>
                        <option value="form" @if($search_scope_type == 'form') selected @endif>特定フォーム</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="small mb-0">メモ</label>
                    <input type="text" name="search_memo" value="{{ $search_memo }}" class="form-control form-control-sm" placeholder="部分一致">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> 検索</button>
                    <a href="{{url('/')}}/manage/spam" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> クリア</a>
                </div>
            </div>
        </form>

        {{-- スパムリスト一覧 --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold">スパムリスト一覧</span>
            <form action="{{url('/')}}/manage/spam/downloadCsv" method="POST" class="d-inline">
                {{ csrf_field() }}
                <input type="hidden" name="search_block_type" value="{{ $search_block_type }}">
                <input type="hidden" name="search_block_value" value="{{ $search_block_value }}">
                <input type="hidden" name="search_scope_type" value="{{ $search_scope_type }}">
                <input type="hidden" name="search_memo" value="{{ $search_memo }}">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-file-download"></i> CSVダウンロード
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th nowrap>種別</th>
                        <th nowrap>値</th>
                        <th nowrap>適用範囲</th>
                        <th nowrap>メモ</th>
                        <th nowrap>登録日時</th>
                        <th nowrap>操作</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($spam_lists as $spam)
                    <tr>
                        <td nowrap>
                            @if ($spam->block_type == SpamBlockType::email)
                                <span class="badge badge-info">メールアドレス</span>
                            @elseif ($spam->block_type == SpamBlockType::domain)
                                <span class="badge badge-warning">ドメイン</span>
                            @else
                                <span class="badge badge-secondary">IPアドレス</span>
                            @endif
                        </td>
                        <td>{{ $spam->block_value }}</td>
                        <td nowrap>
                            @if (is_null($spam->target_id))
                                <span class="badge badge-primary">全体</span>
                            @else
                                @php
                                    $form_name = $forms->where('id', $spam->target_id)->first()->forms_name ?? '不明';
                                @endphp
                                <span class="badge badge-secondary">{{ $form_name }}</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($spam->memo, 30) }}</td>
                        <td nowrap>{{ $spam->created_at->format('Y/m/d H:i') }}</td>
                        <td nowrap>
                            <a href="{{url('/')}}/manage/spam/edit/{{ $spam->id }}" class="btn btn-success btn-sm">
                                <i class="far fa-edit"></i> <span class="d-none d-sm-inline">編集</span>
                            </a>
                            <form action="{{url('/')}}/manage/spam/destroy/{{ $spam->id }}" method="POST" class="d-inline" onsubmit="return confirm('削除してよろしいですか？');">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i> <span class="d-none d-sm-inline">削除</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">スパムリストは登録されていません。</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        {{ $spam_lists->links() }}

        <hr>

        {{-- スパムリスト追加フォーム --}}
        <div class="font-weight-bold mb-2">スパムリストへ追加</div>

        <form action="{{url('/')}}/manage/spam/store" method="POST">
            {{ csrf_field() }}

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">種別 <span class="badge badge-danger">必須</span></label>
                <div class="col-md-10">
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
                        ※ IPアドレス：送信元IPアドレスと一致する場合にブロックします。
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">値 <span class="badge badge-danger">必須</span></label>
                <div class="col-md-10">
                    <input type="text" name="block_value" value="{{ old('block_value') }}" class="form-control" placeholder="例: spam@example.com, spam-domain.com, 192.168.1.100">
                    @include('plugins.common.errors_inline', ['name' => 'block_value'])
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">適用範囲</label>
                <div class="col-md-10">
                    <div class="custom-control custom-radio">
                        <input type="radio" value="global" id="scope_type_global" name="scope_type" class="custom-control-input" @if(old('scope_type', 'global') == 'global') checked @endif>
                        <label class="custom-control-label" for="scope_type_global">全体</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" value="form" id="scope_type_form" name="scope_type" class="custom-control-input" @if(old('scope_type') == 'form') checked @endif>
                        <label class="custom-control-label" for="scope_type_form">特定フォーム</label>
                    </div>
                    <select name="target_forms_id" class="form-control mt-2" style="max-width: 300px;">
                        <option value="">-- フォームを選択 --</option>
                        @foreach($forms as $form)
                            <option value="{{ $form->id }}" @if(old('target_forms_id') == $form->id) selected @endif>{{ $form->forms_name }}</option>
                        @endforeach
                    </select>
                    @include('plugins.common.errors_inline', ['name' => 'target_forms_id'])
                    <small class="form-text text-muted">※「全体」を選択すると、スパムフィルタリングを有効にしているすべてのフォームに適用されます。</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-md-right">メモ</label>
                <div class="col-md-10">
                    <input type="text" name="memo" value="{{ old('memo') }}" class="form-control" placeholder="例: スパム業者からの投稿">
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-10 offset-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 追加
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection
