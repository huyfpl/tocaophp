<?php
// get_time.php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ theo múi giờ của Việt Nam
echo json_encode(array('time' => date('Y-m-d H:i:s')));
?>
