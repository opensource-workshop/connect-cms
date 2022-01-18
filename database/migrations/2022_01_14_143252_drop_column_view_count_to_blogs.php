<?php

use App\Enums\BlogFrameConfig;
use App\Models\Core\FrameConfig;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsFrames;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnViewCountToBlogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 表示件数の移行　バケツからフレームへ
        $blogs = Blogs::get();
        foreach ($blogs as $blog) {
            $blog_frames = BlogsFrames::where('blogs_id', $blog->id)->get();
            foreach ($blog_frames as $blog_frame) {
                $frame_config = new FrameConfig();
                $frame_config->frame_id = $blog_frame->frames_id;
                $frame_config->name = BlogFrameConfig::blog_view_count;
                $frame_config->value = $blog->view_count;
                $frame_config->save();
            }
        }

        // カラム削除
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // カラム復活
        Schema::table('blogs', function (Blueprint $table) {
            $table->integer('view_count')->after('blog_name');
        });

        // 表示件数の移行　フレームからバケツへ
        $frame_configs = FrameConfig::where('name', BlogFrameConfig::blog_view_count)->get();
        foreach ($frame_configs as $frame_config) {
            $blog_frame = BlogsFrames::where('frames_id', $frame_config->frame_id)->first();
            Blogs::where('id', $blog_frame->blogs_id)->update(['view_count' => $frame_config->value]);
            $frame_config->forceDelete();
        }
    }
}
