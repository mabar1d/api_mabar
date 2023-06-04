<?php

namespace App\Http\Controllers;

use App\Models\LogApi;
use App\Models\MasterTeam;
use App\Models\PaymentMidtransLogModel;
use App\Models\PaymentStatusModel;
use App\Models\Personnel;
use App\Models\TeamTournament;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use stdClass;

class CallbackMidtransController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        // Set your server key (Note: Server key for sandbox and production mode are different)
        $server_key = 'SB-Mid-server-0_z-SFD780trnbGAYI6uFxcr';
        // Set true for production, set false for sandbox
        $is_production = false;

        $api_url = $is_production ?
            'https://app.midtrans.com/snap/v1/transactions' :
            'https://app.sandbox.midtrans.com/snap/v1/transactions';


        // // Check if request doesn't contains `/charge` in the url/path, display 404
        // if (!strpos($_SERVER['REQUEST_URI'], '/charge')) {
        //     http_response_code(404);
        //     echo "wrong path, make sure it's `/charge`";
        //     exit();
        // }
        // Check if method is not HTTP POST, display 404
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            echo "Page not found or wrong HTTP request method is used";
            exit();
        }

        // get the HTTP POST body of the request
        $request_body = file_get_contents('php://input');
        // set response's content type as JSON
        header('Content-Type: application/json');
        // call charge API using request body passed by mobile SDK
        $charge_result = $this->chargeAPI($api_url, $server_key, $request_body);
        // set the response http status code
        http_response_code($charge_result['http_code']);
        // then print out the response body
        echo $charge_result['body'];
    }

    /**
     * call charge API using Curl
     * @param string  $api_url
     * @param string  $server_key
     * @param string  $request_body
     */
    function chargeAPI($api_url, $server_key, $request_body)
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            // Add header to the request, including Authorization generated from server key
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($server_key . ':')
            ),
            CURLOPT_POSTFIELDS => $request_body
        );
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }

    public function handle(Request $request)
    {
        $response = new stdClass();
        $response->code = '';
        $response->desc = '';
        $requestData = json_decode($request->input(), true);
        DB::beginTransaction();
        try {
            $orderId = isset($requestData["order_id"]) && $requestData["order_id"] ? $requestData["order_id"] : NULL;
            $explodeOrderId = explode("-", $orderId);
            $userId = isset($explodeOrderId[2]) && $explodeOrderId[2] ? $explodeOrderId[2] : NULL;
            $paymentType = isset($requestData["payment_type"]) && $requestData["payment_type"] ? $requestData["payment_type"] : NULL;
            $bankName = NULL;
            $vaNumber = NULL;
            $resultCircleApi = false;

            if ($paymentType == "bank_transfer") {
                if (!isset($requestData["va_numbers"])) {
                    throw ValidationException::withMessages(['Wrong Format.']);
                }
                $bankName = isset($requestData["va_numbers"][0]["bank"]) && $requestData["va_numbers"][0]["bank"] ? $requestData["va_numbers"][0]["bank"] : NULL;
                $vaNumber = isset($requestData["va_numbers"][0]["va_number"]) && $requestData["va_numbers"][0]["va_number"] ? $requestData["va_numbers"][0]["va_number"] : NULL;
            } elseif ($paymentType == "echannel") {
                $bankName = isset($requestData["biller_code"]) && $requestData["biller_code"] ? $requestData["biller_code"] : NULL;
                $vaNumber = isset($requestData["bill_key"]) && $requestData["bill_key"] ? $requestData["bill_key"] : NULL;
            }

            if ($explodeOrderId[1] == "TR") { //register tournament
                $tournamentId = isset($explodeOrderId[4]) && $explodeOrderId[4] ? $explodeOrderId[4] : NULL;
                if ($requestData["status_code"] == 200 || $requestData["status_code"] == 201) { //payment status is settlement atau pending
                    $headers = array(
                        'Content-Type: application/x-www-form-urlencoded',
                        'Authorization: Bearer ' . env("GOD_BEARER")
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, url('api/registerTournament'));
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, 'user_id=' . $userId . '&tournament_id=' . $tournamentId);
                    $result = curl_exec($ch);
                    if ($result) {
                        $resultCircleApi = json_decode($result);
                    };
                } elseif ($requestData["status_code"] == 202) { //failed registration
                    $getPersonnel = Personnel::where('user_id', $userId)->first();
                    if ($getPersonnel) {
                        if (isset($getPersonnel->team_id) && $getPersonnel->team_id) {
                            $getTeam = MasterTeam::where('id', $getPersonnel->team_id)->first();
                            if ($getTeam) {
                                if ($getTeam->admin_id == $userId) {
                                    TeamTournament::where("team_id", $getTeam->id)->where("tournament_id", $tournamentId)->delete();
                                    $resultCircleApi = new stdClass();
                                    $resultCircleApi->code = "00";
                                    $resultCircleApi->desc = "Success Delete user_id";
                                }
                            }
                        }
                    }
                }
            }

            PaymentStatusModel::updateOrCreate(
                [
                    "user_id" => $userId,
                    "order_id" => $orderId
                ],
                [
                    "status_code" => isset($requestData["status_code"]) && $requestData["status_code"] ? $requestData["status_code"] : NULL,
                    "transaction_status" => isset($requestData["transaction_status"]) && $requestData["transaction_status"] ? $requestData["transaction_status"] : NULL,
                    "transaction_time" => isset($requestData["transaction_time"]) && $requestData["transaction_time"] ? $requestData["transaction_time"] : NULL,
                    "settlement_time" => isset($requestData["settlement_time"]) && $requestData["settlement_time"] ? $requestData["settlement_time"] : NULL,
                    "expiry_time" => isset($requestData["expiry_time"]) && $requestData["expiry_time"] ? $requestData["expiry_time"] : NULL,
                    "gross_amount" => isset($requestData["gross_amount"]) && $requestData["gross_amount"] ? $requestData["gross_amount"] : NULL,
                    "payment_type" => $paymentType,
                    "bank_name" => $bankName,
                    "va_number" => $vaNumber
                ]
            );
            PaymentMidtransLogModel::create(
                [
                    "order_id" => $orderId,
                    "request_body" => isset($requestData) && $requestData ? json_encode($requestData) : NULL,
                    "user_id" => $userId,
                    "status_code" => isset($requestData["status_code"]) && $requestData["status_code"] ? $requestData["status_code"] : NULL,
                    "transaction_status" => isset($requestData["transaction_status"]) && $requestData["transaction_status"] ? $requestData["transaction_status"] : NULL
                ]
            );
            DB::commit();
            $response->code = "00";
            $response->desc = "Success Get Payment Status!";
            $response->data = [
                "responseCirlceApi" => $resultCircleApi
            ];
        } catch (Exception $e) {
            DB::rollback();
            $response->code = '99';
            $response->desc = 'Caught exception: ' .  $e->getMessage();
        }
        LogApi::createLog("MIDTRANS", $request->path(), json_encode($requestData), json_encode($response));
        return response()->json($response);
    }
}
