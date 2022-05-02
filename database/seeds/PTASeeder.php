<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User\Contents\Contents;
use App\Models\User\Menus\Menu;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Whatsnews\Whatsnews;
use App\Models\User\Cabinets\Cabinet;
use App\Models\User\Cabinets\CabinetContent;
use App\Models\User\Calendars\Calendar;
use App\Models\User\Calendars\CalendarFrame;
use App\Models\User\Calendars\CalendarPost;
use App\Models\User\Searchs\Searchs;
use App\User;
use App\Models\Core\UsersRoles;
use App\Models\Core\Configs;
use App\Models\Common\Page;

// 実行コマンドメモ
//※注意※ Connect-CMS初期データ実行後に実行してください
//% php artisan db:seed --class=PTASeeder

class PTASeeder extends Seeder
{
    // インサートユーザID
    const CREATED_ID = 1;
    // インサートユーザNAME
    const CREATED_NAME = 'SEEDER';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //インサート用データを取得する
        $pages_data = $this->getPagesData();
        $contents_data = $this->getContentsData();
        $menus_data = $this->getMenusData();
        list($blogs_frame_data, $blogs_data, $blogs_posts_data) = $this->getBlogsData();
        $whatsnews_data = $this->getWhatsnewsData();
        $cabinets_data = $this->getCabinetsData();
        $calendars_data = $this->getCalendarsData();
//        $counters_data = $this->getCountersData();
        //Config関係
        $configs_data = $this->getConfigsData();
//        $users_data = $this->getUsersData();
        $searchs_data = $this->getSearchsData();

        // データをクリアする
        $this->trancate('pages');
        $this->trancate('frames');
        $this->trancate('buckets');
        $this->trancate('contents');
        $this->trancate('menus');
        $this->trancate('blogs');
        $this->trancate('blogs_posts');
        $this->trancate('whatsnews');
        $this->trancate('cabinets');
        $this->trancate('cabinet_contents');
        $this->trancate('calendars');
        $this->trancateUsers(); // 初期ユーザのみ残す

        foreach($pages_data as $row) {
            // ページデータ作成
            $page_id = $this->insertGetPageId($row);

            // 固定記事データ登録
            if(isset($contents_data[$row['permanent_link']])) {
                $this->insertContentsData($page_id, $contents_data[$row['permanent_link']]);
            }

            // メニューデータ登録
            if(isset($menus_data[$row['permanent_link']])) {
                $this->insertMenusData($page_id, $menus_data[$row['permanent_link']]);
            }

            // ブログデータ登録
            if($blogs_data && $blogs_posts_data) {
                $this->insertBlogsData($blogs_data);
                $this->insertBlogsPostsData($blogs_posts_data);
            }

            // ブログフレームデータ登録
            if(isset($blogs_frame_data[$row['permanent_link']])) {
                $this->insertBlogsFrameData($page_id, $blogs_frame_data[$row['permanent_link']]);
            }

            // 新着情報登録
            if(isset($whatsnews_data[$row['permanent_link']])) {
                $this->insertWhatsnewsData($page_id, $whatsnews_data[$row['permanent_link']]);
            }

            // キャビネット登録
            if(isset($cabinets_data[$row['permanent_link']])) {
                $this->insertCabinetsData($page_id, $cabinets_data[$row['permanent_link']]);
            }

            // カレンダー登録
            if(isset($calendars_data[$row['permanent_link']])) {
                $this->insertCalendarsData($page_id, $calendars_data[$row['permanent_link']]);
            }

            // 検索登録
            if(isset($searchs_data[$row['permanent_link']])) {
                $this->insertSearchsData($page_id, $searchs_data[$row['permanent_link']]);
            }

//            // カウンター登録
//            if(isset($counters_data[$row['permanent_link']])) {
//                $this->insertCountersData($page_id, $counters_data[$row['permanent_link']]);
//            }
        }

        // サイト管理関係
        if($configs_data) {
            $this->insertConfigsData($configs_data);
        }

//        // ユーザデータ登録
//        if($users_data) {
//            $this->insertUsersData($users_data);
//
//        }

