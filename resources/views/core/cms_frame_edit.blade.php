{{--
 * CMSフレーム編集画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- フレーム(編集) --}}
<table class="table"><tr><td>
    <div class="panel-body">
        <ul class="nav nav-tabs">
            <li role="presentation" class="active"><a href="{{URL::to($current_page->permanent_link)}}/?action=frame_setting&frame_id={{ $frame->frame_id }}#{{ $frame->frame_id }}">編集</a></li>
            <li role="presentation"><a href="{{URL::to($current_page->permanent_link)}}/?core_action=frame_delete&frame_id={{ $frame->frame_id }}#{{ $frame->frame_id }}">削除</a></li>
        </ul>
    </div>

    <div class="panel-body">
        <form action="/core/frame/update/{{$current_page->id}}/{{ $frame->frame_id }}" name="form_{{ $frame->frame_id }}_setting" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="page-name" class="col-md-3 control-label">フレームタイトル</label>
                <div class="col-md-9">
                    <input type="text" name="frame_title" id="frame_title" class="form-control" value="{{$frame->frame_title}}">
                </div>
            </div>

            <div class="form-group">
                <label for="page-name" class="col-md-3 control-label">フレームデザイン</label>
                <div class="col-md-9">
                    <select class="form-control" name="frame_design" id="frame_design">
                        <option value="">Choose...</option>
                        <option value="none"    @if($frame->frame_design=="none")    selected @endif>None</option>
                        <option value="default" @if($frame->frame_design=="default") selected @endif>Default</option>
                        <option value="primary" @if($frame->frame_design=="primary") selected @endif>Primary</option>
                        <option value="success" @if($frame->frame_design=="success") selected @endif>Success</option>
                        <option value="info"    @if($frame->frame_design=="info")    selected @endif>Info</option>
                        <option value="warning" @if($frame->frame_design=="warning") selected @endif>Warning</option>
                        <option value="danger"  @if($frame->frame_design=="danger")  selected @endif>Danger</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="page-name" class="col-md-3 control-label">フレーム幅</label>
                <div class="col-md-9">
                    <select class="form-control" name="frame_col" id="frame_col">
                        <option value="">Choose...</option>
                        <option value="0"  @if($frame->frame_col==0)    selected @endif>100%</option>
                        <option value="1"  @if($frame->frame_col==1)    selected @endif>1</option>
                        <option value="2"  @if($frame->frame_col==2)    selected @endif>2</option>
                        <option value="3"  @if($frame->frame_col==3)    selected @endif>3</option>
                        <option value="4"  @if($frame->frame_col==4)    selected @endif>4</option>
                        <option value="5"  @if($frame->frame_col==5)    selected @endif>5</option>
                        <option value="6"  @if($frame->frame_col==6)    selected @endif>6</option>
                        <option value="7"  @if($frame->frame_col==7)    selected @endif>7</option>
                        <option value="8"  @if($frame->frame_col==8)    selected @endif>8</option>
                        <option value="9"  @if($frame->frame_col==9)    selected @endif>9</option>
                        <option value="10" @if($frame->frame_col==10)   selected @endif>10</option>
                        <option value="11" @if($frame->frame_col==11)   selected @endif>11</option>
                        <option value="12" @if($frame->frame_col==12)   selected @endif>12</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="page-name" class="col-md-3 control-label">プラグ</label>
                <div class="col-md-9">
                    <select class="form-control" name="plug_name" id="plug_name">
                        <option value="">プラグは使わない。</option>
                        <option value="OswsRss" @if($frame->plug_name=="OswsRss")   selected @endif>株式会社オープンソース・ワークショップの新着情報</option>
                        <option value="TestDb"  @if($frame->plug_name=="TestDb")    selected @endif>テストのデータベース読み込み</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-offset-3 col-md-9">
                    <button type="submit" class="btn btn-primary form-horizontal">更新</button>
                    <button type="button" class="btn btn-default form-horizontal" onclick="location.href='{{URL::to($current_page->permanent_link)}}'">キャンセル</button>
                </div>
            </div>
        </form>
    </div>
</td></tr></table>
