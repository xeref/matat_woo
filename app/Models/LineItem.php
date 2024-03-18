<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "name",
        "product_id",
        "variation_id",
        "quantity",
        "tax_class",
        "subtotal",
        "subtotal_tax",
        "total",
        "total_tax",
        "taxes",
        "meta_data",
        "sku",
        "price",
        "image",
        "parent_name",
        "order_id"
    ];

    public function checkLineItemExisting($lineItemId) {
        return $this->find($lineItemId);
    }

    public static function sanitizeData($data)
    {
        $sanitizedData = [];
        $sanitizedData = [
            'id' => static::sanitizeString($data['id']),
            'name' => static::sanitizeString($data['name']),
            'product_id' => static::sanitizeString($data['product_id']),
            'variation_id' => static::sanitizeString($data['variation_id']),
            'quantity' => static::sanitizeString($data['quantity']),
            'tax_class' => static::sanitizeString($data['tax_class']),
            'subtotal' => static::sanitizeString($data['subtotal']),
            'subtotal_tax' => static::sanitizeString($data['subtotal_tax']),
            'total' => static::sanitizeString($data['total']),
            'total_tax' => static::sanitizeString($data['total_tax']),
            'taxes' => (count($data['taxes']))? json_encode($data['taxes']):"",
            'meta_data' => (count($data['taxes']))? json_encode($data['meta_data']):"",
            'sku' => static::sanitizeString($data['sku']),
            'price' => static::sanitizeString($data['price']),
            'image' => json_encode($data['image']),
            'order_id' => static::sanitizeString($data['order_id']),
            'parent_name' => static::sanitizeString($data['parent_name']),
        ];
        return $sanitizedData;
    }

    // Sanitize field 
    private static function sanitizeString($value)
    {
        return trim($value);
    }

    public function setImageAttribute($value)
    {
        $this->attributes['image'] = json_encode(trim($value));
    }

    public function setTaxesAttribute($value)
    {
        $this->attributes['taxes'] = json_encode(trim($value));
    }
    
    public function setMetaDataAttribute($value)
    {
        $this->attributes['meta_data'] = json_encode(trim($value));
    }

    public function getImageAttribute($value)
    {
        return json_decode(json_decode($value, true), true);
    }

    public function orders() {
        return $this->belongsTo(Order::class);
    }
}
