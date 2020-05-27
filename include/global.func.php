<?php
/*
	Author: [ChinaWin]
	Development Time: 2017-03-13
*/
defined('IN_WIN') or exit('Access Denied');
function daddslashes($string) {
	return is_array($string) ? array_map('daddslashes', $string) : addslashes($string);
}
function dstripslashes($string) {
	return is_array($string) ? array_map('dstripslashes', $string) : stripslashes($string);
}
function dtrim($string) {
	return str_replace(array(chr(10), chr(13), "\t", ' '), array('', '', '', ''), $string);
}
function dheader($url) {
	global $DT;	
	if(!defined('DT_ADMIN') && $DT['defend_reload']) sleep($DT['defend_reload']);
	exit(header('location:'.$url));
}
function dalert($dmessage = errmsg, $dforward = '', $extend = '') {
	global $CFG, $DT;
	exit(include template('alert', 'message'));
}
function dsubstr($string, $length, $suffix = '', $start = 0) {
	if($start) {
		$tmp = dsubstr($string, $start);
		$string = substr($string, strlen($tmp));
	}
	$strlen = strlen($string);
	if($strlen <= $length) return $string;
	$string = str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string);
	$length = $length - strlen($suffix);
	$str = '';
	if(DT_CHARSET == 'UTF-8') {
		$n = $tn = $noc = 0;
		while($n < $strlen)	{
			$t = ord($string{$n});
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) break;
		}
		if($noc > $length) $n -= $tn;
		$str = substr($string, 0, $n);
	} else {
		for($i = 0; $i < $length; $i++) {
			$str .= ord($string{$i}) > 127 ? $string{$i}.$string{++$i} : $string{$i};
		}
	}
	$str = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $str);
	return $str == $string ? $str : $str.$suffix;
}
function is_robot() {
	return preg_match("/(spider|bot|crawl|slurp|lycos|robozilla)/i", $_SERVER['HTTP_USER_AGENT']);
}
function is_ip($ip) {
	return preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $ip);
}
function is_mobile($mobile) {
	return preg_match("/^1[3|4|5|7|8]{1}[0-9]{9}$/", $mobile);
}
function is_md5($password) {
	return preg_match("/^[a-f0-9]{32}$/", $password);
}
function is_touch() {
	$ck = get_cookie('mobile');
	if($ck == 'pc') return 0;
	if($ck == 'touch' || $ck == 'screen') return 1;
	return preg_match("/(iPhone|iPad|iPod|Android)/i", $_SERVER['HTTP_USER_AGENT']) ? 1 : 0;
}
function dhttp($status, $exit = 1) {
	switch($status) {
		case '301': @header("HTTP/1.1 301 Moved Permanently"); break;
		case '403': @header("HTTP/1.1 403 Forbidden"); break;
		case '404': @header("HTTP/1.1 404 Not Found"); break;
		case '503': @header("HTTP/1.1 503 Service Unavailable"); break;
	}
	if($exit) exit;
}
function check_name($username) {
	if(strpos($username, '__') !== false || strpos($username, '--') !== false) return false; 
	return preg_match("/^[a-zA-Z0-9]{1}[a-zA-Z0-9_\-]{0,}[a-zA-Z0-9]{1}$/", $username);
}
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}
function template($template = 'index', $dir = '') {
	global $CFG;
	// check_name($template) or exit('BAD TPL NAME');
	// if($dir) check_name($dir) or exit('BAD TPL DIR');
	$to = DT_CACHE.'/tpl/'.$CFG['template'].'/'.($dir ? $dir.'/' : '').$template.'.php';
	$isfileto = is_file($to);
	if($CFG['template_refresh'] || !$isfileto) {
		if($dir) $dir = $dir.'/';
		$from = DT_ROOT.'/template/'.$CFG['template'].'/'.$dir.$template.'.htm';
		if($CFG['template'] != 'default' && !is_file($from)) {
			$from = DT_ROOT.'/template/default/'.$dir.$template.'.htm';
		}
		if(!$isfileto || filemtime($from) > filemtime($to) || (filesize($to) == 0 && filesize($from) > 0)) {
			require_once DT_ROOT.'/include/template.func.php';
			template_compile($from, $to);
		}
	}
	return $to;
}
function ob_template($template, $dir = '', $data = array()) {
	extract($GLOBALS, EXTR_SKIP);
	extract($data, EXTR_SKIP);
	ob_start();
	include template($template, $dir);
	$contents = ob_get_contents();
	ob_clean();
	return $contents;
}
function message($dmessage = errmsg, $dforward = 'goback', $dtime = 1) {
	global $CFG, $DT;
	if(!$dmessage && $dforward && $dforward != 'goback') dheader($dforward);
	exit(include template('message', 'message'));
}
function login() {
	global $_userid, $MODULE, $DT_URL, $DT;
	// $_userid or dheader($MODULE[2]['linkurl'].$DT['file_login'].'?forward='.rawurlencode($DT_URL));
	$_userid or dheader($MODULE[1]['linkurl']);
}
function random($length, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz') {
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++)	{
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}
function set_cookie($var, $value = '', $time = 0) {
	global $CFG, $DT_TIME;
	$time = $time > 0 ? $time : (empty($value) ? $DT_TIME - 3600 : 0);
	$port = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
	$var = $CFG['cookie_pre'].$var;
	return setcookie($var, $value, $time, $CFG['cookie_path'], $CFG['cookie_domain'], $port);
}
function get_cookie($var) {
	global $CFG;
	$var = $CFG['cookie_pre'].$var;
	return isset($_COOKIE[$var]) ? $_COOKIE[$var] : '';
}
function get_env($type) {
	switch($type) {
		case 'ip':
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				if(is_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
				$ip = trim(end(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
				if(is_ip($ip)) return $ip;
			}
			if(isset($_SERVER['REMOTE_ADDR']) && is_ip($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
			if(isset($_SERVER['HTTP_CLIENT_IP']) && is_ip($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
			return '0.0.0.0';
		break;
		case 'self':
			return isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['ORIG_PATH_INFO']);
		break;
		case 'referer':
			return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		break;
		case 'domain':
			return $_SERVER['SERVER_NAME'];
		break;
		case 'scheme':
			return $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		break;
		case 'port':
			return ($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443') ? '' : ':'.$_SERVER['SERVER_PORT'];
		break;
		case 'host':
			return preg_match("/^[a-z0-9_\-\.]{4,}$/i", $_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		break;
		case 'url':
			if(isset($_SERVER['HTTP_X_REWRITE_URL']) && $_SERVER['HTTP_X_REWRITE_URL']) {
				$uri = $_SERVER['HTTP_X_REWRITE_URL'];
			} else if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']) {
				$uri = $_SERVER['REQUEST_URI'];
			} else {
				$uri = $_SERVER['PHP_SELF'];
				if(isset($_SERVER['argv'])) {
					if(isset($_SERVER['argv'][0])) $uri .= '?'.$_SERVER['argv'][0];
				} else {
					$uri .= '?'.$_SERVER['QUERY_STRING'];
				}
			}
			$uri = dhtmlspecialchars($uri);
			return get_env('scheme').$_SERVER['HTTP_HOST'].(strpos($_SERVER['HTTP_HOST'], ':') === false ? get_env('port') : '').$uri;
		break;
		case 'mobile':
			$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
			$ck = get_cookie('mobile');
			$os = $browser = '';
			if(strpos($ua, 'android') !== false) {
				$os = 'android';
				if($ck == 'app') {
					$browser = 'app';
				} else if($ck == 'b2b') {
					$browser = 'b2b';
				} else {
					if(strpos($ua, 'micromessenger/') !== false) {
						$browser = 'weixin';
					} else if(strpos($ua, 'qq/') !== false) {
						$browser = 'qq';
					}
				}
			} else if(strpos($ua, 'iphone') !== false || strpos($ua, 'ipod') !== false) {
				$os = 'ios';
				if($ck == 'app') {
					$browser = 'app';
				} else if($ck == 'b2b') {
					$browser = 'b2b';
				} else if($ck == 'screen') {
					$browser = 'screen';
				} else {
					if(strpos($ua, 'micromessenger/') !== false) {
						$browser = 'weixin';
					} else if(strpos($ua, 'qq/') !== false) {
						$browser = 'qq';
					} else if(strpos($ua, 'safari') !== false) {
						$browser = 'safari';
					}
				}
			} else if(strpos($ua, 'adr') !== false && strpos($ua, 'ucbrowser') !== false) {
				$os = 'android';
				$browser = 'uc';
			}
			return array('os' => $os, 'browser' => $browser);
		break;
	}
}
function timetodate($time = 0, $type = 6) {
	if(!$time) $time = $GLOBALS['DT_TIME'];
	// if(!$time) return '';
	$types = array('Y-m-d', 'Y', 'm-d', 'Y-m-d', 'm-d H:i', 'Y-m-d H:i', 'Y-m-d H:i:s');
	if(isset($types[$type])) $type = $types[$type];
	$date = '';
	if($time > 2147212800) {		
		if(class_exists('DateTime')) {
			$D = new DateTime('@'.($time - 3600 * intval(str_replace('Etc/GMT', '', $GLOBALS['CFG']['timezone']))));
			$date = $D->format($type);
		}
	}
	return $date ? $date : date($type, $time);
}
function datetotime($date) {
	$time = strtotime($date);
	if($time === false) {
		if(class_exists('DateTime')) {
			$D = new DateTime($date);
			$time = $D->format('U');
		}
	}
	return $time;
}
function log_write($message, $type = 'php', $force = 0) {
	global $DT_IP, $_username, $log_id;
	if(!SE_DEBUG && !$force) return;
	if($log_id) {
		$log_id++;
	} else {
		$log_id = 1;
	}
	$DT_IP or $DT_IP = get_env('ip');
	$user = $_username ? $_username : 'guest';
	check_name($type) or $type = 'php';
	$log = "<?php exit;?>\n<$type>\n";
	$log .= "\t<time>".timetodate()."</time>\n";
	$log .= "\t<ip>".$DT_IP."</ip>\n";
	$log .= "\t<user>".$user."</user>\n";
	$log .= "\t<php>".$_SERVER['SCRIPT_NAME']."</php>\n";
	$log .= "\t<querystring>".str_replace('&', '&amp;', $_SERVER['QUERY_STRING'])."</querystring>\n";
	$log .= "\t<message>".(is_array($message) ? var_export($message, true) : $message)."</message>\n";
	$log .= "</$type>";
	file_put(DT_ROOT.'/file/log/'.timetodate(0, 'Ym/d').'/'.$type.'-'.timetodate(0, 'Y.m.d H.i.s').'-'.$log_id.'.php', $log);
}
function strip_nr($string, $js = false) {
	$string =  str_replace(array(chr(13), chr(10), "\n", "\r", "\t", '  '),array('', '', '', '', '', ''), $string);
	if($js) $string = str_replace("'", "\'", $string);
	return $string;
}
function cache_read($file, $dir = '', $mode = '') {
	$file = $dir ? DT_CACHE.'/'.$dir.'/'.$file : DT_CACHE.'/'.$file;
	if(!is_file($file)) return $mode ? '' : array();
	return $mode ? file_get($file) : include $file;
}

function cache_write($file, $string, $dir = '') {
	if(is_array($string)) $string = "<?php defined('IN_WIN') or exit('Access Denied'); return ".strip_nr(var_export($string, true))."; ?>";
	$file = $dir ? DT_CACHE.'/'.$dir.'/'.$file : DT_CACHE.'/'.$file;
	$strlen = file_put($file, $string);
	return $strlen;
}
function cache_delete($file, $dir = '') {
	$file = $dir ? DT_CACHE.'/'.$dir.'/'.$file : DT_CACHE.'/'.$file;
	return file_del($file);
}
function cache_clear($str, $type = '', $dir = '') {
	$dir = $dir ? DT_CACHE.'/'.$dir.'/' : DT_CACHE.'/';
	$files = glob($dir.'*');
	if(is_array($files)) {
		if($type == 'dir') {
			foreach($files as $file) {
				if(is_dir($file)) {dir_delete($file);} else {if(file_ext($file) == $str) file_del($file);}
			}
		} else {
			foreach($files as $file) {
				if(!is_dir($file) && strpos(basename($file), $str) !== false) file_del($file);
			}
		}
	}
}
function encrypt($txt, $key = '', $expiry = 0) {
	strlen($key) > 5 or $key = DT_KEY;
	$str = $txt.substr($key, 0, 3);
	return str_replace(array('+', '/', '0x', '0X'), array('-P-', '-S-', '-Z-', '-X-'), mycrypt($str, $key, 'ENCODE', $expiry));
}
function decrypt($txt, $key = '') {
	strlen($key) > 5 or $key = DT_KEY;
	$str = mycrypt(str_replace(array('-P-', '-S-', '-Z-', '-X-'), array('+', '/', '0x', '0X'), $txt), $key, 'DECODE');
	return substr($str, -3) == substr($key, 0, 3) ? substr($str, 0, -3) : '';
}
function mycrypt($string, $key, $operation = 'DECODE', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + $GLOBALS['DT_TIME'] : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - $GLOBALS['DT_TIME'] > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}
function check_referer() {
	global $DT_REF, $CFG, $DT;
	if($DT['check_referer']) {
		if(!$DT_REF) return false;
		$R = parse_url($DT_REF);
		if($CFG['cookie_domain'] && strpos($R['host'], $CFG['cookie_domain']) !== false) return true;
		if($CFG['com_domain'] && strpos($R['host'], $CFG['com_domain']) !== false) return true;
		if($DT['safe_domain']) {
			$tmp = explode('|', $DT['safe_domain']);
			foreach($tmp as $v) {
				if(strpos($R['host'], $v) !== false) return true;
			}
		}		
		$U = parse_url(DT_PATH);
		if(strpos($R['host'], str_replace('www.', '.', $U['host'])) !== false) return true;
		return false;
	}
	return true;
}
function convert($str, $from = 'utf-8', $to = 'gb2312') {
	if(!$str) return '';
	$from = strtolower($from);
	$to = strtolower($to);
	if($from == $to) return $str;
	$from = str_replace('gbk', 'gb2312', $from);
	$to = str_replace('gbk', 'gb2312', $to);
	$from = str_replace('utf8', 'utf-8', $from);
	$to = str_replace('utf8', 'utf-8', $to);
	if($from == $to) return $str;
	$tmp = array();
	if(function_exists('mb_convert_encoding')) {
		if(is_array($str)) {
			foreach($str as $key => $val) {
				$tmp[$key] = mb_convert_encoding($val, $to, $from);
			}
			return $tmp;
		} else {
			return mb_convert_encoding($str, $to, $from);
		}
	} else if(function_exists('iconv')) {
		if(is_array($str)) {
			foreach($str as $key => $val) {
				$tmp[$key] = iconv($from, $to."//IGNORE", $val);
			}
			return $tmp;
		} else {
			return iconv($from, $to."//IGNORE", $str);
		}
	} else {
		require_once DT_ROOT.'/include/convert.func.php';
		return dconvert($str, $to, $from);
	}
}
function rewrite($url, $encode = 0) {
	if(!RE_WRITE) return $url;
	if(strpos($url, '.php?') === false || strpos($url, '=') === false) return $url;
	$url = str_replace(array('+', '-'), array('%20', '%20'), $url);
	$url = str_replace(array('.php?', '&', '='), array('-htm-', '-', '-'), $url).'.html';
	return $url;
}
function load($file) {
	$ext = file_ext($file);
	if($ext == 'css') {
		echo '<link rel="stylesheet" type="text/css" href="'.DT_SKIN.$file.'" />';
	} else if($ext == 'js') {
		echo '<script type="text/javascript" src="'.DT_STATIC.'file/script/'.$file.'"></script>';
	} else if($ext == 'htm') {
		$file = str_replace('ad_m', 'ad_t6_m', $file);
		if(is_file(DT_CACHE.'/htm/'.$file)) {
			$content = file_get(DT_CACHE.'/htm/'.$file);
			if(substr($content, 0, 4) == '<!--') $content = substr($content, 17);
			echo $content;
		} else {
			echo '';
		}
	} else if($ext == 'lang') {
		$file = str_replace('.lang', '.inc.php', $file);
		return DT_ROOT.'/lang/'.DT_LANG.'/'.$file;
	} else if($ext == 'inc' || $ext == 'func' || $ext == 'class') {
		return DT_ROOT.'/include/'.$file.'.php';
	}
}

function captcha($captcha, $enable = 1, $return = false) {
	global $DT_IP, $DT, $session;
	if($enable) {
		if($DT['captcha_cn']) {
			if(strlen($captcha) < 4) {
				$msg = lang('include->captcha_missed');
				return $return ? $msg : message($msg);
			}
		} else {
			if(!preg_match("/^[0-9a-z]{4,}$/i", $captcha)) {
				$msg = lang('include->captcha_missed');
				return $return ? $msg : message($msg);
			}
		}
		if(!is_object($session)) $session = new dsession();
		if(!isset($_SESSION['captchastr'])) {
			$msg = lang('include->captcha_expired');
			return $return ? $msg : message($msg);
		}
		if($_SESSION['captchastr'] != md5(md5(strtoupper($captcha).DT_KEY.$DT_IP))) {
			$msg = lang('include->captcha_error');
			return $return ? $msg : message($msg);
		}
		unset($_SESSION['captchastr']);
	} else {
		return '';
	}
}

function question($answer, $enable = 1, $return = false) {
	global $DT_IP, $session;
	if($enable) {
		if(!$answer) {
			$msg = lang('include->answer_missed');
			return $return ? $msg : message($msg);
		}
		$answer = stripslashes($answer);
		if(!is_object($session)) $session = new dsession();
		if(!isset($_SESSION['answerstr'])) {
			$msg = lang('include->question_expired');
			return $return ? $msg : message($msg);
		}
		if($_SESSION['answerstr'] != md5(md5($answer.DT_KEY.$DT_IP))) {
			$msg = lang('include->answer_error');
			return $return ? $msg : message($msg);
		}
		unset($_SESSION['answerstr']);
	} else {
		return '';
	}
}

function userinfo($username, $cache = 1) {
	global $db, $dc, $CFG;
	if(!check_name($username)) return array();
	$user = array();
	if($cache && $CFG['db_expires']) {
		$user = $dc->get('user-'.$username);
		if($user) return $user;
	}
	$user = $db->get_one("SELECT * FROM {$db->pre}member WHERE username='$username'");
	if($cache && $CFG['db_expires'] && $user) $dc->set('user-'.$username, $user, $CFG['db_expires']);
	return $user;
}
function uidinfo($userid, $cache = 1) {
	global $db, $dc, $CFG;
	$user = array();
	// if($cache && $CFG['db_expires']) {
	// 	$user = $dc->get('user-'.get_user($_userid,'userid','username'));
	// 	if($user) return $user;
	// }
    $sql = "SELECT * FROM {$db->pre}member WHERE userid='$userid' limit 1";
 	$user = $db->get_one($sql);
	// cache_write(md5(md5($user['userid'])).'.php',$user,'user');
	if($cache && $CFG['db_expires'] && $user) $dc->set('user-'.$user['username'], $user, $CFG['db_expires']);//统一使用user-用户名形式缓存
	return $user;
}

function linkurl($linkurl) {
	return strpos($linkurl, '://') === false ? DT_PATH.$linkurl : $linkurl;
}


function tohtml($htmlfile, $module = '', $parameter = '') {
	defined('TOHTML') or define('TOHTML', true);
    extract($GLOBALS, EXTR_SKIP);
	if($parameter) parse_str($parameter);
    include $module ? DT_ROOT.'/module/'.$module.'/'.$htmlfile.'.htm.php' : DT_ROOT.'/include/'.$htmlfile.'.htm.php';
}


function isMobile()  //判断是否为手机端   true  false
{
	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
	{
		return true;
	}
	// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	if (isset ($_SERVER['HTTP_VIA']))
	{
		// 找不到为flase,否则为true
		return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
	}
	// 脑残法，判断手机发送的客户端标志,兼容性有待提高
	if (isset ($_SERVER['HTTP_USER_AGENT']))
	{
		$clientkeywords = array ('nokia',
			'sony',
			'ericsson',
			'mot',
			'samsung',
			'htc',
			'sgh',
			'lg',
			'sharp',
			'sie-',
			'philips',
			'panasonic',
			'alcatel',
			'lenovo',
			'iphone',
			'ipod',
			'blackberry',
			'meizu',
			'android',
			'netfront',
			'symbian',
			'ucweb',
			'windowsce',
			'palm',
			'operamini',
			'operamobi',
			'openwave',
			'nexusone',
			'cldc',
			'midp',
			'wap',
			'mobile'
		);
		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
		{
			return true;
		}
	}
	// 协议法，因为有可能不准确，放到最后判断
	if (isset ($_SERVER['HTTP_ACCEPT']))
	{
		// 如果只支持wml并且不支持html那一定是移动设备
		// 如果支持wml和html但是wml在html之前则是移动设备
		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
		{
			return true;
		}
	}
	return false;
}

function get_user($userid, $param){   //获取wp_user信息   如果是userid name  没找到 则返回userid(中文未激活)
	global $db;
	if( $param ){
		$sql = "SELECT `$param` FROM {$db->pre}user WHERE userid='".$userid."' ";
	 	$user = $db->get_one($sql);
	 	if( $user ){
	 		return $user[$param];
	 	}else{
	 		if( $param == 'name' ){
	 			return $userid;
	 		}else{
	 			return false;
	 		}
	 		
	 	}
	 }else{
	 	$sql = "SELECT * FROM {$db->pre}user WHERE userid='".$userid."' ";
	 	$user = $db->get_one($sql);
	 	if( $user ){
	 		return $user;
	 	}else{
	 		return false;
	 	}
	 }
	
}
function get_user_name($name){   //操作用的
	global $db;
	$name = trim($name);
	$sql = "SELECT `userid` FROM {$db->pre}user WHERE name='".$name."' ";
	$user = $db->get_one($sql);   //有就返回id  木有返回$name
 	if( $user ){
 		return $user['userid'];
 	}else{
 		return $name;
 	}

}
function get_user_status($status){
	$s = trim($status);
	$return = array(
		'可领用' => '1',
		'使用中' => '3',
		'维修中' => '8',
		'已报废' => '9'
		);
	return $return[$s];
}
function get_user_goods($title){
	global $db;
	$title = trim($title);
	$sql = "SELECT `id` FROM {$db->pre}goods WHERE `title` ='".$title."'  ";
 	$goods = $db->get_one($sql);
 	if( $goods ){
 		return $goods['id'];
 	}else{
 		return false;
 	}
}

function get_wp_class($id, $param){  //获取class  有问题呢  get_class
	global $db;
	$sql = "SELECT `$param` FROM {$db->pre}class WHERE id=$id ";
 	$class = $db->get_one($sql);
 	if( $class ){
 		return $class[$param];
 	}else{
 		return false;
 	}
}

function get_goods($id, $param){
	global $db;
	$sql = "SELECT `$param` FROM {$db->pre}goods WHERE id=$id ";
 	$goods = $db->get_one($sql);
 	if( $goods ){
 		return $goods[$param];
 	}else{
 		return false;
 	}
}
function get_goods_belong($bid, $param){
	global $db;
	$sql = "SELECT `$param` FROM {$db->pre}goods_belong WHERE id=$bid ";
 	$goods = $db->get_one($sql);
 	if( $goods ){
 		return $goods[$param];
 	}else{
 		return false;
 	}
}
function get_goods_arr($userid){  //获取当前用户的管理区间
	global $db;
	// $userid = '090617435224803807';
	
	

	if( $userid == '090617435224803807' ){
		$r = $db->query("SELECT id FROM {$db->pre}class");
	}else{
		$r = $db->query("SELECT id FROM {$db->pre}class WHERE `manager` = '".$userid."' ");
	}


	if( $r->num_rows ){  //存在数量
		while( $s = $db->fetch_array($r) ){
			$c .= $s['id'].",";
		
		}
		$c = trim($c,",");  //class集合
	
		$r = $db->query("SELECT id FROM {$db->pre}goods WHERE `cid` in($c) ");
		while( $g = $db->fetch_array($r) ){
			$res .= $g['id'].",";
		
		}

		$res = trim($res,",");
		return $res;
	}else{
		return false;
	}
}

/*type
	title   ： goods标题  ，根据内部外部的选择 切换成gid
	status  ： 可领用，申请中，已转让 ，使用中 。。。。。


  val
  	传入的数据


  diff  用于区分 内外部
 */
function get_select($type, $val){
	global $db;
	if( $type == 'title' ){
		
		$tmp1 = $db->query("SELECT `title`,`is_con` FROM {$db->pre}goods WHERE `fromtype`=1 ");  //内部
		$tmp2 = $db->query("SELECT `title`,`is_con` FROM {$db->pre}goods WHERE `fromtype`=2 ");  //外部
		while( $t1 = $db->fetch_array($tmp1) ){
			$return1[] = $t1;
		}
		while( $t2 = $db->fetch_array($tmp2) ){
			$return2[] = $t2;
		}
		$select = '<select name="'.$type.'" style="padding-left:10px" class="form-control" lay-verify="required" lay-search="">';

		if( $return1 ){
			$select .= '<option value="" style="color:#f60;font-size:16px;">请选择物品分类</option>';
			foreach($return1 as $k=>$v) {
				if( trim($v['title']) == trim($val) ){
					$select .= '<option value="'.$v['title'].'" selected/ >'.$v['title'].'</option>';
				}else{
					$select .= '<option value="'.$v['title'].'">'.$v['title'].'</option>';
				}
				
			}
		}
		if( $return2 ){
			$select .= '<option value="" disabled/ style="color:#f60;font-size:16px;">外部物品列表</option>';
			foreach($return2 as $k=>$v) {
				if( trim($v['title']) == trim($val) ){
					$select .= '<option value="'.$v['title'].'" selected/ >'.$v['title'].'</option>';
				}else{
					$select .= '<option value="'.$v['title'].'">'.$v['title'].'</option>';
				}
				
			}
		}
		
		$select .= '</select>';
	}else if( $type == 'status' ){
		$return = array(
			'可领用' => '可领用',
			'使用中' => '使用中',
			'维修中' => '维修中',
			'已报废' => '已报废'
			);

		$select = '<select name="'.$type.'" class="form-control">';
		foreach($return as $k=>$v) {
			if( trim($v) == trim($val) ){
				$select .= '<option value="'.$k.'" selected/>'.$v.'</option>';
			}else{
				$select .= '<option value="'.$k.'">'.$v.'</option>';
			}	
		}
		$select .= '</select>';
	}
	return $select;
}

function getNickName($name){
	$r = substr($name,-6);
	return $r;
}

function getDays($day){  //获取第一天最后一天    日期格式吧

	$sb = strtotime($day);
	$week = intval(date('W', $sb));	
	$last 	= date("Y-m-d 23:59:59", strtotime("$day Sunday")); 
	$first  = date('Y-m-d 00:00:00', strtotime("$last -6 days")); 
	$arr["W$week"]['first'] = $first;
	$arr["W$week"]['last'] = $last;
    return $arr; 
}
function getNeedBook($gid){
	$tmp1 = $db->query("SELECT `title`,`is_con` FROM {$db->pre}goods WHERE `fromtype`=1 ");  //内部

	
	// $r = $db->get_one("SELECT * FROM `wp_need_book` WHERE `gid`='".$gid."' AND `status`=1 ORDER BY `time` ASC ");

	// dump($r);
	// dump("SELECT * FROM `wp_need_book` WHERE `gid`='".$gid."' AND `status`=1 ORDER BY `time` ASC ");

	return $gid;
}


?>