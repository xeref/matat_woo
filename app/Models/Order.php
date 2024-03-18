<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    protected $fillable = [
        'id',
        'number',
        'order_key',
        'status',
        'date_created',
        'total',
        'customer_id',
        'billing',
        'shipping',
        'customer_note',
    ];

    public function checkOrderExisting($orderId) {
        return $this->where('id','=',$orderId)->first();
    }

    public function checkOrderExistingAndGetBase64($orderId) {
        $data = $this->where('id','=',$orderId)->select('id',
        'number',
        'order_key',
        'status',
        'date_created',
        'total',
        'customer_id',
        'billing',
        'shipping',
        'customer_note')->first();

        return ($data) ?? NULL;
    }

    public static function sanitizeOrderData($data)
    {
        $sanitizedData = [];
        $sanitizedData = [
            'id' => static::sanitizeString($data['id']),
            'number' => static::sanitizeString($data['number']),
            'order_key' => static::sanitizeString($data['order_key']),
            'status' => static::sanitizeString($data['status']),
            'date_created' => static::sanitizeString($data['date_created']),
            'total' => static::sanitizeString($data['total']),
            'customer_id' => static::sanitizeString($data['customer_id']),
            'billing' => json_encode($data['billing']),
            'shipping' => json_encode($data['shipping']),
            'customer_note' => static::sanitizeString($data['customer_note']),
        ];
        return $sanitizedData;
    }

    // Sanitize field 
    private static function sanitizeString($value)
    {
        return trim($value);
    }

    public function setBillingAttribute($value)
    {
        $this->attributes['billing'] = json_encode(trim($value));
    }

    public function setShippingAttribute($value)
    {
        $this->attributes['shipping'] = json_encode(trim($value));
    }

    public function getBillingAttribute($value)
    {
        return json_decode(json_decode($value, true), true);
    }

    public function getShippingAttribute($value)
    {
        return json_decode(json_decode($value, true), true);
    }

    public function lineItems() {
        return $this->hasMany(LineItem::class);
    }
}
