<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\User\Faqs\FaqsPosts;

class UpdatePostTitleStripTagsFaqsPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // faqのタイトルのHTMLタグを取り除いてアップデート
        $faqs_posts = FaqsPosts::get();

        foreach ($faqs_posts as $faqs_post) {
            $faqs_post->post_title = strip_tags($faqs_post->post_title);
            $faqs_post->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
