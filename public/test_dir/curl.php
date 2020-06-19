<?php

// データURL
//$request_url = "http://opensource-workshop.jp";
$request_url = "https://opensource-workshop.jp";
//$request_url = "http://opensource-workshop.jp/service";

// Github からデータ取得（HTTP レスポンスが gzip 圧縮されている）
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_ENCODING, "gzip");

//リクエストヘッダ出力設定
curl_setopt($ch,CURLINFO_HEADER_OUT, true);
$page = curl_exec($ch);

//echo curl_getinfo($ch, CURLINFO_HEADER_OUT);
$ret = curl_getinfo($ch);
print_r($ret);


//$json = json_decode($page);
//echo $json->tls_version;


echo "<pre>";
//var_dump($page);
echo "</pre>";

