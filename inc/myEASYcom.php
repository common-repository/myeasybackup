<?php
/**
 * myEASYcom.php: common functions for the myEASYwp plugins serie
 *
 * Version: 1.4 - 26 January 2012
 * Version: 1.3 - 23 July 2011
 * Author: Ugo Grandolini aka "Camaleo"
 * Support site: http://myeasywp.com
 *
 * Copyright (C) 2010 Ugo Grandolini  (email : info@myeasywp.com)
*/

# TODO ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# DEBUG
#define('MYEASYWP_DOMAIN', 'myeasywp.lan');

# PRODUCTION
define('MYEASYWP_DOMAIN', 'myeasywp.com');

define('MYEASYWP_PATH', '');
# TODO ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


if(!function_exists('measycom_camaleo_links')) {

	function measycom_camaleo_links() {

		/**
		 * display the Camaleo links
		 */
		global $admin_email, $myeasycom_pluginname;    // @since 1.1.1

		if($admin_email == '') {

			global $current_user;
			get_currentuserinfo();

//			$admin_email = get_option('admin_email');   // 1.1.3
			$admin_email = $current_user->user_email;   // 1.1.3
		}
/*
		echo '<div align="right" style="margin:12px 0 0 0;">'
				.'<form method="post" action="http://feedmailpro.com/account/subscribers">'
					.'<img style="margin-right:8px;" src="http://myeasywp.com/common/img/camaleo.gif" align="absmiddle" /> '

					.'<a href="http://myeasywp.com" target="_blank">myeasywp.com: '._('myEASY Series official site').'</a>'
// 1.0.2: BEG
//						.' | '
//					.'<a href="http://wordpress.org/extend/plugins/profile/camaleo" target="_blank">'._('Camaleo&rsquo;s plugins page at WordPress.org').'</a>'
						.' | '
					._('Be the first to know what\'s going on! Subscribe our newsletter now:')
						.'<input name="subscriber[feed_id]" value="674" type="hidden" />'
						.'<input name="user_credentials" value="wanWqB41oGpzAAx3-w9u" type="hidden" />'
						.'<input name="subscriber[email]" size="15" type="text" value="'.$admin_email.'" />'
						.' <input class="button-primary" name="commit" value="Subscribe" type="submit" />'
				.'</form>'
// 1.0.2: END
			.'</div>'
		;
*/
	?><div align="right" style="margin:12px 0 0 0;">
		<span id="mc-response"><?php

			require_once('mc/inc/store-address.php');
			if($_GET['submit']) {

				echo storeAddress();
			}

		?></span>
		<form id="signup" action="" method="get">
			<img style="margin-right:8px;" src="http://myeasywp.com/common/img/camaleo.gif" align="absmiddle" />
			<a href="http://myeasywp.com" target="_blank">myeasywp.com: <?php _e('myEASY Series official site'); ?></a> | <?php

				_e('Be the first to know what\'s going on! Join Our Mailing List:');

				?><input type="text" name="email" id="email" value="<?php echo $admin_email; ?>" />
			<div style="margin:-10px 0 10px 0;">
				<div style="float:right;margin:12px 0 4px 20px;">
					<input class="button-primary" name="commit" value="Join" type="submit" />
				</div>
				<div style="float:right;margin:10px 0 0 0;">
					<a href="http://services.myeasywp.com/?page=privacy" target="_blank"><?php
						_e('Your privacy is critically important to us!'); ?>
					</a>
				</div>
			</div>
		</form>
		<script type="text/javascript">var myeasyplugin = '<?php echo myEASYcomCaller; ?>';</script>

		<script type="text/javascript" src="<?php echo plugins_url() . $myeasycom_pluginname . 'inc/mc/inc/'; ?>jquery-1.4.2.min.js"></script>
<!--	<script type="text/javascript" src="<?php echo plugins_url() . $myeasycom_pluginname . 'inc/mc/inc/'; ?>mailing-list.js"></script> -->
		<script type="text/javascript" src="<?php echo plugins_url() . $myeasycom_pluginname . 'js/'; ?>myeasywp.js"></script>
		<?php
	}
}

if(!function_exists('measycom_sanitize_input')) {

	function measycom_sanitize_input($field,$usebr=false,$removespaces=false) {

		/**
		 * remove unwanted chars in a field
		 * @since 1.0.1
		 */
		$inp = array("\r\n","\n","\r");

		if($usebr) {

			$out = array('<br />','<br />','<br />');
		}
		else {

			$out = array('','','');
		}

		if($removespaces) {

			array_push($inp, ' ');
			array_push($out, '');
		}
		$clean = str_replace($inp, $out, $field);
		$clean = stripslashes($clean);
		return $clean;
	}
}

if(!function_exists('measycom_advertisement')) {

	function measycom_advertisement($ref_code) {

		/**
		 * display the advertisment stuff
		 */
//		$html = measycom_get_adcontents('/service/myads-1.1.php?p='.$ref_code.'&u='.$_SERVER['SERVER_NAME']);
		$html = measycom_get_adcontents('/service/ad-'.$ref_code.'.html');

		echo '<div style="width:auto;height:auto;background:transparent;padding:0;margin:8px 0 0 0;">'
				.$html
			.'</div>'
		;
	}
}

if(!function_exists('measycom_pro_stats')) {

	/**
	 * @since 1.2.1
	 */
	function measycom_pro_stats($ref_code) {

		/**
		 * log usage statistic
		 */
//		measycom_get_adcontents('/service/myads-1.1.php?p='.$ref_code.'&u='.$_SERVER['SERVER_NAME'], true);
		measycom_get_adcontents('/service/ad-'.$ref_code.'.html', true);
//echo measycom_get_adcontents('/service/myads-1.1.php?p='.$ref_code.'&u='.$_SERVER['SERVER_NAME']);
	}
}

