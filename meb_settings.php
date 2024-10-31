<style type="text/css" media="all">
div.left { width:45%; }
div.right { width:49%; }
</style><?php
/**
 * Plugin Settings
 *
 * @package myEASYbackup
 * @author Ugo Grandolini
 * @since 0.0.5
 * @version 1.0.5.3
 *
 */

#	0.1.4: moved to the contextual help
//if(defined('mebak_DEBUG') && mebak_DEBUG==true)
//{
//	require_once(MEBAK_PATH . 'inc/myDEBUG.php');
//}

/**
 * we need to be sure to get proper data before saving it
 * @since 1.0.2
 */
$OK2SAVE = true;
$OK2SAVEmsg = ''; // 1.0.5

/**
 * show the "setting contents" section if there is an error
 * @since 1.0.5
 */
$settings_contents_div_display = 'none';


/**
 * check if the WordPress administration path is ok
 * @since 1.0.5
 */
//echo 'meb_wpadmin_path['.($_POST['meb_wpadmin_path'] ? $_POST['meb_wpadmin_path'] : MEBAK_WPADMIN_PATH ).']<br>';
if(!isset($_POST['meb_wpadmin_path'])) {

	$_POST['meb_wpadmin_path'] = MEBAK_WPADMIN_PATH;
}
$_POST['meb_wpadmin_path'] = trim($_POST['meb_wpadmin_path']);


//$meh_wpadmin_path = ABSPATH . $_POST['meb_wpadmin_path'];                 # 1.0.5
$meh_wpadmin_path = $_SERVER['DOCUMENT_ROOT'] . $_POST['meb_wpadmin_path']; # 1.0.5.3
//echo '$meh_wpadmin_path['.$meh_wpadmin_path.']<br>';

if(!is_dir($meh_wpadmin_path)) {

	$OK2SAVE = false;
	$OK2SAVEmsg .= __('The WordPress administration path:', MEBAK_LOCALE )
					.' <code>'.ABSPATH . $_POST['meb_wpadmin_path'].'</code> '
					.__('does not exists on the server.', MEBAK_LOCALE )
					.'<br />'
	;
}
//echo '<code>'.ABSPATH . $_POST['meb_wpadmin_path'].'</code> ';


if(defined('MEBAK_MISSING_UPLOAD_PATH_MSG')) {

	/**
	 * @since 1.0.5.9
	 */
	$OK2SAVE = false;
	$OK2SAVEmsg .= constant('MEBAK_MISSING_UPLOAD_PATH_MSG')
					.'<br />'
	;
}


/**
 * check if the mysqldump command is ok
 * @since 1.0.2
 */
if(!isset($_POST['meb_mysqldump_path'])) {

	$_POST['meb_mysqldump_path'] = str_replace('mysqldump','', PATH_MYSQLDUMP);
}
$_POST['meb_mysqldump_path'] = trim($_POST['meb_mysqldump_path']);

if((int)$_POST['meb_force_phpcode']==0) {

	/**
	 * check only if PHP is not forced to be executed
	 */
	$_POST['meb_mysqldump_path'] = trim(str_replace('mysqldump','', $_POST['meb_mysqldump_path']));

	if(substr($_POST['meb_mysqldump_path'],-1,1)=='/') {

		$_POST['meb_mysqldump_path'] = substr($_POST['meb_mysqldump_path'],0,-1);
	}

	$disabled = ini_get('disable_functions');   # 1.0.4
	if(strpos($disabled, 'exec', 0)===false     # 1.0.4
			&& is_dir(MEBAK_UPLOAD_PATH) && is_writable(MEBAK_UPLOAD_PATH)) {  # 1.0.5.9

		$result = 1;

//		@exec( $_POST['meb_mysqldump_path'] . '/mysqldump --help', $output, $result );

		$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );

		$cmd = $_POST['meb_mysqldump_path'] . '/mysqldump --help 1> ' . escapeshellarg($tmpfile) .' 2>&1';
		@system( $cmd, $result );

		$cmd_output = file_get_contents($tmpfile);
		unlink($tmpfile);

//echo '['.$tmpfile.']<hr>'.$tmpfile.'<hr>'.$cmd.'<hr>'.$result.'<hr>'.$cmd_output;

		if($result>0) {

			$OK2SAVE = false;
			$OK2SAVEmsg .= __('The path to mysqldump:', MEBAK_LOCALE )
							.' <code>'.$_POST['meb_mysqldump_path'] . '/mysqldump'.'</code> '
							. __('is wrong.', MEBAK_LOCALE )
							.'<br />'
			;
		}
	}
}

if($OK2SAVE == false) {

	$OK2SAVEmsg .= '<p><strong>'. __('It is not possible to proceed until you fix the above problems!', MEBAK_LOCALE ) . '</strong></p>';

	echo '<div class="error">' . $OK2SAVEmsg . '</div>';

	$settings_contents_div_display = 'block';
}

if(strlen(get_option('meb_backup_root'))==0) {

	$settings_contents_div_display = 'block';
}




