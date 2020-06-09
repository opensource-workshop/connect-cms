{{--
 * 検索条件の補足
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
--}}
<div id="collapse-search-help" class="collapse form-group mt-3">
    <div class="card">
        <div class="card-body">
            <b>通常の検索</b><br>
            <p>
                単語を入力すると、全項目に部分一致検索になります。<br>
                半角空白( )かカンマ(,)の区切って検索すると、and検索になります。<br>
                <div class="alert alert-secondary" role="alert">
                    例) apple word bear<br>
                    例) 場所 担当
                </div>
            </p>
            <b>項目を指定して検索</b><br>
            <p>
                <code class="highlighter-rouge"><カラム名>=<検索値></code>形式にすると、指定した項目で完全一致検索ができます。<br>
                指定できる項目は下記表の通りです。<br>
                <div class="alert alert-secondary" role="alert">
                    例) type_code1=topic code=1<br>
                    例) type_name=場所
                </div>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th scope="col">カラム名</th>
                            <th scope="col">表示名</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>plugin_name</td><td>プラグイン（英語）</td></tr>
                        <tr><td>plugin_name_full</td><td>プラグイン（日本語）</td></tr>
                        <tr><td>bucket_name</td><td>bucket_name</td></tr>
                        <tr><td>buckets_id</td><td>buckets_id</td></tr>
                        <tr><td>prefix</td><td>prefix</td></tr>
                        <tr><td>type_name</td><td>type_name</td></tr>
                        <tr><td>type_code1</td><td>type_code1</td></tr>
                        <tr><td>type_code2</td><td>type_code2</td></tr>
                        <tr><td>type_code3</td><td>type_code3</td></tr>
                        <tr><td>type_code4</td><td>type_code4</td></tr>
                        <tr><td>type_code5</td><td>type_code5</td></tr>
                        <tr><td>code</td><td>コード</td></tr>
                        <tr><td>value</td><td>値</td></tr>
                        <tr><td>additional1</td><td>additional1</td></tr>
                        <tr><td>additional2</td><td>additional2</td></tr>
                        <tr><td>additional3</td><td>additional3</td></tr>
                        <tr><td>additional4</td><td>additional4</td></tr>
                        <tr><td>additional5</td><td>additional5</td></tr>
                        <tr><td>display_sequence</td><td>表示順</td></tr>
                    </tbody>
                </table>
            </p>
            <b>空白を含む単語の検索</b><br>
            <p>
                ""か''で囲む事で空白を含む検索ができます。<br>
                <div class="alert alert-secondary" role="alert">
                    例) "apple word"<br>
                    例) "type_code1=topic code"<br>
                    例) "value=コネクト 太郎"<br>
                </div>
            </p>
        </div>
    </div>
</div>
