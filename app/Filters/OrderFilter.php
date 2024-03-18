<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderFilter
{
    public static function apply(Builder $query, $filters)
    {

        if (isset($filters['search']) && $filters['search']) {
            $query->where(function ($query) use ($filters) {
                $query->where('id', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('order_key', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('customer_id', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('customer_note', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }
}