//if($OK2SAVE == true) {       # 1.0.2

	switch($_POST['btn']) {

		#----------------
		case SAVE_BTN:
		#----------------
			#
			#	save the posted value in the database
			#
			if(isset($_POST['meb_isPRODUCTION'])) {

				update_option( 'meb_isPRODUCTION', 1 );
			}
			else {

				update_option( 'meb_isPRODUCTION', 0 );
			}

			if(isset($_POST['meb_isDEBUG'])) {

				update_option( 'meb_isDEBUG', 1 );
			}
			else {

				update_option( 'meb_isDEBUG', 0 );
			}

			if(isset($_POST['meb_compression'])) {

				update_option( 'meb_compression', $_POST['meb_compression'] );
			}
			else {

				update_option( 'meb_compression', 6 );
			}

			if(isset($_POST['meb_ftp_server'])) {

				update_option( 'meb_ftp_server', $_POST['meb_ftp_server'] );
			}

			if(isset($_POST['meb_ftp_user_name'])) {

				update_option( 'meb_ftp_user_name', $_POST['meb_ftp_user_name'] );
			}

			if(isset($_POST['meb_ftp_remote_path'])) {

				#   0.9.1
				update_option( 'meb_ftp_remote_path', $_POST['meb_ftp_remote_path'] );
			}

			if(isset($_POST['meb_ftp_port'])) {

				#   0.9.1
				update_option( 'meb_ftp_port', $_POST['meb_ftp_port'] );
			}

			if(isset($_POST['meb_ftp_timeout'])) {

				#   1.0.5.6
				update_option( 'meb_ftp_timeout', $_POST['meb_ftp_timeout'] );
			}

			if(isset($_POST['meb_ftp_pasv'])) {

				#   1.0.5.6
				update_option( 'meb_ftp_pasv', $_POST['meb_ftp_pasv'] );
			}

			//if(isset($_POST['meb_ftp_user_pass']))
			//{
			//	update_option( 'meb_ftp_user_pass', $_POST['meb_ftp_user_pass'] );
			//}

			if(isset($_POST['meb_force_phpcode'])) {

				update_option( 'meb_force_phpcode', 1 );
			}
			else {

				update_option( 'meb_force_phpcode', 0 );
			}

			if(isset($_POST['meb_zip_pass'])) {

				#	0.1.3
				update_option( 'meb_zip_pass', 1 );
			}
			else {

				update_option( 'meb_zip_pass', 0 );
			}

			if(isset($_POST['meb_zip_verbose'])) {

				#	0.1.4
				update_option( 'meb_zip_verbose', 1 );
			}
			else {

				update_option( 'meb_zip_verbose', 0 );
			}

			if(isset($_POST['meb_sys_archiving_tool'])) {

				#	0.1.4
				update_option( 'meb_sys_archiving_tool', $_POST['meb_sys_archiving_tool'] );
			}

			if(isset($_POST['meb_tar_compress'])) {

				#	0.1.4
				update_option( 'meb_tar_compress', 1 );
			}
			else {

				update_option( 'meb_tar_compress', 0 );
			}

			if(isset($_POST['meb_backup_root'])) {

				#	0.1.1
				update_option( 'meb_backup_root', $_POST['meb_backup_root'] );
			}

			if(isset($_POST['meb_donation_code'])) {

				#	0.1.1
				update_option( 'meb_donation_code', $_POST['meb_donation_code'] );
			}

//			if(isset($_POST['meb_php_ram'])) {
//
//				#	0.1.4
//				update_option( 'meb_php_ram', $_POST['meb_php_ram'] );
//			}
			#	1.0.5.9
			update_option( 'meb_php_ram', (int)$_POST['meb_php_ram'] );

#
#	0.9.1: BEG
#-------------
			if(isset($_POST['meb_email_backup'])) {

				update_option( 'meb_email_backup', $_POST['meb_email_backup'] );
			}

			if(isset($_POST['meb_email_backup_remove'])) {

				update_option( 'meb_email_backup_remove', $_POST['meb_email_backup_remove'] );
			}
			else {

				update_option( 'meb_email_backup_remove', 0 );
			}

			if(isset($_POST['myeasy_showcredits'])) {

				update_option( 'myeasy_showcredits', 1 );
			}
			else {

				update_option( 'myeasy_showcredits', 0 );
			}

			if(isset($_POST['meb_ally_pubkey']) && isset($_POST['meb_ally_privkey'])) {

				update_option( 'myewally_userKey', $_POST['meb_ally_pubkey'] );
			}

			if(isset($_POST['meb_remove_oldds'])) {

				update_option( 'meb_remove_oldds', (int)$_POST['meb_remove_oldds'] );
			}
#-------------
#	0.9.1: END
#

//echo'(1save) '.$_POST['meb_wpadmin_path'].'<br>';

			if(isset($_POST['meb_wpadmin_path'])) {

//echo'(2save) '.$_POST['meb_wpadmin_path'].'<br>';
				/**
				 * @since 1.0.2
				 */
//die();
				update_option( 'meb_wpadmin_path', trim($_POST['meb_wpadmin_path']));
			}

			if(isset($_POST['meb_mysqldump_path'])) {

				/**
				 * @since 1.0.2
				 */
				update_option( 'meb_mysqldump_path', trim($_POST['meb_mysqldump_path']));
			}

//echo $_POST['meb_mysqldump_path'];die();

			if(isset($_POST['meb_exclude_folder_deep'])) {

				/**
				 * @since 1.0.5.5
				 */
				update_option( 'meb_exclude_folder_deep', (int)$_POST['meb_exclude_folder_deep']);
			}

			if(isset($_POST['meb_exclude_folder'])) {

				/**
				 * @since 1.0.5.5
				 */
				update_option( 'meb_exclude_folder', serialize($_POST['meb_exclude_folder']));
			}

#######################


//			if(MYEASYBACKUP_FAMILY>9 && file_exists(MEBAK_LITE_PATH.'lite-cron-setup.php')) {
			if(defined('MYEASYBACKUP_FAMILY') && constant('MYEASYBACKUP_FAMILY')==='LITE') {

				/**
				 *	Schedule backups
				 *	LITE version
				 *	@since 0.1.4
				 */
				$when = $_POST['wp_cron_schedule'];

				if($when!='never') {

					//$time = strtotime(strval($_POST['backup-time']));
					//if(!empty($time) && time()<$time)
					//{
					//	wp_clear_scheduled_hook(MEB_PRE_CRON_MAIN.'_cron');	#	unschedule previous
					//	$scheds = (array) wp_get_schedules();
					//	$name = get_option('wp_cron_backup_schedule');
					//	if(0!=$time)
					//	{
					//		wp_schedule_event($time, $name, MEB_PRE_CRON_MAIN.'_cron');
					//		echo gmdate(get_option('date_format') . ' ' . get_option('time_format'), $time + (get_option('gmt_offset') * 3600));
					//		//exit;
					//	}
					//}
					//else
					//{

						//wp_clear_scheduled_hook(MEB_PRE_CRON_MAIN.'_cron');	#	unschedule previous
	/*					wp_clear_scheduled_hook(MEB_CRON_HOOK);	#	unschedule previous
						$scheds = (array) wp_get_schedules();
						$name = strval($when);
						$interval = (isset($scheds[$name]['interval'])) ? (int)$scheds[$name]['interval'] : 0;

						update_option('wp_cron_backup_schedule', $name, false);
						if(0!==$interval)
						{
							wp_schedule_event(time() + $interval, $name, 'wp_db_backup_cron');
						}
	*/
					//}
				}
			}


	#######################


			?><div class="updated">
					<p><strong><?php _e('Options saved!', MEBAK_LOCALE ); ?></strong></p>
					<p><?php _e('Redirecting to the main page in a while...', MEBAK_LOCALE ); ?></p>
				</div>
				<script type="text/javascript">setTimeout("window.location='tools.php?page=myEASYbackup_tools';", 1000);</script><?php

			break;
			#
		default:
	}
//}


#
#	populate the input fields when the page is loaded
#

#
#	1.0.0: BEG
#-------------
#   if(!isset($_POST['meb_isPRODUCTION']))		{ $_POST['meb_isPRODUCTION']		= get_option('meb_isPRODUCTION'); }
$tmp = get_option( 'meb_isPRODUCTION' );
if($tmp=='') { $tmp = true; }
$_POST['meb_isPRODUCTION'] = $tmp;
#-------------
#	1.0.0: END
#

if(!isset($_POST['meb_isDEBUG']))			{ $_POST['meb_isDEBUG']				= get_option('meb_isDEBUG'); }
if(!isset($_POST['meb_compression']))		{ $_POST['meb_compression']			= get_option('meb_compression'); }

if(!isset($_POST['meb_ftp_server']))		{ $_POST['meb_ftp_server']			= get_option('meb_ftp_server'); }
if(!isset($_POST['meb_ftp_user_name']))		{ $_POST['meb_ftp_user_name']		= get_option('meb_ftp_user_name'); }
//if(!isset($_POST['meb_ftp_user_pass']))	{ $_POST['meb_ftp_user_pass']		= get_option('meb_ftp_user_pass'); }


#
#	0.0.9: BEG
#-------------
if(!isset($_POST['meb_force_phpcode'])) {

	#	if(!isset($_POST['meb_force_phpcode']))	{ $_POST['meb_force_phpcode']		= get_option('meb_force_phpcode'); }
	$tmp = get_option( 'meb_force_phpcode' );
	if($tmp=='') {

		if(isSYSTEM==1) {   //  1.0.0

			$tmp = false;   //  1.0.0
		}
		else {

			$tmp = true;
		}
	}

	$_POST['meb_force_phpcode'] = $tmp;
}
#-------------
#	0.0.9: END
#

#
#	0.1.1: BEG
#-------------
if(!isset($_POST['meb_backup_root']))		{ $_POST['meb_backup_root']			= get_option('meb_backup_root'); }
if(strlen($_POST['meb_backup_root'])==0) {

	$_POST['meb_backup_root'] = constant('MEBAK_WP_PARENT_PATH');
}
if(!isset($_POST['meb_donation_code']))		{ $_POST['meb_donation_code']		= get_option('meb_donation_code'); }
#-------------
#	0.1.1: END
#

#
#	0.1.3: BEG
#-------------
if(!isset($_POST['meb_zip_pass'])) {

	$tmp = get_option( 'meb_zip_pass' );
	if($tmp=='') { $tmp = false; }

	$_POST['meb_zip_pass'] = $tmp;
}
#-------------
#	0.1.3: END
#

#
#	0.1.4: BEG
#-------------
if(!isset($_POST['meb_zip_verbose'])) {

	$tmp = get_option( 'meb_zip_verbose' );
	if($tmp=='') { $tmp = false; }
	$_POST['meb_zip_verbose'] = $tmp;
}

