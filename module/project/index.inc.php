<?php
if( $pid ){
	$project = $db->get_one("SELECT * FROM {$DT_PRE}project WHERE pid='".$pid."' ");
	// dump($project);
	$keywords = explode("/", $project['keywords']);
	$p_tmp = $db->query("SELECT pid,title FROM {$DT_PRE}project");
	while($r = $db->fetch_array($p_tmp)){
		$p_list[] = $r;
	}
}else{
	$p_tmp = $db->query("SELECT * FROM {$DT_PRE}project");
	while($r = $db->fetch_array($p_tmp)){
		if( $r['keywords'] ){
			$r['k_arr'] = explode('/', $r['keywords']);
		}
		$project_list[] = $r;
	}

	// dump($project_list);
}

