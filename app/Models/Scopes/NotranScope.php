<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NotranScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Applying the logic to filter out users who have no transactions
        $builder->leftJoin('tbl_transactions as t', function($join) {
            $join->on('users.phone_number', '=', 't.sender_phone')
                ->orOn('users.phone_number', '=', 't.receiver_phone');
        })
            ->whereNull('t.sender_phone')
            ->whereNull('t.receiver_phone')
            ->select('users.first_name', 'users.last_name', 'users.phone_number', 'users.country');
    }
}
