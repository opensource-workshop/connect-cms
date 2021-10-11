{{--
 * 課題管理 試験設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

{{-- 試験設定フォーム --}}
@if (empty($learningtasks_posts->id))
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">課題データを作成してから、試験の設定をしてください。</p>
        </div>
    </div>
@else
    {{-- ダウンロードのアクション --}}
    <script type="text/javascript">
        {{-- ダウンロードのsubmit JavaScript --}}
        function submit_download_shift_jis() {
            if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で試験日時をダウンロードします。\nよろしいですか？') ) {
                return;
            }
            examination_download_csv.character_code.value = '{{CsvCharacterCode::sjis_win}}';
            examination_download_csv.submit();
        }
        function submit_download_utf_8() {
            if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で試験日時をダウンロードします。\nよろしいですか？') ) {
                return;
            }
            examination_download_csv.character_code.value = '{{CsvCharacterCode::utf_8}}';
            examination_download_csv.submit();
        }
    </script>
    <form action="{{url('/')}}/download/plugin/learningtasks/downloadCsvExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}" method="post" name="examination_download_csv">
        {{ csrf_field() }}
        <input type="hidden" name="character_code" value="">
    </form>

    <form action="{{url('/')}}/redirect/plugin/learningtasks/saveExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/editExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

        <div class="card mb-3 border-danger">
            <div class="card-body">
                <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
            </div>
        </div>

        <div class="mb-2">
            @include('plugins.common.errors_form_line')
        </div>

        <h5><span class="badge badge-secondary">使用項目の設定</span></h5>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">試験提出機能</label>
            <div class="col-md-9">
                <div class="custom-control custom-radio custom-control-inline">
                    @if(empty(old("post_examination_setting", $tool->getFunction('post_examination_setting', true))))
                        <input type="radio" value="" id="examination_null" name="post_examination_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_examination.show">
                    @else
                        <input type="radio" value="" id="examination_null" name="post_examination_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_examination.show">
                    @endif
                    <label class="custom-control-label" for="examination_null">課題管理設定に従う</label>
                </div><br />
                <div class="custom-control custom-radio custom-control-inline">
                    @if(old("post_examination_setting", $tool->getFunction('post_examination_setting', true)) == 'off')
                        <input type="radio" value="off" id="use_examination_off" name="post_examination_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_examination.show">
                    @else
                        <input type="radio" value="off" id="use_examination_off" name="post_examination_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_examination.show">
                    @endif
                    <label class="custom-control-label" for="use_examination_off">使用しない</label>
                </div><br />
                <div class="custom-control custom-radio custom-control-inline">
                    @if(old("post_examination_setting", $tool->getFunction('post_examination_setting', true)) == 'on')
                        <input type="radio" value="on" id="use_examination_on" name="post_examination_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_examination:not(.show)" aria-expanded="true" aria-controls="collapse_post_examination">
                    @else
                        <input type="radio" value="on" id="use_examination_on" name="post_examination_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_examination:not(.show)" aria-expanded="true" aria-controls="collapse_post_examination">
                    @endif
                    <label class="custom-control-label" for="use_examination_on">この課題独自に設定する</label>
                </div>
            </div>
        </div>

        {{-- 独自設定の場合のみ表示、その他は隠す --}}
        <div class="collapse {{$tool->getSettingShowstr("post_examination_setting")}} collapse_post_examination" id="collapse_post_examination">
            <h5><span class="badge badge-secondary">課題独自の項目設定</span></h5>

            <div class="form-group row mb-0">
                <label class="col-md-3 text-md-right">使用するレポート試験機能</label>
                <div class="col-md-9 d-md-flex">

                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" name="post_settings[use_examination]" value="on" class="custom-control-input" id="use_examination"data-toggle="collapse" data-target="#collapse_use_examination" aria-expanded="false" aria-controls="collapse_use_examination" @if(old("use_examination", $tool->getFunction('use_examination', true)) == 'on') checked=checked @endif>
                        <label class="custom-control-label" for="use_examination">提出（レポート試験機能を使う）</label>
                    </div>
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" name="post_settings[use_examination_evaluate]" value="on" class="custom-control-input" id="use_examination_evaluate" data-toggle="collapse" data-target="#collapse_use_examination_evaluate" aria-expanded="false" aria-controls="collapse_use_examination_evaluate" @if(old("use_examination_evaluate", $tool->getFunction('use_examination_evaluate', true)) == 'on') checked=checked @endif>
                        <label class="custom-control-label" for="use_examination_evaluate">評価</label>
                    </div>
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" name="post_settings[use_examination_reference]" value="on" class="custom-control-input" id="use_examination_reference" data-toggle="collapse" data-target="#collapse_use_examination_reference" aria-expanded="false" aria-controls="collapse_use_examination_reference" @if(old("use_examination_reference", $tool->getFunction('use_examination_reference', true)) == 'on') checked=checked @endif>
                        <label class="custom-control-label" for="use_examination_reference">教員から参考資料</label>
                    </div>
                </div>
            </div>

            {{-- 提出（レポート試験機能を使う）の場合のみ表示、他は隠す --}}
            <div class="collapse" id="collapse_use_examination">

                <div class="form-group row mb-0">
                    <label class="col-md-3 text-md-right">提出</label>
                    <div class="col-md-9 d-md-flex">

                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" name="post_settings[use_examination_file]" value="on" class="custom-control-input" id="use_examination_file" @if(old("use_examination_file", $tool->getFunction('use_examination_file', true)) == 'on') checked=checked @endif>
                            <label class="custom-control-label" for="use_examination_file">アップロード</label>
                        </div>
                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" name="post_settings[use_examination_comment]" value="on" class="custom-control-input" id="use_examination_comment" @if(old("use_examination_comment", $tool->getFunction('use_examination_comment', true)) == 'on') checked=checked @endif>
                            <label class="custom-control-label" for="use_examination_comment">本文入力</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="post_settings[use_examination_mail]" value="on" class="custom-control-input" id="use_examination_mail" @if(old("use_examination_mail", $tool->getFunction('use_examination_mail', true)) == 'on') checked=checked @endif>
                            <label class="custom-control-label" for="use_examination_mail">メール送信（教員宛）</label>
                        </div>
                    </div>
                </div>

                {{-- レポート試験機能の 評価 を使う場合のみ表示、他は隠す --}}
                <div class="collapse" id="collapse_use_examination_evaluate">

                    <div class="form-group row mb-0">
                        <label class="col-md-3 text-md-right">評価</label>
                        <div class="col-md-9 d-md-flex">
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" name="post_settings[use_examination_evaluate_file]" value="on" class="custom-control-input" id="use_examination_evaluate_file" @if(old("use_examination_evaluate_file", $tool->getFunction('use_examination_evaluate_file', true)) == 'on') checked=checked @endif>
                                <label class="custom-control-label" for="use_examination_evaluate_file">アップロード</label>
                            </div>
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" name="post_settings[use_examination_evaluate_comment]" value="on" class="custom-control-input" id="use_examination_evaluate_comment" @if(old("use_examination_evaluate_comment", $tool->getFunction('use_examination_evaluate_comment', true)) == 'on') checked=checked @endif>
                                <label class="custom-control-label" for="use_examination_evaluate_comment">コメント入力</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="post_settings[use_examination_evaluate_mail]" value="on" class="custom-control-input" id="use_examination_evaluate_mail" @if(old("use_examination_evaluate_mail", $tool->getFunction('use_examination_evaluate_mail', true)) == 'on') checked=checked @endif>
                                <label class="custom-control-label" for="use_examination_evaluate_mail">メール送信（受講者宛）</label>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- レポート試験機能の 教員から参考資料 を返す場合のみ表示、他は隠す --}}
                <div class="collapse" id="collapse_use_examination_reference">

                    <div class="form-group row mb-0">
                        <label class="col-md-3 text-md-right">教員から参考資料</label>
                        <div class="col-md-9 d-md-flex">
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" name="post_settings[use_examination_reference_file]" value="on" class="custom-control-input" id="use_examination_reference_file" @if(old("use_examination_reference_file", $tool->getFunction('use_examination_reference_file', true)) == 'on') checked=checked @endif>
                                <label class="custom-control-label" for="use_examination_reference_file">アップロード</label>
                            </div>
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" name="post_settings[use_examination_reference_comment]" value="on" class="custom-control-input" id="use_examination_reference_comment" @if(old("use_examination_reference_comment", $tool->getFunction('use_examination_reference_comment', true)) == 'on') checked=checked @endif>
                                <label class="custom-control-label" for="use_examination_reference_comment">コメント入力</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="post_settings[use_examination_reference_mail]" value="on" class="custom-control-input" id="use_examination_reference_mail" @if(old("use_examination_reference_mail", $tool->getFunction('use_examination_reference_mail', true)) == 'on') checked=checked @endif>
                                <label class="custom-control-label" for="use_examination_reference_mail">メール送信（受講者宛）</label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="form-group row">
                    <label class="col-md-3 text-md-right">表示方法</label>
                    <div class="col-md-9 d-md-flex">
                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" name="post_settings[use_examination_status_collapse]" value="on" class="custom-control-input" id="use_examination_status_collapse" @if(old("use_examination_status_collapse", $tool->getFunction('use_examination_status_collapse', true)) == 'on') checked=checked @endif>
                            <label class="custom-control-label" for="use_examination_status_collapse">履歴を開閉する</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <h5><span class="badge badge-secondary">申し込み設定</span></h5>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">申し込み可能判定</label>
            <div class="col-md-9">
                <div class="custom-control custom-radio custom-control-inline">
                    @if(empty(old("post_examination_timing", $tool->getFunction('post_examination_timing', true))))
                        <input type="radio" value="" id="post_examination_timing_null" name="post_examination_timing" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="" id="post_examination_timing_null" name="post_examination_timing" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="post_examination_timing_null">レポートが合格してから</label>
                </div><br />
                <div class="custom-control custom-radio custom-control-inline">
                    @if(old("post_examination_timing", $tool->getFunction('post_examination_timing', true)) == 'one')
                        <input type="radio" value="one" id="post_examination_timing_one" name="post_examination_timing" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="one" id="post_examination_timing_one" name="post_examination_timing" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="post_examination_timing_one">レポートが1回でも提出済みなら（合否のチェックはしない）</label>
                </div><br />
                <div class="custom-control custom-radio custom-control-inline">
                    @if(old("post_examination_timing", $tool->getFunction('post_examination_timing', true)) == 'no_fail')
                        <input type="radio" value="no_fail" id="examination_timing_no_fail" name="post_examination_timing" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="no_fail" id="examination_timing_no_fail" name="post_examination_timing" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="examination_timing_no_fail">レポートが提出済み＆最新が不合格ではない（合格のチェックはしない）</label>
                </div>
            </div>
        </div>

        <h5><span class="badge badge-secondary">日時設定</span></h5>

        <div class="form-group row">
            <div class="col-12 mb-2">
                <div class="row">
                    <label class="text-left col-4">
                        試験日時一覧
                    </label>
                    <div class="text-right col">
                        <div class="btn-group mr-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="submit_download_shift_jis();">
                                <i class="fas fa-file-download"></i> ダウンロード
                            </button>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">ドロップダウンボタン</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" onclick="submit_download_shift_jis(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                <a class="dropdown-item" href="#" onclick="submit_download_utf_8(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                            </div>
                        </div>

                        <a href="{{url('/')}}/plugin/learningtasks/importExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">
                            <span class="btn btn-success btn-sm"><i class="fas fa-file-upload"></i> インポート</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th nowrap>試験開始 <span class="badge badge-danger">必須</span></th>
                                <th nowrap>試験終了 <span class="badge badge-danger">必須</span></th>
                                <th nowrap>申込終了</th>
                                <th nowrap><i class="fas fa-trash-alt" title="削除"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($examinations as $examination)
                                <tr>
                                    <td nowrap class="align-middle">
                                        <input type="hidden" value="{{$examination->id}}" name="edit_examination_id[{{$examination->id}}]">

                                        {{-- [TODO] スマホでのカレンダーピッカー表示、table表示に難あり --}}
                                        <div class="input-group date" style="width: 220px;" id="edit_start_at{{$examination->id}}" data-target-input="nearest">
                                            <input type="text"
                                                name="edit_start_at[{{$examination->id}}]"
                                                value="{{old('edit_start_at.'.$examination->id, $examination->start_at)}}"
                                                class="form-control datetimepicker-input @if ($errors->has('edit_start_at.'.$examination->id)) border-danger @endif"
                                                data-target="#edit_start_at{{$examination->id}}"
                                            >
                                            <div class="input-group-append" data-target="#edit_start_at{{$examination->id}}" data-toggle="datetimepicker">
                                                <div class="input-group-text @if ($errors->has('edit_start_at.'.$examination->id)) border-danger @endif"><i class="far fa-clock"></i></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td nowrap class="align-middle">
                                        <div class="input-group date" style="width: 220px;" id="edit_end_at{{$examination->id}}" data-target-input="nearest">
                                            <input type="text"
                                                name="edit_end_at[{{$examination->id}}]"
                                                value="{{old('edit_end_at.'.$examination->id, $examination->end_at)}}"
                                                class="form-control datetimepicker-input @if ($errors->has('edit_end_at.'.$examination->id)) border-danger @endif"
                                                data-target="#edit_end_at{{$examination->id}}"
                                            >
                                            <div class="input-group-append" data-target="#edit_end_at{{$examination->id}}" data-toggle="datetimepicker">
                                                <div class="input-group-text @if ($errors->has('edit_end_at.'.$examination->id)) border-danger @endif"><i class="far fa-clock"></i></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td nowrap class="align-middle">
                                        <div class="input-group date" style="width: 220px;" id="edit_entry_end_at{{$examination->id}}" data-target-input="nearest">
                                            <input type="text"
                                                name="edit_entry_end_at[{{$examination->id}}]"
                                                value="{{old('edit_entry_end_at.'.$examination->id, $examination->entry_end_at)}}"
                                                class="form-control datetimepicker-input @if ($errors->has('edit_entry_end_at.'.$examination->id)) border-danger @endif"
                                                data-target="#edit_entry_end_at{{$examination->id}}"
                                            >
                                            <div class="input-group-append" data-target="#edit_entry_end_at{{$examination->id}}" data-toggle="datetimepicker">
                                                <div class="input-group-text @if ($errors->has('edit_entry_end_at.'.$examination->id)) border-danger @endif"><i class="far fa-clock"></i></div>
                                            </div>
                                        </div>

                                        <script type="text/javascript">
                                            $(function () {
                                                $('#edit_start_at{{$examination->id}}').datetimepicker({
                                                    locale: 'ja',
                                                    sideBySide: true,
                                                    dayViewHeaderFormat: 'YYYY年 M月',
                                                    format: 'YYYY-MM-DD HH:mm'
                                                });
                                                $('#edit_end_at{{$examination->id}}').datetimepicker({
                                                    locale: 'ja',
                                                    sideBySide: true,
                                                    dayViewHeaderFormat: 'YYYY年 M月',
                                                    format: 'YYYY-MM-DD HH:mm'
                                                });
                                                $('#edit_entry_end_at{{$examination->id}}').datetimepicker({
                                                    locale: 'ja',
                                                    sideBySide: true,
                                                    dayViewHeaderFormat: 'YYYY年 M月',
                                                    format: 'YYYY-MM-DD HH:mm'
                                                });
                                            });
                                        </script>
                                    </td>
                                    <td nowrap class="align-middle">
                                        <div class="custom-control custom-checkbox">
                                            {{-- チェック外した場合にも値を飛ばす対応 --}}
                                            <input type="hidden" value="0" name="del_examinations[{{$examination->id}}]">

                                            <input type="checkbox" name="del_examinations[{{$examination->id}}]" value="1" class="custom-control-input" id="del_examinations[{{$examination->id}}]" @if(old("del_examination.$examination->id")) checked=checked @endif>
                                            <label class="custom-control-label" for="del_examinations[{{$examination->id}}]"></label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td nowrap class="align-middle" colspan="4">※ 設定されている試験がありません。</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @foreach($examinations as $examination)
                        @include('plugins.common.errors_inline', ['name' => 'edit_start_at.'.$examination->id])
                        @include('plugins.common.errors_inline', ['name' => 'edit_end_at.'.$examination->id])
                        @include('plugins.common.errors_inline', ['name' => 'edit_entry_end_at.'.$examination->id])
                    @endforeach
                    <small class="text-muted">
                        ※ 削除する場合はチェックします。<br />
                        ※ 例えば「申込終了日時」を 4/19 00:00 と設定した場合、4/18 23:59まで申込可能になります。<br />
                        ※ 「申込終了日時」を設定しない場合、「試験終了日時」まで申込可能になります。<br />
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-12">試験日時追加</label>
            <div class="col">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group date" id="start_at" data-target-input="nearest">
                            <input type="text" name="start_at" value="{{old('start_at')}}" class="form-control datetimepicker-input @if ($errors->has('start_at')) border-danger @endif" data-target="#start_at" placeholder="開始日時">
                            <div class="input-group-append" data-target="#start_at" data-toggle="datetimepicker">
                                <div class="input-group-text @if ($errors->has('start_at')) border-danger @endif"><i class="far fa-clock"></i></div>
                            </div>
                        </div>
                        @include('plugins.common.errors_inline', ['name' => 'start_at'])
                    </div>
                    <div class="col-md-4">
                        <div class="input-group date" id="end_at" data-target-input="nearest">
                            <input type="text" name="end_at" value="{{old('end_at')}}" class="form-control datetimepicker-input @if ($errors->has('end_at')) border-danger @endif" data-target="#end_at" placeholder="終了日時">
                            <div class="input-group-append" data-target="#end_at" data-toggle="datetimepicker">
                                <div class="input-group-text @if ($errors->has('end_at')) border-danger @endif"><i class="far fa-clock"></i></div>
                            </div>
                        </div>
                        @include('plugins.common.errors_inline', ['name' => 'end_at'])
                    </div>
                    <div class="col-md-4">
                        <div class="input-group date" id="entry_end_at" data-target-input="nearest">
                            <input type="text" name="entry_end_at" value="{{old('entry_end_at')}}" class="form-control datetimepicker-input @if ($errors->has('entry_end_at')) border-danger @endif" data-target="#entry_end_at" placeholder="申込終了日時">
                            <div class="input-group-append" data-target="#entry_end_at" data-toggle="datetimepicker">
                                <div class="input-group-text @if ($errors->has('entry_end_at')) border-danger @endif"><i class="far fa-clock"></i></div>
                            </div>
                        </div>
                        @include('plugins.common.errors_inline', ['name' => 'entry_end_at'])
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(function () {
                $('#start_at').datetimepicker({
                    locale: 'ja',
                    sideBySide: true,
                    dayViewHeaderFormat: 'YYYY年 M月',
                    format: 'YYYY-MM-DD HH:mm'
                });
                $('#end_at').datetimepicker({
                    locale: 'ja',
                    sideBySide: true,
                    dayViewHeaderFormat: 'YYYY年 M月',
                    format: 'YYYY-MM-DD HH:mm'
                });
                $('#entry_end_at').datetimepicker({
                    locale: 'ja',
                    sideBySide: true,
                    dayViewHeaderFormat: 'YYYY年 M月',
                    format: 'YYYY-MM-DD HH:mm'
                });
            });
        </script>

        <h5><span class="badge badge-secondary">問題設定</span></h5>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">ファイル一覧</label>
            <div class="col-md-9">
                <div class="card p-2">
                @isset($post_files)
                @foreach($post_files as $examination_file)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="del_task_file[{{$examination_file->id}}]" value="1" class="custom-control-input" id="del_task_file[{{$examination_file->id}}]" @if(old("del_task_file.$examination_file->id")) checked=checked @endif>
                        <label class="custom-control-label" for="del_task_file[{{$examination_file->id}}]"><a href="{{url('/')}}/file/{{$examination_file->upload_id}}" target="_blank" rel="noopener">{{$examination_file->client_original_name}}</a></label>
                    </div>
                @endforeach
                @else
                    <div class="card-body p-0">
                        試験関係のファイルは添付されていません。
                    </div>
                @endisset
                </div>
                <small class="text-muted">削除する場合はチェックします。</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right" for="add_task_file">試験問題など</label>
            <div class="col-md-9">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="add_task_file" name="add_task_file" accept=".pdf, .doc, .docx">
                    <label class="custom-file-label" for="add_task_file" data-browse="参照">PDF もしくは ワード形式。</label>
                    @include('plugins.common.errors_inline', ['name' => 'add_task_file'])
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'">
                <i class="fas fa-angle-left"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> 詳細へ</span>
            </button>
            <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">
                {{-- <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span> --}}
                <i class="fas fa-undo-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span>
            </button>
            <input type="hidden" name="bucket_id" value="">
            {{-- change: 課題管理の試験設定は、登録・更新時に確認ダイアログを表示しない（試験日時登録で何度も確定ボタン押すため）
            @if (empty($learningtasks_posts->id))
                <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 登録確定</button>
            @else
                <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
            @endif
            --}}
            @if (empty($learningtasks_posts->id))
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
            @else
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
            @endif
        </div>
    </form>

    {{-- 初期状態で開くもの --}}
    @if(old("post_settings.use_examination", $tool->getFunction('use_examination', true)) == 'on')
        <script>
            $('#collapse_use_examination').collapse('show')
        </script>
    @endif

    @if(old("post_settings.use_examination_evaluate", $tool->getFunction('use_examination_evaluate', true)) == 'on')
        <script>
            $('#collapse_use_examination_evaluate').collapse('show')
        </script>
    @endif

    @if(old("post_settings.use_examination_reference", $tool->getFunction('use_examination_reference', true)) == 'on')
        <script>
            $('#collapse_use_examination_reference').collapse('show')
        </script>
    @endif

@endif

<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
