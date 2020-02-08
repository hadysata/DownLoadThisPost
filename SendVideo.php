<?php

//-------------------------------------------------------

//Imports
require_once('connections.php');

//Connctions Settings :
ignore_user_abort(true) ;
set_time_limit(0);

//-------------------------------------------------------

xSendVideo($_GET["Url"] , $_GET["SendTo"]);



function xSendVideo($url,$SendTo){

    $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));    //Make the conn faster
    $time = generateUploadId();
    $uuid = uuid();
    $video = file_get_contents($url,false,$context);
    $post = 'recipient_users=%5B%5B' . $SendTo . '%5D%5D&action=send_item&client_context=&_csrftoken=dZEY078Np5mkwY2KbUSVbZcebusg6nxs&video_result=&_uuid=' . $uuid . '&upload_id=' . $time;

   if (UploadVideo1($uuid,$time) == '{"offset":0}'){
     if (UploadVideo2($uuid,$time,strlen($video),$video) == '{"xsharing_nonces": {}, "status": "ok"}'){
         if (strpos(SendVideo($post), 'thread_id') !== false){  
                //  status --> sent
                die($uuid . " : OK");
            }else{
                sleep(5);
                if (strpos(SendVideo($post), 'thread_id') !== false){ 
                    //  status --> sent
                die($uuid . " : OK 2nd");
                }else{
                    //  status --> can't send video
                die($uuid . " : Fail");
                }
            }
        }
    }

}

?>