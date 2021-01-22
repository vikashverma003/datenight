<?php

namespace App;
use URL;

class Helper
{
    public static function userimg($data)
    {
        if($data){
            return  $data;
        }else{
          return  URL::to("/").'/images/users/no_img.png';
        }
      
    }

    public static function productImage($data)
    {
        if($data){
            return  $data;
        }else{
          return  URL::to("/").'/images/review-banner.png';
        }
      
    }

    public static function encodeNum($num)
    {
       return base64_encode($num);
    }

    public static function decodeNum($num) 
    {
       return base64_decode($num); 
    } 


    public static function SendPushNotifications($token,$title,$body,$badge=null){

      

        $ch = curl_init("https://fcm.googleapis.com/fcm/send"); 
          $notification = array('title' =>$title , 'body' => $body, 'vibrate'=> true, 'sound'=> 'true', 'content-available' => true, 'priority' => 'high','badge' => $badge); 
          $data = array('title' => $title, 'body' => $body,'badge' => $badge);
          $arrayToSend = array('to' => $token, 'notification' => $notification, 'data' => $data);
          
          $json = json_encode($arrayToSend); 
           $headers = array();
           $headers[] = 'Content-Type: application/json';
           $headers[] = 'Authorization: key=AAAASQoK9Hg:APA91bGmKnBixCa1QAcinBmQ-WVuUsDYN_2XkKXlME1m2onHM6J9qu0jF_7BR1Vi1Edo4sz1wb5xhk1W9SKdsc-YHbC_Qa5lI9GaFST30VsvvfHAW7OrtD6D7RpAlbbh8mDXaYrETcQP';
           curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
           curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
           curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
           
           curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, true);  
           curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
           curl_setopt($ch, CURLOPT_POST, 1);
           $response = curl_exec($ch);
           curl_close($ch);
          return $response ;     
      }  
     




}
