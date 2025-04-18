
{{--
 * 課題管理　CSVインポートメッセージ
 *
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
{{-- 例外などのエラーメッセージ --}}
@if (session('error'))
    <div class="alert alert-danger">
        {{session('error')}}
    </div>
@endif
{{-- エラー詳細 --}}
@if (session('csv_import_errors'))
    @php
        $import_errors = collect(session('csv_import_errors'));
        // 0行目にエラーの概要が格納される
        $description = $import_errors->where('line', 0)->first()['message'];
    @endphp
    <div class="alert alert-danger">
        <h4 class="alert-heading">エラー詳細</h4>
        {{$description}}
        <ul>
        @foreach ($import_errors->where('line', '!=', 0) as $error)
            <li>
                {{$error['line']}}行目 {{$error['message']}}
            </li>
        @endforeach
        </ul>
    </div>
@endif
{{-- スキップ詳細 --}}
@if (session('csv_import_skipped_details'))
    <div class="alert alert-warning">
        <h4 class="alert-heading">スキップ詳細</h4>
        <ul>
        @foreach (session('csv_import_skipped_details') as $skip_detail)
            <li>
                {{$skip_detail['line']}}行目 {{$skip_detail['message']}}
            </li>
        @endforeach
        </ul>
    </div>
@endif