if(!isset($_POST['meb_sys_archiving_tool'])) {

	$tmp = get_option( 'meb_sys_archiving_tool' );
	if($tmp=='') { $tmp = 'z'; }
	$_POST['meb_sys_archiving_tool'] = $tmp;
}

if(!isset($_POST['meb_tar_compress'])) {

	$tmp = get_option( 'meb_tar_compress' );
	if($tmp=='') { $tmp = false; }
	$_POST['meb_tar_compress'] = $tmp;
}

if(!isset($_POST['meb_php_ram']) && $tmp!='') {

	$tmp = get_option( 'meb_php_ram' );
	$_POST['meb_php_ram'] = $tmp;
}
#-------------
#	0.1.4: END
#

#
#	0.9.1: BEG
#-------------
if(!isset($_POST['meb_email_backup']) && $tmp!='') {

	$tmp = get_option( 'meb_email_backup' );
	$_POST['meb_email_backup'] = $tmp;
}

if(!isset($_POST['meb_email_scheduled_report'])) {

	$tmp = get_option( 'meb_email_scheduled_report' );
	if($tmp=='') { $tmp = 1; }
	$_POST['meb_email_scheduled_report'] = $tmp;
}

if(!isset($_POST['meb_email_backup_remove'])) {

	$tmp = get_option( 'meb_email_backup_remove' );
	if($tmp=='') { $tmp = 0; }
	$_POST['meb_email_backup_remove'] = $tmp;
}

if(!isset($_POST['myeasy_showcredits'])) {

	$tmp = get_option('myeasy_showcredits');
	if(strlen($tmp)==0) { $tmp = 1;}

	$_POST['myeasy_showcredits']= $tmp;
}

if(!isset($_POST['meb_ftp_remote_path']))	{ $_POST['meb_ftp_remote_path']		= get_option('meb_ftp_remote_path'); }

if(!isset($_POST['meb_ftp_port']))	        { $_POST['meb_ftp_port']		    = get_option('meb_ftp_port'); }
if((int)$_POST['meb_ftp_port']==0) {

	$_POST['meb_ftp_port'] = 21;
}

if(!isset($_POST['meb_ftp_timeout']))		{ $_POST['meb_ftp_timeout']		    = get_option('meb_ftp_timeout'); }

if(!isset($_POST['meb_ftp_pasv'])) {

	$tmp = get_option( 'meb_ftp_pasv' );
	if($tmp=='') { $tmp = 1; }
	$_POST['meb_ftp_pasv'] = $tmp;
}


if(!isset($_POST['meb_ally_pubkey'])
	|| trim($_POST['meb_ally_pubkey'])=='')	{ $_POST['meb_ally_pubkey']	= get_option('myewally_userKey'); }

$_POST['meb_remove_oldds'] = get_option( 'meb_remove_oldds' );

#-------------
#	0.9.1: END
#

#
#	1.0.2: BEG
#-------------
/**
 * wp-admin path
 */
//echo'(1) '.$_POST['meb_wpadmin_path'].'<br>';

if(!isset($_POST['meb_wpadmin_path']))		{ $_POST['meb_wpadmin_path']	= get_option('meb_wpadmin_path'); }

//echo'(2) '.$_POST['meb_wpadmin_path'].'<br>';

if(strlen($_POST['meb_wpadmin_path'])==0) {

//	$_POST['meb_wpadmin_path'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', constant('MEBAK_WP_PATH')).'wp-admin';
	$_POST['meb_wpadmin_path'] = '/wp-admin';
}

//echo'(3) '.$_POST['meb_wpadmin_path'].'<br>';

if(substr($_POST['meb_wpadmin_path'],0,1)!='/') {

	$_POST['meb_wpadmin_path'] = '/'.$_POST['meb_wpadmin_path'];
}

//echo'(4) '.$_POST['meb_wpadmin_path'].'<br>';

$_POST['meb_wpadmin_path'] = trim($_POST['meb_wpadmin_path']);

//echo'(5) '.$_POST['meb_wpadmin_path'].'<br>';

/**
 * mysqldump
 */
if(!isset($_POST['meb_mysqldump_path']))	{ $_POST['meb_mysqldump_path']	= get_option('meb_mysqldump_path'); }

if(strlen($_POST['meb_mysqldump_path'])==0) {

	$result = 1;

//	@exec( PATH_MYSQLDUMP.' --help', $output, $result );
//	if($result==0) {
//
//		$check = PATH_MYSQLDUMP;
//	}
//	else {
//
//		$check = __('NOT available', MEBAK_LOCALE );
//	}

	$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );
	$cmd = PATH_MYSQLDUMP . ' -V 1> ' . escapeshellarg($tmpfile) .' 2>&1';
	@system( $cmd, $result );

	$cmd_output = file_get_contents($tmpfile);
	unlink($tmpfile);

//echo $cmd.'<br>'.$result;

	if($result==0) {

		$check = PATH_MYSQLDUMP;
	}
	else {

		$check = __('NOT available', MEBAK_LOCALE );
	}

	$_POST['meb_mysqldump_path'] = str_replace('mysqldump','', PATH_MYSQLDUMP);
}
$_POST['meb_mysqldump_path'] = trim(str_replace('mysqldump','', $_POST['meb_mysqldump_path']));

if(substr($_POST['meb_mysqldump_path'],-1,1)=='/') {

	$_POST['meb_mysqldump_path'] = substr($_POST['meb_mysqldump_path'],0,-1);
}
#-------------
#	1.0.2: END
#


//if(!defined('MYEASYBACKUP_FAMILY') || (int)MYEASYBACKUP_FAMILY<1)	#	0.1.3
//if(defined('MYEASYBACKUP_FREE')) {
//
//	#	free
//	#
//	measycom_advertisement('meb');
//}

?>
<form name="meb_settings" method="post" action="">

<div>
<div class="optionsGroup" style="height:20px;">
	<div class="optionsGroup-title">
		<input value="<?php _e('System settings', MEBAK_LOCALE ); ?>" type="button" class="button-primary" style="font-weight:bold;margin-top:-4px;" onclick="toggleOptions('settings');" />
		<span style="font-weight:normal;">&laquo;&mdash; <?php _e('click on the button to open/close the system settings section', MEBAK_LOCALE ); ?></span>
	</div>
	<div id="settings-toggler" class="optionsGroup-toggler-open" style="width:auto;" onclick="toggleOptions('settings');">&nbsp;</div>
	<div style="clear:both;"></div>
</div>
<div id="settings-contents" class="optionsGroup-contents" style="display:<?php echo $settings_contents_div_display; ?>;">
<div class="light">
	<div class="left"><?php
		#
		#	WordPress install path
		#	@since 1.0.2
		#
		echo '<b>&raquo; ' . __('The WordPress administration path.', MEBAK_LOCALE ) . '</b>'

			.'<br /><i>'
				. __('On some installations the WordPress administration folder (wp-admin) is placed on a subfolder, in such cases you need to specify its URL here.', MEBAK_LOCALE )

			.'</i>'
		;

	?></div>
	<div class="right">
		<?php echo (is_ssl() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME']; ?><input type="text" name="meb_wpadmin_path" value="<?php echo $_POST['meb_wpadmin_path']; ?>" size="40" />
	</div>
	<div style="clear:both;"></div>
</div>

<div class="light">
	<div class="left"><?php

		#
		#	Use PHP code rather than system() commands
		#
		echo '<b>&raquo; ' . __('Use PHP code rather than system() commands:', MEBAK_LOCALE ) . '</b>';

		if(isSYSTEM==1)													#	0.1.1
		{
			echo '<br /><i>' . __('Note: system() commands are enabled on this server, for better performances you may like to disable this feature.', MEBAK_LOCALE ) . '</i>';
		}
		else
		{
			echo '<br /><i>' . __('Note: system() commands are NOT enabled on this server, you must enable this feature to be able to backup your data.', MEBAK_LOCALE ) . '</i>';
		}

	?></div>
	<div class="right"><?php

			$checked ='';
			if($_POST['meb_force_phpcode']==1) { $checked = ' checked="checked"'; }

		?><input type="checkbox" id="handle_force_phpcode" name="meb_force_phpcode"
				 onclick="javascript:
									document.getElementById('zip_options').style.display='none';
									document.getElementById('tar_options').style.display='none';
									if(this.checked==true){
										document.getElementById('sys_options').style.display='none';
									}else{
										document.getElementById('sys_options').style.display='block';
										var radio=document.getElementsByName('meb_sys_archiving_tool');
										if(radio){
											var i=0,arch='';
											for(i=0;i<radio.length;i++){
												if(radio[i].checked==true){
													arch=radio[i].value;
												}
											};
											switch(arch){
												case 'z':document.getElementById('zip_options').style.display='block';break;
												case 't':document.getElementById('tar_options').style.display='block';break;
											};
										};
									};"
				 value="1"<?php echo $checked; ?> />
	</div>
	<div style="clear:both;"></div>
</div>

<div id="sys_options" class="med"
	style="<?php if(!$_POST['meb_force_phpcode']) { ?>display:block;<?php } else { ?>display:none;<?php } ?>padding:0;/*margin-top:8px;width:100%;background:#dfdfdf;-moz-border-radius:6px;border-radius:6px;*/">

	<div style="padding:8px;"><?php

			echo '<b>&raquo; ' . __('How the system commands should work.', MEBAK_LOCALE ) . '</b>';

	?></div>

	<div class="light" style="margin:8px;width:98%;">
		<div class="left"><?php
			#
			#	path to mysqldump command
			#	@since 1.0.2
			#
			echo '<b>' . __('Path to the mysqldump command.', MEBAK_LOCALE ) . '</b>'

				.'<br /><i>'
					. __('On some installations it might be impossible to determine how to execute the mysqldump command.', MEBAK_LOCALE )
					. __('In such cases you need to then enter the full path to mysqldump in this field or use the PHP code.', MEBAK_LOCALE )

					. '<br /><br />' . __('To know the full path you can:', MEBAK_LOCALE )
					. '<ul>'
						. '<li>* ' . __('ask your hosting provider for "the full path to mysqldump"', MEBAK_LOCALE ) . '</li>'
						. '<li>* ' . __('connect remotely to your server (ssh) and issue the "which mysqldump" command', MEBAK_LOCALE ) . '</li>'
					. '</ul>'

				.'</i>'
			;

		?></div>
		<div class="right">
			<input type="text" name="meb_mysqldump_path" value="<?php echo $_POST['meb_mysqldump_path']; ?>" size="40" />/mysqldump<?php

				echo '<br /><br />'
						.'<div style="background-color:#DFDFDF;border:1px solid #FFA500;margin:8px;float:left;width:90%;padding:12px;-moz-border-radius:6px 6px 6px 6px;">'
							. __('Example:', MEBAK_LOCALE )
							.'<br />' . __('If your full path is "/usr/bin/example/mysqldump" you just need to enter "/usr/bin/example" here above.', MEBAK_LOCALE )
							.'<br />' . __('On a Macintosh development system based on MAMP/MAMP Pro use "/Applications/MAMP/Library/bin" as your full path.', MEBAK_LOCALE )
						.'</div>'
				;
		
		?></div>
		<div style="clear:both;"></div>
	</div>

	<div class="light" style="margin:8px;width:98%;">
		<div class="left"><?php
			#
			#	compression for the zip file, used only on system() enabled servers
			#
			echo '<b>' . __('Data set compression level:', MEBAK_LOCALE ) . '</b>'

				. '<br /><i>' . __('If you choose "Tar" to compress your data set, this option will be taken into account only if you also enable the "Would you like to compress your data set?" option &mdash; see below.', MEBAK_LOCALE ) . '</i>';

		?></div>
		<div class="right"><?php

				$checked_1 ='';
				$checked_2 ='';
				$checked_3 ='';
				$checked_4 ='';
				$checked_5 ='';
				$checked_6 ='';
				$checked_7 ='';
				$checked_8 ='';
				$checked_9 ='';

				switch($_POST['meb_compression'])
				{
					case 1: $checked_1 = ' checked="checked"'; break;
					case 2: $checked_2 = ' checked="checked"'; break;
					case 3: $checked_3 = ' checked="checked"'; break;
					case 4: $checked_4 = ' checked="checked"'; break;
					case 5: $checked_5 = ' checked="checked"'; break;
					case 7: $checked_7 = ' checked="checked"'; break;
					case 8: $checked_8 = ' checked="checked"'; break;
					case 9: $checked_9 = ' checked="checked"'; break;
					#
					default:
						$checked_6 = ' checked="checked"';
				}

			?><input type="radio" name="meb_compression" id="comp1" value="1"<?php echo $checked_1; ?> />
				<label for="comp1"> 1 <?php _e('Minimal compression, faster execution speed', MEBAK_LOCALE ); ?> </label>
			<br /><input type="radio" name="meb_compression" id="comp2" value="2"<?php echo $checked_2; ?> />
				<label for="comp2"> 2 </label>
			<br /><input type="radio" name="meb_compression" id="comp3" value="3"<?php echo $checked_3; ?> />
				<label for="comp3"> 3 </label>
			<br /><input type="radio" name="meb_compression" id="comp4" value="4"<?php echo $checked_4; ?> />
				<label for="comp4"> 4 </label>
			<br /><input type="radio" name="meb_compression" id="comp5" value="5"<?php echo $checked_5; ?> />
				<label for="comp5"> 5 </label>
			<br /><input type="radio" name="meb_compression" id="comp6" value="6"<?php echo $checked_6; ?> />
				<label for="comp6"> 6 <?php _e('Default compression', MEBAK_LOCALE ); ?> </label>
			<br /><input type="radio" name="meb_compression" id="comp7" value="7"<?php echo $checked_7; ?> />
				<label for="comp7"> 7 </label>
			<br /><input type="radio" name="meb_compression" id="comp8" value="8"<?php echo $checked_8; ?> />
				<label for="comp8"> 8 </label>
			<br /><input type="radio" name="meb_compression" id="comp9" value="9"<?php echo $checked_9; ?> />
				<label for="comp9"> 9 <?php _e('Maximum compression, slower execution speed', MEBAK_LOCALE ); ?> </label>
		</div>
		<div style="clear:both;"></div>
	</div>

	<div class="dark" style="margin:8px;width:98%;">
		<div class="left"><?php
				#
				#	System archiving tool
				#	@since 0.1.4
				#
				echo '<b>' . __('What system command do you like to use to create the data set?', MEBAK_LOCALE ) . '</b>'

					.'<br /><i>'

						. __('The plugin is able to use two of the most common archive command usually available on Linux systems: "Tar" and "Zip".', MEBAK_LOCALE )
						. __(' If "Zip" is installed on your system, you may like to use it as it allows to password protect your data set.', MEBAK_LOCALE )
						. '<br /><br />' . __('Note: if your site is on a non-Linux server there are few chances "Zip" or "Tar" are installed; if both are misisng, the only way to backup will be by enabling the "Use PHP code rather than system() commands" option.', MEBAK_LOCALE )

					.'</i>'
				;

				$checked_z ='';
				$checked_t ='';

				if($_POST['meb_sys_archiving_tool']=='z') { $checked_z = ' checked="checked"'; }
				if($_POST['meb_sys_archiving_tool']=='t') { $checked_t = ' checked="checked"'; }

				if($checked_z=='' && $checked_t=='')
				{
					$checked_z = ' checked="checked"';
				}

		?></div>
		<div class="right"><?php

				if(isSYSzip==true)
				{
					?><input type="radio" name="meb_sys_archiving_tool" id="c_sat_z" value="z"<?php echo $checked_z; ?>
								 onclick="javascript:if(this.checked==true){
											document.getElementById('zip_options').style.display='block';
											document.getElementById('tar_options').style.display='none';
										}else{
											document.getElementById('zip_options').style.display='none';
											document.getElementById('tar_options').style.display='block';
										};" /> <label for="c_sat_z">Zip</label><?php
				}
				else
				{
					echo '<p>' . __('Zip is NOT available', MEBAK_LOCALE ) . '</p>';
				}

				if(isSYStar==true)
				{
					?><input type="radio" name="meb_sys_archiving_tool" id="c_sat_t" style="margin-left:16px;" value="t"<?php echo $checked_t; ?>
								 onclick="javascript:if(this.checked==true){
											document.getElementById('zip_options').style.display='none';
											document.getElementById('tar_options').style.display='block';
										}else{
											document.getElementById('zip_options').style.display='block';
											document.getElementById('tar_options').style.display='none';
										};" /> <label for="c_sat_t">Tar</label><?php
				}
				else
				{
					echo '<p>' . __('Tar is NOT available', MEBAK_LOCALE ) . '</p>';
				}

		?></div>
		<div style="clear:both;"></div>

		<div id="zip_options" style="<?php if($_POST['meb_force_phpcode']!=1 && $checked_z!='') { ?>display:block;<?php } else { ?>display:none;<?php } ?>margin-top:8px;width:100%;-moz-border-radius:6px;border-radius:6px;">
			<div class="left"><?php
				#
				#	Ask for password when creating a data set
				#	@since 0.1.3
				#
				echo '<b>' . __('Would you like to password protect your data sets?', MEBAK_LOCALE ) . '</b>'

					.'<br /><i>'

						. __('By enabling this option you will be required to enter a password each time you create a data set. To be able to open the compressed backup you will need to enter this password again.', MEBAK_LOCALE )
						//. '<br />' . __('Note: does work only ....', MEBAK_LOCALE )

					.'</i>'
				;

				$checked ='';
				if($_POST['meb_zip_pass']==1) { $checked = ' checked="checked"'; }

			?></div>
			<div class="right">
				<input type="checkbox" name="meb_zip_pass" value="1"<?php echo $checked; ?> />
			</div>
			<div style="clear:both;"></div>
		</div>

		<div id="tar_options" style="<?php if($_POST['meb_force_phpcode']!=1 && $checked_t!='') { ?>display:block;<?php } else { ?>display:none;<?php } ?>margin-top:8px;width:100%;-moz-border-radius:6px;border-radius:6px;">
			<div class="left"><?php
				#
				#	Compress when using tar?
				#	@since 0.1.4
				#
				echo '<b>' . __('Would you like to compress your data set?', MEBAK_LOCALE ) . '</b>'

					.'<br /><i>'

						. __('Compressing makes the data set smaller but, while the compression is performed, the plugin will require additional resources (memory).', MEBAK_LOCALE )
						. __(' This is due to the way the plugin adds all the needed files in the data set. To keep sensitive data out of the WordPress folder in fact, the plugin needs to add the needed files to the data set in few steps.', MEBAK_LOCALE )
						//. '<br />' . __('I have not found out a way to directly use Tar to compress this way, so I let some PHP code do it. If you have any clues', MEBAK_LOCALE )
						//. ' <a href="http://myeasywp.com/contact" target="_blank">' . __('please get in touch', MEBAK_LOCALE ) . '</a>.'

					.'</i>'
				;

				$checked ='';
				if($_POST['meb_tar_compress']==1) { $checked = ' checked="checked"'; }

			?></div>
			<div class="right">
				<input type="checkbox" name="meb_tar_compress" value="1"<?php echo $checked; ?> />
			</div>
			<div style="clear:both;"></div>
		</div>

	</div>
