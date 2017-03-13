<?php
require_once("Services/Amazon.php");
// 接続情報定義
require_once("Define.php");
// defineには下記を設定
//define("AWS_ACCESSKEYID", "アクセスキー");
//define("AWS_SECRETKEY", "シークレットキーF");
//define("AWS_ASSOCIATEID", "アソシエイトプログラムのトラッキングID");

define("GITHUB_URL_BASE", "https://github.com/zn3sky/");
define("REPOSITORY_NAME", "sample-php-amazon-pa-api");
define("MAX_KEYWORD_COUNT", 20);


$amazon = new Services_Amazon(AWS_ACCESSKEYID, AWS_SECRETKEY, AWS_ASSOCIATEID);
$amazon->setLocale('JP');

// 検索キーワード
$keyword = "";
if (!empty($_POST["keyword"])) {
	$keyword = $_POST["keyword"];
	if (mb_strlen($keyword) > MAX_KEYWORD_COUNT) {
		// 不正な遷移
		http_response_code( 404 );
		exit;
	}
} else {
	$keyword = "レディース　トレンチコート";
}
// サーチインデックス
$searchindex = "Apparel";

$opt = array(
	"Keywords" => $keyword,
	"Sort" => "salesrank",// Allの場合は指定できない
	// http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html
	"ResponseGroup" => "BrowseNodes,Medium,Variations"
	//"ResponseGroup" => "Images"
);

$response = $amazon->ItemSearch($searchindex, $opt);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=REPOSITORY_NAME?></title>
<link href="/bootstrap-3.3.7-dist/css/bootstrap.min.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container-fluid">

<div class="jumbotron">
<h1><?=REPOSITORY_NAME?></h1>
<p><a href="<?=GITHUB_URL_BASE?><?=REPOSITORY_NAME?>.git"><?=GITHUB_URL_BASE?><?=REPOSITORY_NAME?>.git</a></p>
</div>


<form method="POST" name="SEARCH_FORM" onSubmit="return formCheck();">
<div class="form-group">
<span class="label label-info">searchindex</span>
<input type="text" name="searchindex" size="10" value="<?=htmlspecialchars($searchindex)?>" disabled>
<span class="label label-info">キーワード(<?=MAX_KEYWORD_COUNT?>文字まで)</span>
<input type="text" name="keyword" size="30" value="<?=htmlspecialchars($keyword)?>">
</div>
<button type="submit">検索</button>
</form>
<script type="text/javascript">
<!--
function formCheck(){
	var flag = 0;
	var input_length = document.SEARCH_FORM.keyword.value.length;
	
	if (input_length == 0){
		alert("検索キーワードを入力してください");
		return false;
	} else if(input_length > <?=MAX_KEYWORD_COUNT?>) {
		alert("キーワードは<?=MAX_KEYWORD_COUNT?>文字までです。");
		return false;
	}
	
	return true;
}
// -->
</script>
<?php
// 検索結果表示
if (!PEAR::isError($response)) {

	// 検索結果情報	
	echo "<div>{$response['TotalResults']}件みつかりました</div>";

	echo "<a href=\"{$response['MoreSearchResultsUrl']}\" target=\"_blank\">検索結果をもっとみる（amazonページへ）</a>";
	
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
	echo "一時的にamazonのAPIが応答しません。しばらくたってから再検索してください。";
	
	//dubug
	//echo "<pre>";
	//var_dump($response);
	//echo "</pre>";
}

// 検索結果表示
function dispItem($itemList) {

	echo "<ul class=\"list-group\">";

	foreach ($itemList as $key => $val) {
		// 画像が各種多数帰ってくるのでMediumImageのみ表示
		if (preg_match("/Image/", $key)) {
			if ($key != "MediumImage") {
				continue;
			}
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

