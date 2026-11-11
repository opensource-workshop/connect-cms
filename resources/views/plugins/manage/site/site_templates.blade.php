{{-- サイトひな型の選択・適用状態 --}}
@extends('plugins.manage.manage')

@section('manage_content')
<div class="card">
    <div class="card-header p-0">
        @include('plugins.manage.site.site_manage_tab')
    </div>
    <div class="card-body">
        @include('plugins.common.errors_form_line')
        @include('plugins.common.errors_inline', ['name' => '0', 'class' => 'mb-3'])

        <h5 class="border-bottom pb-2 mb-3">サイトひな型</h5>
        <p>初期状態のサイトに一度だけ適用できます。ページやプラグインをまとめて作成し、作成後は通常の編集画面から変更できます。</p>
        <p class="text-muted">実行中は、他の管理者による編集、移行処理、書込バッチを停止してください。</p>

        @if ($application)
            <p><a href="{{ url('/manage/site/siteTemplateResult/' . $application->id) }}"><i class="far fa-file-alt" aria-hidden="true"></i> 適用結果を確認する</a></p>
        @endif

        @if ($state['site_reasons'])
            <div class="alert alert-warning" role="alert">
                <p class="mb-2"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> このサイトにはサイトひな型を適用できません。</p>
                <ul class="mb-0">
                    @foreach ($state['site_reasons'] as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="site-template-form" method="POST" action="{{ url('/manage/site/confirmSiteTemplate') }}">
            @csrf
            <h6 class="mt-4">サイトひな型一覧 @if (!$state['site_reasons']) <span class="badge badge-danger">必須</span> @endif</h6>
            <p id="site-template-help" class="form-text text-muted">サイトひな型を選択すると、作成するページなどの詳細が開きます。</p>
            @include('plugins.common.errors_inline', ['name' => 'template_id', 'class' => 'mb-3'])
            <div class="table-responsive">
                <table id="site-template-list" class="table table-hover">
                    <thead>
                        <tr><th scope="col">サイトひな型名</th><th scope="col">説明</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($templates as $template)
                            <tr>
                                <td>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="site-template-{{ $loop->index }}" class="custom-control-input" name="template_id" value="{{ $template['id'] }}" data-template-available="{{ empty($template_states[$template['id']]['template_reasons']) ? '1' : '0' }}" required aria-controls="site-template-detail-{{ $loop->index }}" aria-describedby="site-template-description-{{ $loop->index }}" @if (old('template_id') === $template['id']) checked @endif>
                                        <label id="site-template-label-{{ $loop->index }}" class="custom-control-label" for="site-template-{{ $loop->index }}">{{ $template['name'] }}</label>
                                        @if ($template_states[$template['id']]['template_reasons'])
                                            <span class="text-warning ml-1" role="img" aria-label="利用できないプラグインがあります。詳細を確認してください。">
                                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <p id="site-template-description-{{ $loop->index }}" class="mb-1">{{ $template['description'] }}</p>
                                    <button type="button" class="btn btn-link btn-sm p-0" data-site-template-toggle data-target="#site-template-detail-{{ $loop->index }}" aria-expanded="{{ old('template_id') === $template['id'] ? 'true' : 'false' }}" aria-controls="site-template-detail-{{ $loop->index }}">
                                        <i class="fas {{ old('template_id') === $template['id'] ? 'fa-chevron-up' : 'fa-chevron-down' }}" aria-hidden="true"></i>
                                        <span class="site-template-detail-caption">{{ old('template_id') === $template['id'] ? '詳細を閉じる' : '詳細を確認する' }}</span>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="p-0 border-0">
                                    <div id="site-template-detail-{{ $loop->index }}" class="collapse @if (old('template_id') === $template['id']) show @endif" data-parent="#site-template-list" role="region" aria-labelledby="site-template-label-{{ $loop->index }}">
                                        <div class="p-3 bg-light">
                                            @if ($template_states[$template['id']]['template_reasons'])
                                                <div class="alert alert-warning" role="alert" data-template-warning="{{ $template['id'] }}">
                                                    @foreach ($template_states[$template['id']]['template_reasons'] as $reason)
                                                        <p class="mb-0">{{ $reason }}</p>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <h6>{{ $template['name'] }}の詳細</h6>
                                            <p class="text-muted">必要プラグイン：
                                                @foreach ($template['required_plugins'] as $required_plugin)
                                                    {{ \App\Services\SiteTemplates\SiteTemplateDefinition::PLUGIN_LABELS[$required_plugin] }}{{ $loop->last ? '' : '、' }}
                                                @endforeach
                                            </p>
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead>
                                                        <tr><th scope="col">作成するページ</th><th scope="col">URL</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($template['pages'] as $template_page)
                                                            <tr><td>{{ $template_page['name'] }}</td><td class="text-break">{{ $template_page['url'] }}</td></tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (!$state['site_reasons'])
                <div class="form-group">
                    <label for="site_name" class="col-form-label">サイト名 <span class="badge badge-danger">必須</span></label>
                    <input type="text" id="site_name" class="form-control @if ($errors->has('site_name')) border-danger @endif" name="site_name" placeholder="学校名など" required maxlength="100" aria-describedby="site_name_help" value="{{ old('site_name') }}">
                    @include('plugins.common.errors_inline', ['name' => 'site_name', 'class' => ''])
                    <small id="site_name_help" class="form-text text-muted">100文字以内で入力してください。サイト基本設定と、サイト名を差し込む固定記事に使用します。</small>
                </div>
                <div class="form-group text-center mb-0">
                    <button type="submit" id="site-template-confirm" class="btn btn-primary"><i class="fas fa-check" aria-hidden="true"></i> 作成内容を確認する</button>
                </div>
            @endif
        </form>
    </div>
</div>
<script>
    (function () {
        const form = document.getElementById('site-template-form');
        const $ = window.jQuery;
        const updateAvailability = function () {
            const selected = form.querySelector('input[name="template_id"]:checked');
            const confirm = document.getElementById('site-template-confirm');
            if (confirm) {
                confirm.disabled = !selected || selected.getAttribute('data-template-available') !== '1';
            }
        };
        const showDetails = function (event) {
            const radio = event.target;
            if (radio.matches('input[type="radio"][name="template_id"]') && radio.checked) {
                updateAvailability();
                $(document.getElementById(radio.getAttribute('aria-controls'))).collapse('show');
            }
        };
        $(form).find('[data-parent="#site-template-list"]').on('show.bs.collapse hide.bs.collapse', function (event) {
            if (event.target !== this) {
                return;
            }
            const expanded = event.type === 'show';
            const button = form.querySelector('[data-target="#' + this.id + '"]');
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            button.querySelector('.site-template-detail-caption').textContent = expanded ? '詳細を閉じる' : '詳細を確認する';
            button.querySelector('i').classList.toggle('fa-chevron-up', expanded);
            button.querySelector('i').classList.toggle('fa-chevron-down', !expanded);
        });
        updateAvailability();
        form.addEventListener('change', showDetails);
        form.addEventListener('click', function (event) {
            const button = event.target.closest('[data-site-template-toggle]');
            if (button) {
                const expanded = button.getAttribute('aria-expanded') === 'true';
                $(document.getElementById(button.getAttribute('aria-controls'))).collapse(expanded ? 'hide' : 'show');
            } else {
                showDetails(event);
            }
        });
    })();
</script>
@endsection
