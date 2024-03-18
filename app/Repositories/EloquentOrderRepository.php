<?php

namespace App\Repositories;

use App\Models\Order;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function all()
    {
        return Order::all();
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function find($id)
    {
        return Order::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $order = Order::findOrFail($id);
        $order->update($data);

        return $order;
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return true;
    }
}
