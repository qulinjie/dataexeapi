<?php
class GF extends Base {
	public static function is_int_or_intstr($var){
		if(is_int($var)) return true;
		if(is_string($var)){
			if(preg_match("/^[0-9][0-9]*$/", $var)){
				return true;
			}else {
				return false;
			}
		}
		return false;
	}
	
	public static function smart_show_time($timestamp){
		//获取当前时间戳
		$current_time = time();
		//获取当天0点时间戳
		$today_zero = strtotime('today');
		//当日时间差
		$today_diff = $current_time - $today_zero;
		//时间差
		$time_diff = $current_time - $timestamp;
		if($time_diff < 0)
		{
			return 'invalid time';
		}
	
		if($time_diff > $today_diff){
			if($time_diff - $today_diff < 24 * 60 * 60){
				//昨天
				return '昨天 ' . date('H:i', $timestamp);
			}else {
				return date('Y-m-d H:i', $timestamp);
			}
		}
		if($time_diff > 3600){
			//大于一个小时
			return '今天 ' . date('H:i', $timestamp);
		}
		if($time_diff > 60) {
			$min =  floor($time_diff / 60);
			return $min . '分钟前';
		}
		return $time_diff . '秒前';
	}
	
	public static function sendEmail($fromemail, $toemail, $subject , $message, $fromName = '', $toName = '', $check = 1, $additional = '', $charset = 'utf-8') {
		$conf = Controller::getConfig('conf');
		$host = $conf['smtp_host'];
		$port = $conf['stmp_port'];
		$username = $conf['smtp_user'];
		$password = $conf['smtp_password'];
		
		
		$CRLF = "\r\n";
		$fromName = trim ( $fromName ) == '' ? $fromemail : $fromName;
		$toName = trim ( $toName ) == '' ? $toemail : $toName;
		$send_from = "=?$charset?B?" . base64_encode ( $fromName ) . "?= <$fromemail>";
		$send_to = "=?$charset?B?" . base64_encode ( $toName ) . "?= <$toemail>";
		$send_subject = "=?$charset?B?" . base64_encode ( str_replace ( array (
				"\r",
				"\n" 
		), array (
				'',
				' ' 
		), $subject ) ) . '?=';
		$send_message = chunk_split ( base64_encode ( str_replace ( "\r\n.", " \r\n..", str_replace ( "\n", "\r\n", str_replace ( "\r", "\n", str_replace ( "\r\n", "\n", str_replace ( "\n\r", "\r", $message ) ) ) ) ) ) );
		$additional = "To: {$send_to}{$CRLF}From: {$send_from}{$CRLF}MIME-Version: 1.0{$CRLF}Content-type: text/html; charset=$charset{$CRLF}{$additional}Content-Transfer-Encoding: base64{$CRLF}";
		$fp = fsockopen ( $host, $port, $errno, $errstr );
		if (! $fp) {
			return 'SCE'; // smtp connect error
		}
		if (strncmp ( fgets ( $fp, 512 ), '220', 3 ) != 0) {
			return 'SCE'; // smtp connect error
		}
		fwrite ( $fp, "EHLO $fromName{$CRLF}" );
		if ($check) {
			while ( $rt = strtolower ( fgets ( $fp, 512 ) ) ) {
				if (strpos ( $rt, "-" ) !== 3 || empty ( $rt )) {
					break;
				} elseif (strpos ( $rt, "2" ) !== 0) {
					return "AE"; // auth error
				}
			}
			fwrite ( $fp, "AUTH LOGIN{$CRLF}" );
			if (strncmp ( fgets ( $fp, 512 ), '334', 3 ) != 0) {
				return "ALE"; // auth login error
			}
			fwrite ( $fp, base64_encode ( $username ) . $CRLF );
			if (strncmp ( fgets ( $fp, 512 ), '334', 3 ) != 0) {
				return "ALUE"; // auth login username error
			}
			fwrite ( $fp, base64_encode ( $password ) . $CRLF );
			if (strncmp ( fgets ( $fp, 512 ), '235', 3 ) != 0) {
				return 'ALPE'; // auth login password error
			}
		}
		$from = preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $fromemail );
		fwrite ( $fp, "MAIL FROM: <$from>$CRLF" );
		if (strncmp ( fgets ( $fp, 512 ), '250', 3 ) != 0) {
			return 'EFE'; // email from error
		}
		fwrite ( $fp, "RCPT TO: <$toemail>$CRLF" );
		if (strncmp ( fgets ( $fp, 512 ), '250', 3 ) != 0) {
			return 'ETE'; // email toemail error
		}
		fwrite ( $fp, "DATA$CRLF" );
		if (strncmp ( fgets ( $fp, 512 ), '354', 3 ) != 0) {
			return 'EDE'; // email data error
		}
		$msg = "Date: " . Date ( "r" ) . $CRLF;
		$msg .= "Subject: $send_subject" . $CRLF;
		$msg .= $additional . $CRLF;
		$msg .= $send_message . $CRLF . "." . $CRLF;
		fwrite ( $fp, $msg );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 250) {
			return 'ESE'; // email send error
		}
		fwrite ( $fp, "QUIT\r\n" );
		fclose ( $fp );
		return 'OK';
	}
}