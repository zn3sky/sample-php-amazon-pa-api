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

// TODO 
$keyword = "トレンチコート";
$searchindex = "Apparel";
$opt = array(
	"Keywords" => $keyword,
	"Sort" => "salesrank",// Allの場合は指定できない
	// http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html
	"ResponseGroup" => "BrowseNodes,Medium,Variations"
);

$response = $amazon->ItemSearch($searchindex, $opt);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>sample-php-amazon-pa-api</title>
<link href="/bootstrap-3.3.7-dist/css/bootstrap.min.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container-fluid">

<div class="jumbotron">
<h1>sample-php-amazon-pa-api</h1>
<p><a href="https://github.com/zn3sky/sample-php-amazon-pa-api.git">https://github.com/zn3sky/sample-php-amazon-pa-api.git</a></p>
</div>


<form>
<div class="form-group">
<span class="label label-info">searchindex</span>
<input type="text" name="searchindex" value="<?=htmlspecialchars($searchindex)?>" disabled>
<span class="label label-info">キーワード</span>
<input type="text" name="keyword" value="<?=htmlspecialchars($keyword)?>" disabled>
</div>
<!--<button type="submit">検索</button>-->
</form>
<?php

// 検索結果表示
if (!PEAR::isError($response)) {

	// 検索結果情報	
	echo "<div>{$response['TotalResults']}件みつかりました</div>";

	echo "<a href=\"{$response['MoreSearchResultsUrl']}\" target=\"_blank\">検索結果をもっとみる（amazonページへ）</a>";
	
	echo "<hr>";
	
	
	// 検索結果表示
	echo "<table class=\"table table-hover\">";
	
	// ASIN ループ
	foreach ($response["Item"] as $no => $item) {
		echo "<tr><td><button class=\"btn btn-default\" type=\"button\">", ($no+1), "件目</button></td><td>";
		
		dispItem($item);
		
		echo "</td></tr>";
	}
	
	echo "</table>";
	
} else {
	echo "一時的にamazonのAPIが応答しません。しばらくたってから再読み込みしてください。";
	
	//dubug
	//echo "<pre>";
	//var_dump($response);
	//echo "</pre>";
}

// 検索結果表示
function dispItem($itemList) {

	echo "<ul class=\"list-group\">";

	foreach ($itemList as $key => $val) {
		// 大きめ画像はスキップ
		if ($key == "MediumImage" || $key == "LargeImage") {
			continue;
		}
		// 画像の縦横サイズはスキップ
		if ($key == "Height" || $key == "Width") {
			continue;
		}
		
		echo "<li class=\"list-group-item\">";
		
		// 親要素の場合は再帰的に子を取り出す
		if (is_array($val)) {
			echo "<span class=\"label label-default\">", htmlspecialchars($key), "</span>";
			echo dispItem($val);
		// 子要素の場合
		} else {
			// 画像URLの場合はimgタグにする
			if (preg_match("/^https:\/\/images-fe.ssl-images-amazon.com\//", $val)) {
				echo "<img src=\"", htmlspecialchars($val), "\" class=\"img-responsive img-thumbnail\">";
			// 商品ページURLの場合はリンクタグにする
			} else if (preg_match("/^https:\/\/www.amazon.co.jp\//", $val)) {
				echo "<a class=\"btn btn-default\" href=\"", htmlspecialchars($val) ,"\" role=\"button\" target=\"_blank\">amazonのページでみる</a>";
			// その他はそのまま表示
			} else {
				echo "<span class=\"label label-default\">", htmlspecialchars($key), "</span><span>", htmlspecialchars($val), "</span>";
			}
		}
		
		echo "</li>";
		
	}
	
	echo "</ul>";
	
}
?>

</div>
</body>
</html>