</div>

<div id="php_options" class="light">
	<div class="left"><?php
		#
		#	Memory available to PHP @since 0.1.4
		#
		$ram = array();
		$ram[] = '8';
		$ram[] = '16';
		$ram[] = '24';
		$ram[] = '32';
		$ram[] = '40';
		$ram[] = '48';
		$ram[] = '56';
		$ram[] = '64';
		$ram[] = '80';
		$ram[] = '96';
		$ram[] = '128';
		$ram[] = '192';
		$ram[] = '224';
		$ram[] = '256';
		$ram[] = '288';
		$ram[] = '320';
		$ram[] = '352';
		$ram[] = '384';
		$ram[] = '416';
		$ram[] = '448';
		$ram[] = '480';
		$ram[] = '512';

		echo '<b>&raquo; ' . __('Memory available to PHP:', MEBAK_LOCALE ) . '</b>'

			.'<br /><i>'

				. __('The amount of memory your hosting is making available to your PHP scripts on this server is', MEBAK_LOCALE )
				. ' <b>' . str_replace('M', '', ini_get('memory_limit')) . __('Mb', MEBAK_LOCALE ) . '</b>. '

				. __('If you get the "Fatal error: Allowed memory size of {number} bytes exhausted (tried to allocate {number} bytes) in..." while creating a data set, you can try to increase this value.', MEBAK_LOCALE )
			.'</i>';

	?></div>
	<div class="right">
		<select name="meb_php_ram">
			<option value=""><?php _e('Select the amount of memory...', MEBAK_LOCALE ); ?></option><?php

			foreach($ram as $r)
			{
				$selected = '';
				if($r==$_POST['meb_php_ram'])
				{
					$selected = ' selected="selected"';
				}

				?><option value="<?php echo $r; ?>"<?php echo $selected;?>><?php echo $r; ?></option><?php
			}

		?></select> <?php _e('Mb', MEBAK_LOCALE ); ?>

	</div>
	<div style="clear:both;"></div>
</div>

<div class="light">
	<div class="left"><?php
		#
		#	debug toggler
		#
		echo '<b>&raquo; ' . __('Check this to show debug code:', MEBAK_LOCALE ) . '</b>'

			.'<br /><i>' . __('Debug code is shown in the contextual help section. To see the debug code, enable this option then click on the Update Options button. After the options are being saved click on the Help tab on top right of the page, right below the Log Out link.', MEBAK_LOCALE ) . '</i>';

	?></div>
	<div class="right"><?php

			$checked ='';
			if($_POST['meb_isDEBUG']==1) { $checked = ' checked="checked"'; }

		?><input type="checkbox" name="meb_isDEBUG" value="1"<?php echo $checked; ?> />
	</div>
	<div style="clear:both;"></div>
</div>

<div class="light">
	<div class="left"><?php
			#
			#	Verbose output
			#	@since 0.1.4
			#
			echo '<b>&raquo; ' . __('Would you like to perform your backups in verbose mode?', MEBAK_LOCALE ) . '</b>'

				.'<br /><i>'

					. __('By enabling this option you will see on the screen the complete list of files added to the data set. If your WordPress folder hold quite a big number of folders/files you may like to unset this option.', MEBAK_LOCALE )

				.'</i>'
			;

			$checked ='';
			if($_POST['meb_zip_verbose']==1) { $checked = ' checked="checked"'; }

	?></div>
	<div class="right">
		<input type="checkbox" name="meb_zip_verbose" value="1"<?php echo $checked; ?> />
	</div>
	<div style="clear:both;"></div>
</div>

<div class="light">
	<div class="left"><?php
		#
		#	production server toggler
		#
		echo '<b>&raquo; ' . __('Check if this is a production server:', MEBAK_LOCALE ) . '</b>'

			.'<br /><i>' . __('On production servers, for security reasons, errors are never shown on the screen but written in a log file.', MEBAK_LOCALE ) . '</i>';

	?></div>
	<div class="right"><?php

			$checked ='';
			if($_POST['meb_isPRODUCTION']==1) { $checked = ' checked="checked"'; }

		?><input type="checkbox" name="meb_isPRODUCTION" value="1"<?php echo $checked; ?> />
	</div>
	<div style="clear:both;"></div>
