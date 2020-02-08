<?php

//-------------------------------------------------------

//Imports
require_once('connections.php');

//Connctions Settings :
ignore_user_abort(true) ;
set_time_limit(0);

//-------------------------------------------------------


xSendPic($_GET["Url"] , $_GET["SendTo"]);


function xSendPic($url,$SendTo){

    $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));    //Make the conn faster
    $time = generateUploadId();

    $ThreadID = GetThreadID($SendTo);

    if ($ThreadID != ""){
    $pic = file_get_contents($url,false,$context);
   if (UploadPic($time,strlen($pic),$pic) != ""){
            if (SendPic($ThreadID,$time) != ""){
               // change status --> sent
             die($ThreadID . " : Done");   
         }else{
             die($ThreadID . " : Fail");   
         }
        }
    }


}

?>