<?php
// Set default datetime zone
date_default_timezone_set('Asia/Kolkata');
if(isset($_GET['url']) && !empty($_GET['url'])){
    $domainURL = $_GET['url'];
    $domainURL = trim($domainURL, '/');

    // If scheme not included, prepend it
    if (!preg_match('#^http(s)?://#', $domainURL)) {
        $domainURL = 'http://' . $domainURL;
    }

    $urlParts = parse_url($domainURL);

    // remove www
    $domainURL = preg_replace('/^www\./', '', $urlParts['host']);
    $output = '';
    // Check ssl expiry date 
    $command = "echo | openssl s_client -servername ".$domainURL." -connect ".$domainURL.":443 2>/dev/null | openssl x509 -dates | grep notAfter | sed -e 's#notAfter=##'"; 
    exec($command,$output);
    $expiryGMT = strval($output[0]);
    $expiryUTC = date("d-M-Y H:iA",strtotime($expiryGMT));
    $remainingDays = date_diff(date("Y-m-d",strtotime($expiryGMT)),date('Y-m-d'));
    $expiryDate = date('Y-m-d',strtotime($expiryGMT));
    $now = date("Y-m-d");
    // $remainingDays = dateDifference($date1 , $date2);
    if($expiryDate > $now){
        $remainingDays = dateDifference($expiryDate , $now);
        echo json_encode(array("url"=>$domainURL,"ssl_status"=>"active","expiry_date"=>$expiryUTC,"remainingDays"=>$remainingDays));
    }else{
        echo json_encode(array("url"=>$domainURL,"ssl_status"=>"expired"));
    }
}
function dateDifference($date_1 , $date_2 , $differenceFormat = '%a Days' )
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
   
    $interval = date_diff($datetime1, $datetime2, true);
   
    return $interval->format($differenceFormat);
   
}
?>