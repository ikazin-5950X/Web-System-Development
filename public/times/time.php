<?php
  $date = new \DateTime("now", new \DateTimeZone('Asia/Tokyo'));

  $svg_box_size = 1000;
  $circle_r = 450;
  // $hour_x_def = ;
  // $hour_y_def = ;
  // $min_x_def = ;
  // $min_y_def = ;


  $hours = intval($date->format('h'));
  $max_hours = 12;
  $hours_rad = ($hours / $max_hours) * 2 * pi();
  $hours_x = ($svg_box_size / 2) + round(sin($hours_rad) * ($circle_r * 0.65));
  $hours_y = ($svg_box_size / 2) - round(cos($hours_rad) * ($circle_r * 0.65));

  $minutes = intval($date->format('i'));
  $max_minutes = 60;
  $minutes_rad = ($minutes / $max_minutes) * 2 * pi();
  $minutes_x = ($svg_box_size / 2) + round(sin($minutes_rad) * ($circle_r * 0.85));
  $minutes_y = ($svg_box_size / 2) - round(cos($minutes_rad) * ($circle_r * 0.85));

  $sec = intval($date->format('s'));
  $max_sec = 60;
  $sec_rad = ($sec / $max_sec) * 2 * pi();
  $sec_x = ($svg_box_size / 2) + round(sin($sec_rad) * ($circle_r * 0.95));
  $sec_y = ($svg_box_size / 2) - round(cos($sec_rad) * ($circle_r * 0.95));

  $data = [
    'time' => $date->format('Y-m-d H:i:s'),
    'seconds_x' => $sec_x,
    'seconds_y' => $sec_y,
    'minutes_x' => $minutes_x,
    'minutes_y' => $minutes_y,
    'hours_x' => $hours_x,
    'hours_y' => $hours_y,
    ];

    header('Content-Type: application/json');
    echo json_encode($data);
?>