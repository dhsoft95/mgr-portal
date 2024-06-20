<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TransactionScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */

    public function apply(\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $builder, Model $model)
    {
        $builder->select('tbl_transactions.sender_amount',
            'tbl_transactions.id',
            'tbl_transactions.receiver_amount',
            'tbl_transactions.created_at',
            'tbl_status.name as status',
            'tbl_transactions.trxId',
            'tbl_transactions.sender_channel_country',
            'tbl_transactions.sender_channel_id',
            'tbl_transactions.receiver_channel_id',
            'tbl_transactions.sender_channel_currency',
            'tbl_transactions.receiver_channel_currency')
            ->join('tbl_status', 'tbl_transactions.status', '=', 'tbl_status.id')
            ->orderBy('tbl_transactions.created_at', 'desc');
    }
}
