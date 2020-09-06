{{--
 * 契約管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 契約管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.contract.contract_tab')
    </div>

    <div class="card border-primary m-3">
        <div class="card-header bg-primary cc-primary-font-color">公式サポート契約内容</div>
        <table class="table">
            <thead>
            <tr>
                <th>契約項目</th>
                <th>契約内容</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <th>契約種別</th>
                <td>サポート契約</td>
            </tr>
            <tr>
                <th>契約期間</th>
                <td>2020年9月1日～2021年8月31日</td>
            </tr>
            <tr>
                <th>契約番号</th>
                <td>A0010-0001</td>
            </tr>
            <tr>
                <th>アクティベーション・キー</th>
                <td>Xh4gt$a-iU7m</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="card border-primary m-3">
        <div class="card-header bg-primary cc-primary-font-color">公式サポート契約更新</div>

        <div class="card-body">
            <form name="form_apis" id="form_apis" class="form-horizontal" method="post" action="{{url('/')}}/manage/contract/update">
                {{ csrf_field() }}

                <div class="form-group row">
                    <label for="name" class="col-md-4 col-form-label text-md-right">契約番号</label>
                    <div class="col-md-8">
                        <input id="site_id" type="text" class="form-control" name="site_id" value="" placeholder="送られてきた契約番号" required="" autofocus="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="name" class="col-md-4 col-form-label text-md-right">アクティベーション・キー</label>
                    <div class="col-md-8">
                        <input id="activation_key" type="text" class="form-control" name="activation_key" value="" placeholder="送られてきたアクティベーション・キー" required="" autofocus="">
                    </div>
                </div>

                {{-- Submitボタン --}}
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection
