<?php

namespace App\Modules\Importer\Models;

use App\Core\LogModel;

class Importer extends LogModel
{
    protected $table = 'importer_log';
    protected $primaryKey  = 'id';

    public $timestamps = false;

    //const CREATED_AT = 'created_at';
    //const UPDATED_AT = 'modified_at';

    protected $fillable = [];

    // relationships

    // scopes

    // getters
}

