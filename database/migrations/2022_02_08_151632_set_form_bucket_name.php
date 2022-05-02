<?php

use App\Models\Common\Buckets;
use App\Models\User\Forms\Forms;
use Illuminate\Database\Migrations\Migration;

class SetFormBucketName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $forms = Forms::get();
        foreach ($forms as $form) {
            $bucket = Buckets::find($form->bucket_id);
            $bucket->bucket_name = $form->forms_name;
            $bucket->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $forms = Forms::get();
        foreach ($forms as $form) {
            $bucket = Buckets::find($form->bucket_id);
            $bucket->bucket_name = '無題';
            $bucket->save();
        }
    }
}
