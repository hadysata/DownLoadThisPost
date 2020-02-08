<?php

//-------------------------------------------------------

//Imports
require_once('connections.php');

//Connctions Settings :
ignore_user_abort(true) ;
set_time_limit(0);
date_default_timezone_set('Asia/Riyadh');
//-------------------------------------------------------

LoadStory(GetInboxDM());

LoadStory(GetPendingDM());


function LoadStory($inbox){
        
    if($inbox != ""){
        
    $inbox = ($inbox->inbox->threads);
        foreach ($inbox as $thread) {
            $UserID = ($thread->inviter->pk) ;
            if($UserID != "3460682298"){
         foreach ($thread->items as $item) {
            if($item->item_type == "story_share"){
                
                
                try{ 
                    
                $media = ($item->story_share->media);
                $shortcode = ($media->code);
                
                if(($media->media_type) == "2"){
                    $url = ($media->video_versions[0]->url);
                    if(CheckDBStory($UserID, $shortcode)){
                     AddToDB($shortcode, $url, $UserID,"Story Video" , "0", date("l h:i"));
                      SendVideoHelper($url , $UserID); //Send the Video DM
                }
                }elseif(($media->media_type) == "1"){
                    $url = ($media->image_versions2->candidates[0]->url);
                     if(CheckDBStory($UserID, $shortcode)){
                     AddToDB($shortcode, $url, $UserID,"Story Image" , "0", date("l h:i"));
                      SendPicHelper($url , $UserID); //Send the pic DM
                }
                }
                
    } catch (Exception $e) {
             echo "Error";
        }
                 
            }
            
         }
            }
        }
    
    
    }
    

    
}

?>