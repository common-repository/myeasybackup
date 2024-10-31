<?php
/**
 * AJAX responder
 *
 * @package myEASYbackup
 * @author Ugo Grandolini
 * @since 0.1.3
 * @version 1.0.11
 */

/**
 * we do not want the result to be cached
 * @since 1.0.5.9 moved here
 */
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: nocache');
header('Expires: Fri, 31 Dec 1999 23:59:59 GMT');

define('isAJAX', true);

#
#	splitters
#
$splitter_tag	= '|-ajax-tag-|';
$splitter_block	= '|-ajax-block-|';
$splitter_cmd	= '|-ajax-cmd-|';

#-------------------------------------------------------------
#
#	first of all let's check if we are called by our server
#
#-------------------------------------------------------------
#
#	1.0.0: BEG
#-------------
//$tmp = explode('://', $_SERVER['HTTP_REFERER']);
//$path = explode('/', $tmp[1]);
//$referer = $path[0];
//echo '$referer['.$referer.']';
//var_dump($_SERVER);

/*
 * var_dump($_SERVER);
 * ["HTTP_REFERER"]=>string(88) "http://www.example.com/wp-admin/options-general.php?page=myEASYbackup_options"
 * ["HTTP_HOST"]=>string(26) "www.example.com"
 * ["SERVER_NAME"]=>string(22) "example.com"
 * $referer[www.example.com]
 */
//if(($_SERVER['HTTP_HOST'] != $_SERVER['SERVER_NAME'])
//		||
//	($_SERVER['HTTP_HOST'] != $referer)
//		||
//	($_SERVER['SERVER_NAME'] != $referer) ) {


