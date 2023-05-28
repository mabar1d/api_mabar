<?php

namespace App\Http\Controllers;

use App\Helpers\MidtransApi;
use App\Models\LogApi;
use App\Models\MasterTournament;
use Illuminate\Http\Request;
use App\Models\PaymentStatusModel;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Exception;

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
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $paymentType = isset($requestData['payment_type']) ? trim($requestData['payment_type']) : NULL;
            $grossAmount = isset($requestData['gross_amount']) ? intval($requestData['gross_amount']) : 0;
            $itemsDetail = isset($requestData['items_detail']) ? json_decode(trim($requestData['items_detail']), true) : NULL;
            $customerDetail = isset($requestData['customer_detail']) ? json_decode(trim($requestData['customer_detail']), true) : NULL;

            if (!$validator->fails()) {
                $orderId = $paymentType . "-" . $userId . "-" . time();
                $createTransaction = MidtransApi::transactions($orderId, $grossAmount, $itemsDetail, $customerDetail);
                if ($createTransaction) {
                    if (!isset($createTransaction["error_messages"])) {
                        $dataSaveDB = [
                            "order_id" => $orderId,
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
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }

    public function getListTransactions(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = $request->input();
        try {
            $validator = Validator::make($requestData, [
                'user_id' => 'required|string',
                // 'order_id' => 'required|string',
                'payment_code' => 'required|numeric',
                'search' => 'string',
                'page' => 'numeric',
            ]);
            $userId = isset($requestData['user_id']) ? trim($requestData['user_id']) : NULL;
            $paymentCode = isset($requestData['payment_code']) ? trim($requestData['payment_code']) : NULL;
            // $orderId = isset($requestData['order_id']) ? intval($requestData['order_id']) : 0;
            $search = trim($requestData['search']);
            $page = !empty($requestData['page']) ? trim($requestData['page']) : 1;
            if (!$validator->fails()) {
                $limit = 20;
                $query = PaymentStatusModel::select('*');
                $query->where('user_id', $userId);
                // $query->where('order_id', $orderId);
                $query->where('status_code', $paymentCode);
                if ($search) {
                    $query->where('name', 'like', $search . '%');
                }
                if ($page > 1) {
                    $offset = ($page - 1) * $limit;
                    $query->offset($offset);
                }
                $execQuery = $query->orderBy("updated_at", "desc");
                $execQuery = $query->limit($limit)
                    ->get();

                $resultData = array();
                if ($execQuery->first()) {
                    foreach ($execQuery as $rowPaymentStatus) {
                        $orderId = $rowPaymentStatus["order_id"];
                        $explodeOrderId = explode("-", $orderId);
                        $typeCg = $explodeOrderId[1];
                        $userCg = $explodeOrderId[2];
                        $typeCgId = $explodeOrderId[4];
                        if ($typeCg == "TR") {
                            $getInfoOrder = MasterTournament::select("id", "name")
                                ->where("id", $typeCgId)
                                ->first();
                            $itemName = isset($getInfoOrder["name"]) && $getInfoOrder["name"] ? $getInfoOrder["name"] : NULL;
                        }
                        $rowPaymentStatus["item_name"] = $itemName;
                        $paymentType = isset($rowPaymentStatus["payment_type"]) && $rowPaymentStatus["payment_type"] ? $rowPaymentStatus["payment_type"] : NULL;
                        $vaNumber = isset($rowPaymentStatus["va_number"]) && $rowPaymentStatus["va_number"] ? $rowPaymentStatus["va_number"] : NULL;
                        $bankName = isset($rowPaymentStatus["bank_name"]) && $rowPaymentStatus["bank_name"] ? $rowPaymentStatus["bank_name"] : NULL;
                        if ($paymentType == "bank_transfer") {
                            $paymentTypeName = "Virtual Account/Bank Transfer";
                            $vaNumberFinal = $bankName . " - " . $vaNumber;
                            $rowPaymentStatus["payment_type_name"] = $paymentTypeName;
                            $rowPaymentStatus["va_final"] = $vaNumberFinal;
                        }
                        $resultData[] = $rowPaymentStatus;
                    }
                    $response->code = '00';
                    $response->desc = 'Success Get List Payment.';
                    $response->data = $resultData;
                } else {
                    $response->code = '02';
                    $response->desc = 'List Payment is Empty.';
                }
            } else {
                $response->code = '01';
                $response->desc = $validator->errors()->first();
            }
        } catch (Exception $e) {
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog($userId, $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }
}
