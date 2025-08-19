<?php

namespace Filaforge\QuickLinks\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardBookmark extends Model
{
    protected $table = 'dashboard_bookmarks';

    protected $fillable = [
        'user_id',
        'label',
        'url',
        'order',
    ];
}



