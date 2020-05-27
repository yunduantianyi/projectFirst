<?php
/*
	Author: [ChinaWin]
	Development Time: 2017-03-20
*/
require 'common.inc.php';

$title = '物品领用';



$from = 'index';
// $a = mysqli_connect('122.195.59.86','root','');
// dump($a);
// $ss = $db->get_one("SELECT * FROM {$DT_PRE}bug WHERE id=2441 ");
// dump($ss);
$use = isMobile() ? 'pe' : 'pc';


include template('index', 'index');
?>