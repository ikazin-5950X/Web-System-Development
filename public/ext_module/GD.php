<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $colorCode = $_POST['color'];

    // カラーコードのバリデーション
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $colorCode)) {
        echo "無効なカラーコードです。例: #ff5733";
        exit;
    }

    // カラーコードからRGB値を抽出
    $red = hexdec(substr($colorCode, 1, 2));
    $green = hexdec(substr($colorCode, 3, 2));
    $blue = hexdec(substr($colorCode, 5, 2));

    // 画像の幅と高さ
    $width = 200;
    $height = 200;

    // 画像を生成
    $image = imagecreatetruecolor($width, $height);

    // 色を作成
    $color = imagecolorallocate($image, $red, $green, $blue);

    // 画像を塗りつぶし
    imagefill($image, 0, 0, $color);

    // ブラウザに画像を出力
    header("Content-Type: image/png");
    imagepng($image);

    // 画像リソースを破棄
    imagedestroy($image);
} else {
    echo "フォームからカラーコードを入力してください。";
}
