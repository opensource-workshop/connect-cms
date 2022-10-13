{{--
 * 項目の設定画面
 *
 * 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")

    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.slideshows.slideshows_frame_edit_tab')

@endsection

@section("plugin_setting_$frame->id")
    @auth
        @if (empty($slideshows_id))
            {{-- バケツデータ未作成の場合 --}}
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                フォーム選択画面から選択するか、フォーム新規作成で作成してください。
            </div>
        @else
            {{-- バケツデータ作成済みの場合 --}}
            <div class="form-group" id="app_{{ $frame->id }}">
                {{-- 項目の追加、削除等処理用の汎用フォーム --}}
                <form action="" id="slideshow_items" name="slideshow_items" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="slideshows_id" value="{{$slideshows_id}}">
                    <input type="hidden" name="item_id" value="">
                    <input type="hidden" name="display_sequence" value="">
                    <input type="hidden" name="display_sequence_operation" value="">
                    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/slideshows/editItem/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

                    {{-- エラーメッセージエリア ※共通blade呼び出し --}}
                    @include('plugins.common.errors_form_line')

                    {{-- エラーメッセージ --}}
                    @if (session('slideshows_error_message'))
                    <div class="alert alert-danger mt-2">
                        <i class="fas fa-exclamation-circle"></i> {{ session('slideshows_error_message') }}
                    </div>
                    @endif

                    {{-- メッセージエリア --}}
                    <div class="alert alert-info mt-2">
                        @if (session('flash_message'))
                            <i class="fas fa-exclamation-circle"></i>{{ session('flash_message') }}
                            <a href="https://manual.connect-cms.jp/user/slideshows/index.html" target="_brank"><i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i></a>
                        @else
                            <ul>
                                <li>
                                    スライドショーに表示させる画像やリンクを設定します。
                                    <a href="https://manual.connect-cms.jp/user/slideshows/index.html" target="_brank"><i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i></a>
                                </li>
                                <li>PDFを選択して追加することで、PDFの内容を画像に変換して登録できます。</li>
                            </ul>
                        @endif
                    </div>

                    {{-- 項目一覧 --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr class="d-none d-xl-table-row">
                                    <th class="text-center text-nowrap align-middle d-block d-xl-table-cell">表示順</th>
                                    <th class="text-center text-nowrap align-middle d-block d-xl-table-cell">表示</th>
                                    <th class="text-center text-nowrap align-middle d-block d-xl-table-cell">画像<label class="badge badge-danger">必須</label></th>
                                    <th class="text-center text-nowrap align-middle d-block d-xl-table-cell">リンクURL</th>
                                    <th class="text-center text-nowrap align-middle d-block d-xl-table-cell">キャプション</th>
                                    <th class="text-center text-nowrap align-middle d-block d-xl-table-cell">リンクターゲット</th>
                                    <th class="text-center text-nowrap align-middle d-block d-xl-table-cell">削除</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- 新規登録用の行 --}}
                                <tr>
                                    <th colspan="7">【項目の追加行】</th>
                                </tr>
                                @include('plugins.user.slideshows.default.slideshows_edit_row_add')
                                <tr>
                                    <th colspan="7">【既存の設定行】</th>
                                </tr>
                                {{-- 更新用の行 --}}
                                @foreach($items as $item)
                                    @include('plugins.user.slideshows.default.slideshows_edit_row')
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- ボタンエリア --}}
                    <div class="text-center mt-3 mt-md-0">
                        {{-- キャンセルボタン --}}
                        <button type="button"
                            class="btn btn-secondary mr-2"
                            onclick="location.href='{{URL::to($page->permanent_link)}}'"
                        >
                            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span>
                        </button>
                        @if ($items->count() > 0)
                            {{-- 更新ボタン --}}
                            <button
                                type="button"
                                class="btn btn-primary mr-2"
                                onclick="javascript:return submit_update_items();"
                            >
                                <i class="fas fa-check"></i> 更新
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        <script>
            /**
             * 項目の追加ボタン押下
             */
            function submit_add_item() {
                slideshow_items.action = "{{url('/')}}/redirect/plugin/slideshows/addItem/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                slideshow_items.submit();
            }

            /**
             * PDF選択の追加ボタン押下
             */
            function submit_add_pdf() {
                slideshow_items.action = "{{url('/')}}/redirect/plugin/slideshows/addPdf/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                slideshow_items.submit();
            }

            /**
             * 項目の削除ボタン押下
             */
            function submit_delete_item(item_id) {
                if(confirm('項目を削除します。\nよろしいですか？')){
                    slideshow_items.action = "{{url('/')}}/redirect/plugin/slideshows/deleteItem/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                    slideshow_items.item_id.value = item_id;
                    slideshow_items.submit();
                }
                return false;
            }

            /**
             * 項目の更新ボタン押下
             */
            function submit_update_items() {
                if(confirm('更新します。\nよろしいですか？')){
                    slideshow_items.action = "{{url('/')}}/redirect/plugin/slideshows/updateItems/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                    slideshow_items.submit();
                }
                return false;
            }

            /**
             * 項目の表示順操作ボタン押下
             */
            function submit_display_sequence(item_id, display_sequence, display_sequence_operation) {
                slideshow_items.action = "{{url('/')}}/redirect/plugin/slideshows/updateItemSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                slideshow_items.item_id.value = item_id;
                slideshow_items.display_sequence.value = display_sequence;
                slideshow_items.display_sequence_operation.value = display_sequence_operation;
                slideshow_items.submit();
            }

            /**
             * ツールチップ
             */
            $(function () {
                // 有効化
                $('[data-toggle="tooltip"]').tooltip()
            })

            /**
             * 新規追加行・更新行のimgのsrc、及び、モーダルのヘッダーに表示しているファイル名をイベントから抽出して動的に書き換える
             */
             const app_{{ $frame->id }} = new Vue({
                el: "#app_{{ $frame->id }}",
                data: function() {
                    return {
                        // 更新行用の変数
                        @foreach($items as $item)
                            image_url_{{ $item->id }} : "{{ url('/') }}/file/{{ $item->uploads_id }}",
                            file_name_{{ $item->id }} : "{{ $item->client_original_name }}",
                        @endforeach
                        // 新規追加行用の変数
                        image_url_add:"",
                        file_name_add:"",
                        selected_pdf:""
                    }
                },
                methods: {
                    // 受け取ったイベントから画像オブジェクトのURL、ファイル名を生成してHTMLにセットする
                    setImageResource(items_id, event){
                        const file = event.target.files[0];
                        // console.log(file);
                        eval("this.image_url_" + items_id + " = URL.createObjectURL(file);");
                        eval("this.file_name_" + items_id + " = file.name;");
                    },
                    // 選択中のPDFを表示する
                    setPdfFile(event){
                        let file = event.target.files[0];
                        if (file === undefined) {
                            this.selected_pdf = "";
                        }
                        this.selected_pdf = file.name;
                    }
                }
            });
        </script>
    @endauth
@endsection
