<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


trait CommonUtil {

    public function ValidationResponseFormating($e) {
        $errorResponse = [];
        $errors = $e->validator->errors();
        $col = new Collection($errors);
        foreach ($col as $error) {
            foreach ($error as $errorString) {
                $errorResponse[] = $errorString;
            }
        }
        return $errorResponse;
    }

    public function uploadGalery($image,$uploadPath=null){
      
          $name = uniqid().'_'.time().'.'.$image->getClientOriginalExtension();
          if(is_null($uploadPath)){
          $destinationPath = public_path(env('IMAGE_UPLOAD_PATH'));
          }else{
            $destinationPath = public_path($uploadPath);
        }
          $image->move($destinationPath, $name);
          return $name;
  
      }


    public function uploadBase64($image,$uploadPath=null,$extension=null){
        if(is_null($uploadPath)){
            $destinationPath = public_path(env('IMAGE_UPLOAD_PATH'));
            }else{
              $destinationPath = public_path($uploadPath);
          }
      preg_match("/data:image\/(.*?);/",$image,$image_extension); // extract the image extension
		;
    preg_match("/data:video\/(.*?);/",$image,$video_extension); // extract the image extension
    ;
  

       $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace('data:video/webm;base64,', '', $image);
        $image = str_replace('data:video/mp4;base64,', '', $image);
        $image = str_replace('data:audio/ogg;base64,', '', $image);

		
        $image = str_replace(' ', '+', $image);
     if(is_null($extension)){
		$extentsionName=sizeOf($image_extension)>0?$image_extension[1]:(sizeOf($video_extension)>0?$video_extension[1]:'');
     }else{
      $extentsionName=$extension;
    }
    $imagenamedd=Str::random(20);
        $imageName =$imagenamedd.'.'.$extentsionName;
        \File::put($destinationPath.'/'. $imageName, base64_decode($image));
        return $imageName;
    }   

    public function printLog($lineNo,$fileName,$message,$type=1){
        Log::info("**********************".$fileName.":" .$lineNo."***********************");
        switch($type){
            case 1:
                Log::info($message);
                break;
            case 2: 
                Log::error($message);
                break;  
        }
        Log::info("**********************".$fileName.":" .$lineNo."***********************");
       

    }  

    public function getCurrentTime($timezone){
       return  now()->setTimezone($timezone);
    }

    public function video_thumb($video,$uploadPath=null){
        if(is_null($uploadPath)){
            $destinationPath = public_path(env('IMAGE_UPLOAD_PATH'));
            }else{
              $destinationPath = public_path($uploadPath);
          }
          $imagenamedd=Str::random(20);
          $imageName1 = $imagenamedd.'.png';
            $time = 3;
            $infile =  $destinationPath.'/'.$video;
            $thumbnail = $destinationPath.'/'. $imageName1;
            
            $cmd = sprintf(
                'ffmpeg -i %s -ss %s -f image2 -vframes 1 %s',
                $infile, $time, $thumbnail
            ); 
            
            exec($cmd);
            return  $imageName1;
      
    }
}
