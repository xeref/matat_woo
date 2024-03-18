<?php

namespace App\Traits;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait CommonTrait
{
    public function makeOrderRequest()
    {
        try {
            $ordersUrl = env('THIRDPARTY_ENDPOINT') . 'orders';
            $username = env('THIRDPARTY_USERNAME');
            $password = env('THIRDPARTY_PASSWORD');
            $response = Http::withBasicAuth($username,$password)
                                ->get($ordersUrl);
            Log::info($response);
            Log::info(json_encode($response));
            if($response && $response->json() && isset($response->json()['data']['status']) && $response->json()['data']['status'] == 404) {
                return [
                    "status" => "error",
                    "data" => $response->json()
                ];
            } 
            return [
                "status" => "success",
                "data" => $response->json()
            ];
        } catch (\Throwable $th) {
            // throw $th;
            Log::info('Error on order fetching.');
            Log::info(json_encode($th));
        }
        
    }
}
