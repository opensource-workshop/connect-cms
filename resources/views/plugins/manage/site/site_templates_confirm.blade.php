{{-- サイトひな型の適用確認 --}}
@extends('plugins.manage.manage')

@section('manage_content')
@php
    $template_buckets = collect($plan['definition']['buckets'])->keyBy('key');
    $template_frames_by_page = collect($plan['definition']['frames'])->groupBy('page');
@endphp
<div class="card">
    <div class="card-header p-0">
        @include('plugins.manage.site.site_manage_tab')
    </div>
    <div class="card-body">
        <h5 class="border-bottom pb-2 mb-3">サイトひな型の確認</h5>
        <p>{{ $plan['definition']['name'] }}</p>

        <div class="alert alert-warning" role="alert">
            <p class="mb-2"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> 作成後、ページと入力したサイト名が公開されます。</p>
            <p class="mb-2">初期トップページを再利用し、レイアウトをヘッダー・左・中央・フッターに変更します。</p>
            <p class="mb-0">実行中は他の管理者による編集、移行処理、書込バッチを停止してください。成功後の一括取り消しはありません。</p>
        </div>

        <h6 class="mt-4">サイト名の変更</h6>
        <dl class="row">
            <dt class="col-md-3">変更前</dt><dd class="col-md-9 text-break">{{ $plan['previous_site_name'] }}</dd>
            <dt class="col-md-3">変更後</dt><dd class="col-md-9 text-break">{{ $plan['input']['site_name'] }}</dd>
        </dl>

        <h6 class="mt-4">ページ構成・プラグイン配置</h6>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th scope="col">ページ・URL</th><th scope="col">配置</th></tr>
                </thead>
                <tbody>
                    @foreach ($plan['definition']['pages'] as $template_page)
                        <tr>
                            <td>{{ $template_page['name'] }}<br><span class="text-muted text-break">{{ $template_page['url'] }}</span></td>
                            <td>
                                @php
                                    $page_areas = $template_frames_by_page->get($template_page['key'], collect())->groupBy('area')->sortKeys();
                                @endphp
                                @foreach ($page_areas as $area_id => $area_frames)
                                    <div class="@if (!$loop->first) border-top mt-2 pt-2 @endif">
                                        <div class="font-weight-bold mb-1">
                                            {{ \App\Enums\AreaType::enum[$area_id] }}
                                            @if ($area_id !== \App\Enums\AreaType::main)
                                                <small class="font-weight-normal text-muted">（共通・継承）</small>
                                            @endif
                                        </div>
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($area_frames as $template_frame)
                                                <li>{{ $template_buckets[$template_frame['bucket']]['name'] ?? '共通メニュー' }}：{{ \App\Services\SiteTemplates\SiteTemplateDefinition::PLUGIN_LABELS[$template_frame['plugin']] }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ url('/manage/site/applySiteTemplate') }}">
            @csrf
            <input type="hidden" name="confirmation_token" value="{{ $token }}">
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="confirmed" name="confirmed" value="1" required>
                    <label class="custom-control-label" for="confirmed">内容を確認しました</label>
                </div>
            </div>
            <div class="form-group text-center mb-0">
                <a href="{{ url('/manage/site/siteTemplates') }}" class="btn btn-secondary mr-2 mb-2"><i class="fas fa-times" aria-hidden="true"></i> 戻る</a>
                <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-check" aria-hidden="true"></i> この構成を作成する</button>
            </div>
        </form>
    </div>
</div>
@endsection