</div>

<div class="light">
	<div style="padding:8px;">
		<a id="remove_oldds_settings" href="remove_oldds_settings"></a><?php
			#
			#	Remove old backups settings
			#	@since 0.9.2
			#
			echo '<b>&raquo; ' . __('Keeping your backup folder tidy.', MEBAK_LOCALE ) . '</b>';

	?></div>
	<div class="left"><?php
		#
		#	Days
		#
		echo '<b>' . __('Days required to automatically remove a data set:', MEBAK_LOCALE ) . '</b>'

			.'<br /><i>'

				. __('When your data set gets older than the number of days you set here, it will be automatically removed once somebody visits your site.', MEBAK_LOCALE )
				. '<br />'
				. __('Leave the field empty if you do not want to remove old backups.', MEBAK_LOCALE )

			.'</i>'
		;

	?></div>
	<div class="right">
		<input type="text" name="meb_remove_oldds" value="<?php echo $_POST['meb_remove_oldds']; ?>" size="4" maxlength="128" />
	</div>
	<div style="clear:both;"></div>
</div>

<div class="light">
	<div style="padding:8px;">
		<a id="ftp_settings" href="ftp_settings"></a><?php
			#
			#	FTP server
			#
			echo '<b>&raquo; ' . __('Settings about the FTP server where to send the data set.', MEBAK_LOCALE ) . '</b>'
				.'<br /><i>'
				. __('Here you set the information that the plugin will use to connect to your FTP server.', MEBAK_LOCALE )
				.'<br />'
				. __('Note: for security reasons the password is not saved in the options database.', MEBAK_LOCALE )
			.'</i>';

	?></div>
	<div class="left"><?php
		#
		#	FTP server
		#
		echo '<b>' . __('Server name:', MEBAK_LOCALE ) . '</b>';

		$inp = array();         $out = array();
		$inp[] = 'http://';     $out = '';
		$inp[] = 'https://';    $out = '';
		$inp[] = 'ftp://';      $out = '';
		$inp[] = 'ftps://';     $out = '';

		$_POST['meb_ftp_server'] = str_replace($inp, $out, $_POST['meb_ftp_server']);

	?></div>
	<div class="right">
		<input type="text" name="meb_ftp_server" id="meb_ftp_server" value="<?php echo $_POST['meb_ftp_server']; ?>" size="40" maxlength="128" />
	</div>
	<div style="clear:both;"></div>

	<div class="left"><?php
		#
		#	FTP user
		#
		echo '<b>' . __('User name:', MEBAK_LOCALE ) . '</b>';

	?></div>
	<div class="right">
		<input type="text" name="meb_ftp_user_name" id="meb_ftp_user_name" value="<?php echo $_POST['meb_ftp_user_name']; ?>" size="40" maxlength="128" />
	</div>
	<div style="clear:both;"></div>

	<div class="left"><?php
		#
		#	FTP remote path
		#
		echo '<b>' . __('Remote path:', MEBAK_LOCALE ) . '</b>'
				.'<br /><i>'
					. __('If you like to upload to a specific directory you can set it here. It is advisable to use a directory above `www`, `public_html` and `public_ftp`, see the note about where to save your data sets here below.', MEBAK_LOCALE ) . '</i>'
		;

	?></div>
	<div class="right">
		<input type="text" name="meb_ftp_remote_path" id="meb_ftp_remote_path" value="<?php echo $_POST['meb_ftp_remote_path']; ?>" size="40" maxlength="128" />
	</div>
	<div style="clear:both;"></div>

	<div class="left"><?php
		#
		#	FTP port
		#
		$_POST['meb_ftp_port'] = (int)$_POST['meb_ftp_port'];

		echo '<b>' . __('Port:', MEBAK_LOCALE ) . '</b>'
				.'<br /><i>'
					. __('Usually you do not need to change the default port (21). If your hosting provider uses a different port, you can change this value.', MEBAK_LOCALE ) . '</i>'
		;

	?></div>
	<div class="right">
		<input type="text" name="meb_ftp_port" id="meb_ftp_port" value="<?php echo $_POST['meb_ftp_port']; ?>" size="6" maxlength="128" />
	</div>
	<div style="clear:both;"></div>

	<div class="left"><?php
		#
		#	FTP timeout @since 1.0.5.6
		#
		$_POST['meb_ftp_timeout'] = (int)$_POST['meb_ftp_timeout'];
		if($_POST['meb_ftp_timeout']==0) {

			$_POST['meb_ftp_timeout'] = 90;
		}

		echo '<b>' . __('Timeout:', MEBAK_LOCALE ) . '</b>'
				.'<br /><i>'
					. __('This parameter specifies the timeout for all network operations when performing the FTP upload.', MEBAK_LOCALE ) . '</i>'
		;

	?></div>
	<div class="right">
		<input type="text" name="meb_ftp_timeout" id="meb_ftp_timeout" value="<?php echo $_POST['meb_ftp_timeout']; ?>" size="6" maxlength="128" /> <?php _e('seconds', MEBAK_LOCALE ); ?>
	</div>
	<div style="clear:both;"></div>

	<div class="left"><?php
		#
		#	FTP pasv mode @since 1.0.5.6
		#
		$ftppasv_n ='';
		$ftppasv_y ='';

		switch($_POST['meb_ftp_pasv']) {

			case '0':
				$ftppasv_n = ' checked="checked"'; break;

			case '1':
			default:
				$ftppasv_y = ' checked="checked"';
		}

		echo '<b>' . __('Passive mode:', MEBAK_LOCALE ) . '</b>'
				.'<br /><i>'
					. __('Turns ON or OFF the passive mode. In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind firewall.', MEBAK_LOCALE ) . '</i>'
		;

	?></div>
	<div class="right">
		<input type="radio" name="meb_ftp_pasv" id="c_ftppasv_y" value="1"<?php echo $ftppasv_y; ?>/> <label for="c_ftppasv_y"><?php
			_e('On', MEBAK_LOCALE ); ?></label>
		<input type="radio" name="meb_ftp_pasv" id="c_ftppasv_n" value="0"<?php echo $ftppasv_n; ?>/> <label for="c_ftppasv_n"><?php
			_e('Off', MEBAK_LOCALE ); ?></label>
	</div>

	<div style="clear:both;"></div>
	<div class="left"><?php
		#
		#	Check remote contents
		#
		$ftpcontents_n ='';
		$ftpcontents_y ='';

		switch($_POST['meb_ftp_contents']) {

			case '1': $ftpcontents_y = ' checked="checked"'; break;
			case '0':
			default:
				$ftpcontents_n = ' checked="checked"';
		}

		echo '<b>' . __('Would you like to show a list of the remote contents?', MEBAK_LOCALE ) . '</b>'
				.'<br /><i>'
					. __('Sometimes you are not sure about how the server is configured, by having a look at the remote folder you can get an idea about what value you want to enter in the "Remote path" field here above.', MEBAK_LOCALE ) . '</i>'
		;

	?></div>
	<div class="right" style="width:auto;">
		<input type="radio" name="meb_ftp_contents" id="c_ftpcontents_n" value="0"<?php echo $ftpcontents_n; ?>/> <label for="c_ftpcontents_n"><?php
			_e('No', MEBAK_LOCALE ); ?></label>
		<input type="radio" name="meb_ftp_contents" id="c_ftpcontents_y" value="1"<?php echo $ftpcontents_y; ?>/> <label for="c_ftpcontents_y"><?php
			_e('Yes', MEBAK_LOCALE ); ?></label>
	</div>

	<div id="ftp_check_connection_result" style="display:none;clear:both;width:80%;margin-left:10%;"></div>

	<div style="float:right;"><?php

		echo '<script type="text/javascript">'
				.'function ask_ftp_password(){'
					.'ae_prompt(check_ftp_password, \''
							.__( 'Please type the password to test your FTP connection:', MEBAK_LOCALE )
						.'\', \'\');'
				.'}'
				.'function check_ftp_password(pwd){'
					.'if(pwd && pwd.length>0){'
						.'var el=document.getElementById(\'ftp_check_connection_result\');'
						.'el.innerHTML=\'<div class="dark" style="color:#ffffff;padding:8px;text-align:center;"><img src="'.MYEASY_CDN_IMG.'wait.gif" style="margin-right:8px;" />Please wait...</div>\';'
						.'el.style.display=\'block\';'
						.'var radio=document.getElementsByName(\'meb_ftp_contents\');'
						.'if(radio){'
							.'var i=0,ftpcont=\'\';'
							.'for(i=0;i<radio.length;i++){'
								.'if(radio[i].checked==true){'
									.'ftpcont=radio[i].value;'
								.'}'
							.'};'
						.'};'
						.'var pasv=0, radio=document.getElementsByName(\'meb_ftp_pasv\');'
						.'for(i=0;i<radio.length;i++){'
							.'if(radio[i].checked==true){'
								.'pasv=radio[i].value;'
							.'};'
						.'};'
						.'sndReq(\'ftp_check_connection\',\'ftp_check_connection_result\','
									.'pwd+\''.AJAX_PARMS_SPLITTER
									.'\'+document.getElementById(\'meb_ftp_server\').value+\''.AJAX_PARMS_SPLITTER
									.'\'+document.getElementById(\'meb_ftp_user_name\').value+\''.AJAX_PARMS_SPLITTER
									.'\'+document.getElementById(\'meb_ftp_remote_path\').value+\''.AJAX_PARMS_SPLITTER
									.'\'+document.getElementById(\'meb_ftp_port\').value+\''.AJAX_PARMS_SPLITTER
									.'\'+ftpcont+\''.AJAX_PARMS_SPLITTER
									.'\'+document.getElementById(\'meb_ftp_timeout\').value+\''.AJAX_PARMS_SPLITTER
									.'\'+pasv+\''.AJAX_PARMS_SPLITTER
									. MEB_BACKUP_AJAX_VALIDATOR
						.'\');'
						.'return false;'
					.'}'
				.'}'
			.'</script>'
		;

		?><input class="button-secondary" style="margin:7px 12px;" type="button" name="btn"
				value="<?php _e('Test the connection', MEBAK_LOCALE ); ?>"
				onclick="javascript:ask_ftp_password();return false;" />
	</div>
	<div style="clear:both;padding-bottom:8px;"></div>
