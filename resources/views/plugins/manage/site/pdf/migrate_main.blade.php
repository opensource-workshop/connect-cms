{{--
 * サイト管理（サイト設計書）の移行データ -移行データ一覧のテンプレート
 *
 * @author horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">移行データ</h2>
<br />
<h4>移行データ一覧</h4>
@if ($nc2_sort_pages)
    <h5>NC2移行対象ページ一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th" style="width: 50%;">ページ名</th>
            <th class="doc_th" style="width: 45%;">パーマリンク</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($nc2_sort_pages as $nc2_page)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$nc2_page->page_name}}</td><td>{{$nc2_page->permalink}}</td>
            </tr>
        @endforeach
    </table>
    <div>
    NC2移行対象ページ件数：{{$cnt}}
    <div style="font-size: 10px;">※migration_configで除外定義したデータを含みます</div>
    </div>
@endif
@if ($pages)
    <h5>Connect-CMSページ一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th" style="width: 50%;">ページ名</th>
            <th class="doc_th" style="width: 45%;">パーマリンク</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($pages as $page)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$page->page_name}}</td><td>{{$page->permanent_link}}</td>
            </tr>
        @endforeach
    </table>
    <div>
    Connect-CMSページ件数：{{$cnt}}
    <div style="font-size: 10px;">※{{date('Y年m月d日')}}時点でのデータになります</div>
    </div>
@endif

@if ($nc2_journals)
    <h5>NC2移行対象ブログ</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th">日誌名</th>
            <th class="doc_th">件数</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($nc2_journals as $nc2_journal)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$nc2_journal->journal_name}}</td><td>{{$nc2_journal->count}}</td>
            </tr>
        @endforeach
    </table>
    <div style="font-size: 10px;">※migration_configで除外定義した日誌データを含みます</div>
@endif
@if ($blogs)
    <h5>Connect-CMSブログ一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th">ブログ名</th>
            <th class="doc_th">件数</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($blogs as $blog)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$blog->blog_name}}</td><td>{{$blog->blogs_posts_count}}</td>
            </tr>
        @endforeach
    </table>
    <div style="font-size: 10px;">※{{date('Y年m月d日')}}時点でのデータになります</div>
@endif

@if ($nc2_multidatabases)
    <h5>NC2移行対象汎用DB</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th">DB名</th>
            <th class="doc_th">件数</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($nc2_multidatabases as $nc2_multidatabase)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$nc2_multidatabase->multidatabase_name}}</td><td>{{$nc2_multidatabase->multidatabase_content_count}}</td>
            </tr>
        @endforeach
    </table>
    <div style="font-size: 10px;">※migration_configで除外定義した汎用DBデータを含みます</div>
@endif
@if ($databases)
    <h5>Connect-CMSデータベース一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th">データベース名</th>
            <th class="doc_th">件数</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($databases as $database)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$database->databases_name}}</td><td>{{$database->databases_inputs_count}}</td>
            </tr>
        @endforeach
    </table>
    <div style="font-size: 10px;">※{{date('Y年m月d日')}}時点でのデータになります</div>
@endif

@if ($nc2_users)
    <h5>NC2移行対象会員</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th">ログインID</th>
            <th class="doc_th">ハンドル</th>
            <th class="doc_th">権限</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($nc2_users as $nc2_user)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$nc2_user->login_id}}</td><td>{{$nc2_user->handle}}</td><td>{{$nc2_user->role_authority_name}}</td>
            </tr>
        @endforeach
    </table>
    <div style="font-size: 10px;">※migration_configで除外定義した会員データを含みます</div>
@endif

@if ($users)
    <h5>Connect-CMS会員一覧</h5>
    <table border="0" class="table_css">
        <tr nobr="true">
            <th class="doc_th" style="width: 5%;">No.</th>
            <th class="doc_th">ログインID</th>
            <th class="doc_th">ユーザ名</th>
            <th class="doc_th">権限</th>
        </tr>
        @php 
            $cnt=0;
        @endphp
        @foreach($users as $user)
            @php 
            $cnt++;
            @endphp
            <tr nobr="true">
                <td>{{$cnt}}</td><td>{{$user->userid}}</td><td>{{$user->name}}</td><td>{!! $user->str_role_name !!}</td>
            </tr>
        @endforeach
    </table>
    <div style="font-size: 10px;">※{{date('Y年m月d日')}}時点でのデータになります</div>
@endif

