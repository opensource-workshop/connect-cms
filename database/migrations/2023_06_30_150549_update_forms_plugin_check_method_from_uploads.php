<?php

use App\Enums\PluginName;
use App\Models\Common\Uploads;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFormsPluginCheckMethodFromUploads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Uploads::where('plugin_name', PluginName::getPluginName(PluginName::forms))->update(['check_method' => 'canDownload']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
