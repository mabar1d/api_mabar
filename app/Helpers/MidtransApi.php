<?php

namespace App\Helpers;

use stdClass;

class MidtransApi
{

    public static function transactions($orderId, $grossAmount, $itemsDetail, $customerDetail)
    {
        $authorization = env("AUTH_MIDTRANS_API");
        $urlThirdParty = env("URL_MIDTRANS_API");

        $bodyParameters = new stdClass();
        $bodyParameters->transaction_details = [
            "order_id" => $orderId,
            "gross_amount" => $grossAmount
        ];
        $bodyParameters->credit_card = [
            "secure" => true
        ];
        $bodyParameters->item_details = $itemsDetail;
        $bodyParameters->customer_details = $customerDetail;
        $bodyParameters->callbacks = [
            "finish" => "testweb"
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlThirdParty . 'transactions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($bodyParameters),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $authorization
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        if ($response) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }
}