if(!function_exists('measycom_donate')) {

	function measycom_donate($ref_code) {

		/**
		 * display the donation stuff
		 */
		$html = measycom_get_adcontents('/service/myads-1.1.php?p=donate&d='.$ref_code.'&u='.$_SERVER['SERVER_NAME']);

		echo '<div style="width:auto;height:auto;background:transparent;padding:0;margin:8px 0 0 0;">'
				.$html
			.'</div>'
		;
	}
}

if(!function_exists('measycom_get_adcontents')) {

	function measycom_get_adcontents($domain_path, $isSTAT='') {

		/**
		 * $domain_path = '/service/myads-1.1.php?p={code | donate}&d={code}'
		 */
		$domain = MYEASYWP_DOMAIN;
		$domain_path = MYEASYWP_PATH.$domain_path;

		$html = '';

		$fp = @fsockopen($domain, 80, $errno, $errstr, 3);

		if(!$fp) {

			/**
			 * HTTP ERROR
			 */
			$html = 'Connection error measycom_get_adcontents(' . $domain_path . ')';
		}
		else {

			/**
			 * get the info
			 */
			$header = "GET $domain_path HTTP/1.0\r\n"           // 1.0 !!
						."Host: $domain\r\n"
						."Connection: Close\r\n\r\n"
						//."Connection: keep-alive\r\n\r\n"
			;
			fwrite($fp, $header);

			if($isSTAT==true) {
				/**
				 * for stat there is no need to go further
				 * @since 1.2.1
				 */
				fclose($fp);
				return;
			}

			$result = '';
			while (!feof($fp)) {

				$result .= fgets($fp, 1024);
			}

			$needle = '[start]';
			$p = strpos($result, $needle, 0);
			if($p!==false) {

				$beg = $p + strlen($needle);
				$end = strpos($result, '[end]', $p);
				$html = substr($result, $beg, ($end-$beg));
			}

			fclose($fp);
		}
		return $html;
	}
}

if(!function_exists('measycom_get_response')) {

	function measycom_get_response($domain, $domain_path, $port=80, $timeout=3) {

		/**
		 * @since 1.0.5.6
		 */
		if(!$domain || !$domain_path) {

			return false;
		}

		$html = '';

//echo '$port['.$port.']<br>';

		$ssl = '';
		if((int)$port==443) {

			if(!function_exists('openssl_ open')) {

				$port = 80;
			}
			else {

				$ssl = 'ssl://';
			}
		}

//echo '$port['.$port.']<br>';

		$fp = @fsockopen($ssl . $domain, $port, $errno, $errstr, $timeout);

		if(!$fp) {

			/**
			 * HTTP ERROR
			 */
			$html = 'Connection error measycom_get_response(' . $domain . ') '. $errno . ' ' . $errstr;
		}
		else {

			/**
			 * get the info
			 */
			$header = "GET $domain_path HTTP/1.0\r\n"           // 1.0 !!
						."Host: $domain\r\n"
						."Connection: Close\r\n\r\n"
						//."Connection: keep-alive\r\n\r\n"
			;
			fwrite($fp, $header);

			$result = '';
			while (!feof($fp)) {

				$result .= fgets($fp, 1024);
			}

			/**
			 * remove headers
			 */
			$headerend = strpos($result, "\r\n\r\n");
			if($headerend !== false) {

				$html = substr($result, $headerend+4);
			}
			else {

				$html = $result;
			}
			fclose($fp);
		}
		return $html;
	}
}

if(!function_exists('measycom_mimetype')) {

	function measycom_mimetype($path,$file) {

		/**
		 * Return the mimetype
		 */
		if(function_exists('mime_content_type')) {

			$file_name_type = mime_content_type($path.$file);
		}
		else {

			#	mime_content_type() is NOT installed on non-Linux servers!
			#
			$ext = explode('.',$file);
			$i = count($ext);
			switch($ext[($i-1)]) {

				case 'rar':
				case 'tgz':
				case 'gz':
				case 'zip':
					$file_name_type = 'application/zip';
					break;

				case 'pdf':
					$file_name_type = 'application/pdf';
					break;

				default:
					$file_name_type = 'text/plain';
			}
		}
		return($file_name_type);
	}
}

