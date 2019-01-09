<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustedUUID extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trusted_uuids';

    protected $fillable = [
        'uuid',
    ];
}
