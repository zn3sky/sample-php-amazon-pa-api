<pre>
<?php
require_once('Services/Amazon.php');

// 接続情報定義
require_once("Define.php");
// defineには下記を設定
//define("AWS_ACCESSKEYID", "アクセスキー");
//define("AWS_SECRETKEY", "シークレットキーF");
//define("AWS_ASSOCIATEID", "アソシエイトプログラムのトラッキングID");

$amazon = new Services_Amazon(AWS_ACCESSKEYID, AWS_SECRETKEY, AWS_ASSOCIATEID);
$amazon->setLocale('JP');
$response = $amazon->ItemSearch('All', array('Keywords' => 'トレンチコート'));

var_dump($response);

?>
</pre>