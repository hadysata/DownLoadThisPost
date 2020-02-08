<?php

//-------------------------------------------------------

//Imports
require_once('connections.php');

//Connctions Settings :
ignore_user_abort(true) ;
set_time_limit(0);
date_default_timezone_set('Asia/Riyadh');
//-------------------------------------------------------

LoadRequests();

function LoadRequests(){

    $Mentions = GetMentions();
    if ($Mentions != ""){
    $Mentions = ($Mentions->graphql->user->activity_feed->edge_web_activity_feed->edges); 

    foreach ($Mentions as $value) {
                  // type 5 --> Mention on post
        if($value->node->type == "5"){
            $shortcode = ($value->node->media->shortcode);
            $user = ($value->node->user->username);
            $userID = ($value->node->user->id);
            $order = ($value->node->text);

            if(CheckAvatar($order) == "true"){
                $order = "Avatar";
            }else{
                $order = (int) filter_var($order, FILTER_SANITIZE_NUMBER_INT) ;
            }
            SetupForSend($shortcode,$user,GetRightOrder($order) ,$userID);
             }
        }
    }
}

function CheckAvatar($comment){
$targets = array('avatar', 'Av' , "aV" ,  'Avatar' ,  'av', 'عرض');

foreach($targets as $t)
    {
      if (strpos($comment,$t) !== false) {
       return "true";
       break;
        }
    }
    return "false";
}

function SetupForSend($shortcode,$user,$order,$userID){


    if(CheckDB($shortcode , $user ,$order )){

        if ($order === "Avatar"){

        $username = Download($shortcode);
        $username = ($username->graphql->shortcode_media->owner->username) ;
        $url =  GetAvatarURL($username);
        
        AddToDB($shortcode,$url, $user, "Avatar Image" , $order, date("l h:i"));
         echo SendUserURL($userID,$user,"Avatar Image");
        SendPicHelper($url , $userID); //Send the raw Image to DM

        }else{

        $data = Download($shortcode);
        if ($data != "" || $data != NULL){
       $url = GetDownloadURL($data,$order);

        if($url != ""){
       if (strpos($url, 'mp4') !== false) {
            $type = "Video";
       }elseif(strpos($url, 'jpg') !== false){
             $type = "Image";
       }else{$type = "UNDEFIND";}

       if($url != "" || $url != "https://static.cdninstagram.com/rsrc.php/null.jpg"){
        AddToDB($shortcode, $url, $user,$type , $order, date("l h:i"));
        echo SendUserURL($userID,$user,$type);
        if($type == "Video"){SendVideoHelper($url , $userID);} //Send the raw video to DM
        if($type == "Image"){SendPicHelper($url , $userID);} //Send the raw Image to DM
                }
                
            }else{
              AddToDB($shortcode, "", $user, "Error ->not found" , $order, date("l h:i"));
            }
        }else{
                 AddToDB($shortcode, "", $user, "Error ->not found" , $order, date("l h:i"));
        }
    }


        }
    

}

function GetRightOrder($order){
    if($order === "Avatar"){
        return "Avatar";
    }else{
     if($order > 0 && $order <= 10)
     {
         return ($order - 1);
     }else{
         return 0;
     }
    }
}

function GetAvatarURL($username){
        $data = GetUserInfo($username) ;
       return ($data->graphql->user->profile_pic_url_hd) ;
}

function GetDownloadURL($data,$order){

    if(($data->graphql->shortcode_media->__typename) == "GraphVideo"){
        return ($data->graphql->shortcode_media->video_url);
    }elseif(($data->graphql->shortcode_media->__typename) == "GraphImage"){
        return ($data->graphql->shortcode_media->display_resources[2]->src);
                                //Slides
    }elseif(($data->graphql->shortcode_media->__typename) == "GraphSidecar"){
        
        try{
        
        if(($data->graphql->shortcode_media->edge_sidecar_to_children->edges[((int)$order)]->node->__typename) == 'GraphVideo'){
            return ($data->graphql->shortcode_media->edge_sidecar_to_children->edges[((int)$order)]->node->video_url);
            
        }elseif(($data->graphql->shortcode_media->edge_sidecar_to_children->edges[((int)$order)]->node->__typename) == 'GraphImage'){
            return ($data->graphql->shortcode_media->edge_sidecar_to_children->edges[((int)$order)]->node->display_resources[2]->src);
        }else{
            return "";
        }
        
        } catch (Exception $e) {
             return "";
        }
    }
   
}

function SendUserURL($UserID,$Username,$Type){
    
    $url = "thisvideo.tech/u/$Username";
    $Replys = [
        "Welcome $Username!!\nYour $Type is under processing now, just wait a minute...\n\nIf I did't send the $Type, you can download it from here : $url",
        "Hi $Username!\n Your $Type is ready, wait a few seconds...",
        "Hey $Username, $Type you've requested is ready to download, just wait a second\n\nYou can also download it from your link : $url",
        "Thank you for using our bot $Username\nYour $Type is under processing now...",
        "Boss! Your $Type is ready, just wait a minute..."
            ];
    
        $Reply = $Replys[mt_rand(0, count($Replys) - 1)];
        
       if (SendDM($UserID,$Username,$Reply)){
           return "$Username:DM SEND";
       }else{
        return "$Username:DM failed!!";
       }
        
    
}


?>