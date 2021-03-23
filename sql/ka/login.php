<?php
$cp_user = "knightsb";
$cp_pwd = "XGy5PgHvXm";
$url = "https://uk1.cp.netnerd.com:2083/login";
$cookies = "/root/ka/cookies.txt";


// Create new curl handle
$ch=curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies );
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); // Save cookies to
curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$cp_user&pass=$cp_pwd");
curl_setopt($ch, CURLOPT_TIMEOUT, 100020);

// Execute the curl handle and fetch info then close streams.
$f = curl_exec($ch);
$h = curl_getinfo($ch);
    if ($f == false) {
        error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
    }

if ($f == true and strpos($h['url'],"cpsess"))
{
    // Get the cpsess part of the url
    $pattern="/.*?(\/cpsess.*?)\/.*?/is";
    $preg_res=preg_match($pattern,$h['url'],$cpsess);
}
$token=$cpsess[1];
curl_close($ch);
echo $token;
return $token;

?>
