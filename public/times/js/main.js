function updateClock() {
    $.ajax({
        url: 'time.php',
        method: 'GET',
        success: function(data) {
            $('#currentTime').text(data.time);
            $('#second_hand').attr('x2', data.seconds_x).attr('y2', data.seconds_y);
            $('#long_hand').attr('x2', data.minutes_x).attr('y2', data.minutes_y);
            $('#hour_hand').attr('x2', data.hours_x).attr('y2', data.hours_y);
        }
    });
}

$(document).ready(function() {
    updateClock(); // 初回更新
    setInterval(updateClock, 1000); // 毎秒更新
});