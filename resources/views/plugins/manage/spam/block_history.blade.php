{{--
 * スパムブロック履歴一覧
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

        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle"></i> スパムフィルタリングでブロックされた履歴を確認できます。
        </div>

        {{-- 検索フォーム --}}
        <form action="{{url('/')}}/manage/spam/blockHistory" method="GET" class="mb-3">
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
                    <label class="small mb-0">マッチした値</label>
                    <input type="text" name="search_block_value" value="{{ $search_block_value }}" class="form-control form-control-sm" placeholder="部分一致">
                </div>
                <div class="col-auto">
                    <label class="small mb-0">フォーム名</label>
                    <input type="text" name="search_forms_name" value="{{ $search_forms_name }}" class="form-control form-control-sm" placeholder="部分一致">
                </div>
                <div class="col-auto">
                    <label class="small mb-0">IPアドレス</label>
                    <input type="text" name="search_client_ip" value="{{ $search_client_ip }}" class="form-control form-control-sm" placeholder="部分一致">
                </div>
                <div class="col-auto">
                    <label class="small mb-0">期間（開始）</label>
                    <input type="date" name="search_date_from" value="{{ $search_date_from }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="small mb-0">期間（終了）</label>
                    <input type="date" name="search_date_to" value="{{ $search_date_to }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> 検索</button>
                    <a href="{{url('/')}}/manage/spam/blockHistory" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> クリア</a>
                </div>
            </div>
        </form>

        {{-- ブロック履歴一覧 --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold">ブロック履歴一覧</span>
            <form action="{{url('/')}}/manage/spam/downloadBlockHistoryCsv" method="POST" class="d-inline">
                {{ csrf_field() }}
                <input type="hidden" name="search_block_type" value="{{ $search_block_type }}">
                <input type="hidden" name="search_block_value" value="{{ $search_block_value }}">
                <input type="hidden" name="search_client_ip" value="{{ $search_client_ip }}">
                <input type="hidden" name="search_forms_name" value="{{ $search_forms_name }}">
                <input type="hidden" name="search_date_from" value="{{ $search_date_from }}">
                <input type="hidden" name="search_date_to" value="{{ $search_date_to }}">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-file-download"></i> CSVダウンロード
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th nowrap>ブロック日時</th>
                        <th nowrap>種別</th>
                        <th nowrap>マッチした値</th>
                        <th nowrap>フォーム名</th>
                        <th nowrap>IPアドレス</th>
                        <th nowrap>送信メールアドレス</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($block_histories as $history)
                    <tr>
                        <td nowrap>{{ $history->created_at ? $history->created_at->format('Y/m/d H:i') : '' }}</td>
                        <td nowrap>
                            @if ($history->block_type == SpamBlockType::email)
                                <span class="badge badge-info">メールアドレス</span>
                            @elseif ($history->block_type == SpamBlockType::domain)
                                <span class="badge badge-warning">ドメイン</span>
                            @else
                                <span class="badge badge-secondary">IPアドレス</span>
                            @endif
                        </td>
                        <td>{{ $history->block_value }}</td>
                        <td nowrap>
                            @if ($history->forms_id)
                                @if (isset($form_page_urls[$history->forms_id]))
                                    <a href="{{ $form_page_urls[$history->forms_id] }}" target="_blank">{{ $forms[$history->forms_id]->forms_name ?? '不明' }} <i class="fas fa-external-link-alt small"></i></a>
                                @else
                                    {{ $forms[$history->forms_id]->forms_name ?? '不明' }}
                                @endif
                            @endif
                        </td>
                        <td>{{ $history->client_ip }}</td>
                        <td>{{ $history->submitted_email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">ブロック履歴はありません。</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        {{ $block_histories->links() }}

    </div>
</div>

@endsection