</div>

</div><!-- settings-contents -->
</div>

<!-- @since 0.9.1 ae_prompt code: beg -->
<div id="aep_ovrl" style="display:none;">&nbsp;</div>
<div id="aep_ww" style="display:none;">
	<div id="aep_win">
		<div id="aep_t"></div>
		<div id="aep_w">
			<span id="aep_prompt"></span>
			<br /><input type="password" id="aep_text" onKeyPress="if((event.keyCode==10)||(event.keyCode==13)){ae_clk(1)};if(event.keyCode==27){ae_clk(0);};" />
			<br />
			<div>
				<input type="button" id="aep_ok" onclick="ae_clk(1);" value="<?php _e( 'OK', MEBAK_LOCALE ); ?>" />
				<input type="button" id="aep_cancel" onclick="ae_clk(0);" value="<?php _e( 'Cancel', MEBAK_LOCALE ); ?>" />
			</div>
		</div>
	</div>
</div>
<!--[if IE]>
<style type="text/css">#aep_ovrl, #aep_ww { position:absolute;top:0px; }</style>
<![endif]-->
<!-- ae_prompt code: end -->

<div class="light">
	<a id="backup_folder" href="backup_folder"></a>
	<div class="left"><?php
		#
		#	Backup folder
		#	@since 0.1.1
		#
		echo '<b>' . __('Where would you like to save your data sets (the backup .zip files)?', MEBAK_LOCALE ) . '</b>'

			.'<br /><i>'

				. __('Note: all folders, being them writable or not, are listed with an label showing the folder privileges. If you have created a folder for this purpose be sure to give at least 755 permissions (enable writing on non-Linux servers).', MEBAK_LOCALE )

			.'</i>'
		;

		?><div class="warning">
			<b><?php

				echo __('WARNING!', MEBAK_LOCALE )

					.'<br />'

						. __('If you set a folder below the `www`, the `public_html` or the `public_ftp` folder, your backup data WILL BE EXPOSED to the public: anyone knowing the file name and this folder name WILL BE ABLE TO DOWNLOAD IT simply by using a browser.', MEBAK_LOCALE )
						. '<br /><br />' . __('The ability to save to a public folder was introduced for the users running their site on a hosting that has enabled the &ldquo;open_basedir&rdquo; restriction.', MEBAK_LOCALE )
						. ' ' . __('With the &ldquo;open_basedir&rdquo; restriction active the plugin is NOT able to read and write outside the path its running &mdash; and that MUST be public otherwise you are not able to use it.', MEBAK_LOCALE )
						. '<br /><br />' . __('Be aware then that, when you set a public folder, you are WARMLY advised TO SAVE EACH DATA SET AS SOON AS ITS CREATED and then REMOVE IT IMMEDIATELY AFTER: man advised...', MEBAK_LOCALE )

					.''
				;

			?></b>
		</div>
	</div>
	<div class="right"><?php

		$tmp_path = MEBAK_WP_PARENT_PATH;								#	0.1.1

//echo 'meb_backup_root['.$_POST['meb_backup_root'].']<br>';

		?><div id="dirs_list_container" style="background-color:#F1F1F1;width:100%;border:1px solid #DFDFDF;margin:0 0 8px 0;padding:8px;-moz-border-radius:6px;border-radius:6px;"></div>
		<script type="text/javascript">sndReq('get_site_dirs_list','dirs_list_container','<?php echo $_POST['meb_backup_root'] . AJAX_PARMS_SPLITTER . MEB_BACKUP_AJAX_VALIDATOR; /* 1.0.10 */ ?>');</script>
	</div>
	<div style="clear:both;"></div>
</div>