if(!function_exists('measycom_emailer')) {

	function measycom_emailer(
						$to,
						$subject,
						$body,
						$reply		= '',
						$cc			= '',
						$bcc		= '',
						$from		= '',
						$text_type	= 'html',
						$x_prio		= '3',
						$attach_path= '',
						$attach_file= '',
						$_CHARSET   = 'utf-8'
	) {

		/**
		 * email sender wrapper
		 */
		//define('_CR_',"\n");					#	05/05/2010
		define('_CR_',"\r\n");					#	05/05/2010
		define('_TAB_',"\t");

		$user_body = $body;

		/**
		 * Initializations
		 */
		$myHOST = str_replace('www.','',$_SERVER['HTTP_HOST']);

		if($reply=='')				{ $reply = 'noreply@'.$myHOST; }

		if($from=='')				{ $from  = 'myEASYrobot#robot@'.$myHOST; }
		list($user_id, $from_id)	= explode('#', $from);
		if($user_id=='')			{ $user_id ='myEASYrobot'; }
		if($from_id=='')			{ $from_id ='robot@'.$myHOST; }

		$domain = $_SERVER['SERVER_NAME'];
		$mime_boundary = '------------{'.md5(uniqid(time())).'}';

		/**
		 * Set the common headers
		 */
		$headers = 'MIME-Version: 1.0'._CR_;
		$headers .= 'Reply-To:'.$reply._CR_;

		if(is_array($cc)) {

			/**
			 * copy to
			 */
			$t = count($cc);
			$headers .= 'Cc:';
			for($i=0;$i<$t;$i++) { $headers .= $cc[$i].', '; }
			$headers = substr($headers,0,-2)._CR_;
		}
		else { if($cc) { $headers .= 'Cc:'.$cc._CR_; } }

		if(is_array($bcc)) {

			/**
			 * blind copy to
			 */
			$t = count($bcc);
			$headers .= 'Bcc:';
			for($i=0;$i<$t;$i++) { $headers .= $bcc[$i].', '; }
			$headers = substr($headers,0,-2)._CR_;
		}
		else { if($bcc) { $headers .= 'Bcc:'.$bcc._CR_; } }

		$headers .= 'User-Agent: '.$_SERVER['HTTP_USER_AGENT']._CR_;
		$headers .= 'From: '.$user_id.' <'.$from_id.'>'._CR_;
		$headers .= 'Message-ID: <'.md5(uniqid(time())).'@'.$domain.'>'._CR_;

		switch($x_prio) {

			/**
			 * priority
			 */
			case '1':	$x_prio .= ' (Highest)';	break;
			case '2':	$x_prio .= ' (High)';		break;
			case '3':	$x_prio .= ' (Normal)';		break;
			case '4':	$x_prio .= ' (Low)';		break;
			case '5':	$x_prio .= ' (Lowest)';		break;

			default:
				$x_prio = '3 (Normal)';
		}

		if($x_prio)	{ $headers .= 'X-Priority: '.$x_prio._CR_; }

		/**
		 * Message Priority for Exchange Servers
		 *
		 * $headers .=	'X-MSmail-Priority: '.$x_prio_des._CR_;
		 *
		 * !!! WARNING !!!---# Hotmail and others do NOT like PHP mailer...
		 * $headers .=	'X-Mailer: PHP/'.phpversion()._CR_;---#
		 *
		 * $headers .= 'X-Mailer: Microsoft Office Outlook, Build 11.0.6353'._CR_;
		 * $headers .= 'X-MimeOLE: Produced By Microsoft MimeOLE V6.00.2900.2527'._CR_;
		 *
		 */
		$headers .= 'X-Sender: '.$user_id.' <'.$from_id.'>'._CR_;

		$headers .= 'X-AntiAbuse: This is a solicited email for - '.$to.' - '._CR_;
		$headers .= 'X-AntiAbuse: Servername - {'.$domain.'}'._CR_;

		$headers .= 'X-AntiAbuse: User - '.$from_id._CR_;

		/**
		 * Set the right start of header
		 */
		if($attach_path && $attach_file) {

			if(!is_array($attach_path) || !is_array($attach_file)) {

				$_attach_path = array();
				$_attach_file = array();

				$_attach_path[] = $attach_path;
				$_attach_file[] = $attach_file;
			}
			else {

				$_attach_path = $attach_path;
				$_attach_file = $attach_file;
			}

			$a = 0;
			foreach($_attach_file as $key=>$attach_file) {

				$attach_path = $_attach_path[$key];

				$file_name_type = measycom_mimetype($attach_path, $attach_file);
				$file_name_name = $attach_file;

				/**
				 * Read the file to be attached
				 */
				$data = '';
				$file = @fopen($attach_path.$attach_file,'rb');
				if($file) {

					while(!feof($file)) { $data .= @fread($file, 8192); }
					@fclose($file);
				}

				/**
				 * Base64 encode the file data
				 */
				$data = chunk_split(base64_encode($data));

				if($a==0) {											/* send the body only once */

					/**
					 * Complete headers
					 */
					$headers .= 'Content-Type: multipart/mixed;'._CR_;
					$headers .= ' boundary="'.$mime_boundary.'"'."\n\n";

					/**
					 * Add a multipart boundary above the text message
					 */
					$mail_body_attach  = 'This is a multi-part message in MIME format.'._CR_;
					$mail_body_attach .= '--'.$mime_boundary."\n";
					$mail_body_attach .= 'Content-Type: text/'.$text_type.'; charset='.$_CHARSET.';'."\n";
					$mail_body_attach .= 'Content-Transfer-Encoding: 8bit'."\n\n";
					$mail_body_attach .= $body."\n";

					$body = $mail_body_attach;
				}

				/**
				 * Add the file attachment
				 */
				$mail_file_attach = '--'.$mime_boundary."\n";
				$mail_file_attach .= 'Content-Type: '.$file_name_type.";\n";
				$mail_file_attach .= ' name="'.$file_name_name.'"'."\n";
				$mail_file_attach .= 'Content-Disposition: attachment;'."\n";
				$mail_file_attach .= ' filename="'.$file_name_name.'"'."\n";
				$mail_file_attach .= 'Content-Transfer-Encoding: base64'."\n\n";
				$mail_file_attach .= $data."\n";

				$body .= $mail_file_attach;
				$a++;
			}
		}
		else {

			if($text_type=='plain') {

				$headers .= 'Content-Type: text/'.$text_type.'; charset='.$_CHARSET.';'."\n";
				$headers .= 'Content-Transfer-Encoding: 8bit'._CR_;
			}

			if($text_type=='html') {

				$headers .= 'Content-Type: multipart/alternative;'._CR_;
				$headers .= ' boundary="'.$mime_boundary.'"'."\n\n";

				$mail_body_multipart  = 'This is a multi-part message in MIME format.'._CR_;

				/**
				 * plain version
				 */
				$inp = array();
				$out = array();

				$inp[] = '<br>';        $out = "\n";
				$inp[] = '<br />';      $out = "\n";
				$inp[] = '<hr>';        $out = "\n------------------------------------------\n";
				$inp[] = '<hr />';      $out = "\n------------------------------------------\n";

				$plain = str_replace($inp, $out, $body);
				$plain = strip_tags($plain);

				$mail_body_multipart .= '--'.$mime_boundary."\n";
				$mail_body_multipart .= 'Content-Type: text/plain; charset='.$_CHARSET."\n";
				$mail_body_multipart .= 'Content-Transfer-Encoding: 8bit'."\n\n";
				$mail_body_multipart .= $plain."\n";

				/**
				 * html version
				 */
				$mail_body_multipart .= '--'.$mime_boundary."\n";
				$mail_body_multipart .= 'Content-Type: text/html; charset='.$_CHARSET.'; format=flowed'."\n";
				$mail_body_multipart .= 'Content-Transfer-Encoding: 8bit'."\n\n";
				$mail_body_multipart .= $body."\n\n";

				$body = $mail_body_multipart."\n".'--'.$mime_boundary."--\n";
			}
		}

		#
		#	$extra_header = '-fwebmaster@{'.$domain.'}'; # this is the User of the machine or hosting account
		#
//echo 'Subject:'.$subject
//	.'<br>Reply:'.$reply
//	.'<br>cc:'.$cc
//	.'<br>To:'.$to
//	.'<br>Body:<br>'.$body
//	.'<br>From_id:'.$from_id
//	.'<br>headers:'.$headers
//	.'<br>Mail Server:'.$_SESSION['misc']['MAILSRV'].':'.$_SESSION['misc']['MAILSRVPORT']
//	.'<br>E sender:'.$_SESSION['misc']['E_SENDER']
//	;
//die();

//$tmp = false;	#debug

		$tmp = @mail($to, $subject, $body, $headers); #, $extra_header);

		if($tmp==true) {

			return '*OK*';
		}
		else {

			$html = '<hr>There has been a mail error sending to:'.$to.'<hr>';

			$html .= 'Subject:'.$subject
					.'<br>Reply:'.$reply
					.'<br>cc:'.$cc
					.'<br>Body:<br>'.$body
					.'<br>From_id:'.$from_id
//					.'<br>Mail Server:'.$_SESSION['misc']['MAILSRV'].':'.$_SESSION['misc']['MAILSRVPORT']
					.'<br>Headers:'.$headers
			;

			echo $html;
			return $html;
		}
	}
}

