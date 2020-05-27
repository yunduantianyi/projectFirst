<?php
$ac = $_GET['ac'];
if( $ac == 'add' ){
	$id = intval($_GET['id']);
	if( $id ){
		$ad = $db->get_one("SELECT * FROM {$DT_PRE}ad WHERE id=$id");
		// dump($ad);
	}
}else if( $ac == 'ads' ){
	$id = intval( $_GET['id'] );
	$res = $db->query("SELECT * FROM {$DT_PRE}ads WHERE aid=$id ORDER BY  pid DESC");
	while( $r = $db->fetch_array($res) ){
		$r['start'] = timetodate($r['start'],3);
		$r['end'] = timetodate($r['end'],3);
		$r['addtime'] = timetodate($r['addtime'],3);
		$ads[] = $r;
	}
	// dump($ads);
}else if( $ac == 'eads' ){
	$pid = intval($_GET['pid']);
	$a = $db->get_one("SELECT * FROM {$DT_PRE}ads WHERE pid=$pid");
}

