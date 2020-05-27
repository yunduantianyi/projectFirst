<?php
$id = intval($_GET['id']);
$c = $db->get_one("SELECT * FROM {$DT_PRE}company WHERE id=".$id);
// dump($c);


