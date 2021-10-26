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
use App\User;
use App\Models\Core\UsersRoles;
use App\Models\Core\Configs;



// 実行コマンドメモ
//※注意※ Connect-CMS初期データ実行後に実行してください
//% php artisan db:seed --class=ElementaryschoolSeeder

class ElementaryschoolSeeder extends Seeder
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
        list($blogs_frame_data,$blogs_data,$blogs_posts_data) = $this->getBlogsData();
        $whatsnews_data = $this->getWhatsnewsData();
        $cabinets_data = $this->getCabinetsData();
        $calendars_data = $this->getCalendarsData();
        $counters_data = $this->getCountersData();
        //Config関係
        $configs_data = $this->getConfigsData();
        $users_data = $this->getUsersData();

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
            // カウンター登録
            if(isset($counters_data[$row['permanent_link']])) {
                $this->insertCountersData($page_id, $counters_data[$row['permanent_link']]);
            }
        }

        // サイト管理関係
        if($configs_data) {
            $this->insertConfigsData($configs_data);
        }
        // ユーザデータ登録
        if($users_data) {
            $this->insertUsersData($users_data);

        }
    }
    
    private function getPagesData()
    {
        /*
            SELECT 
                P.page_name, P.permanent_link, P.layout, P._lft, P._rgt, P.parent_id 
            FROM `pages` P 
            ORDER BY P.id ASC
        */
        $pages_arr = [];
        $pages_arr[] = ["ホーム","/","1|1|0|1","0","0",NULL];
        $pages_arr[] = ["学校紹介","/about",NULL,"3","12",NULL];
        $pages_arr[] = ["校長挨拶","/about/greeting",NULL,"4","5","2"];
        $pages_arr[] = ["アクセス","/about/access",NULL,"6","7","2"];
        $pages_arr[] = ["教育目標","/about/goal",NULL,"8","9","2"];
        $pages_arr[] = ["学校経営方針","/about/policy",NULL,"10","11","2"];
        $pages_arr[] = ["教育活動","/activities",NULL,"15","16",NULL];
        $pages_arr[] = ["いじめ防止基本方針","/activities/ijime",NULL,"39","40",NULL];
        $pages_arr[] = ["学校ブログ","/blog",NULL,"17","22",NULL];
        $pages_arr[] = ["行事予定","/event",NULL,"23","28",NULL];
        $pages_arr[] = ["学校だより","/news",NULL,"29","34",NULL];
        $pages_arr[] = ["グランドデザイン","/concept",NULL,"1","2",NULL];
        $pages_arr[] = ["学校評価","/assessment",NULL,"41","42",NULL];
        $pages_arr[] = ["学園情報","/gakuen",NULL,"13","14",NULL];
        $pages_arr[] = ["緊急時の対応","/emergency-guidelines",NULL,"35","36",NULL];
        $pages_arr[] = ["緊急連絡カード","/emergency-card",NULL,"37","38",NULL];
        $pages = [];
        foreach($pages_arr as $key => $row) {
            $pages[] = ['page_name' => $row[0],'permanent_link' => $row[1],'layout' => $row[2],'_lft' => $row[3],'_rgt' => $row[4],'parent_id' => $row[5],];
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
        $contens_arr[] = ["/","4",NULL,"none","default","6","<footer class='pt-3'><div><div>〇〇小学校</div><div>住所：〒000&minus;0000&nbsp;〇〇市100-1<div><div>TEL：000&minus;000&minus;0000　FAX：111&minus;111&minus;1111</div><div style='text-align: center;'>Copyright 2021 サイト名 All Rights Reserved.</div></div></div></div></footer>"];
        $contens_arr[] = ["/","0",NULL,"none","default","2","<div class='mt-4 d-md-flex justify-content-between'><div><img src='/images/core/sample_image/sample_top_title_image.png' style='width: 100%; max-width: 600px;' class='img-fluid' alt='' /></div><div><img src='/images/core/sample_image/sample_top_school_image.png' style='width: 100%; max-width: 400px;' class='img-fluid' alt='top.png' /></div></div>"];
        $contens_arr[] = ["/emergency-card","2","緊急連絡カード","default","default","1","<p>緊急連絡カード</p>"];
        $contens_arr[] = ["/emergency-guidelines","2","緊急時の対応","default","default","1","<p>緊急時の対応について</p>"];
        $contens_arr[] = ["/about","2","沿革","default","default","3","<p>沿革</p>"];
        $contens_arr[] = ["/about/goal","2","教育目標","default","default","1",NULL];
        $contens_arr[] = ["/about/access","2","アクセス","default","default","1","<p class='embed-responsive embed-responsive-16by9'><iframe src='https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d12967.571965619665!2d139.7778109!3d35.655008!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x65c28bf6b0d9cdaf!2z44ix44Kq44O844OX44Oz44K944O844K5772l44Ov44O844Kv44K344On44OD44OX!5e0!3m2!1sja!2sjp!4v1626848547573!5m2!1sja!2sjp' width='600' height='450' style='border:0;' allowfullscreen='' loading='lazy'></iframe></p>"];
        $contens_arr[] = ["/about/greeting","2","校長挨拶","default","default","1","<div>校長からの挨拶</div><div style='text-align: right;'>〇〇小学校&nbsp;&nbsp;&nbsp;&nbsp;校長&nbsp;&nbsp;〇〇&nbsp;〇〇</div>"];
        $contens_arr[] = ["/about","2","学校紹介","default","default","1","<p>校名や学校の紹介文章、校舎の写真など</p>"];
        $contens_arr[] = ["/about","2","校章","default","default","2","<p>校章とその意味や由来について</p>"];
        $contens_arr[] = ["/activities/ijime","2","いじめ防止基本方針","default","default","1","<div>いじめ防止基本方針をまとめた資料のアップロードやいじめ防止基本方針を記載しましょう。</div><div><a href='/file/' class='cc-icon-pdf'>いじめ防止基本方針.pdf</a></div>"];
        $contens_arr[] = ["/news","2","おたより","default","default","2","<p>おたよりを記載してください。<br />配下のページに遷移させたい場合は、管理メニュー＞ページ管理&gt;おたより 編集 より「下層ページへ自動転送する」を設定してください。</p>"];
        $contens_arr[] = ["/event","2","行事予定","default","default","2","<p>行事予定を記載してください。<br />配下のページに遷移させたい場合は、管理メニュー＞ページ管理&gt;行事予定＞編集 より「下層ページへ自動転送する」を設定してください。</p>"];
        $contens_arr[] = ["/activities","2","教育活動","default","default","1","<div>教育活動を記載しましょう</div>"];
        $contens_arr[] = ["/about/policy","2","学校経営方針","default","default","1","<h4>学校経営方針</h4><div>学校経営方針を記載しましょう</div><hr /><h4>目指す学校像</h4><div>目指す学校像を記載しましょう</div><hr /><h4>目指す教師像</h4><div>目指す教師像を記載しましょう</div><hr /><h4>目指す児童生徒像</h4><div>目指す児童生徒像を記載しましょう</div>"];
        $contents_data = [];
        foreach($contens_arr as $val){
            $contents_data[$val[0]][] = ["area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5], "content_text" => $val[6],];
        }        

        return $contents_data;
    }
    private function getMenusData()
    {
        // 初期配置するメニューデータを記載する
        /*
            SELECT P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, M.`select_flag`, M.`page_ids` FROM `menus` M
            INNER JOIN frames F ON M.frame_id = F.id
            INNER JOIN pages P ON F.page_id = P.id
            ORDER BY M.id ASC;
        */
        // 1ページに複数個配置されることを想定する
        $menus_arr = [];
        $menus_arr[] = ["/","1",NULL,"none","opencurrenttree","5","0",""];
        $menus_arr[] = ["/","0",NULL,"none","dropdown","4","1","1,2,3,4,9,12,15"];
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
                P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, 
                BL.blog_name, BP.contents_id, BP.blogs_id, BP.post_title, BP.post_text, BP.post_text2, BP.read_more_flag, 
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
        $blogs_arr[] = ["/blog","2","学校ブログ","default","default","2","学校ブログ","4","3","学校ブログのサンプル１","<p>学校ブログのサンプルの記事</p><p>１つ目です。</p>",NULL,"0",NULL,NULL,'2021-07-15 18:50:25'];
        $blogs_arr[] = ["/","2","学校ブログ","default","default","2","学校ブログ","4","3","学校ブログのサンプル１","<p>学校ブログのサンプルの記事</p><p>１つ目です。</p>",NULL,"0",NULL,NULL,'2021-07-15 18:50:25'];
        $blogs_arr[] = ["/blog","2","学校ブログ","default","default","2","学校ブログ","3","3","学校ブログのサンプル２","<p>この記事は学校ブログのサンプル記事です。</p><p>記事＋以下のように画像（<span style='font-size: 1rem;'>写真</span><span style='font-size: 1rem;'>）も掲載できます。</span></p><p></p><p><img src='/images/core/sample_image/sample_blogs_posts_image.jpg' width='500' height='230' class='img-fluid' alt='' /></p>",NULL,"0","続きを読む","閉じる",'2021-07-15 19:00:00'];
        $blogs_arr[] = ["/","2","学校ブログ","default","default","2","学校ブログ","3","3","学校ブログのサンプル２","<p>この記事は学校ブログのサンプル記事です。</p><p>記事＋以下のように画像（<span style='font-size: 1rem;'>写真</span><span style='font-size: 1rem;'>）も掲載できます。</span></p><p></p><p><img src='/images/core/sample_image/sample_blogs_posts_image.jpg' width='500' height='230' class='img-fluid' alt='' /></p>",NULL,"0","続きを読む","閉じる",'2021-07-15 19:00:00'];
        $blogs_frame_data = [];
        $blogs_data = [];
        $blogs_posts_data = [];
        foreach($blogs_arr as $val){
            $blogs_frame_data[$val[0]][$val[5]] = ["area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5], "blog_name" => $val[6]];
            $blogs_data[$val[8]] = ["blog_name" => $val[6]];
            $blogs_posts_data[] = [
                            "blog_name" => $val[6],
                            "contents_id" => $val[7],"blogs_id" => $val[8], "post_title" => $val[9],"post_text" => $val[10], "post_text2" => $val[11],
                            "read_more_flag" => $val[12], "read_more_button" => $val[13], "close_more_button" => $val[14], "posted_at" => $val[15],
            ];
        }

        return [$blogs_frame_data,$blogs_data,$blogs_posts_data];
    }
    private function getWhatsnewsData()
    {
        // 初期配置する固定記事データを記載する
        /*
            SELECT 
                P.permanent_link, F.area_id, F.frame_title, F.frame_design, F.template, F.display_sequence, 
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
        $whatsnews_arr[] = ["/","2","新着情報","default","onerow","1","新着情報","0","3","0","1","10","0","0","0","1",NULL,"0","もっと見る","5","primary","rounded","0","blogs","1","学校ブログ"];
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
        $cabinets_arr[] = ["/news","2","学校だより","default","default","1","学校だより","5120","0",NULL];
        $cabinets_arr[] = ["/concept","2","グランドデザイン","default","default","1","グランドデザイン","10240","0",NULL];
        $cabinets_arr[] = ["/assessment","2","学校評価","default","default","1","学校評価","5120","0",NULL];
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
        // 初期配置するキャビネットデータを記載する
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
        $calendars_arr[] = ["/event","2","行事予定","default","default","1","行事予定"];
        $calendars_data = [];
        foreach($calendars_arr as $val){
            $calendars_data[$val[0]][$val[5]] = ["area_id" => $val[1], "frame_title" => $val[2], "frame_design" => $val[3], "template" => $val[4], "display_sequence" => $val[5],
             "name" => $val[6]
            ];
        }        
        return $calendars_data;
    }
    private function getCountersData()
    {
        return [];
    }
    private function getConfigsData()
    {
        $configs_arr = [];
        $configs_arr[] = ["base_site_name","水鶏小学校","general"];
        $configs_arr[] = ["additional_theme","Users/e_school","general"];
        $configs_arr[] = ["browser_width_header","100%","browser_width"];
        $configs_arr[] = ["browser_width_center",NULL,"browser_width"];
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
