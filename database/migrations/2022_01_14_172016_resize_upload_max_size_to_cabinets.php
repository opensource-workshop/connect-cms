<?php

use App\Models\User\Cabinets\Cabinet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ResizeUploadMaxSizeToCabinets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Cabinet::where('upload_max_size', '1024000')->update(['upload_max_size' => '1048576']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Cabinet::where('upload_max_size', '1048576')->update(['upload_max_size' => '1024000']);
    }
}