if(!function_exists('measycom_get_real_path')) {

	/**
	 * @since 1.1.1
	 *
	 * required to calculate paths when running on servers with linked paths configuration
	 *
	 */
	function measycom_get_real_path($docPth, $filePth) {

		$docAry = explode('/', $docPth);
		$pthAry = explode('/', $filePth);

		$docLastId = count($docAry)-1;
		$docLast = $docAry[$docLastId];
		$pthLastId = count($pthAry)-1;

		$e = 0;
		$f = 0;
		for($i=$pthLastId;$i>=0;$i--) {

			if($pthAry[$i]==$docLast) {

				$e = $i;
			}
			if($pthAry[$i]=='wp-content') {

				$f = $i;
			}
		}

		if($e==0) { $e = $docLastId-1; }
		if($f==0) { $f = $pthLastId; }

		$e++;
		$pth = '';
		for($i=$e;$i<$f;$i++) {

			$pth .= $pthAry[$i].'/';
		}

		if(substr($pth,-1)!='/') { $pth = $pth.'/'; }
		if(substr($pth,0,1)!='/') { $pth = '/'.$pth; }

		return $pth;
	}
}

if(!function_exists('sortTreeAry')) {

	/**
	 * @since 1.2.2
	 */
	function sortTreeAry($ary) {

//var_dump($ary);
		$tree = array();
		foreach($ary as $deep => $dir) {

//echo $dir . '<br>';
			if(is_array($dir)) {

				foreach($dir as $key => $data) {

//echo $deep.') '.str_replace(MEBAK_WP_PARENT_PATH, '', $data) . '<br>';
					$tree[] = str_replace(MEBAK_WP_PARENT_PATH, '', $data);
				}
			}
			else {

				$tree[] = str_replace(MEBAK_WP_PARENT_PATH, '', $data);
			}
		}

		sort($tree);

//var_dump($tree);
//	foreach($tree as $k => $dir) {
//
//		echo $dir . '<br>';
//	}

		return $tree;
	}
}

if(!function_exists('getFolderTree')) {

	/**
	 * @since 1.2.2
	 */
	function getFolderTree($folder, $maxDeep=4, $read=0, $deep=0, $treeAry='') {

		$files = @scandir($folder);

		$t = 0;
		$d = 0;

		if((int)$read>0) { $t = $read; }
		if((int)$deep>0) { $d = $deep; }

		$d++;

		if(is_array($files)) {

			foreach($files as $fname) {
//echo '[*]' . $fname . '<br />';

				if($fname!='.' && $fname!='..' && is_dir($folder . '/' . $fname)) {

//echo '[D:'.$d.']' . $fname . '<br />';

					if(!isset($treeAry[$d])) {

						$treeAry[$d] = array();
					}

					$treeAry[$d][] = $folder . '/' . $fname;
					$t++;

					if($d < $maxDeep) {

						$treeAry = getFolderTree($folder . '/' . $fname, $maxDeep, $t, $d, $treeAry);
					}
				}
			}
		}
		return $treeAry;
	}
}

