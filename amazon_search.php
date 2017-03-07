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

$keyword = "トレンチコート";
$opt = array(
	"Keywords" => $keyword,
	"Sort" => "salesrank",// Allの場合は指定できない
	"ResponseGroup" => "ItemIds,ItemAttributes,Images"
);

$response = $amazon->ItemSearch("Apparel", $opt);

// 検索結果表示
if (!PEAR::isError($response)) {
	
	echo htmlspecialchars($keyword), "の検索結果/", "{$response['TotalResults']}件みつかりました<br>";
	
	echo "<a href=\"{$response['MoreSearchResultsUrl']}\" target=\"_blank\">amazonでみる</a>";

	echo "<hr>";

	foreach ($response["Item"] as $item) {
		// 商品ID
		echo "ASIN:<a href=\"{$item['DetailPageURL']}\">{$item["ASIN"]}</a>(親ASIN:{$item["ParentASIN"]})<br>";
		// 画像
		echo "<img src=\"", htmlspecialchars($item["SmallImage"]["URL"]), "\">";
		echo "<img src=\"", htmlspecialchars($item["MediumImage"]["URL"]), "\">";
		echo "<a href=\"", htmlspecialchars($item["LargeImage"]["URL"]), "\" target=\"_brank\">大きな画像をみる</a><br>";
		// 商品詳細
		foreach ($item["ItemAttributes"] as $title => $attr) {
			if (!is_array($attr)) {
				echo htmlspecialchars($title), ":", htmlspecialchars($attr), "<br>";
			} else {
				foreach ($attr as $title2 => $attr2) {
					if (!is_array($attr2)) {
						echo htmlspecialchars($title2), ":", htmlspecialchars($attr2), "/";
					} else {
						foreach ($attr as $title3 => $attr3) {
							if (!is_array($attr2)) {
								echo htmlspecialchars($title3), ":", htmlspecialchars($attr3), "/";
							} else {
								print_r($attr3);
							}
						}
					}
				}
			}
		}
		
		echo "<hr>";
	}
	
} else {
	echo "一時的に検索できません。再読み込みしてください。";
}

//dubug
//echo "<pre>";
//var_dump($response);
//echo "</pre>";
?>
