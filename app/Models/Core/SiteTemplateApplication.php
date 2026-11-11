<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class SiteTemplateApplication extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['result' => 'array', 'applied_at' => 'datetime'];
}
