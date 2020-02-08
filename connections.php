<?php

//-----------------------------
//Defines : 

//DATABASE :
define('DB_SERVER', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');
//Cookies : (Use the cookies for your bot account)
define('WebCookies', 'sessionid='); 
define('AppCookies', '');
define('AppUserAgent' , 'User-Agent:Instagram 45.0.0.10.07 Android (78/2.4.2; 840dpi; 1280x720; samsung; qcom; en_US; 786584)');
//-----------------------------


function Request($url,$headers,$json,$method,$for, $PostData = ""){
    $ch = curl_init();
    if($for == "WEB"){
    curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/$url");
    curl_setopt($ch, CURLOPT_COOKIE, WebCookies);
    }elseif($for == "WEBDP"){
    curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/$url");
    curl_setopt($ch, CURLOPT_COOKIE, WebDPCookie());
    }elseif($for == "APP"){
    curl_setopt($ch, CURLOPT_URL, "https://i.instagram.com/$url");
    curl_setopt($ch, CURLOPT_COOKIE, AppCookies);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        //For localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        //For localhost
    if($method == "POST"){curl_setopt($ch, CURLOPT_POSTFIELDS,$PostData);}
     $output = curl_exec($ch);  
    curl_close($ch);

    //Return the respone
    if($json){
    return json_decode($output);
    }else{
        return $output ;
        }

}

function fast_request($url, $check_ssl=true) {
    
    try{

  $cmd = "curl -X POST -H 'Content-Type: application/json'";
  $cmd.= " -d '" . "" . "' '" . $url . "'";

  if (!$check_ssl){
    $cmd.= "'  --insecure"; // this can speed things up, though it's not secure
  }
  $cmd .= " > /dev/null 2>&1 &"; //just dismiss the response

  exec($cmd, $output, $exit);
  sleep(10);
    } catch (Exception $e) {
             fast_request($url, $check_ssl);
        }
  return $exit == 0;
}

//------------------------------------------------------------------------------------

function uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function generateUploadId(){
     static $_lastUploadId = null;

    $result = null;
        while (true) {
            $result = number_format(round(microtime(true) * 1000), 0, '', '');
            if ($_lastUploadId !== null && $result === $_lastUploadId) {
                usleep(1000);
            } else { // OK!
                $_lastUploadId = $result;
                break;
            }
        }
    
    return $result;
}

//------------------------------------------------------------------------------------

    

function AddToDB($shortcode, $url, $user,$type ,$PostOrder,$time){

        $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
 if ($db->connect_error) {
     die("Connection failed: " . $db->connect_error);
 } 
 
 $sql = "INSERT INTO URLDTP (shortcode,url,type,username,PostOrder,time)
 VALUES ('$shortcode', '$url' , '$type' , '$user', '$PostOrder', '$time')";
 
 if ($db->query($sql) === TRUE) {
      echo "$shortcode,";
 }
 
 $db->close();

}
 
function CheckDB($shortcode , $user ,$order){

     $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
    if ($db->connect_error)
    {
     die("Connection failed: " . $db->connect_error);
    } 
 
     
    $sql = "SELECT id FROM URLDTP WHERE shortcode = '$shortcode' AND username='$user' AND PostOrder='$order'";
    $result = $db->query($sql);
    
    if (!$result) {
    trigger_error('Invalid query: ' . $db->error);
        }
  
    if ($result->num_rows < 1) {
        return true;
    }else{
     return false;
    }
 
    $db->close(); 
}

function CheckDBStory($user , $code){

     $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
    if ($db->connect_error)
    {
     die("Connection failed: " . $db->connect_error);
    } 
 
     
    $sql = "SELECT id FROM URLDTP WHERE shortcode = '$code' AND username='$user'";
    $result = $db->query($sql);
    
    if (!$result) {
    trigger_error('Invalid query: ' . $db->error);
        }
  
    if ($result->num_rows < 1) {
        return true;
    }else{
     return false;
    }
 
    $db->close(); 
}



//------------------------------------------------------------------------------------

function GetMentions(){

    $headers = [
        'Accept: */*',
        'X-Requested-With: XMLHttpRequest',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
        'X-IG-App-ID: 936619743392459',
        'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
        'X-CSRFToken: 4jpy6F6yjsqW6pNAzSMwM7yiWBZ286IQ',
        ];

     return Request(
     "accounts/activity/?__a=1&include_reel=true",$headers,true,"GET","WEB"
     );
}

function Download($shortcode){

    $headers = [
        'Accept: */*',
        'X-Requested-With: XMLHttpRequest',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
        'X-IG-App-ID: 936619743392459',
        'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
        'X-CSRFToken: 4jpy6F6yjsqW6pNAzSMwM7yiWBZ286IQ',
        ];

     return Request(
     "p/$shortcode/?__a=1",$headers,true,"GET","WEBDP"
     );
}

function UploadVideo1($uuid,$time){

    $headers = [
        "X_FB_VIDEO_WATERFALL_ID: $uuid",
        'X-Instagram-Rupload-Params: {"upload_media_height":"640","direct_v2":"1","upload_media_width":"640","upload_media_duration_ms":"5733","upload_id":"' . $time .'","retry_context":"{\"num_step_auto_retry\":0,\"num_reupload\":0,\"num_step_manual_retry\":0}","media_type":"2"}',
        'X-IG-Connection-Type: WIFI',
         'X-IG-Capabilities: 3brTBw==',
        'X-IG-App-ID: 567067343352427',
         AppUserAgent,
         'X-FB-HTTP-Engine: Liger',
         'Accept-Language: ar-SA, en-US',
    ];

    return Request(
    "rupload_igvideo/$time" . "_0_1",$headers,false,"GET","APP"
    );
}

function UploadVideo2($uuid,$time,$video_len,$video){

     $headers = [
        "X_FB_VIDEO_WATERFALL_ID: $uuid",
        'X-Instagram-Rupload-Params: {"upload_media_height":"640","direct_v2":"1","upload_media_width":"640","upload_media_duration_ms":"5733","upload_id":"' . $time .'","retry_context":"{\"num_step_auto_retry\":0,\"num_reupload\":0,\"num_step_manual_retry\":0}","media_type":"2"}',
        AppUserAgent,
        'X-Entity-Type: video/mp4',
         'Offset: 0',
         "X-Entity-Name: $time" . "_0_1",
         "X-Entity-Length: $video_len",
         'X-IG-Connection-Type: WIFI',
         'X-IG-Capabilities: 3brTBw==',
         'X-IG-App-ID: 567067343352427',
         'Accept-Language: ar-SA, en-US',
         'Content-Type: application/octet-stream',
         'X-FB-HTTP-Engine: Liger',
    ];

     return Request(
     "rupload_igvideo/$time" . "_0_1",$headers,false,"POST","APP",$video
     );
}

function SendVideo($post){

    $headers = [
        AppUserAgent,
        'X-IG-Connection-Speed: -1kbps',
        'X-IG-Bandwidth-Speed-KBPS: 558.012',
        'X-IG-Bandwidth-TotalBytes-B: 1585034',
        'X-IG-Bandwidth-TotalTime-MS: 2392',
        'retry_context: {"num_step_auto_retry":0,"num_reupload":0,"num_step_manual_retry":0}',
        'X-IG-Connection-Type: WIFI',
        'X-IG-Capabilities: 3brTBw==',
        'X-IG-App-ID: 567067343352427',
        'Accept-Language: ar-SA, en-US',
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'X-FB-HTTP-Engine: Liger',
        ];

    return Request(
    "api/v1/direct_v2/threads/broadcast/configure_video/",$headers,false,"POST","APP",$post
    );
}

function GetThreadID($UserID){

    try{
    $headers = [
        'Accept: */*',
        'Sec-Fetch-Mode: cors',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
        'X-IG-WWW-Claim: hmac.AR1cNpslMHtZ3j2Pw5KgbjW2OKQ8bb7lcQ0NRLc4tGki75uL',
        'X-Instagram-AJAX: 14d008e2bc7b',
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest',
        'X-CSRFToken: emMfqQa8eKV888pua70mGnBJ1Sr3rklE',
        'X-IG-App-ID: 1217981644879628',
        'Sec-Fetch-Site: same-origin',
        'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
        ];

     $threadid = json_decode(Request(
     "direct_v2/web/create_group_thread/",$headers,false,"POST","WEB","recipient_users=%5B%22" . $UserID . "%22%5D"
     ));

     return ($threadid->thread_id); 
         }catch(Exception $e) {
          return "" ;
        }
}

function UploadPic($time,$pic_length,$pic){

    $headers = [
    'Sec-Fetch-Mode: cors',
    "X-Entity-Name: direct_$time",
    'Offset: 0',
    'X-Instagram-AJAX: 086cfef91e6a',
    'Content-Type: image/jpeg',
    'Accept: */*',
    'X-Instagram-Rupload-Params: {"media_type":1,"upload_id":"'. $time .'","upload_media_height":1080,"upload_media_width":1080}',
    'X-Requested-With: XMLHttpRequest',
    "X-Entity-Length: $pic_length",
    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
    'X-CSRFToken: MzsmHbMnmyQnqM7S9SpJxXCtTizc19wX',
    'X-IG-App-ID: 1217981644879628',
    'Sec-Fetch-Site: same-origin',
    'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
    ];

     return Request(
     "rupload_igphoto/direct_$time",$headers,false,"POST","WEB",$pic
     );
}

function SendPic($ThreadID,$time){

    $headers = [
    'Sec-Fetch-Mode: cors',
    "X-Instagram-AJAX: 086cfef91e6a",
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: */*',
    'X-Requested-With: XMLHttpRequest',
    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
    'X-CSRFToken: MzsmHbMnmyQnqM7S9SpJxXCtTizc19wX',
    'X-IG-App-ID: 1217981644879628',
    "Sec-Fetch-Site: same-origin",
    'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
    ];

     return Request(
     "direct_v2/web/threads/broadcast/configure_photo/",$headers,false,"POST","WEB","action=send_item&allow_full_aspect_ratio=1&content_type=photo&mutation_token=&sampled=1&thread_id=$ThreadID&upload_id=$time"
     );
}

function GetUserInfo($username){

    $headers = [
        'Accept: */*',
        'X-Requested-With: XMLHttpRequest',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
        'X-IG-App-ID: 936619743392459',
        'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
        'X-CSRFToken: 4jpy6F6yjsqW6pNAzSMwM7yiWBZ286IQ',
        ];

     return Request(
     "$username/?__a=1",$headers,true,"GET","WEBDP"
     );

}

function GetInboxDM(){

    $headers = [
        'Accept: */*',
        'Sec-Fetch-Mode: cors',
        'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
        'X-IG-WWW-Claim: hmac.AR1cNpslMHtZ3j2Pw5KgbjW2OKQ8bb7lcQ0NRLc4tGki75uL',
        'X-Instagram-AJAX: 14d008e2bc7b',
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest',
        'X-CSRFToken: emMfqQa8eKV888pua70mGnBJ1Sr3rklE',
        'X-IG-App-ID: 1217981644879628',
        'Sec-Fetch-Site: same-origin',
        'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
        ];

     return Request(
     "direct_v2/web/inbox/?persistentBadging=true&limit=25&thread_message_limit=2",
     $headers,true,"GET","WEB"
     );

}

function GetPendingDM(){

    $headers = [
        'Accept: */*',
        'Sec-Fetch-Mode: cors',
        'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
        'X-IG-WWW-Claim: hmac.AR1cNpslMHtZ3j2Pw5KgbjW2OKQ8bb7lcQ0NRLc4tGki75uL',
        'X-Instagram-AJAX: 14d008e2bc7b',
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest',
        'X-CSRFToken: emMfqQa8eKV888pua70mGnBJ1Sr3rklE',
        'X-IG-App-ID: 1217981644879628',
        'Sec-Fetch-Site: same-origin',
        'Accept-Language: ar,en-US;q=0.9,en;q=0.8,und;q=0.7',
        ];

     return Request(
     "direct_v2/web/pending_inbox/",
     $headers,true,"GET","WEB"
     );

}

function SendDM($UserID , $Username, $Text){

     $headers = [
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        'Accept: */*',
        'X-IG-Capabilities: 3brTBw==',
        'Accept-Language: en-SA;q=1, ar-SA;q=0.9',
        AppUserAgent,
        'X-IG-Connection-Type: WiFi',
    ];
    
    $post = 'recipient_users=%5B%5B' . $UserID . '%5D%5D&action=send_item&client_context=&_csrftoken=dZEY078Np5mkwY2KbUSVbZcebusg6nxs&_uuid=' . uuid() . '&link_urls=%5B%22http%3A%5C%2F%5C%2Fthisvideo.tech%5C%2Fu%5C%2F' . $Username . '%22%5D&link_text=' . $Text;
    
     $Request = Request(
     "api/v1/direct_v2/threads/broadcast/link/",$headers,false,"POST","APP",$post
     );
     
     if($Request != "" || $Request != NULL){
     
        if (strpos($Request, 'status_code": "200') !== false) {
         return true;
     }else{
         return false;
        }
     }else{
         return false;
     }

}

function SendVideoHelper($Url , $SendTo){
    
  
     return fast_request("SendVideo.php?Url=" . urlencode($Url) . "&SendTo=$SendTo");
    
}

function SendPicHelper($Url , $SendTo){
    
    return fast_request("SendPic.php?Url=" . urlencode($Url) . "&SendTo=$SendTo");
    
}

function WebDPCookie(){
    // Cookies for random accounts, to help the bot 
        $cookie = [
        "sessionid=",
        "sessionid=",
        "sessionid="
            ];
    
        return $cookie[mt_rand(0, count($cookie) - 1)];
}


//------------------------------------------------------------------------------------

set_error_handler (
    function($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);     
    }
);

//------------------------------------------------------------------------------------

?>