if(isset($_SERVER['HTTP_HOST'])
	&& isset($_SERVER['SERVER_NAME'])
	&& isset($_SERVER['HTTP_REFERER'])) { /* this entire condition @since 1.0.5.9 */

	if(stripos($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']) !== false
		&& stripos($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']) !== false) {
#-------------
#	1.0.0: END
#
		echo strip_tags($_POST['tag'])
			.$splitter_tag
			.'<div class="warning">'

				. 'There is an issue with the caller...'

			.'</div>'
		;
		exit();
	}
}

//session_start();########################################

#
#	initialize some variables
#
define('AJAX_CALLER', true);

//echo '(a) '.time();
require_once('meb-config.php');
//echo '(b) '.time();

$YMD = date('Ymd', time());

/*===========================================================

	The js caller can send parameters both as GET or POST.

	POST is generally considered more sure and it also allows
	for longer parameters to be sent.

	If you like to configure the js to pass the parameters
	by GET, you need to change the $_INPUT assignment few
	lines here below.

  ===========================================================*/
//echo '$_GET:';var_dump($_GET);echo "\n\n";
//echo '$_POST:';var_dump($_POST);echo "\n\n";

//$_INPUT = $_GET;
$_INPUT = $_POST;

if(!is_array($_INPUT) || count($_INPUT)==0)
{
	#	in any case we expect parameters to be sent as an array
	#	if not, better to quit...
	#
	exit();
}

if(strpos($_INPUT['parms'], AJAX_PARMS_SPLITTER) !== false)
{
	#	if there is more than one parameter, they are separated
	#	by the constant defined in AJAX_PARMS_SPLITTER
	#
	$parms = explode(AJAX_PARMS_SPLITTER, $_INPUT['parms']);
}
else
{
	#	there is only one parameter, to keep the same logic
	#	we create an array of parameters anyway
	#
	$parms = array();
	$parms[0] = $_INPUT['parms'];
}




if(!function_exists('wp_create_nonce')) {

	require_once( ABSPATH . 'wp-includes/pluggable.php');
}

$meb_backup_ajax_validator = $parms[(count($parms) - 1)];
$meb_backup_ajax_validator_key = dirname(__FILE__);

if(! wp_verify_nonce($meb_backup_ajax_validator, $meb_backup_ajax_validator_key)) {

	/**
	 * @since 1.0.11
	 */
	echo strip_tags($_POST['tag']) . $splitter_tag
		. '<div class="warning">'
			. __( 'There is an issue with the caller...', MEBAK_LOCALE )
//			. '<br>' . $meb_backup_ajax_validator . ' | ' . $meb_backup_ajax_validator_key . ' = ' . wp_verify_nonce($meb_backup_ajax_validator, $meb_backup_ajax_validator_key) // debug
		. '</div>';

	exit();
}

#
#	$parms
#
#	{n} = parameters
#
//define(AJAX_DEBUG, true);	#	uncomment to see debug code

$parms_string = '';
if(defined('AJAX_DEBUG') && AJAX_DEBUG==true)
{
	$t = count($parms);
	$parms_string = '<p class="todo">';
	for($i=0;$i<$t;$i++)
	{
		$parms_string .= '$parms['.$i.']:'.$parms[$i].'<br />';
	}
	$parms_string .= '</p>';
}
//die();

/**
 * @since 1.0.5.9 moved above
 */
//header('Cache-Control: no-cache, must-revalidate');
//header('Pragma: nocache');
//header('Expires: Fri, 31 Dec 1999 23:59:59 GMT');


echo $_INPUT['tag']		#	the tag id we are going to write to
	.$splitter_tag		#	splitter for the remaining output
	.$parms_string		#	filled only for debug purpose
;


#================================
#
#	its time to prepare some
#	output data...
#
#================================
switch($_INPUT['action'])
{
	#---------------------------
	case 'get_site_dirs_list':
	#---------------------------
		#
		#	get the list of dirs in a given folder
		#
		#	0: the given folder
		#
		if(!is_dir($parms[0])) {

			/**
			 * @since 0.9.2
			 */
			echo '<div class="warning"style="margin:0 0 8px 0;">'
					.__( 'The directory ', MEBAK_LOCALE )
					.' <span style="color:red;font-weight:bold;">' . $parms[0] . '</span> '
					.__( 'has been setup in the plugin options but its not present on the server anymore. Most probably it was deleted after the plugin option was saved.', MEBAK_LOCALE )
					.'<br />'.__( 'To let you proceed, I have resetted it to your root directory.', MEBAK_LOCALE )
				.'</div>'
			;
			$parms[0] = MEBAK_WP_PARENT_PATH;
		}

		$files = @scandir($parms[0]);           #   0.9.3; added @

		$tmp = str_replace('\\','/', $parms[0]);
		$tpath = explode('/',$tmp);
		$t = count($tpath) - 1;
		$up_folder = '';
		for($i=0;$i<$t;$i++)
		{
			$up_folder .= $tpath[$i] . '/';
		}
		$up_folder = substr($up_folder,0,-1);

		if(strlen($up_folder)==0)
		{
			$up_folder = MEBAK_WP_PARENT_PATH;
			if(substr($up_folder,-1)=='/')
			{
				$up_folder = substr($up_folder,0,-1);
			}
//echo '{resetted}';
		}

//$up_folder = '/uuu/wordpress-2.9.1';	#debug


		$folder_img = 'folder.png';
		if(file_exists($parms[0].'/wp-config.php'))
		{
			$folder_img = 'wordpress-folder.png';
		}

		if(is_writable($parms[0]))
		{
			$tmp = 	__( 'Is writable', MEBAK_LOCALE );
		}
		else
		{
			$tmp = 	__( 'Is', MEBAK_LOCALE )
					.' <span style="color:red;font-weight:bold;">' . __( 'NOT', MEBAK_LOCALE ) . '</span> '
					.__( 'writable', MEBAK_LOCALE );
		}

		clearstatcache();
		$perms = fileperms($parms[0]);

		echo ''
			//.MEBAK_WP_PARENT_PATH	# debug
			.'<div style="margin-bottom:8px;">'
				. __( 'This is the folder where your data sets will be saved. It will be saved in your settings once you click on the "Update Options" button at the end of the page.', MEBAK_LOCALE )

				. '<br /><img src="'.MYEASYBACKUP_LINK.'/img/'.$folder_img.'" align="absmiddle" />'
				. $tmp . ', '
				. __( 'permissions: ', MEBAK_LOCALE ) . format_permissions($perms)

			.'</div>'

			.'<div style="margin:-8px 0 0 8px;">'
				.'<b>' . $parms[0] . '</b><br />'
			.'</div>'

			.'<div style="background-color:#EAEAEA;border:1px solid #DFDFDF;width:90%;margin:8px;padding:12px;-moz-border-radius:6px;border-radius:6px;">'

				. '<p style="margin-top:0;">' . __( 'Please click on the folder icon here below to navigate one folder up; click on any folder name to enter into it.', MEBAK_LOCALE ) . '</p>'
				. '<p style="margin-top:0;">' . __( 'To save your time, there is a small dot in front of each directory name: if the dot is green, I can write in the directory &ndash; so it can be used for the plugin. If the dot is red I cannot write in the directory.', MEBAK_LOCALE ) . '</p>'

				.'<div style="float:left;">'
					.'<p style="margin:0;cursor:pointer;" onclick="javascript:'
									.'sndReq(\'get_site_dirs_list\',\'dirs_list_container\',\''.$up_folder . AJAX_PARMS_SPLITTER . $meb_backup_ajax_validator .'\');'
									.'">'
//. '{{{ back to: '.$up_folder.' }}}'
										. '<img src="'.MYEASYBACKUP_LINK.'/img/folder-up-off.png?'.$YMD.'" width="48px" '
												.'onmouseover="this.src=\'' . MYEASYBACKUP_LINK.'/img/folder-up.png?' . $YMD . '\';" '
												.'onmouseout="this.src=\'' . MYEASYBACKUP_LINK.'/img/folder-up-off.png' . '\';" '
												.'alt="'   . __( 'Move one folder up', MEBAK_LOCALE ) . '" '
												.'title="' . __( 'Move one folder up', MEBAK_LOCALE ) . '" />'
					. '</p>'
				.'</div>'
				.'<div style="float:left;margin-left:12px;">'
		;

		$t = 0;

		if(is_array($files)) {      #   0.9.2

			foreach($files as $fname) {
//echo '[*]'.$fname . '<br />';

				if($fname!='.' && $fname!='..'
					&& is_dir($parms[0] . '/' . $fname)
					//&& is_writable($_POST['meb_backup_root'] . $fname)
				) {

					/**
					 * @since 0.9.2
					 */
					if(is_writable($parms[0].'/'.$fname)) {
						$tmp = '<span style="color:green;text-shadow:0 0 2px #01BF00;font-size:16px;">&bull;</span>&nbsp;';
					}
					else {
						$tmp = '<span style="color:red;text-shadow:0 0 2px #FF6F6F;font-size:16px;">&bull;</span>&nbsp;';
					}

					echo '<p class="item_folder" style="margin:0;cursor:pointer;" onclick="javascript:'
										.'sndReq(\'get_site_dirs_list\',\'dirs_list_container\',\''.$parms[0].'/'.$fname . AJAX_PARMS_SPLITTER . $meb_backup_ajax_validator .'\');'
										.'">' . $tmp . $fname . '</p>';
					$t++;
				}
			}

			if($t==0) {

				_e( 'This folder does not contain any subfolder.', MEBAK_LOCALE );
			}
		}

		echo 	'</div>'
				.'<div style="clear:both;"></div>'
			.'</div>'

			.'<div style="background-color:#DFDFDF;border:1px solid #CFCFCF;margin:8px;float:left;width:90%;padding:12px;-moz-border-radius:6px;border-radius:6px;">'

				.'<input type="hidden" name="meb_backup_root" value="'.$parms[0].'" />'
				//.'<input type="hidden" name="openfolder" value="'.$parms[0].'" />'

				. '<p style="margin-top:0;">'
					. __( 'To add a new subfolder below the actually selected one, please enter its name in the field here below. Once the subfolder will be successfully created, it will be automatically selected.', MEBAK_LOCALE )
				. '</p>'

				.'<input id="new_folder" name="newfolder" type="text" value="' . strip_tags($_POST['newfolder']) . '" size="40" maxlength="128" />' // 1.0.11

				.'<div style="text-align:right;margin-top:8px;">'
					.'<input class="button-primary" style="margin-left:18px;" type="button" value="'
								. __( 'Create folder', MEBAK_LOCALE )
							.'" onclick="javascript:'
										.'if(document.getElementById(\'new_folder\').value!=\'\'){'
//.'alert(document.getElementById(\'new_folder\').value);'	#debug
											.'sndReq(\'create_dir_exec\',\'dirs_list_container_msgs\',\''.$parms[0].AJAX_PARMS_SPLITTER.'\'+document.getElementById(\'new_folder\').value+\''. AJAX_PARMS_SPLITTER . $meb_backup_ajax_validator .'\');'
										.'}else{'
											.'alert(\''.__( 'Please enter the name of the folder you want to create!', MEBAK_LOCALE ) . '\');'
										.'}'
						.';" />'
				.'</div>'

				.'<div id="dirs_list_container_msgs" style="clear:both;padding:6px;font-weight:bold;"></div>'

			.'</div>'
			.'<div style="clear:both;"></div>'
		;


		//echo $splitter_block
		//	.$js;

		exit();
		break;
		#
	#---------------------------
	case 'create_dir_exec':
	#---------------------------
		#
		#	0:	the parent folder
		#	1:	the folder to create
		#
		$js = '';

		if(file_exists($parms[0]) && is_dir($parms[0]))
		{
			chdir($parms[0]);

			if(!is_dir($parms[1]))
			{
				$result = @mkdir($parms[1]);

				if($result==true)
				{
					echo '<span style="color:green;">' . __( 'Done!', MEBAK_LOCALE ) . '</span>';

					$js = 'sndReq(\'get_site_dirs_list\',\'dirs_list_container\',\''.$parms[0].'/'.$parms[1] . AJAX_PARMS_SPLITTER . $meb_backup_ajax_validator .'\');';
				}
				else
				{
					echo '<span style="color:red;">'
							. __( 'Error! It was not possible to create the requested folder.', MEBAK_LOCALE )
						. '</span>';
				}
			}
			else
			{
					echo '<span style="color:red;">'
							. __( 'Error! The requested folder is already there.', MEBAK_LOCALE )
						. '</span>';
			}

		}

		if($js!='')
		{
			echo $splitter_block
				.$js;
		}

		exit();
		break;
		#
	#---------------------------
	case 'ftp_upload':
	#---------------------------
		#
		#	upload a data set by ftp
		#
		#	0:	password
		#	1:	filename to upload
		#	2:	upload restore tool?
		#	3:	upload path - @since 0.9.1
		#	4:	upload port - @since 0.9.1
		#	5:	timeout - @since 1.0.5.7
		#	6:	pasv - @since 1.0.5.7
		#
		$js = '';

		if(substr($parms[3],-1)=='/') {

			$remotePath = $parms[3];
		}
		else {

			$remotePath = $parms[3] . '/';
		}

		if((int)$parms[4]==0) {

			/**
			 * if the ftp server port is missing we use the default one
			 */
//			$parms[4] = 21;       // 1.0.5.7
			$parms[4] = FTP_PORT; // 1.0.5.7
		}

		if((int)$parms[5]==0) {

			$parms[5] = FTP_TIMEOUT;
		}

		if($parms[6]=='') {

			$parms[6] = FTP_PASSIVE;
		}

		$FTP_PORT    = $parms[4];
		$FTP_TIMEOUT = $parms[5];
		$FTP_PASSIVE = $parms[6];

		if((int)$FTP_PASSIVE==1) {

			$DESC_PASSIVE = 'On';
		}
		else {

			$DESC_PASSIVE = 'Off';
		}

		$hide_upload = ''
				.'<script type="text/javascript">'
//.'alert(\'bobo\');'
							.'document.getElementById(\'wait_upload\').style.display=\'none\';'
							.'document.body.style.cursor=\'default\';'
						.'</script>'
		;

		$settings = $hide_upload
				.'<div style="margin-top:20px;">'
				.'<input type="button" class="button-secondary" style="cursor:pointer;" value="'
						. __( 'Set the FTP credentials', MEBAK_LOCALE )
						. '" onclick="javascript:'
												.'document.getElementById(\'wait_progress\').innerHTML=\'\';'
												.'window.location=\''.'options-general.php?page=myEASYbackup_options#ftp_settings\';'
						.'" />'
			.'</div><br />'
		;

		$close = $hide_upload
				.'<input type="button" class="button-secondary" style="cursor:pointer;" '
						.'onclick="javascript:'
											.'document.body.style.cursor=\'default\';'
											.'document.getElementById(\'wait_progress\').innerHTML=\'\';'
											//.'document.getElementById(\'myeasybackup_popWin\').style.display=\'none\';'
											.'window.location=\''.'tools.php?page=myEASYbackup_tools\';'
								.'" '
						.'value="'
							. __( 'Close', MEBAK_LOCALE ) .'"'
					.' />'
		;

		if(!defined('FTP_SERVER') || strlen(FTP_SERVER)==0
				||
			!defined('FTP_USER_NAME') || strlen(FTP_USER_NAME)==0
			//	||
			//!defined('FTP_USER_PASS') || strlen(FTP_USER_PASS)==0
			//|| 1==1	#debug
		) {
			echo ''
					.'<h3 style="color:red;">'
						. __( 'Ooops...', MEBAK_LOCALE )
					. '</h3>'

					. __( 'It looks like you have not defined the FTP credentials!', MEBAK_LOCALE )
					.$settings
				.'</div>'
			;
			exit();
			break;
		}

		echo ''
			.'<div>'
			.'<p style="color:#ffffff;font-weight:bold;">'
				. __( 'FTP upload', MEBAK_LOCALE )
			.'</p>'
		;

		$source_file = MEBAK_BACKUP_PATH . '/' . $parms[1];
		$destination_file = $remotePath . $parms[1];

		#
		#	set up basic ftp connection
		#
//		$conn_id = @ftp_connect(FTP_SERVER, FTP_PORT);                        // 1.0.5.7
		$conn_id = @ftp_connect(FTP_SERVER, (string)$FTP_PORT, $FTP_TIMEOUT); // 1.0.5.7
		$login_result = '';

		if($conn_id
			//&& 1==2	#debug
		) {

			$login_result = @ftp_login($conn_id, FTP_USER_NAME, $parms[0]);
		}
		else {

			echo ''
					.'<h3 style="color:red;">'
					. __( 'FTP connection has failed!', MEBAK_LOCALE )
					. '</h3>'

					. '<p>' . __( 'Server', MEBAK_LOCALE )
						. ': <b>' . FTP_SERVER . '</b>'
					.'</p>'

					. '<p>' . __( 'User', MEBAK_LOCALE )
						. ': <b>' . FTP_USER_NAME . '</b>'
					.'</p>'

					. '<p>' . __( 'Port', MEBAK_LOCALE )
						. ': <b>' . $FTP_PORT . '</b>'
					.'</p>'

					. '<p>' . __( 'Timeout', MEBAK_LOCALE )
						. ': <b>' . $FTP_TIMEOUT . '</b>'
					.'</p>'

					. '<p>' . __( 'Passive mode', MEBAK_LOCALE )
						. ': <b>' . $DESC_PASSIVE . '</b>'
					.'</p>'

					.$close
				.'</div>'
			;
			exit();
			break;
		}

		//if((!$conn_id) || (!$login_result))
		if((!$login_result)) {

			echo ''
					.'<h3 style="color:red;">'
					. __( 'FTP login has failed!', MEBAK_LOCALE )
					. '</h3>'

					. '<p>' . __( 'Server', MEBAK_LOCALE )
						. ': <b>' . FTP_SERVER . '</b>'
					.'</p>'

					. '<p>' . __( 'User', MEBAK_LOCALE )
						. ': <b>' . FTP_USER_NAME . '</b>'
					.'</p>'

					. '<p>' . __( 'Port', MEBAK_LOCALE )
						. ': <b>' . $FTP_PORT . '</b>'
					.'</p>'

					. '<p>' . __( 'Timeout', MEBAK_LOCALE )
						. ': <b>' . $FTP_TIMEOUT . '</b>'
					.'</p>'

					. '<p>' . __( 'Passive mode', MEBAK_LOCALE )
						. ': <b>' . $DESC_PASSIVE . '</b>'
					.'</p>'

					.$close
				.'</div>'
			;
			exit();
			break;
		}

		if($conn_id) {

			#	upload the file
			#
			if($FTP_PASSIVE==1) {    // @since 1.0.5.7 pasv mode is optional

				@ftp_pasv($conn_id, true);
			}
			else {

				@ftp_pasv($conn_id, false);
			}

//echo 'conn_id:'.$conn_id;

			#
			#	0.0.7: BEG
			#-------------
			//$upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);

			$local_file_size = filesize($source_file);
			$started = time();
			//$upload = false;

			$fh = @fopen($source_file, 'r');
			$ret = @ftp_nb_fput($conn_id, $destination_file, $fh, FTP_BINARY);

//apache_setenv('no-gzip', 1);
//ini_set('output_buffering', 'off');
//ini_set('zlib.output_compression', 0);

			while($ret==FTP_MOREDATA) {

				$now = time();
				$transferred_size = ftell($fh);
				$percentage = (int)(($transferred_size/$local_file_size)*100);

				$passed = $now - $started;
				if($passed<1) { $passed = 1; }

				$transfer_rate = (int)($transferred_size/$passed/1024);

				$est = (($local_file_size-$transferred_size)/$transfer_rate/1000);

				$estimated = '';
				$time = false;
				if(date('H', $est)>0) {

					$estimated = '<b>' . ltrim(date('H', $est), '0') . '</b> ' . __( 'hours', MEBAK_LOCALE ) . ' ';
					$time = true;
				}

				if(date('i', $est)>0 || $time==true) {

					$estimated .= '<b>' . ltrim(date('i', $est), '0') . '</b> ' . __( 'minutes', MEBAK_LOCALE ) . ' ';
					$time = true;
				}
				//if(date('s', $est)>0 || $time==true) {

					$estimated .= '<b>' . (int)ltrim(date('s', $est), '0') . '</b> ' . __( 'seconds', MEBAK_LOCALE ) . ' ';
					$time = true;
				//}

//ob_end_flush();
//ob_flush();
//flush();
////echo '<script>window.status='.$percentage.';</script>';
//echo '<script>document.title='.$percentage.';</script>';
//ob_flush();
//flush();

//				echo '<script>document.getElementById(\'wait_progress\').innerHTML=\''
//
//						. '<b>' . $percentage . '</b>%'
//
//						. '<p>'
//							. __( 'estimated remaining time', MEBAK_LOCALE ) . ' ' . $estimated
//						. '</p>'
//
//						. '<p>'
//							. __( 'transfer rate', MEBAK_LOCALE ) . ' <b>' . $transfer_rate . ' kb/sec</b>'
//						. '</p>'
//
//					. '\';</script>'
//				;
//				flush();
//				ob_flush();

				$ret = @ftp_nb_continue($conn_id);

			}

			### if($ret==FTP_FINISHED) { $upload = true; }	/*---| this one sometimes gives you a wrong result and made me loose a lot of time... |---*/

			fclose($fh);

//			flush();
//			ob_flush();
//die();

			#
			#	get the data set size on the ftp server
			#
//			$conn_id = @ftp_connect(FTP_SERVER, FTP_PORT);
//			$login_result = @ftp_login($conn_id, FTP_USER_NAME, $parms[0]);

			clearstatcache();
			$remote_file_size = @ftp_size($conn_id, $destination_file);

			#
			#	if the data set size is the same we can consider the upload successfull
			#
			$upload = false;
			if($remote_file_size==$local_file_size) {

				$upload = true;
			}

			#
			#	upload the myeasyrestore tool on demand
			#
			if($upload==true) {

				if($parms[2]==1) {

//					echo ''
//							. '<p>'
//								. __( 'Uploading the myEASYrestore tool...', MEBAK_LOCALE )
//							. '</p>'
//
//					;
//					flush();
//					ob_flush();

					$source_tool_file = MEBAK_PATH . 'service/myEASYrestore';
					$destination_tool_file = $remotePath . 'myEASYrestore.php';

					$upload_tool = ftp_put($conn_id, $destination_tool_file, $source_tool_file, FTP_ASCII);
				}
			}

# 0.9.1: BEG
			#
			#	close the ftp stream
			#
//			ftp_close($conn_id);
			$localTool_file_size = filesize($source_tool_file);

			#
			#	get the data set size on the ftp server
			#
//			$conn_id = @ftp_connect(FTP_SERVER, FTP_PORT);
//			$login_result = @ftp_login($conn_id, FTP_USER_NAME, $parms[0]);

			clearstatcache();
			$remoteTool_file_size = @ftp_size($conn_id, $destination_tool_file);

			#
			#	close the ftp stream
			#
			@ftp_close($conn_id);

			#
			#	if the data set size is the same we can consider the upload successfull
			#
			$uploadTools = false;
			if($remoteTool_file_size==$localTool_file_size) {

				$uploadTools = true;
			}
# 0.9.1: END
		}

		#
		#	upload result
		#
		if(!$upload) {

			echo '<h3 style="color:red;">'
					. __( 'FTP upload has failed!', MEBAK_LOCALE )
				. '</h3>'
			;
		}
		else {

			echo '<h3 style="color:green;">'
				.__( 'Upload completed successfully!', MEBAK_LOCALE )
				.'</h3>'
				.'<p>'
					. __( 'FTP server', MEBAK_LOCALE )
					. ': <b>' . FTP_SERVER . '</b>'
					. '<br />'
					. __( 'Port', MEBAK_LOCALE ) . ': <b>' . $FTP_PORT . '</b>, '
					. __( 'timeout', MEBAK_LOCALE ) . ': <b>' . $FTP_TIMEOUT . '</b>, '
					. __( 'passive mode', MEBAK_LOCALE ) . ': <b>' . $DESC_PASSIVE . '</b>'
				.'</p>'

				.'<p>'
					. __( 'File on this server', MEBAK_LOCALE )
					. ': <b>' . $source_file . '</b>'
				.'</p>'
				.'<p>'
					. __( 'Data set size on this server:', MEBAK_LOCALE ) . ' <b>' . number_format($local_file_size, 0) . '</b>'
				.'</p>'
				.'<p>'
					. __( 'File on the remote server', MEBAK_LOCALE )
					. ': <b>' . $destination_file . '</b>'
				.'</p>'
				.'<p>'
					.__( 'Data set size on ', MEBAK_LOCALE ) . FTP_SERVER . ': <b>' . number_format($remote_file_size, 0) . '</b>'
				.'</p>'
			;
# 0.9.1: BEG
			if($parms[2]==1) {

				if($uploadTools==true) {

					echo '<p>'
							. __( 'Tools file on this server', MEBAK_LOCALE )
							. ': <b>' . $source_tool_file . '</b>'
						.'</p>'
						.'<p>'
							. __( 'Tools size on this server:', MEBAK_LOCALE ) . ' <b>' . number_format($localTool_file_size, 0) . '</b>'
						.'</p>'
						.'<p>'
							. __( 'Tools file on the remote server', MEBAK_LOCALE )
							. ': <b>' . $destination_tool_file . '</b>'
						.'</p>'
						.'<p>'
							.__( 'Tools size on ', MEBAK_LOCALE ) . FTP_SERVER . ': <b>' . number_format($remoteTool_file_size, 0) . '</b>'
						.'</p>'
					;
				}
				else {

					echo '<h3 style="color:red;">'
							. __( 'FTP tools upload has failed!', MEBAK_LOCALE )
						. '</h3>'
					;
				}
			}
			else {

				echo '<h3>'
						. __( 'You decided NOT to upload the myEASYrestore tool.', MEBAK_LOCALE )
					. '</h3>'
				;
			}

//			if($parms[2]==1)
//			{
//				if(!$upload_tool)
//				{
//					echo '<h3 style="color:red;">'
//							. __( 'Unable to upload the myEASYrestore tool!', MEBAK_LOCALE )
//						. '</h3>'
//					;
//				}
//				else
//				{
//					echo '<h3 style="color:green;">'
//							. __( 'myEASYrestore tool successfully uploaded!', MEBAK_LOCALE )
//						. '</h3>'
//					;
//				}
//			}
//			else
//			{
//				echo '<h3>'
//						. __( 'You decided NOT to upload the myEASYrestore tool.', MEBAK_LOCALE )
//					. '</h3>'
//				;
//			}
# 0.9.1: END

		}

		echo $close
			.'</div>'
				//.'<script type="text/javascript">document.getElementById(\'wait_upload\').style.display=\'none\';document.body.style.cursor=\'default\';</script>'
				//.'<input type="button" class="button-secondary" style="cursor:pointer;" onclick="javascript:document.body.style.cursor=\'default\';document.getElementById(\'wait_progress\').innerHTML=\'\';document.getElementById(\'myeasybackup_popWin\').style.display=\'none\';" value="'
				//		. __( 'Close', MEBAK_LOCALE )
				//	.'" />'
		;

		$js = 'document.getElementById(\'wait_upload\').style.display=\'none\';';

		echo $splitter_block
			.$js;

		exit();
		break;
		#
	#---------------------------
	case 'ftp_check_connection':
	#---------------------------
		#
		#	check the ftp connection - @since 0.9.1
		#
		#	0:	password
		#	1:	server
		#	2:	user
		#	3:	remote path
		#	4:	port
		#	5:	check contents
		#	6:	timeout - @since 1.0.5.7
		#	7:	pasv - @since 1.0.5.7
		#
		$js = '';

		$ftpPassword = $parms[0];
		$ftpServer = $parms[1];
		$ftpUser = $parms[2];
		$ftpPath = $parms[3];
		$ftpPort = $parms[4];
		$ftpContents = (int)$parms[5];

		if((int)$parms[6]==0) {

			$parms[6] = FTP_TIMEOUT;
		}

		if($parms[7]=='') {

			$parms[7] = FTP_PASSIVE;
		}

		$FTP_TIMEOUT = $parms[6];
		$FTP_PASSIVE = $parms[7];

		if((int)$FTP_PASSIVE==1) {

			$DESC_PASSIVE = 'On';
		}
		else {

			$DESC_PASSIVE = 'Off';
		}


		/**
		 * validation
		 */
		if($ftpServer=='') {

			echo '<div class="warning" style="margin:0;text-align:center;">' . __( 'Missing the FTP server name', MEBAK_LOCALE ) . '</div>';
			exit();
		}

		if((int)$ftpPort==0) {

			/**
			 * if the ftp server port is missing we use the default one
			 */
			$ftpPort = FTP_PORT;
		}

		if($ftpUser=='') {

			echo '<div class="warning" style="margin:0;text-align:center;">' . __( 'Missing the FTP user name', MEBAK_LOCALE ) . '</div>';
			exit();
		}

		if($ftpPassword=='') {

			echo '<div class="warning" style="margin:0;text-align:center;">' . __( 'Missing the FTP password', MEBAK_LOCALE ) . '</div>';
			exit();
		}

//		if(substr($ftpPath,-1)=='/') { $remotePath = $ftpPath; } else { $remotePath = $ftpPath . '/'; }
//		if(substr($ftpPath,0,1)!='/') { $remotePath = '/' . $ftpPath; }

		$remotePath = $ftpPath;

//		$conn_id = @ftp_connect($ftpServer, (string)$ftpPort);               // 1.0.5.7
		$conn_id = @ftp_connect($ftpServer, (string)$ftpPort, $FTP_TIMEOUT); // 1.0.5.7
		if(!$conn_id) {

			echo '<div class="warning" style="margin:0;text-align:center;">'
					. __( 'The FTP <b>connection</b> has failed, please check the <b>server name</b> and the following <b>connection parameters</b>:', MEBAK_LOCALE )

					. '<br />' . __( 'port', MEBAK_LOCALE )
						. ': <b>' . $ftpPort . '</b>, '

					. __( 'timeout', MEBAK_LOCALE )
						. ': <b>' . $FTP_TIMEOUT . '</b>, '

					. __( 'passive mode', MEBAK_LOCALE )
						. ': <b>' . $DESC_PASSIVE . '</b>'

				.'</div>';
			exit();
		}
		else {

			$login_result = @ftp_login($conn_id, $ftpUser, $ftpPassword);
			if(!$login_result) {

				echo '<div class="warning" style="margin:0;text-align:center;">' . __( 'The FTP <b>login</b> has failed: please check the <b>user name</b>, also be sure to enter the correct <b>password</b>.', MEBAK_LOCALE ) . '</div>';
				@ftp_close($conn_id);
				exit();
			}
		}

		@ftp_pasv($conn_id, true);

//		$contents = @ftp_nlist($conn_id, $remotePath);
		$contents = @ftp_rawlist($conn_id, $remotePath, false);

		@ftp_close($conn_id);

//var_dump($contents);

		if($contents===false || count($contents)==0) {

			$green = '#58af57';
			$path = ', ' . __( 'however I was not able to find the remote path.<br />Be sure to create it before uploading your data sets.', MEBAK_LOCALE )
					. '<br />' . __( 'Note: on some servers if the remote path is empty it can be reported as "non existing"; this is due to the FTP server software running on the remote server.', MEBAK_LOCALE )
			;
		}
		else {

			$green = '#008000';
			$path = __( ' and the remote path is there!', MEBAK_LOCALE );
		}

		echo '<div class="warning" style="clear:both;margin:0;text-align:center;font-weight:bold;color:#ffffff;background-color:'.$green.';border-color:'.$green.';">'
				. __( 'The FTP connection is OK ', MEBAK_LOCALE )

				. __( '(connection parameters: port', MEBAK_LOCALE )
					. ': <b>' . $ftpPort . '</b>, '

				. __( 'timeout', MEBAK_LOCALE )
					. ': <b>' . $FTP_TIMEOUT . '</b>, '

				. __( 'passive mode', MEBAK_LOCALE )
					. ': <b>' . $DESC_PASSIVE . '</b>) '

				.$path
			.'</div>'
		;

		if($ftpContents==1) {

			echo '<div class="warning" style="clear:both;margin:8px;text-align:left;color:#000000;background-color:#ffffff;border:1px solid #777777;">'
					.'<i>'.__( 'Contents for', MEBAK_LOCALE )
					.':<br />'
					.'<b>' . $remotePath . '</b></i><br />'
			;

			if(is_array($contents)) {

				echo '<span style="font-family:monospace;">';
				foreach($contents as $entry) {

					if($entry!='.' && $entry!='..') {

						echo $entry . '<br />';
					}
				}
				echo '</span>';
			}
			else {

				echo '<span style="color:red;font-weight:bold;">'
						. __( 'Cannot find any content on the remote FTP server for the required path.', MEBAK_LOCALE )
					.'</span>';
			}

			echo '</div>';
		}

		exit();
		break;
		#
	#---------------------------
	case 'meb_ally_ftp_key':
	#---------------------------
		#
		#	generate & store the myEASYbackup key - @since 0.9.1
		#
		#	0:	private key
		#	1:	public key
		#
		$tmp = get_option( 'meb_ally_auth_key' );
		$meb_ally_auth_key = md5($parms[1].$parms[0]);

		$checkme = $parms[1] . $meb_ally_auth_key;

//$tmp = '';//debug
//delete_option('meb_ally_auth_key');//debug

		if($tmp!=$meb_ally_auth_key) {

			/**
			 * authenticate the key
			 */
//			$result = file_get_contents('https://services.myeasywp.com/index.php?page=auth-mebkey&' . $checkme);     //  1.0.5.6
			$result = measycom_get_response('services.myeasywp.com', '/index.php?page=auth-mebkey&' . $checkme, 443);//  1.0.5.6

			if($result==$parms[1]) {

				$result = update_option( 'meb_ally_auth_key', $meb_ally_auth_key );

				if($result==true) {

					echo '<div class="warning" style="margin:8px 0 0 0;text-align:center;font-weight:bold;color:#ffffff;background-color:#008000;border-color:#008000;">'
							. __( 'The myEASYbackup KEY was successfully generated.', MEBAK_LOCALE ) . '</div>';
				}
				else {

					echo '<div class="warning" style="margin:8px 0 0 0;text-align:center;">'
							. __( 'I was not able to save the myEASYbackup KEY.', MEBAK_LOCALE ) . '</div>';
				}
			}
			else {

				echo '<div class="warning" style="margin:8px 0 0 0;text-align:center;">'
						. __( 'I was not able to authenticate your myEASYwebally API keys.', MEBAK_LOCALE )
						. '<br />' . __( 'Are you sure you entered the right ones?', MEBAK_LOCALE ) . '</div>';
			}
		}
		else {

			echo '<div class="warning" style="margin:8px 0 0 0;text-align:center;font-weight:bold;color:#ffffff;background-color:#008000;border-color:#008000;">'
					. __( 'The myEASYbackup KEY is already on file.', MEBAK_LOCALE )
//					. ' (' . strlen($result) . ')'
					. '</div>';
		}

		exit();
		break;
		#
	#---------------------------
	case 'ftp_dressing_upload':
	#---------------------------
		#
		#	upload a data set by ftp to the dressing server - @since 0.9.1
		#
		#	0:	filename to upload
		#	1:	upload the restore tool?
		#	2:	subdomain id
		#	3:	subdomain name
		#
		$hide_upload = ''
				.'<script type="text/javascript">'
							.'document.getElementById(\'wait_upload\').style.display=\'none\';'
							.'document.body.style.cursor=\'default\';'
						.'</script>'
		;

		$settings = $hide_upload
				.'<div style="margin-top:20px;">'
				.'<input type="button" class="button-secondary" style="cursor:pointer;" value="'
						. __( 'Set the FTP credentials', MEBAK_LOCALE )
						. '" onclick="javascript:'
												.'document.getElementById(\'wait_progress\').innerHTML=\'\';'
												.'window.location=\''.'options-general.php?page=myEASYbackup_options#ftp_settings\';'
						.'" />'
			.'</div><br />'
		;

		$close = $hide_upload
				.'<input type="button" class="button-secondary" style="cursor:pointer;" '
						.'onclick="javascript:'
											.'document.body.style.cursor=\'default\';'
											.'document.getElementById(\'wait_progress\').innerHTML=\'\';'
											//.'document.getElementById(\'myeasybackup_popWin\').style.display=\'none\';'
											.'window.location=\''.'tools.php?page=myEASYbackup_tools\';'
								.'" '
						.'value="'
							. __( 'Close', MEBAK_LOCALE ) .'"'
					.' />'
		;

		$meb_ally_request = get_option( 'meb_ally_auth_key' ) . get_option( 'myewally_userKey' );

		if(strlen($meb_ally_request)>0) {

			/**
			 * get the infos
			 */
//			$result = file_get_contents('https://services.myeasywp.com/index.php?page=dressing-get-ftp&' . $meb_ally_request . $parms[2]);     //  1.0.5.6
			$result = measycom_get_response('services.myeasywp.com', '/index.php?page=dressing-get-ftp&' . $meb_ally_request . $parms[2], 443);//  1.0.5.6
			$info = unserialize($result);

//var_dump($info);echo '<hr>';
//echo '$result = '.$result.'<br>';
/*
array(1) { [0]=>  array(4) {
 * ["us_RRN"]=>  string(2) "22"
 * ["subdomain"]=>  string(6) "dwp301"
 * ["ftpUser"]=>  string(10) "ftpuDWP301"
 * ["ftpPwd"]=>  string(10) "ftppDWP301" }
 * }
*/
			/**
			 * check the info
			 */
			if((int)$info[0]['us_RRN']==(int)$parms[2] && $info[0]['subdomain']==$parms[3]) {

				/**
				 * do the upload
				 */
/*************************************************/

				echo '<div>';

				$FTP_SERVER = $info[0]['subdomain'] . '.dr.myeasywp.com';
				$FTP_PORT = (int)$info[0]['ftpPort'];

				$FTP_USER_NAME = $info[0]['ftpUser'];
				$FTP_PWD = $info[0]['ftpPwd'];

				$remotePath = $info[0]['lnxUser'] . '/';
				$source_file = MEBAK_BACKUP_PATH . '/' . $parms[0];
				$destination_file = $remotePath . $parms[0];

$start = time();

				#
				#	set up basic ftp connection
				#
				$conn_id = @ftp_connect($FTP_SERVER, $FTP_PORT);
				$login_result = '';

				if($conn_id
					//&& 1==2	#debug
				) {
					$login_result = @ftp_login($conn_id, $FTP_USER_NAME, $FTP_PWD);
				}
				else
				{
					echo ''
							.'<h3 style="color:red;">'
							. __( 'FTP connection has failed!', MEBAK_LOCALE )
							. '</h3>'

							. '<p>' . __( 'Server', MEBAK_LOCALE )
								. ': <b>' . $FTP_SERVER . '</b>'
							.'</p>'

							.$close
						.'</div>'
					;
					exit();
					break;
				}

				if((!$login_result))
				{
					echo ''
							.'<h3 style="color:red;">'
							. __( 'FTP login has failed!', MEBAK_LOCALE )
							. '</h3>'

							. '<p>' . __( 'Server', MEBAK_LOCALE )
								. ': <b>' . $FTP_SERVER . '</b>'
							.'</p>'

							.$close
						.'</div>'
					;
					exit();
					break;
				}

				if($conn_id)
				{
					#	upload the file
					#
					@ftp_pasv($conn_id, true);

//echo 'conn_id:'.$conn_id;

					$local_file_size = filesize($source_file);
					$started = time();

					$fh = @fopen($source_file, 'r');
					$ret = @ftp_nb_fput($conn_id, $destination_file, $fh, FTP_BINARY);

					while($ret==FTP_MOREDATA)
					{
						$ret = @ftp_nb_continue($conn_id);
					}

					@fclose($fh);

					#
					#	get the data set size on the ftp server
					#
//					$conn_id = @ftp_connect($FTP_SERVER, $FTP_PORT);
//					$login_result = @ftp_login($conn_id, $FTP_USER_NAME, $FTP_PWD);

					clearstatcache();
					$remote_file_size = @ftp_size($conn_id, $destination_file);

					#
					#	if the data set size is the same we can consider the upload successfull
					#
					$upload = false;
					if($remote_file_size==$local_file_size)
					{
						$upload = true;
					}

					#
					#	upload the myeasyrestore tool on demand
					#
					if($upload==true)
					{
						if($parms[1]==1)
						{
							$source_tool_file = MEBAK_PATH . 'service/myEASYrestore';
							$destination_tool_file = $remotePath . 'myEASYrestore.php';

							$upload_tool = @ftp_put($conn_id, $destination_tool_file, $source_tool_file, FTP_ASCII);
						}
					}

					#
					#	close the ftp stream
					#
//					@ftp_close($conn_id);
					$localTool_file_size = filesize($source_tool_file);

					#
					#	get the data set size on the ftp server
					#
//					$conn_id = @ftp_connect($FTP_SERVER, $FTP_PORT);
//					$login_result = @ftp_login($conn_id, $FTP_USER_NAME, $FTP_PWD);

					clearstatcache();
					$remoteTool_file_size = @ftp_size($conn_id, $destination_tool_file);

					#
					#	close the ftp stream
					#
					@ftp_close($conn_id);

					#
					#	if the data set size is the same we can consider the upload successfull
					#
					$uploadTools = false;
					if($remoteTool_file_size==$localTool_file_size)
					{
						$uploadTools = true;
					}
				}

$end = time();
//echo 'Upload time ' . ($end - $start) . 'secs.';

				#
				#	upload result
				#
				if(!$upload)
				{
					echo '<h3 style="color:red;">'
							. __( 'FTP upload has failed!', MEBAK_LOCALE )
						. '</h3>'
					;
				}
				else
				{
					echo '<h3 style="color:green;">'
						.__( 'Upload completed successfully!', MEBAK_LOCALE )
						.'</h3>'
						.'<p>'
							. __( 'FTP server', MEBAK_LOCALE )
							. ': <b>' . $FTP_SERVER . '</b>'
						.'</p>'
						.'<p>'
							. __( 'File on this server', MEBAK_LOCALE )
							. ': <b>' . $source_file . '</b>'
						.'</p>'
						.'<p>'
							. __( 'Data set size on this server:', MEBAK_LOCALE ) . ' <b>' . number_format($local_file_size, 0) . '</b>'
						.'</p>'
						.'<p>'
							. __( 'File on the remote server', MEBAK_LOCALE )
							. ': <b>' . $destination_file . '</b>'
						.'</p>'
						.'<p>'
							.__( 'Data set size on ', MEBAK_LOCALE ) . $FTP_SERVER . ': <b>' . number_format($remote_file_size, 0) . '</b>'
						.'</p>'
					;

					if($parms[1]==1) {

						if($uploadTools==true) {

							echo '<p>'
									. __( 'Tools file on this server', MEBAK_LOCALE )
									. ': <b>' . $source_tool_file . '</b>'
								.'</p>'
								.'<p>'
									. __( 'Tools size on this server:', MEBAK_LOCALE ) . ' <b>' . number_format($localTool_file_size, 0) . '</b>'
								.'</p>'
								.'<p>'
									. __( 'Tools file on the remote server', MEBAK_LOCALE )
									. ': <b>' . $destination_tool_file . '</b>'
								.'</p>'
								.'<p>'
									.__( 'Tools size on ', MEBAK_LOCALE ) . $FTP_SERVER . ': <b>' . number_format($remoteTool_file_size, 0) . '</b>'
								.'</p>'
							;
						}
						else {

							echo '<h3 style="color:red;">'
									. __( 'FTP tools upload has failed!', MEBAK_LOCALE )
								. '</h3>'
							;
						}
					}
					else
					{
						echo '<h3>'
								. __( 'You decided NOT to upload the myEASYrestore tool.', MEBAK_LOCALE )
							. '</h3>'
						;
					}
				}

				echo $close
					.'</div>'
						//.'<script type="text/javascript">document.getElementById(\'wait_upload\').style.display=\'none\';document.body.style.cursor=\'default\';</script>'
						//.'<input type="button" class="button-secondary" style="cursor:pointer;" onclick="javascript:document.body.style.cursor=\'default\';document.getElementById(\'wait_progress\').innerHTML=\'\';document.getElementById(\'myeasybackup_popWin\').style.display=\'none\';" value="'
						//		. __( 'Close', MEBAK_LOCALE )
						//	.'" />'
				;

/*************************************************/
			}
			else {

				echo '<div class="warning" style="margin:8px 0 0 0;text-align:center;">'
						. __( 'I was not able to get the needed information from the remote server.', MEBAK_LOCALE )
						. '<br /><a href="http://myeasywp.com/contact">' . __( 'Please get in touch', MEBAK_LOCALE ) . '</a>'
						. ' ' . __( 'to help us discover and fix the problem: thank you!', MEBAK_LOCALE ) . '</div>';
				exit();
				break;
			}


		}
		else {

			/**
			 * missing one or more option(s)
			 */
		}

		$js = 'document.getElementById(\'wait_upload\').style.display=\'none\';';

		echo $splitter_block
			.$js;

		exit();
		break;
		#
	#---------------------------
	case 'schedule_backup':
	#---------------------------
//		if(MYEASYBACKUP_FAMILY>9 && file_exists(MEBAK_LITE_PATH.'lite-cron-ajax.php')) {
		if(MYEASYBACKUP_FAMILY=='LITE') {

			include_once(MEBAK_LITE_PATH.'lite-cron-ajax.php');
		}
		else {

			echo '<p style="color:red;font-weight:bold;">' . __( 'Error! The LITE module is missing.', MEBAK_LOCALE ) . '</p>';
		}

		if($js!='') {

			echo $splitter_block
				.$js;
		}

		exit();
		break;
		#
	#---------------------------
	default:
	#---------------------------
		echo '<fieldset style="color:#000000;background:#ffffff;margin:0px;padding:6px;font-family:monospace;font-size:12px;">'
					.'<div align="center">'
						.'<img src="'.MYEASYBACKUP_LINK.'img/warning.png" border="0" alt="WARNING!" /><br />'
						.'Missing AJAX command...<br />'
		;
		$err = '';
		foreach($_INPUT as $key=>$val)
		{
			$err .= $key.'=>'.$val.', ';
		}
		echo substr($err,0,-2)
			.'</div>'
			.'<br /></fieldset>'
		;
}

?>