if(!class_exists('myeasywp_news')) {

	class myeasywp_news {

		var $version = '1.0';

		var $ref_code;   // caller plugin
		var $ref_family; // caller family
		var $html;
		var $cache;

		function plugin_init() {

			/**
			 * initializations
			 */
			$this->cache = ABSPATH.'wp-content/uploads/myeasywp_dashnews-'.$this->ref_code.'.txt';

			$this->html = $this->fill_html();
//echo '>>>'.$this->html.'<br>['.$this->ref_family.']<br>';

			if(is_dir(ABSPATH.'wp-content/uploads') && is_writable(ABSPATH.'wp-content/uploads')) {

				if(file_exists($this->cache)) {

					if(filemtime($this->cache)<(time()-86400)) {

//echo 'update<br>';
						file_put_contents($this->cache, $this->html);
					}
					else {

//echo 'use<br>';
					}
				}
				else {

//echo 'create<br>';
					file_put_contents($this->cache, $this->html);
				}
			}

			add_action('wp_dashboard_setup', array($this, 'register_widget'));
		}

		function register_widget() {

			wp_add_dashboard_widget('myeasywp-news', 'myEASYwp.com news', array($this, 'myeasywp_dashnews'));
		}

		function fill_html() {

			/**
			 * get the html contents
			 */
			if($this->ref_family==false) {

//				return $this->get_data('/service/myads-1.1.php?p='.$this->ref_code.'&u='.$_SERVER['SERVER_NAME']);
				return $this->get_data('/service/ad-'.$this->ref_code.'.html');
			}
			else {

//				return $this->get_data('/service/myads-1.1.php?p='.$this->ref_code.'&u='.$_SERVER['SERVER_NAME'], true);
				return $this->get_data('/service/ad-'.$this->ref_code.'.html', true);
			}
		}

		function print_html() {

			/**
			 * print the html contents
			 */
			if(file_exists($this->cache)) {

				echo file_get_contents($this->cache);
			}
			else {

				echo $this->fill_html();
			}
		}

		function get_data($domain_path, $isSTAT='') {

			/**
			 * $domain_path = '/service/myads-1.1.php?p={code | donate}&d={code}'
			 */
			$domain = MYEASYWP_DOMAIN;
			$domain_path = MYEASYWP_PATH.$domain_path;
			$html = '';

			$fp = @fsockopen($domain, 80, $errno, $errstr, 3);

			if(!$fp) {

				/**
				 * HTTP ERROR
				 */
				$html = 'Connection error myeasycom_get_adcontents(' . $domain_path . ')';
			}
			else {

				/**
				 * get the info
				 */
				$header = "GET $domain_path HTTP/1.0\r\n"           // 1.0 !!
							."Host: $domain\r\n"
							."Connection: Close\r\n\r\n"
							//."Connection: keep-alive\r\n\r\n"
				;
				fwrite($fp, $header);

				if($isSTAT==true) {
					/**
					 * for stat there is no need to go further
					 * @since 1.2.1
					 */
					fclose($fp);
					return;
				}

				$result = '';
				while (!feof($fp)) {

					$result .= fgets($fp, 1024);
				}

				$needle = '[start]';
				$p = strpos($result, $needle, 0);
				if($p!==false) {

					$beg = $p + strlen($needle);
					$end = strpos($result, '[end]', $p);
					$html = substr($result, $beg, ($end-$beg));
				}

				fclose($fp);
			}
			return $html;
		}

		function myeasywp_dashnews() {

			if(file_exists($this->cache)) {

				$this->html = file_get_contents($this->cache);
			}
			else {

//				$html = measycom_get_adcontents('/service/myads-1.1.php?p='.$this->ref_code.'&u='.$_SERVER['SERVER_NAME']);
				$this->html = $this->fill_html();
			}

			echo '<div style="width:auto;height:auto;background:transparent;padding:0;margin:8px 0 0 0;">'
				     .$this->html
			     .'</div>';
		}
	}
}