        // 校外パトロールにカレンダーフレームの追加
        $patrol_page = Page::where('permanent_link', '/patrol')->first();
        $calendar = Calendar::where('name', '行事予定')->first();
        $frame_row = $this->genFrameRows($patrol_page->id, 2, "行事予定", "primary", "calendars", "default", $calendar->bucket_id, 2);
        $frame_id = $this->insertGetFrameId($frame_row);
    }

    private function getPagesData()
    {
        /*
            SELECT 
                P.page_name, P.permanent_link, P.layout, P.transfer_lower_page_flag, P._lft, P._rgt, P.parent_id 
            FROM `pages` P 
            ORDER BY P.id ASC
        */
        $pages_arr = [];
        $pages_arr[] = ["ホーム"           ,"/"              ,"1|0|0|1" ,0 ,1  ,2  ,NULL];
        $pages_arr[] = ["学校お便り"       ,"/otayori"       ,NULL      ,0 ,3  ,4  ,NULL];
        $pages_arr[] = ["PTAより"          ,"/pta"           ,"1|1|0|1" ,1 ,5  ,12 ,NULL];
        $pages_arr[] = ["本部"             ,"/pta/honbu"     ,NULL      ,0 ,6  ,7  ,3];
        $pages_arr[] = ["広報"             ,"/pta/kouho"     ,NULL      ,0 ,8  ,9  ,3];
        $pages_arr[] = ["会計"             ,"/pta/kaikei"    ,NULL      ,0 ,10 ,11 ,3];
        $pages_arr[] = ["学級"             ,"/gakkyu"        ,NULL      ,0 ,13 ,14 ,NULL];
        $pages_arr[] = ["校外パトロール"   ,"/patrol"        ,NULL      ,0 ,15 ,16 ,NULL];
        $pages_arr[] = ["サークル"         ,"/circle"        ,"1|1|0|1" ,1 ,17 ,20 ,NULL];
        $pages_arr[] = ["スポーツサークル" ,"/circle/sports" ,NULL      ,0 ,18 ,19 ,9];
        $pages_arr[] = ["研修"             ,"/traning"       ,NULL      ,0 ,21 ,22 ,NULL];
        $pages_arr[] = ["サイト内検索"     ,"/search"        ,NULL      ,0 ,23 ,24 ,NULL];
        $pages = [];
        foreach($pages_arr as $key => $row) {
            $pages[] = [
                'page_name'                => $row[0],
                'permanent_link'           => $row[1],
                'layout'                   => $row[2],
                'transfer_lower_page_flag' => $row[3],
                '_lft'                     => $row[4],
                '_rgt'                     => $row[5],
                'parent_id'                => $row[6],
            ];
        }
        return $pages;
    }
    private function getContentsData()
    {
        // 初期配置する固定記事データを記載する
        /*
            SELECT 
                P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, C.content_text 
            FROM `contents` C 
            INNER JOIN buckets B ON B.id = C.bucket_id 
            INNER JOIN frames F ON B.id = F.bucket_id 
            INNER JOIN pages P ON P.id = F.page_id 
            WHERE C.status = '0' ORDER BY C.id DESC
        */
        // 1ページに複数個配置されることを想定する
        $contens_arr = [];
        $contens_arr[] = [
            "/",
            2, NULL, "none", "default", 1,
            '<div class="contents"><div class="svg-wrapper" style="width: 100%;"><svg width="1200" height="300" style="width: 100%;"><rect width="100%" height="100%" style="fill: #6b8e23; stroke-width: 1; stroke: #000000;"></rect><text x="50" y="50" style="font-size:14px; fill: #000;">ここに、学校の写真や校章画像を指定してください。</text></svg></div></div>'
        ];
        $contens_arr[] = [
            "/",
            2, "PTA会長よりご挨拶", "primary", "default", 2,
            '<p style="float: left; margin: 0px 10px 0px 5px; text-align: center;"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" style="width: 200px;"> <rect width="200" height="200" style="fill: #6b8e23; stroke-width: 1; stroke: #000000;"></rect> </svg><br />PTA 会長 〇〇〇　〇〇</p><p>PTA の意義や、目的などをこのエリアに記載しましょう。</p>'
        ];
        $contens_arr[] = [
            "/",
            4, NULL, "none", "default", 1,
            '<div><table class="table cc-table-md-responsive"><tbody><tr><td><a href="' . url('/') . '/otayori">学校お便り</a></td><td>PTAより<br /><a href="' . url('/') . '/pta/honbu">本部</a><br /><a href="' . url('/') . '/pta/kouho">広報</a><br /><a href="' . url('/') . '/pta/kaikei">会計</a></td><td><a href="' . url('/') . '/gakkyu">学級</a><br /><a href="' . url('/') . '/patrol">校外パトロール</a></td><td><a href="' . url('/') . '/circle">サークル</a><br /><a href="' . url('/') . '/traning">研修</a></td></tr></tbody></table></div>'
        ];
        $contens_arr[] = [
            "/",
            0, NULL, "none", "default", 1,
            '<div class="d-sm-flex flex-row-reverse"><form action="' . url('/') . '/search" method="get" role="search" aria-label="サイト内検索"><div class="input-group mt-2"><input type="text" name="search_keyword" class="form-control" value="" placeholder="キーワードでサイト内検索" title="サイト内検索" /><div class="input-group-append"><button type="submit" class="btn btn-primary" title="検索"> <i class="fas fa-search"></i> </button></div></div></form></div>'
        ];
        $contens_arr[] = [
            "/patrol",
            2, "校外パトロールについて", "primary", "default", 1,
            '<p>校外パトロールの説明を記載します。</p><p>以下に予定を記載します。</p>'
        ];

        foreach($contens_arr as $val){
            $contents_data[$val[0]][] = [
                "area_id"          => $val[1],
                "frame_title"      => $val[2],
                "frame_design"     => $val[3],
                "template"         => $val[4],
                "display_sequence" => $val[5],
                "content_text"     => $val[6],
            ];
        }

        return $contents_data;
    }

    private function getMenusData()
    {
        // 初期配置するメニューデータを記載する
        /*
            SELECT P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, M.`select_flag`, M.`page_ids`
            FROM `menus` M
            INNER JOIN frames F ON M.frame_id = F.id
            INNER JOIN pages P ON F.page_id = P.id
            ORDER BY M.id ASC;
        */
        // 1ページに複数個配置されることを想定する
        $menus_arr = [];
        $menus_arr[] = ["/",       0, NULL, "none", "dropdown", 2, 1, "1,2,3,4,5,6,7,8,9,10,11"];
        $menus_arr[] = ["/pta",    1, NULL ,"none", "default" , 1, 1, "3,4,5,6"];
        $menus_arr[] = ["/circle", 1, NULL ,"none", "default" , 1, 1, "9,10"];

        $menus_data = [];
        foreach($menus_arr as $val){
            $menus_data[$val[0]][] = ["area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5], "select_flag" => $val[6], "page_ids" => $val[7],];
        }
        return $menus_data;
    }

    private function getBlogsData()
    {
        // 初期配置するブログデータを記載する
        /*
            SELECT 
                P.permanent_link,
                F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, 
                BL.blog_name,
                BP.contents_id, BP.blogs_id, BP.post_title, BP.post_text, BP.post_text2, BP.read_more_flag, 
                BP.read_more_button, BP.close_more_button, BP.posted_at
            FROM `blogs` BL 
            INNER JOIN `blogs_posts` BP ON BP.blogs_id = BL.id 
            INNER JOIN buckets B ON B.id = BL.bucket_id 
            INNER JOIN frames F ON B.id = F.bucket_id 
            INNER JOIN pages P ON P.id = F.page_id 
            WHERE BP.status = '0'
            ORDER BY BL.id DESC
        */
        // フレーム違いの重複するblogs,blogs_postsデータはインサート時に弾く
        $blogs_arr = [];
        $blogs_arr[] = [
            "/otayori",
            2, "学校お便り", "primary", "default", 1,
            "学校お便り",
            1, 1, "学校お便りブログのサンプル記事",
            "<p>ブログのサンプルの記事です。</p>",
            NULL, 0, NULL, NULL, date('Y-m-d H:i:s')
        ];
        $blogs_arr[] = [
            "/pta/honbu",
            2, "本部より", "primary", "default", 1,
            "本部より",
            2, 2, "本部ブログのサンプル記事",
            "<p>ブログのサンプルの記事です。</p>",
            NULL, 0, NULL, NULL, date('Y-m-d H:i:s')
        ];
        $blogs_arr[] = [
            "/pta/kouho",
            2, "広報より", "primary", "default", 1,
            "広報より",
            3, 3, "広報ブログのサンプル記事",
            "<p>ブログのサンプルの記事です。</p>",
            NULL, 0, NULL, NULL, date('Y-m-d H:i:s')
        ];
        $blogs_arr[] = [
            "/pta/kaikei",
            2, "会計より", "primary", "default", 1,
            "会計より",
            4, 4, "会計ブログのサンプル記事",
            "<p>ブログのサンプルの記事です。</p>",
            NULL, 0, NULL, NULL, date('Y-m-d H:i:s')
        ];
        $blogs_arr[] = [
            "/gakkyu",
            2, "学級", "primary", "default", 1,
            "学級",
            5, 5, "学級ブログのサンプル記事",
            "<p>ブログのサンプルの記事です。</p>",
            NULL, 0, NULL, NULL, date('Y-m-d H:i:s')
        ];
        $blogs_arr[] = [
            "/circle/sports",
            2, "スポーツサークルより", "primary", "default", 1,
            "スポーツサークルより",
            6, 6, "スポーツサークルブログのサンプル記事",
            "<p>ブログのサンプルの記事です。</p>",
            NULL, 0, NULL, NULL, date('Y-m-d H:i:s')
        ];
        $blogs_arr[] = [
            "/traning",
            2, "研修", "primary", "default", 1,
            "研修より",
            7, 7, "研修のサンプル記事",
            "<p>ブログのサンプルの記事です。</p>",
            NULL, 0, NULL, NULL, date('Y-m-d H:i:s')
        ];

        foreach($blogs_arr as $val){
            $blogs_frame_data[$val[0]][$val[5]] = [
                "area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5], "blog_name" => $val[6]
            ];
            $blogs_data[$val[8]] = ["blog_name" => $val[6]];
            $blogs_posts_data[] = [
                "blog_name" => $val[6],
                "contents_id" => $val[7], "blogs_id" => $val[8], "post_title" => $val[9], "post_text" => $val[10], "post_text2" => $val[11],
                "read_more_flag" => $val[12], "read_more_button" => $val[13], "close_more_button" => $val[14], "posted_at" => $val[15],
            ];
        }

        return [$blogs_frame_data,$blogs_data,$blogs_posts_data];
    }

    private function getWhatsnewsData()
    {
        // 初期配置する新着プラグインを記載する
        /*
            SELECT 
                P.permanent_link,
                F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, 
                W.whatsnew_name ,W.view_pattern ,W.count ,W.days ,W.rss ,W.rss_count ,W.page_method ,W.page_count,
                W.view_posted_name ,W.view_posted_at ,W.important ,W.read_more_use_flag ,W.read_more_name ,W.read_more_fetch_count,
                W.read_more_btn_color_type ,W.read_more_btn_type ,W.read_more_btn_transparent_flag ,W.target_plugins ,W.frame_select ,W.target_frame_ids
            FROM `whatsnews` W 
            INNER JOIN buckets B ON B.id = W.bucket_id 
            INNER JOIN frames F ON B.id = F.bucket_id 
            INNER JOIN pages P ON P.id = F.page_id 
            ORDER BY W.id ASC
        */
        // target_frame_ids は指定が難しいので、SQLとは別にフレーム名を取得する
        $whatsnews_arr = [];
        $whatsnews_arr[] = [
            "/",
            2, "新着情報", "primary", "onerow", 3,
           "新着情報", 0, 10, 0, 1, 10, 0, 0, 0, 1, NULL, 0, "もっと見る", 5, "primary", "rounded", 0, "blogs", 0, ""];
        $whatsnews_data = [];

        foreach($whatsnews_arr as $val){
            // frame_id指定の場合を考慮
            $target_frame_ids = "";
            if(!empty($val[25])){
                $frame_names = explode(",",$val[25]);
                $frame_ids = [];
                foreach($frame_names as $frame_name) {
                    $frame = DB::table('frames')->select('id')->where('frame_title', $frame_name)->first();
                    if($frame){
                        $frame_ids[] = $frame->id;
                    }
                }
                $target_frame_ids = implode(",", $frame_ids);
            }
            $whatsnews_data[$val[0]][] = [
                "area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5], 
                "whatsnew_name" => $val[6], "view_pattern" => $val[7], "count" => $val[8], "days" => $val[9], "rss" => $val[10], "rss_count" => $val[11],
                "page_method" => $val[12], "page_count" => $val[13], "view_posted_name" => $val[14], "view_posted_at" => $val[15], "important" => $val[16],
                "read_more_use_flag" => $val[17], "read_more_name" => $val[18], "read_more_fetch_count" => $val[19], "read_more_btn_color_type" => $val[20],
                "read_more_btn_type" => $val[21], "read_more_btn_transparent_flag" => $val[22], "target_plugins" => $val[23], "frame_select" => $val[24], "target_frame_ids" => $target_frame_ids,
            ];
        }        
        return $whatsnews_data;
    }
    private function getCabinetsData()
    {
        // 初期配置するキャビネットデータを記載する
        /*
            SELECT 
                P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, 
                C.name, C.upload_max_size, C.approval_flag, C.comment
            FROM `cabinets` C 
            INNER JOIN buckets B ON B.id = C.bucket_id 
            INNER JOIN frames F ON B.id = F.bucket_id 
            INNER JOIN pages P ON P.id = F.page_id 
            ORDER BY C.id ASC
        */
        $cabinets_arr = [];
        $cabinets_arr[] = [
            "/pta/kaikei", 2, "会計報告", "primary", "default", 2,
            "会計報告", "5120", 0, NULL
        ];

        $cabinets_data = [];
        foreach($cabinets_arr as $val){
            $cabinets_data[$val[0]][$val[5]] = ["area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5],
             "name" => $val[6], "upload_max_size" => $val[7], "approval_flag" => $val[8], "comment" => $val[9],
            ];
        }        
        return $cabinets_data;
    }

    private function getCalendarsData()
    {
        // 初期配置するカレンダーデータを記載する
        /*
            SELECT 
                P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, 
                C.name
            FROM `calendars` C 
            INNER JOIN buckets B ON B.id = C.bucket_id 
            INNER JOIN frames F ON B.id = F.bucket_id 
            INNER JOIN pages P ON P.id = F.page_id 
            ORDER BY C.id ASC
        */
        $calendars_arr = [];
        $calendars_arr[] = ["/", 2, "行事予定", "primary", "default", 4, "行事予定"];

        $calendars_data = [];
        foreach($calendars_arr as $val){
            $calendars_data[$val[0]][$val[5]] = ["area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5],
             "name" => $val[6]
            ];
        }        
        return $calendars_data;
    }

    private function getSearchsData()
    {
        // 初期配置する検索データを記載する
        /*
            SELECT 
                P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, 
                S.search_name, S.count, S.target_plugins, S.recieve_keyword
            FROM `searchs` S 
            INNER JOIN buckets B ON B.id = S.bucket_id 
            INNER JOIN frames F ON B.id = F.bucket_id 
            INNER JOIN pages P ON P.id = F.page_id 
            ORDER BY S.id ASC
        */
        $searchs_arr = [];
        $searchs_arr[] = [
            "/search",
            2, "サイト内検索", "primary", "default", 1,
            "サイト内検索", 10, "contents,blogs,bbses", 1
        ];

        $searchs_data = [];
        foreach($searchs_arr as $val){
            $searchs_data[$val[0]][$val[5]] = ["area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5],
             "search_name" => $val[6], "count" => $val[7], "target_plugins" => $val[8], "recieve_keyword" => $val[9]
            ];
        }        
        return $searchs_data;
    }

    private function getCountersData()
    {
        return [];
    }
    private function getConfigsData()
    {
        $configs_arr = [];
        $configs_arr[] = ["browser_width_footer","100%","browser_width"];
        $configs_data = [];
        foreach($configs_arr as $val){
            $configs_data[] = ["name" => $val[0], "value" => $val[1], "category" => $val[2]];
        }
        return $configs_data;
    }
    private function getUsersData()
    {
        // 初期登録する会員データを記載する
        /*
            SELECT 
                U.name, U.email, U.email_verified_at, U.userid, U.password, U.status,
                UR.target, UR.role_name, UR.role_value
            FROM `users` U
            LEFT JOIN users_roles UR ON U.id = UR.users_id 
            WHERE U.status = '0' AND U.id != 1
            ORDER BY U.id ASC
        */
        $adm_pass = "C-school-adm";
        $edi_pass = "C-editor";
        $mod_pass = "C-mode";
        $users_arr = [];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","base","role_article_admin","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","base","role_arrangement","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","base","role_reporter","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","base","role_approval","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","base","role_article","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","manage","admin_system","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","manage","admin_page","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","manage","admin_site","1"];
        $users_arr[] = ["管理者",NULL,NULL,"school-adm",$adm_pass,"0","manage","admin_user","1"];
        $users_arr[] = ["編集者",NULL,NULL,"editor",$edi_pass,"0","base","role_reporter","1"];
        $users_arr[] = ["承認者",NULL,NULL,"mode",$mod_pass,"0","base","role_approval","1"];
        foreach($users_arr as $val){
            $users_data[] = ["name" => $val[0], "email" => $val[1], "email_verified_at" => $val[2], "userid" => $val[3], "password" => $val[4], "status" => $val[5],
                                "target" => $val[6], "role_name" => $val[7], "role_value" => $val[8],];
        }        
        return $users_data;
    }




    private function insertContentsData($page_id, $contents_data)
    {
        foreach($contents_data as $row) {
            $this->insertContents($page_id, $row);
        }
    }
    private function insertMenusData($page_id, $menus_data)
    {
        foreach($menus_data as $row) {
            $this->insertMenus($page_id, $row);
        }
    }
    private function insertBlogsData($blogs_data)
    {
        foreach($blogs_data as $blogs){
            $this->insertBlogs($blogs);
        }
    }
    private function insertBlogsPostsData($blogs_posts_data)
    {
        foreach($blogs_posts_data as $blogs_posts){
            $this->insertBlogsPosts($blogs_posts);
        }
    }
    private function insertBlogsFrameData($page_id, $blogs_frame_data)
    {
        foreach($blogs_frame_data as $blogs_frames){
            $this->insertBlogsFrame($page_id, $blogs_frames);
        }
    }
    private function insertWhatsnewsData($page_id, $whatsnews_data)
    {
        foreach($whatsnews_data as $row) {
            $this->insertWhatsnews($page_id, $row);
        }
    }
    private function insertCabinetsData($page_id, $cabinets_data)
    {
        foreach($cabinets_data as $row) {
            $this->insertCabinets($page_id, $row);
        }
    }
    private function insertCalendarsData($page_id, $calendars_data)
    {
        foreach($calendars_data as $row) {
            $this->insertCalendars($page_id, $row);
        }
    }
    private function insertSearchsData($page_id, $searchs_data)
    {
        foreach($searchs_data as $row) {
            $this->insertSearchs($page_id, $row);
        }
    }

    private function insertUsersData($users_data)
    {
        foreach($users_data as $row) {
            $this->insertUsers($row);
        }
    }
    private function insertConfigsData($configs_data)
    {
        foreach($configs_data as $row) {
            $this->insertConfigs($row);
        }
    }
    
    
    

    /* 固定記事を1レコードづつ登録する */
    private function insertContents($page_id ,$row, $plugin_name = "contents")
    {
        // バケツを作る
        $bucket_id = $this->insertGetBucketId($plugin_name);
        // フレームを作る
        $frame_row = $this->genFrameRows($page_id, $row["area_id"], $row["frame_title"], $row["frame_design"], $plugin_name, $row["template"], $bucket_id, $row["display_sequence"]);
        $frame_id = $this->insertGetFrameId($frame_row);

        // 固定記事データ作成
        $contents = new Contents;
        $contents->created_id   = self::CREATED_ID;
        $contents->created_name   = self::CREATED_NAME;
        $contents->bucket_id    = $bucket_id;
        $contents->content_text = $row["content_text"];
        $contents->status = 0;
        $contents->save();
    }
    /* メニューを1レコードづつ登録する */
    private function insertMenus($page_id ,$row, $plugin_name = "menus")
    {
        // バケツは作らない
        // フレームを作る
        $frame_row = $this->genFrameRows($page_id, $row["area_id"], $row["frame_title"], $row["frame_design"], $plugin_name, $row["template"], NULL, $row["display_sequence"]);
        $frame_id = $this->insertGetFrameId($frame_row);
        // メニューデータ作成
        $menus = new Menu;
        $menus->created_id   = self::CREATED_ID;
        $menus->created_name   = self::CREATED_NAME;
        $menus->frame_id = $frame_id;
        $menus->select_flag = $row["select_flag"];
        $menus->page_ids = $row["page_ids"];
        $menus->save();
    }
    /* ブログフレームを登録する */
    private function insertBlogsFrame($page_id ,$row, $plugin_name = "blogs")
    {
        $blogs = DB::table('blogs')->select('bucket_id')->where('blog_name', $row["blog_name"])->first();
        if(!$blogs){
            return;
        }
        $bucket_id = $blogs->bucket_id;
        // フレームを作る
        $frame_row = $this->genFrameRows($page_id, $row["area_id"], $row["frame_title"], $row["frame_design"], $plugin_name, $row["template"], $bucket_id, $row["display_sequence"]);
        $frame_id = $this->insertGetFrameId($frame_row);
    }
    /* ブログを記事登録する */
    private function insertBlogsPosts($row)
    {
        $blogs = DB::table('blogs')->select('id')->where('blog_name', $row["blog_name"])->first();
        if($blogs){
            $is_blogs_posts = DB::table('blogs_posts')->select('id')->where('post_title', $row["post_title"])->exists();
            if($is_blogs_posts){
                return;
            }
            $blogs_post = new BlogsPosts();
            $blogs_post->blogs_id      = $blogs->id;
            $blogs_post->post_title    = $row["post_title"];
            //$blogs_post->categories_id = $row["categories_id"];
            //$blogs_post->important     = $row["important"]
            $blogs_post->posted_at     = $row["posted_at"];
            $blogs_post->post_text     = $row["post_text"];
            $blogs_post->post_text2    = $row["post_text2"];
            $blogs_post->read_more_flag = $row["read_more_flag"];
            $blogs_post->read_more_button = $row["read_more_button"];
            $blogs_post->close_more_button = $row["close_more_button"];
            $blogs_post->status = 0;
            $blogs_post->created_id   = self::CREATED_ID;
            $blogs_post->created_name = self::CREATED_NAME;
            $blogs_post->save();
            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            BlogsPosts::where('id', $blogs_post->id)->update(['contents_id' => $blogs_post->id]);
        }
    }
    /* ブログを登録する */
    private function insertBlogs($row, $plugin_name = "blogs")
    {
        $isBlogs = DB::table('blogs')->where('blog_name', $row["blog_name"])->exists();
        if(!$isBlogs) {
            // バケツを作る
            $bucket_id = $this->insertGetBucketId($plugin_name);
            $blogs = new Blogs;
            $blogs->bucket_id    = $bucket_id;
            $blogs->blog_name    = $row["blog_name"];
            $blogs->view_count   = 10;
            $blogs->save();
        }
    }
    /* 新着情報を登録する */
    private function insertWhatsnews($page_id ,$row, $plugin_name = "whatsnews")
    {
        // バケツを作る
        $bucket_id = $this->insertGetBucketId($plugin_name);
        // フレームを作る
        $frame_row = $this->genFrameRows($page_id, $row["area_id"], $row["frame_title"], $row["frame_design"], $plugin_name, $row["template"], $bucket_id, $row["display_sequence"]);
        $frame_id = $this->insertGetFrameId($frame_row);

        // 新着情報設定データ新規オブジェクト
        $whatsnews = new Whatsnews();
        $whatsnews->bucket_id = $bucket_id;
        // 新着情報設定
        $whatsnews->whatsnew_name     = $row["whatsnew_name"];
        $whatsnews->view_pattern      = $row["view_pattern"];
        $whatsnews->count             = intval($row["count"]);
        $whatsnews->days              = intval($row["days"]);
        $whatsnews->rss               = $row["rss"];
        $whatsnews->rss_count         = intval($row["rss_count"]);
        $whatsnews->page_method       = $row["page_method"];
        $whatsnews->page_count        = intval($row["page_count"]);
        $whatsnews->view_posted_name  = $row["view_posted_name"];
        $whatsnews->view_posted_at    = $row["view_posted_at"];
        $whatsnews->important         = $row["important"];
        $whatsnews->read_more_use_flag = $row["read_more_use_flag"];
        $whatsnews->read_more_name = $row["read_more_name"];
        $whatsnews->read_more_fetch_count = $row["read_more_fetch_count"];
        $whatsnews->read_more_btn_color_type = $row["read_more_btn_color_type"];
        $whatsnews->read_more_btn_type = $row["read_more_btn_type"];
        $whatsnews->read_more_btn_transparent_flag = $row["read_more_btn_transparent_flag"];
        $whatsnews->target_plugins    = $row["target_plugins"];
        $whatsnews->frame_select      = intval($row["frame_select"]);
        $whatsnews->target_frame_ids  = $row["target_frame_ids"];
        $whatsnews->save();
    }
    /* キャビネットを登録する */
    private function insertCabinets($page_id ,$row, $plugin_name = "cabinets")
    {
        // バケツを作る
        $bucket_id = $this->insertGetBucketId($plugin_name);
        // フレームを作る
        $frame_row = $this->genFrameRows($page_id, $row["area_id"], $row["frame_title"], $row["frame_design"], $plugin_name, $row["template"], $bucket_id, $row["display_sequence"]);
        $frame_id = $this->insertGetFrameId($frame_row);
        // 新着情報設定データ新規オブジェクト
        $cabinet = new Cabinet();
        $cabinet->bucket_id = $bucket_id;
        $cabinet->name = $row["name"];
        $cabinet->upload_max_size = $row["upload_max_size"];
        $cabinet->save();
        CabinetContent::updateOrCreate(
            ['cabinet_id' => $cabinet->id, 'parent_id' => null, 'is_folder' => CabinetContent::is_folder_on],
            ['name' => $cabinet->name]
        );
    }

    /* カレンダーを登録する */
    private function insertCalendars($page_id ,$row, $plugin_name = "calendars")
    {
        // バケツを作る
        $bucket_id = $this->insertGetBucketId($plugin_name);
        // フレームを作る
        $frame_row = $this->genFrameRows($page_id, $row["area_id"], $row["frame_title"], $row["frame_design"], $plugin_name, $row["template"], $bucket_id, $row["display_sequence"]);
        $frame_id = $this->insertGetFrameId($frame_row);
        $calendar = new Calendar();
        $calendar->bucket_id = $bucket_id;
        $calendar->name = $row["name"];
        $calendar->save();
        $calendar_frame = CalendarFrame::updateOrCreate(
            ['frame_id' => $frame_id],
            ['calendar_id' => $calendar->id, 'frame_id' => $frame_id],
        );
    }

    /* 検索を登録する */
    private function insertSearchs($page_id ,$row, $plugin_name = "searchs")
    {
        // バケツを作る
        $bucket_id = $this->insertGetBucketId($plugin_name);
        // フレームを作る
        $frame_row = $this->genFrameRows($page_id, $row["area_id"], $row["frame_title"], $row["frame_design"], $plugin_name, $row["template"], $bucket_id, $row["display_sequence"]);
        $frame_id = $this->insertGetFrameId($frame_row);
        $search = new Searchs();
        $search->bucket_id = $bucket_id;
        $search->search_name = $row["search_name"];
        $search->count = $row["count"];
        $search->target_plugins = $row["target_plugins"];
        $search->recieve_keyword = $row["recieve_keyword"];
        $search->save();
    }

    
    /* サイト情報を登録する */
    private function insertConfigs($row)
    {
        $config = Configs::updateOrCreate(
            ['name'     => $row['name']],
            ['name'     => $row['name'],
             'value'    => $row['value'],
             'category' => $row['category']
        ]);
    }
    /* 会員を登録する */
    private function insertUsers($row)
    {
        $user = User::select('*')->where('userid', $row['userid'])->first();
        if(!$user){
            $user = User::create([
                'name'     => $row['name'],
                'email'    => $row['email'],
                'userid'   => $row['userid'],
                'password' => Hash::make($row['password']),
                'status'   => $row['status'],
            ]);
        }
        $user_roles = UsersRoles::select('users_id','target','role_name')->where('users_id', $user->id)->where('target', $row['target'])->where('role_name', $row['role_name'])->first();
        if(!$user_roles){
            UsersRoles::create([
                'users_id'   => $user->id,
                'target'     => $row['target'],
                'role_name'  => $row['role_name'],
                'role_value' => 1,
            ]);
        }
    }
    
    

    /* Framesの行データとして返却 */
    private function genFrameRows($page_id, $area_id, $frame_title, $frame_design, $plugin_name, $template, $bucket_id, $display_sequence)
    {
        return [
            'page_id' => $page_id,
            'area_id' => $area_id,
            'frame_title' => $frame_title,
            'frame_design' => $frame_design,
            'plugin_name' => $plugin_name,
            'frame_col' => 0,
            'template' => $template,
            'plug_name' => NULL,
            'browser_width' => NULL,
            'disable_whatsnews' => 0,
            'disable_searchs' => 0,
            'page_only' => 0,
            'default_hidden' => 0,
            'classname' => NULL,
            'none_hidden' => 0,
            'bucket_id' => $bucket_id,
            'display_sequence' => $display_sequence,
            'content_open_type' => 1,
            'content_open_date_from' => NULL,
            'content_open_date_to' => NULL,
        ];
    }
    /* ページを登録しpage_idを返却 */
    private function insertGetPageId($row)
    {
        $page_name = (isset($row['page_name'])) ? $row['page_name'] : NULL;
        $permanent_link = (isset($row['permanent_link'])) ? $row['permanent_link'] : NULL;
        $background_color = (isset($row['background_color'])) ? $row['background_color'] : NULL;
        $header_color = (isset($row['header_color'])) ? $row['header_color'] : NULL;
        $theme = (isset($row['theme'])) ? $row['theme'] : NULL;
        $layout = (isset($row['layout'])) ? $row['layout'] : NULL;
        $base_display_flag = (isset($row['base_display_flag'])) ? $row['base_display_flag'] : 1;// 1:表示する
        $membership_flag = (isset($row['membership_flag'])) ? $row['membership_flag'] : 0;
        $ip_address = (isset($row['ip_address'])) ? $row['ip_address'] : NULL;
        $class = (isset($row['class'])) ? $row['class'] : NULL;
        $othersite_url = (isset($row['othersite_url'])) ? $row['othersite_url'] : NULL;
        $othersite_url_target = (isset($row['othersite_url_target'])) ? $row['othersite_url_target'] : 0;
        $transfer_lower_page_flag = (isset($row['transfer_lower_page_flag'])) ? $row['transfer_lower_page_flag'] : 0;
        $password = (isset($row['password'])) ? $row['password'] : NULL;
        $_lft = (isset($row['_lft'])) ? $row['_lft'] : NULL;
        $_rgt = (isset($row['_rgt'])) ? $row['_rgt'] : NULL;
        $parent_id = (isset($row['parent_id'])) ? $row['parent_id'] : NULL;
        return DB::table('pages')->insertGetId([
            'page_name' => $page_name,
            'permanent_link' => $permanent_link,
            'background_color' => $background_color,
            'header_color' => $header_color,
            'theme' => $theme,
            'layout' => $layout,
            'base_display_flag' => $base_display_flag,
            'membership_flag' => $membership_flag,
            'ip_address' => $ip_address,
            'class' => $class,
            'othersite_url' => $othersite_url,
            'othersite_url_target' => $othersite_url_target,
            'transfer_lower_page_flag' => $transfer_lower_page_flag,
            'password' => $password,
            '_lft' => $_lft,
            '_rgt' => $_rgt,
            'parent_id' => $parent_id,
        ]);
    }
    /* フレームを登録しframe_idを返却 */
    private function insertGetFrameId($row)
    {
        $page_id = (isset($row['page_id'])) ? $row['page_id'] : NULL;
        $area_id = (isset($row['area_id'])) ? $row['area_id'] : NULL;
        $frame_title = (isset($row['frame_title'])) ? $row['frame_title'] : NULL;
        $frame_design = (isset($row['frame_design'])) ? $row['frame_design'] : NULL;
        $plugin_name = (isset($row['plugin_name'])) ? $row['plugin_name'] : NULL;
        $frame_col = (isset($row['frame_col'])) ? $row['frame_col'] : NULL;
        $template = (isset($row['template'])) ? $row['template'] : NULL;
        $plug_name = (isset($row['plug_name'])) ? $row['plug_name'] : NULL;
        $browser_width = (isset($row['browser_width'])) ? $row['browser_width'] : NULL;
        $disable_whatsnews = (isset($row['disable_whatsnews'])) ? $row['disable_whatsnews'] : NULL;
        $disable_searchs = (isset($row['disable_searchs'])) ? $row['disable_searchs'] : NULL;
        $page_only = (isset($row['page_only'])) ? $row['page_only'] : NULL;
        $default_hidden = (isset($row['default_hidden'])) ? $row['default_hidden'] : NULL;
        $classname = (isset($row['classname'])) ? $row['classname'] : NULL;
        $none_hidden = (isset($row['none_hidden'])) ? $row['none_hidden'] : NULL;
        $bucket_id = (isset($row['bucket_id'])) ? $row['bucket_id'] : NULL;
        $display_sequence = (isset($row['display_sequence'])) ? $row['display_sequence'] : NULL;
        $content_open_type = (isset($row['content_open_type'])) ? $row['content_open_type'] : NULL;
        $content_open_date_from = (isset($row['content_open_date_from'])) ? $row['content_open_date_from'] : NULL;
        $content_open_date_to = (isset($row['content_open_date_to'])) ? $row['content_open_date_to'] : NULL;
        return DB::table('frames')->insertGetId([
            'page_id' => $page_id,
            'area_id' => $area_id,
            'frame_title' => $frame_title,
            'frame_design' => $frame_design,
            'plugin_name' => $plugin_name,
            'frame_col' => $frame_col,
            'template' => $template,
            'plug_name' => $plug_name,
            'browser_width' => $browser_width,
            'disable_whatsnews' => $disable_whatsnews,
            'disable_searchs' => $disable_searchs,
            'page_only' => $page_only,
            'default_hidden' => $default_hidden,
            'classname' => $classname,
            'none_hidden' => $none_hidden,
            'bucket_id' => $bucket_id,
            'display_sequence' => $display_sequence,
            'content_open_type' => $content_open_type,
            'content_open_date_from' => $content_open_date_from,
            'content_open_date_to' => $content_open_date_to,
        ]);
    }
    /* バケツを登録しbucket_idを返却 */
    private function insertGetBucketId($plugin_name, $bucket_name = "無題" )
    {
        return DB::table('buckets')->insertGetId([
                'bucket_name'=>$bucket_name,
                'plugin_name'=>$plugin_name,
        ]);
    }
    private function trancate($table_name)
    {
        DB::table($table_name)->truncate();
    }
    private function trancateUsers()
    {
        $first_user_id = 1;
        UsersRoles::where('users_id', '<>', $first_user_id)->delete();
        User::where('id', '<>', $first_user_id)->delete();
        $sql = "ALTER TABLE `users` AUTO_INCREMENT=1";
        DB::statement($sql);
    }
    
}
