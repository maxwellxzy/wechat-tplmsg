<?php
ini_set("display_errors","On"); //打开debug
include "OrderPush.class.php";
/**************增量更新，不可用于初始化，尽量每天跑一次，如果粉丝多，每天跑10次************/
$start = mktime();
$ch = new OrderPush();
//$ch->getUserList();
$num = $ch->updateUserList();
$stop = mktime();
echo '耗时：'.($stop-$start).'秒，共更新了'.$num["timesup"].'个信息，现在更新到id：'.$num["count"].'<br><a href="">点击继续更新！</a>';
?>