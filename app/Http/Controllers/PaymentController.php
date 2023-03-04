<?php

namespace App\Http\Controllers;

use App\Helpers\MidtransApi;
use App\Models\LogApi;
use App\Models\MasterGame;
use App\Models\MasterTeam;
use Illuminate\Http\Request;
use App\Models\MasterTournament;
use App\Models\Personnel;
use App\Models\RatingTournament;
use App\Models\StandingTournamentMatchModel;
use App\Models\StandingTournamentModel;
use App\Models\TeamTournament;
use App\Models\TreeTournamentMatchModel;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    public function __construct(Request $request)
    {
        $token = $request->bearerToken();
        if ($token != env('GOD_BEARER')) {
            $this->middleware('auth:api');
        }
    }

    public function createPaymentTransactions(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        DB::beginTransaction();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                'payment_type' => 'required|string',
                'gross_amount' => 'required|numeric',
                'items_detail' => 'required|string',
                'customer_detail' => 'required|string'
            ]);
            $userId = isset($requestData['host_id']) ? trim($requestData['host_id']) : NULL;
            $paymentType = isset($requestData['name']) ? trim($requestData['name']) : NULL;
            $grossAmount = isset($requestData['gross_amount']) ? intval($requestData['gross_amount']) : 0;
            $itemsDetail = isset($requestData['items_detail']) ? trim($requestData['items_detail']) : NULL;
            $customerDetail = isset($requestData['customer_detail']) ? trim($requestData['customer_detail']) : NULL;

            if (!$validator->fails()) {
                $orderId = $paymentType . "-" . time();
                $createTransaction = MidtransApi::transactions($orderId, $grossAmount, $itemsDetail, $customerDetail);
                if ($createTransaction) {
                    if (!isset($createTransaction["error_messages"])) {
                        $dataSaveDB = [
                            "token_trx" => $createTransaction["token"],
                            "url_trx" => $createTransaction["redirect_url"]
                        ];
                        $response->code = '00';
                        $response->desc = 'Create Payment Transactions Success!';
                        $response->data = [
                            "url" => $createTransaction["redirect_url"]
                        ];
                        DB::commit();
                    } else {
                        $response->code = '05';
                        $response->desc = "Third Party Messages : " . implode("; ", $createTransaction["error_messages"]);
                    }
                } else {
                    $response->code = '03';
                    $response->desc = "Cannot connect to third party!";
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        // LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }
}
