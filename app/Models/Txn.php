<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Txn implements Scope, \Illuminate\Database\Eloquent\Scope
{

    public function apply(Builder|\Illuminate\Database\Eloquent\Builder $builder, Model $model)
    {
        $builder->join('tbl_statuses', 'tbl_Transactions.status', '=', 'tbl_statuses.id')
            ->select('tbl_Transactions.sender_amount',
                'tbl_Transactions.receiver_amount',
                'tbl_Transactions.created_at',
                'tbl_statuses.name as status',
                'tbl_Transactions.trxId',
                'tbl_Transactions.sender_channel_id',
                'tbl_Transactions.receiver_channel_id',
                'tbl_Transactions.sender_channel_currency',
                'tbl_Transactions.receiver_channel_currency');
    }


}
