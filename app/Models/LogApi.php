<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogApi extends Model
{
    use HasFactory;

    protected $table = 'log_api';
    protected $fillable = [
        'request_user',
        'ip_address',
        'url_api',
        'request',
        'response'
    ];
    protected $hidden = array('id', 'created_at', 'updated_at');

    public static function createLog($user_id, $request_path, $request, $response)
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        $insertLog = array(
            'request_user' => $user_id,
            'ip_address' => $ipaddress,
            'url_api' => $request_path,
            'request' => $request,
            'response' => $response
        );
        LogApi::create($insertLog);
        return true;
    }
}
