<?php
// APCuキーの設定
$counterKey = 'access_counter';

// カウンターを初期化
if (!apcu_exists($counterKey)) {
    apcu_add($counterKey, 0);
}

// カウンターをインクリメント
$counter = apcu_inc($counterKey);

// 現在のカウンター値を表示
echo "このページには " . $counter . " 回アクセスされています。";
?>