<div class="light">
	<div style="padding:8px;">
		<a id="ally_settings" href="ally_settings"></a>
		<div style="float:left;">
			<div style="float:right;width:150px;margin-top:20px;padding-left:20px;"><img src="<?php echo MYEASY_CDN_IMG; ?>webally-160.png" /></div><?php
			#
			#	myEASYwebally
			#
			echo '<b>&raquo; ' . __('Have you enrolled your personal ally yet?', MEBAK_LOCALE ) . '</b>'
				.'<br />'
				. __('Hey, there is a new plugin in town in the myEASY series: myEASYwebally!', MEBAK_LOCALE )
					.'<br />'
					. __('To get his loyal services all you need to do is to <a href="https://services.myeasywp.com/?page=account-add" target="_blank">open your free account</a>: ', MEBAK_LOCALE )
					. __('you will then be provided with two API keys, a PUBLIC and PRIVATE one.', MEBAK_LOCALE )
					.'<br /><br />'
					. __('By using your keys with myEASYbackup you will be able to upload your backups on the The myEASY Dressing Room&trade; server with a couple of mouse clicks.', MEBAK_LOCALE )
					.'<br /><br />'
					. __('On The myEASY Dressing Room&trade; server you can experiment as much as you like on a copy of your blog &ndash; try new plugins, upgrade the exihisting ones or even upgrade the WordPress installation &ndash; in <b>a safe place</b>, totally separated from your public blog.', MEBAK_LOCALE )
					.' '
					. __('Once you are ready compress everything with one click, download it and update your real blog.', MEBAK_LOCALE )
					.'<br /><br />'
					.__('Get more info about what your ally can do for you at the following pages: <a href="http://myeasywp.com/plugins/myeasywebally/" target="_blank">the myEASYwebally plugin page</a>, <a href="https://services.myeasywp.com/" target="_blank">the myEASYwebally server page</a> and the <a href="http://dr.myeasywp.com/" target="_blank">The myEASY Dressing Room&trade;</a>.', MEBAK_LOCALE )
			.'';

	?></div></div>
	<div class="medium" style="width:80%;margin:12px 0 0 10%;">
		<div class="left"><?php
			#
			#	Public key
			#
			echo '<b>' . __('myEASYwebally Public API key:', MEBAK_LOCALE ) . '</b>'
				.'<br /><i>'
				. __('Note: when you click on the "Generate the myEASYbackup KEY" button you will be asked to enter your private API key. Please note that your private will NOT be stored anywhere, it will be only used to generate the myEASYbackup KEY that will be used to authenticate this plugin to the <a href="http://dr.myeasywp.com" target="_blank">dressing</a> server.', MEBAK_LOCALE )
			.'</i>';

		?></div>
		<div class="right">
			<input type="text" name="meb_ally_pubkey" id="meb_ally_pubkey" value="<?php echo $_POST['meb_ally_pubkey']; ?>" size="40" maxlength="128" />
		</div>

		<div id="ally_ftp_key_result" style="display:block;clear:both;width:80%;margin-left:10%;"></div>

		<div style="float:right;"><?php

			echo '<script type="text/javascript">'
					.'function ask_ally_pvtkey(){'
						.'ae_prompt(check_ally_pvtkey, \''
								.__( 'Please enter your myEASYwebally PRIVATE API key:', MEBAK_LOCALE )
							.'\', \'\');'
					.'}'
					.'function check_ally_pvtkey(pwd){'
						.'if(pwd && pwd.length>0){'
							.'var el=document.getElementById(\'ally_ftp_key_result\');'
							.'el.innerHTML=\'<div class="dark" style="color:#ffffff;padding:8px;text-align:center;"><img src="'.MYEASY_CDN_IMG.'wait.gif" style="margin-right:8px;" />Please wait...</div>\';'
							.'sndReq(\'meb_ally_ftp_key\',\'ally_ftp_key_result\','
										.'pwd+\''.AJAX_PARMS_SPLITTER
										.'\'+document.getElementById(\'meb_ally_pubkey\').value+\''.AJAX_PARMS_SPLITTER
										. MEB_BACKUP_AJAX_VALIDATOR
							.'\');'
							.'return false;'
						.'}'
					.'}'
				.'</script>'
			;

			?>
			<input class="button-primary" style="margin:14px 12px 0 0;" type="button" name="btn"
					value="<?php _e('Generate the myEASYbackup KEY', MEBAK_LOCALE ); ?>"
					onclick="javascript:var el=document.getElementById('meb_ally_pubkey');if(el.value.length>0){ask_ally_pvtkey();}else{alert('<?=_e( 'Please enter your myEASYwebally PUBLIC API key!', MEBAK_LOCALE )?>');};return false;" />
		</div>
<!--	<div style="clear:both;"></div> -->
	</div>
	<div style="clear:both;padding-bottom:8px;"></div>
</div>

<div class="light">
	<div style="padding:8px;">
		<a id="email_settings" href="email_settings"></a><?php
			#
			#	Email backups
			#	@since 0.9.1
			#
			echo '<b>&raquo; ' . __('Sending data sets to an email address.', MEBAK_LOCALE ) . '</b>';

	?></div>
	<div class="left"><?php
		#
		#	Email address
		#
		echo '<b>' . __('Email address to which send the backup:', MEBAK_LOCALE ) . '</b>'

			.'<br /><i>'

				. __('Be sure that the receiver account has enough space in his mailbox!', MEBAK_LOCALE )
				. '<br />'
				. __('Leave the field empty if you do not want to send the backup to an email address.', MEBAK_LOCALE )

			.'</i>'
		;

	?></div>
	<div class="right">
		<input type="text" name="meb_email_backup" value="<?php echo $_POST['meb_email_backup']; ?>" size="40" maxlength="128" />
	</div>
	<div style="clear:both;"></div>

	<div class="left"><?php
		#
		#	Keep the backup on the server?
		#
		echo '<b>' . __('Would you like to delete the backup on the server if the email is correctly sent?', MEBAK_LOCALE ) . '</b>'

				.'<br /><i>'

					. __('If the mail command used to send the backup reports no errors, you may like to remove the backup from the server.', MEBAK_LOCALE )
					. '<br />'
					. __('However think twice before enabling this option as the email can leave this server but never be able to reach the destination...', MEBAK_LOCALE )

				.'</i>'
			;

		$remove_n ='';
		$remove_y ='';

		switch($_POST['meb_email_backup_remove']) {

			case '1': $remove_y = ' checked="checked"'; break;
			case '0':
			default:
				$remove_n = ' checked="checked"';
		}

	?></div>
	<div class="right">
		<input type="radio" name="meb_email_backup_remove" id="c_remove_n" value="0"<?php echo $remove_n; ?>/> <label for="c_remove_n"><?php
			_e('No', MEBAK_LOCALE ); ?></label>
		<input type="radio" name="meb_email_backup_remove" id="c_remove_y" value="1"<?php echo $remove_y; ?>/> <label for="c_remove_y"><?php
			_e('Yes', MEBAK_LOCALE ); ?></label>
	</div>
	<div style="clear:both;"></div>
</div>

<?php

//if(MYEASYBACKUP_FAMILY>9 && file_exists(MEBAK_LITE_PATH.'lite-cron-setup.php')) {
if(defined('MYEASYBACKUP_FAMILY') && constant('MYEASYBACKUP_FAMILY')==='LITE') {

	#	Schedule backups
	#	LITE version
	#	@since 0.1.4
	#
	require_once(MEBAK_LITE_PATH.'lite-cron-setup.php');
}

/*
<div class"light|med|dark">
	<div class="left"></div>
	<div class="right"></div>
	<div style="clear:both;"></div>
</div>
*/

//if(!defined('MYEASYBACKUP_FAMILY') || (int)MYEASYBACKUP_FAMILY<1) {	#	0.1.3
if(defined('MYEASYBACKUP_FREE')) {

	#	free
	#
	?>
	<div style="margin:8px 0;text-align:center;"><?php
			#
			#	show credits
			#
			$checked ='';
			if($_POST['myeasy_showcredits']==1) { $checked = ' checked="checked"'; }

			echo '' . __('We invested a lot of time to create this plugin and its related sites, please allow us to place a small credit in your blog footer, here is how it will look:', MEBAK_LOCALE )
					. '<br />'
					. MEBAK_FOOTER_CREDITS
			;

			?><p><input type="checkbox" name="myeasy_showcredits" value="1"<?php echo $checked; ?> />&nbsp;<?php

				echo __('Yes, I like to help you!', MEBAK_LOCALE )
						. ' &mdash; ' . __('If you decide not to show the credits, please consider to <a href="http://myeasywp.com/helping-each-other/" target="_blank">make a donation</a>: you will help us to keep up with the developent.', MEBAK_LOCALE )
					. '</p>'
				;

	?></div><?php

	measycom_donate('meb');
}

//$disabled = ''; #   1.0.5
//if(defined('MEBAK_MISSING_UPLOAD_PATH')) {
//
//	echo MEBAK_MISSING_UPLOAD_PATH;
//	$disabled = ' disabled="disabled"';
//}
$disabled = ''; #   1.0.5.3

?>
<div class="button-separator">
	<input <?php echo $disabled; ?> class="button-primary" style="margin:14px 12px;" type="submit" name="btn" value="<?php echo SAVE_BTN; ?>" />
</div>

<script type="text/javascript">
	if(document.getElementById('handle_force_phpcode').checked==true){
		document.getElementById('sys_options').style.display='none';
	}else{
		document.getElementById('sys_options').style.display='block';
	}

</script>

</form><?php

measycom_camaleo_links();
?>