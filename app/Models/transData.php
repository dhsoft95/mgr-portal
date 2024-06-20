<?php

namespace App\Models;

use App\Models\Scopes\TransactionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class transData extends Model
{
    use HasFactory ;
    protected $table="tbl_transactions";
    public $timestamps=false;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TransactionScope());
    }
}