if(!class_exists('wp_plugin_donation_to_camaleo')) {

	class wp_plugin_donation_to_camaleo {

		var $text_domain;

		function __construct() {

			// If we are not in admin panel. We bail out.
			if(!is_admin()) { return false; }

			// Load Text Domain
			$this->load_textdomain();

			// Register the setting if a user donated
			add_action('admin_menu', array($this, 'add_settings_field'));

			// Check if the user has already donated
			if(get_option('donated_to_camaleo')) { return false; }

			// Add some Js for the donation buttons to the admin header
			add_action('admin_head', array($this, 'print_donation_js'));

			// Add the donation form (hidden)
			add_action('admin_notices', array($this, 'print_donation_form'), 1);

			// Register the Dashboard Widget
			add_action('wp_dashboard_setup', array($this, 'register_widget'));

			// Register donation message
			add_action('donation_message', array($this, 'print_message'));
		}

		function load_textdomain() {//TODO +++

			$this->text_domain = get_class($this);
			$lang_file = dirname(__FILE__) . '/donate_' . get_locale() . '.mo';

			if(is_file($lang_file)) { load_textdomain($this->text_domain, $lang_file); }
		}

		function t($text, $context = '') {

			// Translates the string $text with context $context
			if($context == '') {

				return __($text, $this->text_domain);
			}
			else {

				return _x($text, $context, $this->text_domain);
			}
		}

		function register_widget() {

			// Setup the Dashboard Widget
			wp_add_dashboard_widget('donation-to-camaleo-' . time(),
				$this->t('myEASYwp Series &ndash; Please think about a donation!'),
				array($this, 'print_message')
			);
		}

		function print_donation_js() {

			?><script type="text/javascript">/* <![CDATA[ */
			jQuery(function($) {
				// Start of the DOM ready sequence
				// Hide all fields we do not want show to the cool js users.
				jQuery('.hide_if_js').hide();

				// Show the cool js gui. But only for cool js users.
				jQuery('.show_if_js').show();

				// Catch the currency click
				jQuery('.camaleo_donation_show_ui').click(function() {
					jQuery('.camaleo_donation_ui').slideUp();
					jQuery(this).parent().find('.camaleo_donation_ui').slideDown();
					return false;
				});

				// Catch the donation button click
				jQuery('input.camaleo_donation_button').click(function() {
					// Find the form
					var $form = jQuery('form#camaleo_paypal_donation_form');

					// Find the button
					var $this = jQuery(this).parent();

					// Read currency and amount
					var currency = $this.find('.camaleo_donation_currency').val();
					var amount = $this.find('.camaleo_donation_amount').val();

					// Put the values in the form
					$form
							.find('input[name=currency_code]').val(currency).end()
							.find('input[name=amount]').val(amount).end()
							.submit();
				});
				// End of the catch routine of the donation button

				// Catch the donation select amount change
				jQuery('.camaleo_donation_currency, .camaleo_donation_amount').change(function() {
					jQuery(this).parent().find('input.camaleo_donation_button').removeAttr('disabled');
				});
				// End of the catch of the select amount change

				// End of the DOM Ready sequence
			});
			/* ]]> */</script><?php
		}

		function print_donation_form() {

			// PayPal Donation Form for Camaleo

			?><div style="display:none">
				<form action="https://www.paypal.com/cgi-bin/webscr" id="camaleo_paypal_donation_form" method="post" target="_blank">
					<input type="hidden" name="cmd" value="_xclick"/>
					<input type="hidden" name="business" value="info@myeasywp.com"/>
					<input type="hidden" name="no_shipping" value="1"/>
					<input type="hidden" name="tax" value="0"/>
					<input type="hidden" name="no_note" value="0"/>
					<input type="hidden" name="item_name" value="<?php echo $this->t('Donation to the Open Source Community') ?>"/>
					<input type="hidden" name="on0" value="<?php echo $this->t('Reference') ?>"/>
					<input type="hidden" name="os0" value="<?php echo $this->t('WordPress') ?>"/><?php

					foreach($this->get_current_extensions() AS $index => $extension) {

						?><input type="hidden" name="on<?php echo ($index + 1) ?>" value="<?php echo $this->t('Plugin') ?>"/>
						<input type="hidden" name="os<?php echo ($index + 1) ?>" value="<?php echo htmlspecialchars(strip_tags($extension)) ?>"/><?php
					}

					?><input type="hidden" name="currency_code" value=""/>
					<input type="hidden" name="amount" value=""/>
				</form>
			</div><?php
		}

		function get_current_extensions() {

			// array which contains all my extensions
			static $arr_extension;

			if(isset($arr_extension)) {

				return $arr_extension;
			}
			else {

				$arr_extension = array();
			}

			// Read the active plugins
			foreach((array) get_option('active_plugins') AS $plugin) {

				$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
				if(strpos(strtolower($plugin_data['Author']), 'camaleo') !== false) {

					$arr_extension[] = $plugin_data['Title'];
				}
			}

			// Read the current theme
			$current_theme_info = current_theme_info();
			if($current_theme_info && strpos(strtolower($current_theme_info->author), 'camaleo') !== false) {

				$arr_extension[] = $this->t('the theme') . ' ' . $current_theme_info->title;
			}

			return $arr_extension;
		}

		function print_message() {

			// Read current user
			global $current_user;
			get_currentuserinfo();

			// Get current plugins
			$arr_extension = $this->get_current_extensions();

			// Write the Dashboard message

			?><div class="light" style="float:left;width:auto;background:#f9f9f9;padding:4px;margin:0 10px 3px 0;">
	<img src="http://www.gravatar.com/avatar/86fd9bbedc81e50140384d957f1ff82f?d=wavatar&amp;s=100" class="alignleft" alt="Camaleo" height="100px" width="100px" />
</div>
<div style="text-align:left">
	<h4 style="margin-top:0;"><?php printf($this->t('Hello %1$s!'), $current_user->display_name) ?></h4>
	<p><?php

		echo $this->t('My name is Ugo Grandolini, also known as "Camaleo". I am a simple man who cares to put Love in everything he does, actually living in Como, Italy.');

	?></p>
	<p><?php

		printf($this->t('Beside other software I developed in more than 20 years, I am behind the myEASY Series of WordPress plugins including %1$s.'), $this->Extended_Implode($arr_extension, ', ', ' ' . $this->t('and') . ' '));
		echo ' ' . $this->t('Each plugin is developed, maintained, supported and documented with a lot of Love &amp; several hours of work and effort to make your life easier!');

	?></p>
	<p><?php

		echo $this->t('I love the spirit of the open source movement, to write and share code and knowledge, however I think the system can work only if everyone contributes as she/he can.');

	?></p>
	<p><?php

		printf($this->t('Because you are using %1$s of my WordPress extensions I guess you appreciate my job.'), $this->Number_to_Word(count($arr_extension)));

	?></p>
	<p><?php

		echo $this->t('So please think about a donation. You would also help to keep alive and growing the open source community.');

	?></p>
</div>
<div>
<div class="light" style="float:right;width:30%;background:#f9f9f9;margin-top:0;padding:1%;">
<ul>
	<li><b><?php echo $this->t('Or make me an amazing gift!') ?></b><ul>
			<li>&raquo; <a href="https://www.amazon.com/wishlist/39FU2MJ17RU3B" title="<?php echo $this->t('My Amazon Wish List') ?>" target="_blank"><?php echo $this->t('My Amazon Wish List') ?></a></li>
		</ul>
	</li>
</ul>
</div>
<div class="light" style="float:left;width:63%;background:#f9f9f9;margin-top:0;padding:1%;">
<ul>
	<li class="hide_if_js"><?php echo $this->t('Make a donation via PayPal') ?>:<ul>
			<li>&raquo; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=1220480" target="_blank">United States Dollar ($)</a></li>
			<li>&raquo; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=U49F54BMWKNHU" target="_blank">Pound Sterling (&pound;)</a></li>
			<li>&raquo; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=HECSPGLPTQL24" target="_blank">Euro (&euro;)</a></li>
		</ul>
	</li>
	<li class="show_if_js" style="display:none"><b><?php echo $this->t('Make a donation via PayPal') ?></b>:<ul>
			<li>&raquo; <a href="#" title="<?php echo $this->t('Make a donation in US Dollars') ?>" class="camaleo_donation_show_ui">United States Dollar ($)</a>
				<div class="camaleo_donation_ui">
					<?php echo $this->t('Amount') ?>:
					<input type="hidden" class="camaleo_donation_currency" value="USD"/>
					<select class="camaleo_donation_amount">
						<option value="" disabled="disabled" selected="selected"><?php echo $this->t('Amount in USD') ?></option>
						<option value="82.95">$82.95</option>
						<option value="68.95">$68.95</option>
						<option value="54.95">$54.95</option>
						<option value="41.95">$41.95</option>
						<option value="34.95">$34.95</option>
						<option value="27.95">$27.95</option>
						<option value="20.95">$20.95</option>
						<option value="13.95">$13.95</option>
						<option value="6.95">$6.95</option>
						<option value="">&raquo; <?php echo $this->t('other amount') ?></option>
					</select>
					<input type="button" class="camaleo_donation_button button-primary"
					value="<?php echo $this->t('Proceed to PayPal') ?> &rarr;"
					title="<?php echo $this->t('Proceed to PayPal') ?>" disabled="disabled"/>
				</div>
			</li>
			<li>&raquo; <a href="#" title="<?php echo $this->t('Make a donation in Pound Sterling') ?>" class="camaleo_donation_show_ui">Pound Sterling (&pound;)</a>
				<div class="camaleo_donation_ui hide_if_js">
					<?php echo $this->t('Amount') ?>:
					<input type="hidden" class="camaleo_donation_currency" value="GBP"/>
					<select class="camaleo_donation_amount">
						<option value="" disabled="disabled" selected="selected"><?php echo $this->t('Amount in GBP') ?></option>
						<option value="62.64">&pound;62.64</option>
						<option value="52.24">&pound;52.24</option>
						<option value="41.83">&pound;41.83</option>
						<option value="31.43">&pound;31.43</option>
						<option value="21.02">&pound;21.02</option>
						<option value="15.82">&pound;15.82</option>
						<option value="10.61">&pound;10.61</option>
						<option value="5.41">&pound;5.41</option>
						<option value="">&raquo; <?php echo $this->t('other amount') ?></option>
					</select>
					<input type="button" class="camaleo_donation_button button-primary" value="<?php echo $this->t('Proceed to PayPal') ?> &rarr;" title="<?php echo $this->t('Proceed to PayPal') ?>" disabled="disabled"/>
				</div>
			</li>
			<li>&raquo; <a href="#" title="<?php echo $this->t('Make a donation in Euro') ?>" class="camaleo_donation_show_ui">Euro (&euro;)</a>
				<div class="camaleo_donation_ui hide_if_js">
					<?php echo $this->t('Amount') ?>:
					<input type="hidden" class="camaleo_donation_currency" value="EUR"/>
					<select class="camaleo_donation_amount">
						<option value="" disabled="disabled" selected="selected"><?php echo $this->t('Amount in EUR') ?></option>
						<option value="61.52">61,52 &euro;</option>
						<option value="51.33">51,33 &euro;</option>
						<option value="41.13">41,13 &euro;</option>
						<option value="30.94">30,94 &euro;</option>
						<option value="20.74">20,74 &euro;</option>
						<option value="15.65">15,65 &euro;</option>
						<option value="10.55">10,55 &euro;</option>
						<option value="5.45">5,45 &euro;</option>
						<option value="">&raquo; <?php echo $this->t('other amount') ?></option>
					</select>
					<input type="button" class="camaleo_donation_button button-primary" value="<?php echo $this->t('Proceed to PayPal') ?> &rarr;" title="<?php echo $this->t('Proceed to PayPal') ?>" disabled="disabled"/>
				</div>
			</li>
			<li>&raquo; <a href="#" title="<?php echo $this->t('Make a donation in another currency') ?>" class="camaleo_donation_show_ui"><?php echo $this->t('Other currency') ?></a>
				<div class="camaleo_donation_ui hide_if_js">
					<input type="hidden" class="camaleo_donation_amount" value=""/>
					<select class="camaleo_donation_currency">
						<option value="" disabled="disabled" selected="selected"><?php echo $this->t('International currency') ?></option>
						<option value="CAD">Dollar canadien (C$)</option>
						<option value="JPY">Yen (&yen;)</option>
						<option value="AUD">Australian dollar (A$)</option>
						<option value="CHF">Franken (SFr)</option>
						<option value="NOK">Norsk krone (kr)</option>
						<option value="SEK">Svensk krona (kr)</option>
						<option value="DKK">Dansk krone (kr)</option>
						<option value="PLN">Polski zloty</option>
						<option value="HUF">Magyar forint (Ft)</option>
						<option value="CZK">koruna česká (Kč)</option>
						<option value="SGD">Ringgit Singapura (S$)</option>
						<option value="HKD">Hong Kong dollar (HK$)</option>
						<option value="ILS">שקל חדש (₪)</option>
						<option value="MXN">Peso mexicano (Mex$)</option>
						<option value="NZD">Tāra o Aotearoa (NZ$)</option>
						<option value="PHP">Piso ng Pilipinas (piso)</option>
						<option value="TWD">New Taiwan dollar (NT$)</option>
					</select>
					<input type="button" class="camaleo_donation_button button-primary" value="<?php echo $this->t('Proceed to PayPal') ?> &rarr;" title="<?php echo $this->t('Proceed to PayPal') ?>" disabled="disabled"/>
				</div>
			</li>
		</ul>
	</li>
</ul>
</div>
<div style="clear:both"></div>
</div>
<p><?php echo $this->t('After donation I will let you know how you can hide this notice easily ;-)') ?></p>
<div class="clear"></div><?php

		}

		function add_settings_field() {

			// Register the option field
			register_setting('general', 'donated_to_camaleo');

			// Add Settings Field
			add_settings_field(
				get_class($this),
				$this->t('Donation to Camaleo'),
				array($this, 'print_settings_field'),
				'general'
			);
		}

		function print_settings_field() {

			?><div class="light" style="max-width:600px;padding:8px;"><?php do_action('donation_message') ?></div>
			<input type="checkbox" name="donated_to_camaleo" id="donated_to_camaleo" value="yes" <?php checked(get_option('donated_to_camaleo'), 'yes') ?>/> <label for="donated_to_camaleo"><?php

				echo $this->t('I give the affidavit that I have sent a donation to Camaleo or paid him a fee for his job.');

			?></label><?php
		}

		function Extended_Implode($array, $separator = ', ', $last_separator = ' and ') {

			$array = (array) $array;
			if(Count($array) == 0) { return ''; }
			if(Count($array) == 1) { return $array[0]; }
			$last_item = array_pop($array);
			$result = Implode($array, $separator) . $last_separator . $last_item;

			return $result;
		}

		function Number_to_Word($number) {

			$arr_word = array(
				0 => $this->t('none'),
				1 => $this->t('one'),
				2 => $this->t('two'),
				3 => $this->t('three'),
				4 => $this->t('four'),
				5 => $this->t('five'),
				6 => $this->t('six'),
				7 => $this->t('seven'),
				8 => $this->t('eight'),
				9 => $this->t('nine'),

				10 => $this->t('ten'),
				11 => $this->t('eleven'),
				12 => $this->t('twelve'),
				13 => $this->t('thirteen'),
				14 => $this->t('fourteen'),
				15 => $this->t('fifteen'),
				16 => $this->t('sixteen'),
				17 => $this->t('seventeen'),
				18 => $this->t('eighteen'),
				19 => $this->t('nineteen'),

				20 => $this->t('twenty'),
				21 => $this->t('twenty-one'),
				22 => $this->t('twenty-two'),
				23 => $this->t('twenty-three'),
				24 => $this->t('twenty-four'),
				25 => $this->t('twenty-five'),
				26 => $this->t('twenty-six'),
				27 => $this->t('twenty-seven'),
				28 => $this->t('twenty-eight'),
				29 => $this->t('twenty-nine'),

				30 => $this->t('thirty'),
				31 => $this->t('thirty-one'),
				32 => $this->t('thirty-two'),
				33 => $this->t('thirty-three'),
				34 => $this->t('thirty-four'),
				35 => $this->t('thirty-five'),
				36 => $this->t('thirty-six'),
				37 => $this->t('thirty-seven'),
				38 => $this->t('thirty-eight'),
				39 => $this->t('thirty-nine'),

				40 => $this->t('fourty'),
				41 => $this->t('fourty-one'),
				42 => $this->t('fourty-two'),
				43 => $this->t('fourty-three'),
				44 => $this->t('fourty-four'),
				45 => $this->t('fourty-five'),
				46 => $this->t('fourty-six'),
				47 => $this->t('fourty-seven'),
				48 => $this->t('fourty-eight'),
				49 => $this->t('fourty-nine'),

				50 => $this->t('fifty'),
				51 => $this->t('fifty-one'),
				52 => $this->t('fifty-two'),
				53 => $this->t('fifty-three'),
				54 => $this->t('fifty-four'),
				55 => $this->t('fifty-five'),
				56 => $this->t('fifty-six'),
				57 => $this->t('fifty-seven'),
				58 => $this->t('fifty-eight'),
				59 => $this->t('fifty-nine'),

				60 => $this->t('sixty'),
				70 => $this->t('seventy'),
				80 => $this->t('eighty'),
				90 => $this->t('ninty'),
				100 => $this->t('hundred')
			);

			if(isset($arr_word[$number])) {

				return $arr_word[$number];
			}
			else {

				return $number;
			}
		}
	}
##	new wp_plugin_donation_to_camaleo();
}
?>