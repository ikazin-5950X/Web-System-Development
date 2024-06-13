<?php
echo '<img src="images/icon.jpg" alt="sample" style="width:20%"><br>';

//判別
if (exif_imagetype('images/icon.jpg') != IMAGETYPE_GIF) {
    echo "The picture is not a gif<br>";
} else {
    echo "The picture is a gif<br>";
}

//データ情報出力
//読み込み
$fp = fopen('images/icon.jpg', 'rb');
//エラー処理
if (!$fp) {
    echo 'Error: Unable to open image for reading';
    exit;
}

// ヘッダ読込
$headers = exif_read_data($fp);
//エラー処理
if (!$headers) {
    echo 'Error: Unable to read exif headers';
    exit;
}

// ヘッダに貼り付け
echo 'EXIF Headers:' . PHP_EOL;
//データ表示
foreach ($headers['COMPUTED'] as $header => $value) {
    printf(' %s => %s%s<br>', $header, $value, PHP_EOL);
}