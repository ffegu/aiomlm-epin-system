<?php

namespace Aiomlm\Epin\Models;

use Illuminate\Database\Eloquent\Model;

class Epin extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'epin',
        'amount',
        'issue_to',
        'generated_by',
        'transfer_by',
        'transfer_time',
        'used_by',
        'used_time',
        'status',
        'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'transfer_time',
        'used_time',
    ];


    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
