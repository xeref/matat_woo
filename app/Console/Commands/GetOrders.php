<?php

namespace App\Console\Commands;

use App\Models\LineItem;
use App\Models\Order;
use App\Traits\CommonTrait;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetOrders extends Command
{
    use CommonTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Orders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('This command runs every 10 seconds.');
        Log::info('Order Log Started: '. date('Y-m-d H:m:s'));
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
        return Command::SUCCESS;
    }
}
