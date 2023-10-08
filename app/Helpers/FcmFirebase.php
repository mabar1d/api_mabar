<?php

namespace App\Helpers;

class FcmFirebase
{
    public static function send($to, $title, $body, $icon, $url)
    {
        $token = $to;
        $from = env("SERVER_FCM_KEY");
        $msg = array(
            'body' => $body,
            'title' => $title,
            'image' => $icon,
            'click_action' => $url
        );

        $fields = array(
            'to' => $token,
            'notification' => $msg
        );

        $headers = array(
            'Authorization: key=' . $from,
            'Content-Type: application/json'
        );
        //#Send Reponse To FireBase Server 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result) {
            return json_decode($result);
        } else return false;
    }
}
