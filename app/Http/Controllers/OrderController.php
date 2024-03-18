<?php

namespace App\Http\Controllers;

use App\Filters\OrderFilter;
use App\Models\LineItem;
use App\Models\Order;
use App\Traits\CommonTrait;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{
    
    use CommonTrait;
    
    protected $orderRepository;

    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sort_by', 'sort_direction', 'per_page']);

        $orders = OrderFilter::apply(Order::query()->with('lineItems'), $filters);

        return response()->json($orders);
    }


    public function fetch()
    {
        
        try { 
            $resposeOrderData = $this->makeOrderRequest();
            if(isset($resposeOrderData['status']) && $resposeOrderData['status'] == 'error') {
                return [
                    "status" => "error",
                    "message" => $resposeOrderData['data']['message']
                ];
            }       
            foreach($resposeOrderData['data'] as $order) {
                $newOrder = new Order();
                $orderId = NULL;
                $orderId = ($newOrder->checkOrderExisting($order['id']))? $newOrder->checkOrderExisting($order['id'])->id: NULL;
                $sanitizedData = $newOrder->sanitizeOrderData($order);
                if(!$orderId) {
                    $newOrder->create($sanitizedData);
                    $orderId = $newOrder->id;
                } else {
                    $updateOrder = $newOrder->where('id',$order['id'])->first();
                    unset($sanitizedData['id']);
                    $updateOrder->update($sanitizedData);
                    $orderId = $updateOrder->id;
                }
                Log::info('New Order Id: ' . $orderId);
                if($orderId) {
                    foreach($order['line_items'] as $lineItem) {
                        $newLineItem = new LineItem();
                        $lineItemId = NULL;
                        $lineItemId = ($newLineItem->checkLineItemExisting($lineItem['id']))? $newLineItem->checkLineItemExisting($lineItem['id'])->id: NULL;
                        $lineItem['order_id'] = $orderId;
                        $sanitizedData = $newLineItem->sanitizeData($lineItem);
                        Log::info('Line Item Id: ' . $lineItemId);
    
                        if(!$lineItemId) {
                            $newLineItem->create($sanitizedData);
                        } else {
                            $updateLineItem = $newLineItem->where('id',$lineItem['id'])->first();
                            unset($sanitizedData['id']);
                            $updateLineItem->update($sanitizedData);
                        }
                    }
                }
            } 
        } catch (\Throwable $th) {
            throw $th;
        }        
    }
    
    // 3 months Old Data Deletion
    public function deleteOrders() {
        $resposeOrderData = $this->makeOrderRequest();
        foreach($resposeOrderData['data'] as $order) {
            $orderData = new Order();
            $sanitizedData = $orderData->sanitizeOrderData($order);
            $existingOrderData = $orderData->checkOrderExistingAndGetBase64($order['id']);
            if($existingOrderData) {
                $existingOrderData->billing = json_encode($existingOrderData->billing);
                $existingOrderData->shipping = json_encode($existingOrderData->billing);
                $existingOrderDataRaw = collect($existingOrderData)->toArray();
                $sanitizedData['billing'] = collect(json_decode($sanitizedData['billing']))->toArray();
                $sanitizedData['shipping'] = collect(json_decode($sanitizedData['shipping']))->toArray();
                $targetDate = new DateTime($order['date_created']);
                $targetDate->modify('+3 months');
                $currentDate = new DateTime('now');
                if ($currentDate < $targetDate && (base64_encode(json_encode($existingOrderDataRaw)) == base64_encode(json_encode($sanitizedData)))
                ) {
                    echo "Deleting Data";
                    $orderRow = $orderData->where('id',$order['id'])->first();
                    $orderRow->lineItems()->delete();
                    $orderRow->delete();
                }
            }
        }
    }
}
