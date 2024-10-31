<?php
/*
Plugin Name: myEASYbackup
Plugin URI: http://myeasywp.com/plugins/myeasybackup/
Description: Backup your WordPress site (code and database) with a click.
Version: 1.0.11
Author: Ugo Grandolini aka "camaleo"
Author URI: http://grandolini.com
*/
/*
	Copyright (C) 2010,2012 Ugo Grandolini  (email : info@myeasywp.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
	*/


if(is_admin() && version_compare(PHP_VERSION, '5', '<')) {

	/**
	 * @since 1.0.5.6
	 */
	function myeasy_min_reqs() {

		echo '<div class="error">'
				. '<h3>' . __( 'Warning! It is not possible to activate this plugin as it requires PHP5 and on this server the PHP version installed is: ', MEBAK_LOCALE )
					. '<b>'.PHP_VERSION.'</b></h3>'
				. '<h3><a href="http://www.php.net/releases/#v4" target="_blank">' . __( 'PHP4 was discontinued by the PHP development team on December, 31 2007 !!', MEBAK_LOCALE ) .'</a>'
					. '</h3>'
				. '<p>' . __( 'For security reasons we <b>warmly suggest</b> that you contact your hosting provider and ask to update your account to PHP5.', MEBAK_LOCALE )
					. '</p>'
				. '<p>' . __( 'If they refuse for whatever reason we suggest to <b>change provider as soon as possible</b>.', MEBAK_LOCALE )
					. '</p>'
			.'</div>'
		;

		$plugins = get_option('active_plugins');
		$out = array();
		foreach($plugins as $key => $val) {

			if($val != 'myeasybackup/myeasybackup.php') {

				$out[$key] = $val;
			}
		}

		update_option('active_plugins', $out);
	}
	add_action('admin_head', 'myeasy_min_reqs');

	return;
}


define('MEBAK_FOOTER_CREDITS', '<div style="font-size:9px;text-align:center;"><a href="http://myeasywp.com" target="_blank">Improve Your Life, Go The myEASY Way&trade;</a></div>');

//if($_SERVER['SERVER_NAME']=='dwp') {
//
//	/**
//	 * debug only
//	 */
//	define('MYEASY_CDN', 'http://localhost/myEASY-CDN-RackSpace/myeasy-common/_source/');
//}
//}

/* 1.0.8: BEG */
//define('MYEASY_CDN', 'http://srht.me/f9'); # 0.1.4

$myeasycom_pluginname = '/myeasybackup/'; # 1.0.8.1

define('MYEASY_CDN', plugins_url() . $myeasycom_pluginname);
define('MYEASY_CDN_IMG', MYEASY_CDN . 'img/');
define('MYEASY_CDN_CSS', MYEASY_CDN . 'css/');
define('MYEASY_CDN_JS', MYEASY_CDN . 'js/');
/* 1.0.8: END */


/* 1.0.11: BEG */
if(!defined('AJAX_CALLER') || AJAX_CALLER == false) {

	if(!function_exists('wp_create_nonce')) {

		require_once( ABSPATH . 'wp-includes/pluggable.php');
	}
	$meb_backup_ajax_validator_key = dirname(__FILE__);
	$meb_backup_ajax_validator = wp_create_nonce($meb_backup_ajax_validator_key);

	define('MEB_BACKUP_AJAX_VALIDATOR_KEY', $meb_backup_ajax_validator_key);
	define('MEB_BACKUP_AJAX_VALIDATOR', $meb_backup_ajax_validator);
}
/* 1.0.11: END */


if(get_option('myeasy_showcredits')==1 && !function_exists('myeasy_credits') && !defined('MYEASY_SHOWCREDITS')) {    /* 0.9.1 changed all references from 'meb_showcredits' */

	/**
	 * on demand, show the credits on the footer
	 */
	add_action('wp_footer', 'myeasy_credits');
	function myeasy_credits() {

		echo MEBAK_FOOTER_CREDITS;
		define('MYEASY_SHOWCREDITS', true);
	}
}

if(!is_admin()) {

	/**
	 * @since 0.9.2 - remove all the data sets older than a certain number of days
	 */
	$meb_remove_oldds = (int)get_option( 'meb_remove_oldds' );
	$meb_remove_oldds_last_exec = get_option( 'meb_remove_oldds_last_exec' );

	if($meb_remove_oldds_last_exec!=date('Y-m-d', time()) && $meb_remove_oldds>0 && strlen(MEBAK_BACKUP_PATH)>0) {

		/**
		 * does it only once a day
		 */
		require_once('meb-config.php');

		$removetime = (int)time();
		$removetime = $removetime - (int)($meb_remove_oldds*(24*60*60));
		update_option( 'meb_remove_oldds_last_exec', date('Y-m-d', time()) );

		$datasets = scandir(MEBAK_BACKUP_PATH);

		if(count($datasets)>2)
		{
			#	if the directory is empty there are at least two dirs: '.' and '..'
			#
			foreach($datasets as $ds)
			{
				if($ds!='.' && $ds!='..'
					&& substr($ds,0,12)=='myEASYbackup'
					&& (substr($ds,-4)=='.zip'
						|| substr($ds,-4)=='.tar'
						|| substr($ds,-4)=='.tgz')
				) {

					if(file_exists(MEBAK_BACKUP_PATH . '/' . $ds)
							&& is_file(MEBAK_BACKUP_PATH . '/' . $ds)
							&& filemtime(MEBAK_BACKUP_PATH . '/' . $ds)<$removetime) {

						@unlink(MEBAK_BACKUP_PATH . '/' . $ds);
					}
				}
			}
		}
	}
}

if(!is_admin() && file_exists(MEBAK_LITE_PATH.'lite-cron-init.php')) {

	/**
	 * @since 0.9.1 - when its not logged as admin, execute the cron backup
	 */
	require_once('meb-config.php');
	require_once('inc/myEASYcom.php');
	require_once(MEBAK_LITE_PATH.'lite-cron-init.php');
}

if(is_admin()) {

	/**
	 * @since 0.9.0 - the code is executed only when in the admin pages
	 */
//	if(!isset($_SESSION)) { session_start(); $_SESSION['id'] = session_id(); } # 0.9.1

	require_once('meb-config.php');
	require_once('inc/myEASYcom.php');


$myeasywp_news = new myeasywp_news();
$myeasywp_news->ref_code = 'meb';
$myeasywp_news->ref_family = MYEASYBACKUP_FAMILY;
//$myeasywp_news->ref_family = false;//debug
$myeasywp_news->plugin_init();


//	if(defined('MYEASYBACKUP_FAMILY') && MYEASYBACKUP_FAMILY>0 && file_exists(dirname(__FILE__).'/inc/lite-cron-init.php')) {
	if(defined('MYEASYBACKUP_FAMILY') && constant('MYEASYBACKUP_FAMILY')==='LITE') {

		$html_left = '';
		$html = '';

		if(function_exists('wp_next_scheduled') && function_exists('wp_schedule_event')) {

			/**
			 * cron is called at every page refresh
			 */
			require_once(MEBAK_LITE_PATH.'lite-cron-init.php');

			/**
			 * dashboard widget
			 */
			add_action('wp_dashboard_setup', 'mebLITE_add_dashboard_widget');

			/**
			 * adding the plugin own dashboard widget
			 */
			function mebLITE_add_dashboard_widget() {

				wp_add_dashboard_widget('myEASYbackup-LITE', 'myEASYbackup LITE &ndash; Backups schedule info', 'mebLITE_dashboard_widget_function');
			}

			function mebLITE_dashboard_widget_function() {

				$datetime = get_option('date_format') . ' ' . get_option('time_format');
				$now = time();
				$gmt = (get_option('gmt_offset')*3600);
				$now_gmt = $now + $gmt;

//				$schedule_choices = apply_filters('meb_cb_schedule_choices', wp_get_schedules());

				$next_scheduled_time = wp_next_scheduled('meb_backup_cron');

				$html_left = ''
//				        .'<div style="background-color:#bfbfbf;border:1px solid #CFCFCF;margin:8px;padding:6px;-moz-border-radius:6px;border-radius:6px;">'

//						. '<p style="font-weight:bold;margin-top:0;">' . __( 'Schedule info', MEBAK_LOCALE ) . '</p>'

						. '<p>'
							. __( 'Results of your last scheduled backup', MEBAK_LOCALE ) . ' ('
							. __( 'a date means that the last scheduled backup completed successfully otherwise you will see a description of the error that occurred', MEBAK_LOCALE )
								. '):</p>'

							. '<p style="font-weight:bold;">'
								. get_option('meb_cron_last_exec')
								. '</p>'

						. '<p>' . __( 'Total scheduled backups already made', MEBAK_LOCALE ) . ': <b>' . get_option('meb_cron_triggercount') . '</b></p>'

						. '<p style="font-weight:bold;">' . __( 'Next schedule', MEBAK_LOCALE ) . '</p>'
				;

//$html_left .= date('d/m/y H:i:s', $now).' | '.date('d/m/y H:i:s', $next_scheduled_time);

				if($now<$next_scheduled_time) {

					$diff = $next_scheduled_time - $now;

					$y = 365*60*60*24;
					$m = 30*60*60*24;
					$d = 60*60*24;
					$h = 60*60;

					$years = floor($diff / $y);
					$months = floor(($diff - $years * $y) / $m);
					$days = floor(($diff - $years * $y - $months*$m) / $d);
					$hours = floor(($diff - $years * $y - $months*$m - $days*$d)/ $h);
					$minutes = floor(($diff - $years * $y - $months*$m - $days*$d - $hours*$h)/ 60);
					$seconds = floor(($diff - $years * $y - $months*$m - $days*$d - $hours*$h - $minutes*60));

					$html_left .= ''
						. '<p style="margin-left:20px;">' . __( 'Scheduled at (server time)', MEBAK_LOCALE ) . ': <b>' . date($datetime, $next_scheduled_time) . '</b></p>'
						. '<p style="margin-left:20px;">' . __( 'Scheduled at (your time)', MEBAK_LOCALE ) . ': <b>' . date($datetime, ($next_scheduled_time+$gmt)) . '</b></p>'
						. '<p style="margin-left:20px;">' . __( 'Will be done in', MEBAK_LOCALE ) . ':';

					if($years>0) {

						$html_left .= ''
									. ' <b>' . $years . '</b> ' . __( 'years', MEBAK_LOCALE );
					}

					if($months>0) {

						if($months>1) {

							$html_left .= ''
									. ' <b>' . $months . '</b> ' . __( 'months', MEBAK_LOCALE );
						}
						else {

							$html_left .= ''
									. ' <b>' . $months . '</b> ' . __( 'month', MEBAK_LOCALE );
						}
					}

					if($days>0) {

						if($days>1) {

							$html_left .= ''
									. ' <b>' . $days . '</b> ' . __( 'days', MEBAK_LOCALE );
						}
						else {

							$html_left .= ''
									. ' <b>' . $days . '</b> ' . __( 'day', MEBAK_LOCALE );
						}
					}

					if($hours>0) {

						if($hours>1) {

							$html_left .= ''
									. ' <b>' . $hours . '</b> ' . __( 'hours', MEBAK_LOCALE );
						}
						else {

							$html_left .= ''
									. ' <b>' . $hours . '</b> ' . __( 'hour', MEBAK_LOCALE );
						}
					}

					if($minutes>0) {

						if($minutes>1) {

							$html_left .= ''
										. ' <b>' . $minutes . '</b> ' . __( 'minutes', MEBAK_LOCALE );
						}
						else {

							$html_left .= ''
										. ' <b>' . $minutes . '</b> ' . __( 'minute', MEBAK_LOCALE );
						}
					}

					$html_left .= ''
								. ' <b>' . $seconds . '</b> ' . __( 'seconds', MEBAK_LOCALE )
							.'</p>';
				}
				else {

					$html_left .= '<p style="margin-left:20px;font-weight:bold;">'
							. __( 'Time to backup: as soon as an unlogged user visit the blog, a new backup will be created.', MEBAK_LOCALE ) . '</p>';
				}

				//$html_left .= 'server_time='.date($datetime, $now).'<br>';
				//$html_left .= 'next_scheduled_time='.date($datetime, $next_scheduled_time).'<br>';

				$html_left .= ''
						.'<p style="font-weight:bold;">' . __( 'Server time', MEBAK_LOCALE ) . '</p>'
						.'<p style="margin-left:20px;font-weight:bold;">' . date($datetime, $now) . '</p>'
						.'<p style="font-weight:bold;">' . __( 'Your time', MEBAK_LOCALE )
								. ' (<a href="options-general.php" target="_blank">' . __( 'check/edit your time preferences', MEBAK_LOCALE ) . '</a>)'
							. '</p>'
						.'<p style="margin-left:20px;font-weight:bold;">' . date($datetime, $now_gmt) . '</p>'
				;

//	    		$html_left .= '</div>';

				echo $html_left;
			}
		}
		else {

			$html = '<p style="color:red;font-weight:bold;">'
						. __('Sorry but the WordPress schedule tool is not available on this system.', MEBAK_LOCALE )
					. '</p>'

				. '<p>'
					. __('If you are running WordPress 2.0 or and older version, please consider to upgrade to the latest version.', MEBAK_LOCALE )
				. '</p>'
			;

			echo $html;
		}
	}

//echo 'MEBAK_PATH='.MEBAK_PATH.'<br>';
	$time = time(); # 1.0.8.1

	wp_enqueue_style( 'meb_style', MYEASYBACKUP_LINK.'css/screen.css', '', $time , 'screen' );							#	0.1.4
	wp_enqueue_style( 'myeasywp_calendar', MYEASYBACKUP_LINK.'css/dhtmlgoodies_calendar.css', '', $time , 'screen' );	#	0.1.4
	wp_enqueue_style( 'myeasywp_common', MYEASY_CDN_CSS . 'myeasywp.css', '', $time , 'screen' );		                #	1.0.8

//	wp_enqueue_script( 'myeasybackup_core_js', MYEASYBACKUP_LINK.'js/myeasybackup.js', '', $time , false );				#	0.1.4
//	wp_enqueue_script( 'myeasybackup_core_jsphp', MYEASYBACKUP_LINK.'js/myeasybackup.js.php', '', $time , false );		#	0.1.3
	wp_enqueue_script( 'myeasybackup_core_js', MYEASYBACKUP_LINK.'js/myeasybackup.js', '', $time , false );			    /* 1.0.6.1 */

	wp_enqueue_script( 'myeasywp_ajax_js', MYEASYBACKUP_LINK.'js/ajax_ro.js', '', $time , false );						#	0.1.3
	wp_enqueue_script( 'myeasywp_calendar_js', MYEASYBACKUP_LINK.'js/dhtmlgoodies_calendar.js', '', $time , false );	#	0.1.4
	wp_enqueue_script( 'myeasywp_common', MYEASY_CDN_JS . 'myeasywp.js', '', $time, false );		                    #	1.0.8

	define('MEBAK_POPWIN', '<div id="myeasybackup_popWin" style="display:none;">'

#
#	0.0.6: BEG
#-------------
			.'<div id="wait_backup" style="display:none;">'
				.'<img src="'.MYEASY_CDN_IMG.'adding-files.png" /><br />'

				.'<p style="margin:12px 0;">' . __( 'Please wait, backup in progress...', MEBAK_LOCALE ) . '</p>'

				.'<img src="'.MYEASY_CDN_IMG.'wait.gif" /><br />'
			.'</div>'
			.'<div id="wait_upload" style="display:none;">'
				.'<img src="'.MYEASY_CDN_IMG.'uploading.png" /><br />'

				.'<p style="margin:12px 0;">' . __( 'Please wait, uploading in progress...', MEBAK_LOCALE ) . '</p>'

				.'<img src="'.MYEASY_CDN_IMG.'wait.gif" /><br />'
			.'</div>'
#
#	0.9.1: BEG
#-------------
			.'<div id="drftp_list" style="display:none;padding-top:12px;">'
				.'<img src="'.MYEASY_CDN_IMG.'webally-160.png" /><br />'

				.'<p style="margin:12px 0;">' . __( 'Please choose the Dressing Room&trade; where you want to upload:', MEBAK_LOCALE ) . '</p>'

				.'<div id="drftp_vars" style="display:block;"></div>'
				.'<div id="drftp_select" style="display:block;"></div>'
			.'</div>'
#-------------
#	0.9.1: END
#
			.'<div id="wait_progress" style="margin:12px 0;"></div>'
#-------------
#	0.0.6: END
#

		.'</div>'
	);

	define('MEBAK_BACK_BUTTON',
			'<div>'
				.'<input type="button" class="button-secondary" style="cursor:pointer;" value="'
							. __( 'Back to the main page', MEBAK_LOCALE )
//							. '" onclick="javascript:window.location=\''.$_SERVER['PHP_SELF'].'?page=myEASYbackup_tools\';" />' # 1.0.2
//							. '" onclick="javascript:window.location=\''.(is_ssl() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'].MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools\';" />' # 1.0.2 - 1.0.5.4
							. '" onclick="javascript:window.location=\''.MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools\';" />' # 1.0.5.4
			.'</div>'
			.'<script type="text/javascript">'
				.'var el=document.getElementById(\'backingup\');'
				.'if(el){'
					.'el.style.display=\'none\';'
				.'}'
			.'</script>'
	);

	define('MEBAK_SETTINGS_BUTTON',
			'<div>'
				.'<input type="button" class="button-secondary" style="cursor:pointer;" value="'
							. __( 'Open the settings page', MEBAK_LOCALE )
							. '" onclick="javascript:window.location=\'options-general.php?page=myEASYbackup_options\';" />'
			.'</div>'
	);						#	0.1.2

//echo 'MYEASYBACKUP_LINK:'.MYEASYBACKUP_LINK.'<br>';

	#
	#	hook to add admin menus
	#
	add_action('admin_menu', 'myEASYbackup_add_pages');

	load_plugin_textdomain( MEBAK_LOCALE, 'wp-content/plugins/langs/' . $myEASYbackup_dir, $myEASYbackup_dir .'/langs/' );	#	0.0.5

	#
	#	hook to initialize the plugin @since 0.1.3
	#
	register_activation_hook(__FILE__,'meb_install');

	#
	#	support functions
	#
	function checkvariables() {
		#
		#	check the variables
		#
		$error = false;

		while(list($key, $val) = each($_POST)) {

			if( preg_match( '/[;&<>]+/i' , $val ) ) {
				$error = true;
			}
			else {
				$key = trim( $val );
			}
		}
		if($error==true) {
			echo '<div class="error">'
					. __( 'Invalid characters in one of your variables.', MEBAK_LOCALE )
			.'</div>';
			return true;
		}
		return false;
	}

//define('isZIPARCHIVE', false); # debug


	if(	class_exists(ZipArchive)									#	0.0.2
//		&& class_exists(RecursiveIteratorIterator)					#	0.1.4
	) {
		if(version_compare(PHP_VERSION, '5', '<')					#	0.0.5
//			|| 1==1     #   debug
		) {

			/**
			 * PHP 4
			 */
			require_once(MEBAK_PATH . 'inc/class.php4.addDir.php');
		}
		else {

			/**
			 * PHP 5
			 */
			require_once(MEBAK_PATH . 'inc/class.php5.addDir.php');
		}

		define('isZIPARCHIVE', true);				#	0.0.2
	}
	else {

		define('isZIPARCHIVE', false);				#	0.0.2
	}


	#
	#	core functions
	#
	function myEASYbackup_add_pages() {

		#
		#	settings submenu
		#
		add_options_page(__( 'myEASYbackup', MEBAK_LOCALE ), __( 'myEASYbackup', MEBAK_LOCALE ), 'administrator', 'myEASYbackup_options', 'myEASYbackup_options_page');	#	0.0.5

		#
		#	Add the main page
		#
		add_management_page(__( 'myEASYbackup', MEBAK_LOCALE ), __( 'myEASYbackup', MEBAK_LOCALE ), 'administrator', 'myEASYbackup_tools', 'myEASYbackup_tools_page');
	}

	function meback_contextual_help($text) {

		#
		#	contextual help
		#	@since 0.1.4
		#
		if(defined('mebak_DEBUG') && mebak_DEBUG==true) {

			require_once(MEBAK_PATH . 'inc/myDEBUG.php');	#	output in $html
		}

		switch($_GET['page']) {

			case 'myEASYbackup_options':
				$text = '<h5>' . __( 'Help page for myEASYbackup Settings', MEBAK_LOCALE ) . '</h5>'

						.'<p>' . __( 'On this page you set the options needed to myEASYbackup to know how you like it to perform the backups.', MEBAK_LOCALE ) . '</p>'
				;


				break;
				#
			case 'myEASYbackup_tools':
				$text = '<h5>' . __( 'Help page for myEASYbackup', MEBAK_LOCALE ) . '</h5>'

						.'<p>' . __( 'This is the main myEASYbackup page, where you define what (database, installation, restore init) to backup and start the job.', MEBAK_LOCALE ) . '</p>'
						.'<p>' . __( 'From this page you can also download your backups as well as delete and/or upload them to another server.', MEBAK_LOCALE ) . '</p>'
				;
				break;
				#
		}

		$text .= ''
					.'<p>'

						. __( 'Before asking support please visit the plugin ', MEBAK_LOCALE )

						. '<a href="http://myeasywp.com/plugins/myeasybackup/faq/" target="_blank">'
								. __( 'FAQ page', MEBAK_LOCALE ) . '</a> '

						. __( 'where you can find all the reported problems with their corresponding solutions.', MEBAK_LOCALE )

						. '<br />' . __( 'The FAQ page is being updated as soon as a new issue, and its solution, is discovered.', MEBAK_LOCALE )

					. '</p>'

					.$html	 #	debug info
		;

		return $text;
	}
	add_action('contextual_help', 'meback_contextual_help');


	function myEASYbackup_options_page() {

		#
		#	Settings page	@since 0.0.5
		#
		global $myeasywp_news;

		echo '<div class="wrap">'
			//.'<div id="icon-options-general" class="icon32"><br /></div>'																	#	0.1.4
			.'<div id="icon-options-general" class="icon32" style="background:url('.MYEASY_CDN_IMG.'icon.png);"><br /></div>'
			.'<h2 id="meb-h2-settings">' . __( 'myEASYbackup: Settings', MEB_LOCALE )
		;

		echo ''
			//.'<img src="'.MYEASYBACKUP_LINK.'img/lifesaver.png" style="margin-left:12px;cursor:pointer;" align="absmiddle" width="36px" '
			//		.'alt="'.__( 'Looking for help?', MEBAK_LOCALE ).'" title="'.__( 'Looking for help?', MEBAK_LOCALE ).'" '
			//		.'onclick="window.location=\'http://myeasywp.com/plugins/myeasybackup/faq/\'" />'
			.'</h2>';	#	0.1.4


//		if(version_compare(PHP_VERSION, '5', '<')) {
//
//			/**
//			 * @since 1.0.5.5
//			 */
//			echo '<div class="warning" style="width:auto;margin-left:0;">'
//					. '<h3>' . __( 'Warning! myEASYbackup requires PHP5 and on this server the PHP version installed is: ', MEB_LOCALE )
//						. '<b>'.PHP_VERSION.'</b></h3>'
//					. '<p>' . __( 'PHP4 was updated last time on August, 7 2008 and its now discontinued by the PHP development team.', MEB_LOCALE )
//						. '</p>'
//					. '<p>' . __( 'For security reasons we <b>warmly suggest</b> that you contact your hosting provider and ask to update your account to PHP5.', MEB_LOCALE )
//						. '</p>'
//					. '<p>' . __( 'If they refuse for whatever reason we suggest to <b>change provider as soon as possible</b>.', MEB_LOCALE )
//						. '</p>'
//				.'</div>'
//			;
//
//			return;
//		}

$myeasywp_news->print_html();

		require(MEBAK_PATH . 'meb_settings.php');

		echo '</div>';
	}

	function has_data($value) {

		if(is_array($value)) {

			return (sizeof($value) > 0) ? true : false;
		}
		else {

			return (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) ? true : false;
		}
	}

	function myEASYbackup_tools_page() {

		#
		#	Main page
		#
		global $table_prefix;								#	0.0.6
		global $wpdb;										#	@since 0.1.3
		global $myeasywp_news;

		echo '<div class="wrap">'
				//.'<div id="icon-tools" class="icon32"><br /></div>'																			#	0.1.4
				.'<div id="icon-options-general" class="icon32" style="background:url('.MYEASY_CDN_IMG.'icon.png);"><br /></div>'
				.'<h2>myEASYbackup: ' . __( 'Backup, restore and migrate your WordPress installation', MEBAK_LOCALE )
		;

		echo ''
			//.'<img src="'.MYEASYBACKUP_LINK.'img/lifesaver.png" style="margin-left:12px;cursor:pointer;" align="absmiddle" width="36px" '
			//		.'alt="'.__( 'Looking for help?', MEBAK_LOCALE ).'" title="'.__( 'Looking for help?', MEBAK_LOCALE ).'" '
			//		.'onclick="window.location=\'http://myeasywp.com/plugins/myeasybackup/faq/\'" />'
			.'</h2>';	#	0.1.3


		//if(defined('isLINUX') && isLINUX==true)
		//{
		//	echo ' <img src="'.MYEASYBACKUP_LINK.'img/linux.png" alt="' . __( 'This is a Linux server', MEBAK_LOCALE ) . '" title="' . __( 'This is a Linux server', MEBAK_LOCALE ) . '" align="absmiddle" width="24" />';
		//}
		//else if(defined('isWINDOWS') && isWINDOWS==true)
		//{
		//	echo ' <img src="'.MYEASYBACKUP_LINK.'img/windows.png" alt="' . __( 'This is a Windows server', MEBAK_LOCALE ) . '" title="' . __( 'This is a Windows server', MEBAK_LOCALE ) . '" align="absmiddle" width="24" />';
		//}

		if(defined('MEBAK_MISSING_UPLOAD_PATH')) {

			echo MEBAK_MISSING_UPLOAD_PATH;
		}

		if(!isset($_POST['cdatabase']) || $_POST['cdatabase']=='') {				#	0.1.3

			$_POST['cdatabase'] = 1;
		}

		if(!isset($_POST['cwordpress']) || $_POST['cwordpress']=='') {				#	0.1.3

			$_POST['cwordpress'] = 1;
		}

		if(!isset($_POST['upload_restore_tool'])		#	0.0.6
			&& $_POST['_action']!='backup'				#	0.1.3
		) {

			$_POST['upload_restore_tool'] = 1;
			$_POST['upload_restore_tool_exec'] = 1;		#	0.1.2
		}

		echo '</h2>'

				.'<div id="ftp_result"></div>'	#	0.1.4

//				.'<form id="ftp_upload" name="ftp_upload" method="post" action="'.$_SERVER['PHP_SELF'].'?page=myEASYbackup_tools">' # 1.0.2
				.'<form id="ftp_upload" name="ftp_upload" method="post" action="'
//								.(is_ssl() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'].MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools">' # 1.0.2 - 1.0.5.4
								.MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools">' # 1.0.5.4

					.'<input type="hidden" id="ftp_pwd" name="ftp_pwd" value="'.$_POST['ftp_pwd'].'" />'
					.'<input type="hidden" id="ftp_action" name="ftp_action" value="'.$_POST['ftp_action'].'" />'
					.'<input type="hidden" id="ftp_file" name="ftp_file" value="'.$_POST['ftp_file'].'" />'
					.'<input type="hidden" id="upload_restore_tool_" name="upload_restore_tool" value="'.$_POST['upload_restore_tool'].'" />'	#	0.0.6
				.'</form>'

#
#	0.1.0: BEG
#-------------
//				.'<form id="dwn_backup" name="dwn_backup" method="post" action="'.$_SERVER['PHP_SELF'].'?page=myEASYbackup_tools">' # 1.0.2
				.'<form id="dwn_backup" name="dwn_backup" method="post" action="'
//								.(is_ssl() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'].MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools">' # 1.0.2 - 1.0.5.4
								.MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools">' # 1.0.5.4

					.'<input type="hidden" id="dwn_action" name="dwn_action" value="'.$_POST['dwn_action'].'" />'
					.'<input type="hidden" id="dwn_file" name="dwn_file" value="'.$_POST['dwn_file'].'" />'
				.'</form>'
				.'<form id="dwn_exec_form" name="dwn_exec_form" method="post" action="'.MYEASYBACKUP_LINK.'meb_download.php">'
					.'<input type="hidden" id="dwn_exec" name="dwn_action" value="'.$_POST['dwn_action'].'" />'
					.'<input type="hidden" id="dwn_exec_file" name="dwn_file" value="'.$_POST['dwn_file'].'" />'
				.'</form>'
#-------------
#	0.1.0: END
#


//.'<div class="warning">'
//	.'PHP_SELF['.$_SERVER['PHP_SELF'].'?page=myEASYbackup_tools'.']<br>'
//	.'MYEASYBACKUP_LINK['.MYEASYBACKUP_LINK.']<br>'
//	.'MEBAK_WP_PARENT_PATH['.MEBAK_WP_PARENT_PATH.']<br>'
//	.'MEBAK_PATH['.MEBAK_PATH.']<br>'
//	.'MEBAK_WP_PATH['.MEBAK_WP_PATH.']<br>'
//
//	.'<hr>NEW LINK['.(is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'].'/wp-admin/tools.php?page=myEASYbackup_tools'.']<br>'
//.'</div>'


//				.'<form method="post" name="exec_backup" id="exec_backup" action="'.$_SERVER['PHP_SELF'].'?page=myEASYbackup_tools">' # 1.0.2
				.'<form method="post" name="exec_backup" id="exec_backup" action="'
//								.(is_ssl() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'].MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools">' # 1.0.2 - 1.0.5.4
								.MEBAK_WPADMIN_PATH.'/tools.php?page=myEASYbackup_tools">' # 1.0.5.4

					.'<input type="hidden" id="_action" name="_action" value="" />'
					.'<input type="hidden" id="z_password" name="z_password" value="" />'
					.'<input type="hidden" id="choose_database_" name="cdatabase" value="'.$_POST['cdatabase'].'" />'		#	0.1.3
					.'<input type="hidden" id="choose_wordpress_" name="cwordpress" value="'.$_POST['cwordpress'].'" />'	#	0.1.3
					.'<input type="hidden" id="upload_restore_tool_exec_" name="upload_restore_tool_exec" value="'.(int)$_POST['upload_restore_tool_exec'].'" />'	#	0.1.2
					.MEBAK_POPWIN
		;

//var_dump($_POST);
//die();
//echo '<hr>';var_dump($_SERVER);echo '<hr>';
//echo 'HTTP_REFERER:'.$_SERVER['HTTP_REFERER'].'<br>';

		$disabled = ini_get('disable_functions');		#	0.0.5

//echo 'disabled['.$disabled.']<br>';	#	debug


#
#	1.0.0: BEG
#-------------
		if(strlen(get_option( 'meb_backup_root' ))==0) {

			echo '<div class="warning" style="float:left;">'
					.__( 'Please be sure to <a href="options-general.php?page=myEASYbackup_options">setup the plugin options</a> before tyring to use it!', MEBAK_LOCALE )
				.'</div>'
			;

			return;
		}
#-------------
#	1.0.0: END
#

		if(defined('isWINDOWS') && isWINDOWS==true) {

			/**
			 * running on a windows server
			 * @since 1.0.6
			 */
			?><img src="<?php echo MYEASYBACKUP_LINK; ?>img/warning.png"
							alt="WARNING" align="absmiddle" /> <?php

			echo '<span style="font-weight:bold;color:orange;">'

					. __( 'Sorry, this plugin runs only on a Linux server.', MEBAK_LOCALE )

				. '</span>'
			;

			echo CHANGE_HOST;

			return;
		}

		if(version_compare(PHP_VERSION, '5', '<')) {

			/**
			 * running on PHP4
			 * @since 1.0.6
			 */
			?><img src="<?php echo MYEASYBACKUP_LINK; ?>img/warning.png"
							alt="WARNING" align="absmiddle" /> <?php

			echo '<span style="font-weight:bold;color:orange;">'

					. __( 'Sorry, this plugin requires PHP5.', MEBAK_LOCALE )

				. '</span>'
			;

			echo CHANGE_HOST;

			return;
		}




		if((defined('isWINDOWS') && isWINDOWS==true						#	0.0.4
				&& defined('isZIPARCHIVE') && isZIPARCHIVE==false)		#	0.0.2
		) {

			/**
			 * ZipArchive is not installed on the server :/
			 */
			?><img src="<?php echo MYEASYBACKUP_LINK; ?>img/warning.png"
							alt="WARNING" align="absmiddle" /> <?php

			echo '<span style="font-weight:bold;color:orange;">'

					. __( 'Missing the ZipArchive class', MEBAK_LOCALE )

				. '</span>'
			;

			?><div id="id"><?php

				echo __( 'Current PHP version on this server', MEBAK_LOCALE )

						. ': <b>' . phpversion() . '</b><br />'
				;

				if(version_compare(PHP_VERSION, '4.1.0', '<=')) {

					echo '<p>'
							. __( 'You are running your site on a server that is not updated since 2001!', MEBAK_LOCALE )
						. '</p>'
					;
					echo '<p>'
							. __( 'Please consider to take one of the following actions as soon as possible:', MEBAK_LOCALE )
						. '<ul>'
							. '<li> &raquo; '
								. __( 'ask your provider to update the server configuration', MEBAK_LOCALE )
							. '</li>'
							. '<li> &raquo; '
								. __( 'change provider, can you trust a provider running such an old configuration still in the ', MEBAK_LOCALE )
								. date('Y', time())
								.'?'
							. '</li>'
						. '</ul>'
						. '</p>'
					;
				}
				else if(version_compare(PHP_VERSION, '5', '<')) {

					echo '<p>'
							. __( 'Instructions to install the ZipArchive class are', MEBAK_LOCALE )
							. ' <a href="http://php.net/manual/zip.installation.php" target="_blank"><b>'

								. __( 'available here', MEBAK_LOCALE )

							. '</b></a>'
						. '</p>'
					;

					echo '<p>'
							. __( 'However ', MEBAK_LOCALE )
							.'<span style="font-style:italic;">&laquo;'

								. __( "PHP 4 shows its age. Most of PHP 4's shortcomings have been addressed by PHP 5,", MEBAK_LOCALE )

								.' <b>'
									. __( "released on 2004...", MEBAK_LOCALE )
								.'</b>'

							. '&raquo;</span>'
						. '</p>'
					;

					echo '<p>'
							. __( 'For further information about why you should consider to ask your provider to update the server configuration, visit ', MEBAK_LOCALE )

							. ' <a href="http://gophp5.org" target="_blank"><b>'

								. __( 'gophp5.org', MEBAK_LOCALE )

							. '</b></a>'
						. '</p>'
					;

				}
				else {

					echo '<p>'
							. __( 'Instructions to install the ZipArchive class are', MEBAK_LOCALE )
							. ' <a href="http://php.net/manual/zip.installation.php" target="_blank"><b>'

								. __( 'available here', MEBAK_LOCALE )

							. '</b></a>'
						. '</p>'
					;
				}

				echo CHANGE_HOST;

			?></div><?php

			return;
		}

		if(isSAFEMODE==true) {											#	0.0.5

			#	its not possible to write files on a safe_mode enabled server :/
			#
			echo '<div class="error">'
				. '<h3>'
					. __( 'WARNING: SAFE_MODE is enabled on your server PHP configuration!', MEBAK_LOCALE )
				. '<br />'
					. __( 'Due to this setting myEASYbackup is not able to write the data set on your server.', MEBAK_LOCALE )
				. '</h3>'

				. '<p>' . __( 'This setting was an attempt to solve the shared-server security problem, is deprecated since in PHP 5.3.0 and will be removed in PHP 6.0.0.', MEBAK_LOCALE ) . '</p>'

				. '<p>'
					.'<a href="http://forums.digitalpoint.com/showthread.php?t=916" target="_blank">'
						. __( 'Click here for further info about the SAFE_MODE setting', MEBAK_LOCALE )
					.'</a>'

					.' | <a href="http://php.net/manual/features.safe-mode.php" target="_blank">'
						. __( 'Read more about SAFE_MODE on the PHP site', MEBAK_LOCALE )
					.'</a>'
				. '</p>'

				.CHANGE_HOST

			.'</div>';

			return;
		}

		#
		#	create the backup folder if not present			#	0.0.5: revised for safe_mode enabled servers
		#
		if(strlen(MEBAK_BACKUP_PATH)>0) { # 1.0.5

			if(!file_exists(MEBAK_BACKUP_PATH)) {

				$result = mkdir(MEBAK_BACKUP_PATH);
				chmod(MEBAK_BACKUP_PATH, 0755);

				if($result==true) {

					?><div class="updated">
						<?php echo MEBAK_BACKUP_PATH . ': ' . __('successfully created!', MEBAK_LOCALE); ?>
					</div><?php
				}
				else {

					echo '<div class="error">'
						. __( 'Error when creating ', MEBAK_LOCALE ) . '<b>' . MEBAK_BACKUP_PATH . '</b>'

#
#	0.1.0: BEG
#-------------
						. '<br />'
						//. __( 'Please manually create the', MEBAK_LOCALE ) . ' <b>' . MEBAK_BACKUP_FOLDER . '</b> '	#	0.1.1
						. __( 'Please manually create the', MEBAK_LOCALE ) . ' <b>' . MEBAK_BACKUP_ROOT . '</b> '
						//. __( 'in your', MEBAK_LOCALE ) . ' <b>' . MEBAK_BACKUP_ROOT . '</b> '						#	0.1.1
						. __( 'folder using your preferred FTP program.', MEBAK_LOCALE ) . '<br />'
						. __( 'Then give it full privileges (at least 755) and reload this page.', MEBAK_LOCALE )
#-------------
#	0.1.0: END
#

					.'</div>';
					echo MEBAK_BACK_BUTTON;

					return;
				}
			}
		}

		#
		#	the backup folder is still not present :/
		#
		if(!file_exists(MEBAK_BACKUP_PATH)) {

			if(strlen(MEBAK_BACKUP_PATH)>0) { # 1.0.5

				echo '<div class="error">'
					. __( 'The backup folder does not exist ', MEBAK_LOCALE ) . '<b>' . MEBAK_BACKUP_PATH . '</b>'

#
#	0.1.0: BEG
#-------------
					. '<br />'
					//. __( 'Please manually create the', MEBAK_LOCALE ) . ' <b>' . MEBAK_BACKUP_FOLDER . '</b> '	#	0.1.1
					. __( 'Please manually create the', MEBAK_LOCALE ) . ' <b>' . MEBAK_BACKUP_ROOT . '</b> '
					//. __( 'in your', MEBAK_LOCALE ) . ' <b>' . MEBAK_BACKUP_ROOT . '</b> '						#	0.1.1
					. __( 'folder using your preferred FTP program.', MEBAK_LOCALE ) . '<br />'
					. __( 'Then give it full privileges (at least 755) and reload this page.', MEBAK_LOCALE )
#-------------
#	0.1.0: END
#

				.'</div>';
				echo MEBAK_BACK_BUTTON;
			}
			else {

				echo '<div class="warning">'
					. __( 'To be able to use this plugin, you need to setup the backup folder where the backups will be saved.', MEBAK_LOCALE )
					. '<br />'
					. __( 'You can setup the backup folder in the', MEBAK_LOCALE ) . ' "'
					. '<a href="options-general.php?page=myEASYbackup_options#backup_folder">'
						. __( 'Settings', MEBAK_LOCALE )
					. '</a>" '
					. __( 'menu.', MEBAK_LOCALE )

				.'</div>';
			}

			return;
		}

#
#	0.1.2: BEG
#-------------
		#
		#	using ZipArchive when its possible to use system()
		#
		if(	defined('isSYSTEM') && isSYSTEM==true
			&& defined('isZIPARCHIVE') && isZIPARCHIVE==true
			&& defined('FORCE_PHPCODE') && FORCE_PHPCODE==true
			//|| 1==1 # debug
		) {

			echo '<div class="updated" style="padding:6px;width:640px;">'

				. '<p>' . __( 'It looks like that you can get better performances as the following conditions are met:', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'it is possible to use the PHP `system()` command', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'the plugin settings are forcing it to use the ZipArchive class', MEBAK_LOCALE ) . '</p>'

				. '<p>'
					. __( 'In these conditions it is possible that PHP runs beyond the available memory allocated to your account. In such cases there is nothing I can do to override the limitation.', MEBAK_LOCALE ) . '</p>'

				. '<p>'
					. __( 'However, by opening the Setting page and disabling the "Use PHP code rather than system() commands" option, you will let the plugin to use the system() command overriding the memory limitation and getting better performances as system() is quite faster.', MEBAK_LOCALE ) . '</p>'

				. '<p>'
					. '<b>' . __('NEW! ', MEBAK_LOCALE ) . '</b> '
						. __( 'By using the system() command you are now able to password protect your data sets and decide if you want to create them with the Zip or the Tar system command.', MEBAK_LOCALE )
						. '</p>'

				. MEBAK_SETTINGS_BUTTON

				. '<p>'
					. __( 'Note: remember to save the new setting by clicking on the "Update Options" button once you have changed the setting!', MEBAK_LOCALE ) . '</p>'

			.'</div>';
		}

		#
		#	missing ZipArchive when forcing its use
		#
		if(	defined('isSYSTEM') && isSYSTEM==true
			&& defined('isZIPARCHIVE') && isZIPARCHIVE==false
			&& defined('FORCE_PHPCODE') && FORCE_PHPCODE==true
			//|| 1==1 # debug
		) {

			echo '<div class="updated" style="padding:6px;width:640px;">'

				. '<p>' . __( 'It looks like that you need to change your settings to be able to backup on this server as the following conditions are met:', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'it is possible to use the PHP `system()` command', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'the PHP', MEBAK_LOCALE )
							. ' <a href="http://php.net/manual/en/class.ziparchive.php" target="_blank">ZipArchive</a> '
						. __( 'class and the', MEBAK_LOCALE )
							.' <a href="http://php.net/manual/en/class.recursivedirectoryiterator.php" target="_blank">RecursiveIteratorIterator</a> '
						. __( 'are not installed on this server but the plugin settings are forcing its use', MEBAK_LOCALE ) . '</p>'


				. '<p>'
					. __( 'Solution: open the Setting page and disable the "Use PHP code rather than system() commands" options, then click on the "Update Options" button and try again to create a data set.', MEBAK_LOCALE ) . '</p>'

			.'</div>';
			echo MEBAK_SETTINGS_BUTTON;
			return;
		}

		#
		#	no way to backup :/
		#
		if(	defined('isSYSTEM') && isSYSTEM==false
			&& defined('isZIPARCHIVE') && isZIPARCHIVE==false
			//|| 1==1 # debug
		) {

			echo '<div class="error" style="padding:6px;width:640px;">'

				. '<p>' . __( 'It looks like that there is no way to backup on this server as the following conditions are met:', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'it is not possible to use the PHP `system()` command as it was disabled by your hosting provider', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'it is not possible to use the PHP ZipArchive class as it is not installed on this server', MEBAK_LOCALE ) . '</p>'

				. '<br />'
					. CHANGE_HOST

			.'</div>';
			echo MEBAK_BACK_BUTTON;
			return;
		}
#-------------
#	0.1.2: END
#

		//if($_GET['dwnfile']!='')													#	0.1.0
		if($_POST['dwn_action']=='download' && strlen($_POST['dwn_file'])>0) {		#	0.1.0

			#	download a data set
			#
			echo '<div class="updated" style="width:640px;">'
				. __( 'Downloading', MEBAK_LOCALE ) .'... '. MEBAK_BACKUP_PATH.'/'.$_POST['dwn_file']		#	0.1.0: added the file name
			.'</div>';
			echo MEBAK_BACK_BUTTON;

			?><script type="text/javascript">
				//window.location='<?php echo MYEASYBACKUP_LINK.'meb_download.php?dwnfile=' . $_GET['dwnfile']; ?>';	//	0.1.0
				document.dwn_exec_form.submit();				//	0.1.0
			</script><?php

			return;
		}

/**
 * 0.1.4: ftp upload moved to ajax
 *
 * if(isset($_POST['ftp_pwd']) && isset($_POST['ftp_action']) && isset($_POST['ftp_file']))
 *
 */

		if($_GET['dltfile']!='') {

			#	delete a data set
			#
			if(file_exists(MEBAK_BACKUP_PATH . '/' . $_GET['dltfile'])) {

				unlink(MEBAK_BACKUP_PATH . '/' . $_GET['dltfile']);
				echo '<div class="updated" style="width:640px;">'
						.'<b>' . $_GET['dltfile'] . '</b> '
						. __( 'deleted', MEBAK_LOCALE )
					.'</div>'
				;
			}

			/* ?><meta http-equiv="refresh" content="0; URL=<?php echo $_SERVER['PHP_SELF']; ?>?page=myEASYbackup_tools"><?php # 1.0.2 */
/*			?><meta http-equiv="refresh" content="0; URL=<?php echo (is_ssl() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'].MEBAK_WPADMIN_PATH; ?>/tools.php?page=myEASYbackup_tools"><?php */ # 1.0.2
			?><meta http-equiv="refresh" content="0; URL=<?php echo MEBAK_WPADMIN_PATH; ?>/tools.php?page=myEASYbackup_tools"><?php # 1.0.2
			return;
		}

		#
		#	set variables
		#

#
#	0.1.3: BEG
#-------------
		//if(trim($_POST['server'])=='')	{ $_POST['server'] = DB_HOST; }
		//if(trim($_POST['database'])=='')	{ $_POST['database'] = DB_NAME; }
		//if(trim($_POST['username'])=='')	{ $_POST['username'] = DB_USER; }
		//if(trim($_POST['password'])=='')	{ $_POST['password'] = DB_PASSWORD; }
		$_DB_HOST = DB_HOST;
		$_DB_NAME = DB_NAME;
		$_DB_USER = DB_USER;
		$_DB_PASSWORD = DB_PASSWORD;
#-------------
#	0.1.3: END
#

		if(trim($_POST['filename'])=='')	{ $_POST['filename'] = 'myEASYbackup__'.date('Y-m-d_H-i-s', time()); }

		//if(trim($_POST['cdatabase'])=='')	{ $cdatabase = ' checked="checked"'; }
		//if(trim($_POST['cwordpress'])=='')	{ $cwordpress = ' checked="checked"'; }

		if(!array_key_exists('_action', $_POST)) {

//			if(!defined('MYEASYBACKUP_FAMILY') || (int)MYEASYBACKUP_FAMILY<1) { 	#	0.1.3
//			if(defined('MYEASYBACKUP_FREE')) {
//
//				#	free
//				#
//				measycom_advertisement('meb');
//			}

$myeasywp_news->print_html();

			?>
			<!--<form enctype="multipart/form-data" method="post" name="myeasybackup" id="myeasybackup" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=myEASYbackup_tools">-->
			<!--	<input type="hidden" id="_action" name="_action" value="" />-->
			<div style="margin-bottom:20px;">

				<div class="ok" style="margin:0 0 20px 0;"><?php

						//_e( 'The full path of the new data set will be', MEBAK_LOCALE );					#	0.1.1
						echo __( 'The data set will be saved in this path', MEBAK_LOCALE ) . ':<br /><b>';	#	0.1.1

						//echo MEBAK_BACKUP_PATH . '/' . $_POST['filename'] . '.zip';	#	0.1.1
						echo MEBAK_BACKUP_PATH;											#	0.1.1

						$perms = fileperms(MEBAK_BACKUP_PATH);													#	0.1.4
						echo  '</b><br />' . __( 'Permissions: ', MEBAK_LOCALE ) . format_permissions($perms);	#	0.1.4

	//echo MEBAK_BACKUP_PATH.MEBAK_BACKUP_PATH.MEBAK_BACKUP_PATH.MEBAK_BACKUP_PATH.MEBAK_BACKUP_PATH;

					?></b><?php

					echo '<br /><i>'
							. __( 'Note: you can change this path in the', MEBAK_LOCALE ) . ' "'
							. '<a href="options-general.php?page=myEASYbackup_options#backup_folder">'
								. __( 'Settings', MEBAK_LOCALE )
							. '</a>" '
							. __( 'menu.', MEBAK_LOCALE )
						.'</i>';		#	0.1.1

				?></div><?php

echo '<div class="light" style="padding:8px;width:auto;">'; # 0.9.4

				echo '<h3 style="margin-top:0;">' . __( 'Create a new data set', MEBAK_LOCALE ) . '</h3>';

//echo 'MYEASYBACKUP_FREE['.MYEASYBACKUP_FREE.']<br>';
//echo 'MYEASYBACKUP_FAMILY['.MYEASYBACKUP_FAMILY.']<br>';
//echo 'MEBAK_PRO_PATH['.MEBAK_PRO_PATH.']<br>';

//				if(!defined('MYEASYBACKUP_FAMILY') || (int)MYEASYBACKUP_FAMILY<20) {	#	0.1.4
				if(defined('MYEASYBACKUP_FREE')
					|| (defined('MYEASYBACKUP_FAMILY') && constant('MYEASYBACKUP_FAMILY')!=='PRO')) {

					#	free
					#
					_e( 'Your database tables', MEBAK_LOCALE );

					if($_POST['cdatabase']==1) {

						$database_img = MYEASYBACKUP_LINK . 'img/database.png';
					}
					else {

						$database_img = MYEASYBACKUP_LINK . 'img/database-off.png';
					}

				?> &raquo; <img src="<?php echo $database_img; ?>" style="cursor:pointer;" width="48px" height="48px" title="<?php

									_e( 'Add the database tables to the data set? When colored yes, when greyed no: click to toggle.', MEBAK_LOCALE );

								?>" onclick="javascript:var eld=document.getElementById('choose_database_');
														var elw=document.getElementById('choose_wordpress_');
														if(eld.value==0){
															eld.value=1;
															this.src='<?php echo MYEASYBACKUP_LINK . 'img/database.png'; ?>';
														}else{
															eld.value=0;
															this.src='<?php echo MYEASYBACKUP_LINK . 'img/database-off.png'; ?>';
														};
														if(eld.value==0 && elw.value==0){
															document.getElementById('briefcase-block').style.display='block';
															document.getElementById('briefcase').style.display='none';
														}else{
															document.getElementById('briefcase-block').style.display='none';
															document.getElementById('briefcase').style.display='block';
														};"
								alt="<?php _e( 'DATABASE', MEBAK_LOCALE ); ?>" align="absmiddle" />
				<br /><?php

				/* ?> &raquo; <input id="cdb" type="checkbox" name="cdatabase"<?php echo $cdatabase; ?> /><label for="cdb">
						<img src="<?php echo MYEASYBACKUP_LINK; ?>img/database.png" alt="Add database" title="<?php

								_e( 'Check this to add the database tables to the data set', MEBAK_LOCALE );

							?>" align="absmiddle" />
					</label>
					<br /><?php */
				}

				/*[[pro-folders-selector]]*/
				if(defined('MYEASYBACKUP_FAMILY') && constant('MYEASYBACKUP_FAMILY')=='PRO'
					&& file_exists(MEBAK_PRO_PATH.'pro-folders-selector.php')) {	#	1.0.6
//				if(file_exists('inc/pro-folders-selector.php')) {			    #	0.1.2

					define('isPRO', true);
					include_once(MEBAK_PRO_PATH.'pro-folders-selector.php');
//					include_once('inc/pro-folders-selector.php');
				}
				else {

					define('isPRO', '');

					_e( 'Your WordPress folder', MEBAK_LOCALE );

					if($_POST['cwordpress']==1) {

						$wordpress_img = MYEASYBACKUP_LINK . 'img/wordpress-folder.png';
					}
					else {

						$wordpress_img = MYEASYBACKUP_LINK . 'img/wordpress-folder-off.png';
					}

					?> &raquo; <img src="<?php echo $wordpress_img; ?>" style="cursor:pointer;" width="48px" height="48px" title="<?php

									_e( 'Add the WordPress installation folder to the data set? When colored yes, when greyed no: click to toggle.', MEBAK_LOCALE );

								?>" onclick="javascript:var elw=document.getElementById('choose_wordpress_');
														var eld=document.getElementById('choose_database_');
														if(elw.value==0) {
															elw.value=1;
															this.src='<?php echo MYEASYBACKUP_LINK . 'img/wordpress-folder.png'; ?>';
														}else{
															elw.value=0;
															this.src='<?php echo MYEASYBACKUP_LINK . 'img/wordpress-folder-off.png'; ?>';
														};
														if(eld.value==0 && elw.value==0){
															document.getElementById('briefcase-block').style.display='block';
															document.getElementById('briefcase').style.display='none';
														}else{
															document.getElementById('briefcase-block').style.display='none';
															document.getElementById('briefcase').style.display='block';
														};"
									alt="<?php _e( 'WORDPRESS', MEBAK_LOCALE ); ?>" align="absmiddle" />
					<br /><?php

					/* ?> &raquo; <input id="cwp" type="checkbox" name="cwordpress"<?php echo $cwordpress; ?> /><label for="cwp">
						<img src="<?php echo MYEASYBACKUP_LINK; ?>img/wordpress-folder.png" alt="Add WordPress folder" title="<?php

								_e( 'Check this to add the WordPress installation folder to the data set', MEBAK_LOCALE );

							?>" align="absmiddle" />
					</label>
					<br /><?php */
				}

#
#	0.1.1: BEG
#-------------
				if(!is_writable(MEBAK_BACKUP_PATH)) {

					echo '<div style="padding:8px;background-color:orange;margin-bottom:20px;-moz-border-radius:6px;border-radius:6px;width:640px;">'

							. __( 'Warning: the selected path is', MEBAK_LOCALE )
							. ' <b>' . __( 'NOT', MEBAK_LOCALE ) . '</b> '
							. __( 'writable, it is', MEBAK_LOCALE )
							. ' <b>' . __( 'NOT', MEBAK_LOCALE ) . '</b> '
							. __( 'possible to proceed with the backup!', MEBAK_LOCALE )

					.'</div>';
				}
				else {

#-------------
#	0.1.1: END
#
					?><div><div style="float:left;margin:12px 8px 0 0;"><?php

//						echo __( 'Your data set', MEBAK_LOCALE ) . ' &raquo; ';
						if(isPRO==false)	#	0.1.2
						{
							_e( 'Click on the briefcase to create a new data set', MEBAK_LOCALE );
						}
						else
						{
							_e( 'Click on the briefcase to backup the selected folders', MEBAK_LOCALE );
						}
						echo ' &raquo; ';

					?></div><div id="briefcase-block" style="float:left;display:none;"><img src="<?php echo MYEASYBACKUP_LINK; ?>img/briefcase-stop.png" width="48px" height="48px" align="absmiddle" />
								<span style="margin-left:8px;color:red;font-weight:bold;;"><?php

								_e( 'Please choose what you do want to backup!', MEBAK_LOCALE );

							?></span></div>

							<div id="briefcase" style="float:left;display:block;">

								<img src="<?php echo MYEASYBACKUP_LINK; ?>img/dataset-off.png" width="48px" height="48px" style="cursor:pointer;"
									onmouseover="javascript:this.src='<?php echo MYEASYBACKUP_LINK; ?>img/dataset.png';"
									onmouseout="javascript:this.src='<?php echo MYEASYBACKUP_LINK; ?>img/dataset-off.png';" title="<?php

										if(isPRO==false) {	#	0.1.2

											_e( 'Click on the briefcase to create a new data set', MEBAK_LOCALE );
										}
										else {

											_e( 'Click on the briefcase to backup the selected folders', MEBAK_LOCALE );
										}

									?>" onclick="javascript:<?php

											if(ASK_ZIP_PWD==1 && FORCE_PHPCODE==false) {

												#
												#	@since 0.1.3
												#
												?>var pwd=prompt('<?php

														echo __( 'Please type the password to use to protect your data set', MEBAK_LOCALE ) . '\n\n'
															.__( 'Note: for security reasons the password length must be at least 8 chars; if you like not to password protect your data sets, you can change this behaviour in the Settings page.', MEBAK_LOCALE )
														;

												?>');
												if(pwd && pwd.length>7){
													set_waiting_message();
													document.exec_backup._action.value='backup';
													document.exec_backup.z_password.value=pwd;
													document.exec_backup.submit();
													return false;
												}
												else if(pwd){
													alert('<?php

														echo __( 'You must enter at least 8 chars!', MEBAK_LOCALE );

													?>');
												};
												return false;<?php

											} else {

												?>set_waiting_message();
												document.exec_backup._action.value='backup';
												document.exec_backup.z_password.value='';
												document.exec_backup.submit();
												return false;<?php
											}

									?>" alt="<?php _e( 'BRIEFCASE', MEBAK_LOCALE ); ?>" align="absmiddle" />
							</div>
							<div style="clear:both;"></div>
					<br /><?php

echo '</div>'; # 0.9.4

/* 0.9.4			?><div style="padding:4px 8px 8px 8px;background-color:#F1F1F1;border:1px solid #DFDFDF;margin:8px 0 20px 0;-moz-border-radius:6px;border-radius:6px;"><?php */
					?><div style="width:auto;padding:4px 8px 8px 8px;background-color:#F1F1F1;border:1px solid #DFDFDF;margin:0;-moz-border-radius:6px;border-radius:6px;"><?php

						_e( 'The myEASYrestore tool', MEBAK_LOCALE );

						if($_POST['upload_restore_tool']==1) {

							$tool_img = MYEASYBACKUP_LINK . 'img/mer-tool.png';
						}
						else {

							$tool_img = MYEASYBACKUP_LINK . 'img/mer-tool-off.png';
						}

						?> &raquo; <img src="<?php echo $tool_img; ?>" id="upload_restore_tool_img" style="cursor:pointer;" width="48px" height="48px" title="<?php

										_e( 'Add the myEASYrestore tool to the data set? When colored yes, when greyed no: click to toggle.', MEBAK_LOCALE );

									?>" onclick="javascript:var el=document.getElementById('upload_restore_tool_');
															var el2=document.getElementById('upload_restore_tool_exec_');// 0.1.2
															if(el.value==0) {
																el.value=1;
																el2.value=1;// 0.1.2
																this.src='<?php echo MYEASYBACKUP_LINK . 'img/mer-tool.png'; ?>';
															}else{
																el.value=0;
																el2.value=0;// 0.1.2
																this.src='<?php echo MYEASYBACKUP_LINK . 'img/mer-tool-off.png'; ?>';
															};"
										alt="<?php _e( 'WRENCH', MEBAK_LOCALE ); ?>" align="absmiddle" />
						<br /><?php

						echo '<i>'

							. __( 'Note: in order to be able to use the myEASYrestore tool, you need to enable this option: a) when creating a backup; b) when uploading a data set.', MEBAK_LOCALE )
							. '<br /><br />' . __( 'If you include the myEASYrestore tool when creating the data set, the myEASYrestore_ini.php will be included in the data set even if you do not add your WordPress installation folder.', MEBAK_LOCALE )

							. '</i>'
						;

					?></div></div><?php
				}															#	0.1.1

			?></div>
			<input type="hidden" name="filename" value="<?php echo $_POST['filename']; ?>" /><?php

			$datasets = scandir(MEBAK_BACKUP_PATH);
			$isDatasets = 0;

			if(is_array($datasets)) {

				foreach($datasets as $key => $ds) {

					if($ds!='.' && $ds!='..'
						&& substr($ds,0,12)=='myEASYbackup'
						&& (substr($ds,-4)=='.zip'
							|| substr($ds,-4)=='.tar'
							|| substr($ds,-4)=='.tgz')
					) {

						$isDatasets++;
					}
				}
			}

			if($isDatasets<1) {

				/**
				 * no data sets found
				 */
//				echo '<code style="font-size:13px;">* ' . __( 'There are no data sets on the server', MEBAK_LOCALE ) . ' *</code>';
				echo '<div class="ads" style="font-family:\'Lucida Grande\',Verdana,Arial,\'Bitstream Vera Sans\',sans-serif;padding:12px;font-size:13px;width:auto;">'
						. __( 'There are no data sets on the server.', MEBAK_LOCALE )
						. '<br />' . __( 'Data sets will be saved in this path:', MEBAK_LOCALE )
						. '<br /><b>' . MEBAK_BACKUP_PATH . '</b>'
					. '</div>';
			}
			else {

				echo '<h3 style="margin-bottom:0;">' . __( 'Data sets on the server', MEBAK_LOCALE ) . '</h3>';

				echo '<i>'

					. __( 'A data set (each briefcase) is a single file including your backup.', MEBAK_LOCALE )
					. '<br />'
					. __( 'The file extension can be one of the following: .zip, .tar or .tgz pending on your settings. You can set an option to compress your data set.', MEBAK_LOCALE )
					. '<br />'
					. __( 'Each data set content depends on what options you selected when doing the backup: the tables, the WordPress installation folder (and all its contents) or both.', MEBAK_LOCALE )

					.'</i><br /><br />'
				;							#	0.0.9, 0.1.1

#
#	0.0.6: BEG
#-------------
/*
				_e( 'The myEASYrestore tool', MEBAK_LOCALE );

				if($_POST['upload_restore_tool']==1)
				{
					$tool_img = MYEASYBACKUP_LINK . 'img/mer-tool.png';
				}
				else
				{
					$tool_img = MYEASYBACKUP_LINK . 'img/mer-tool-off.png';
				}

				?> &raquo; <img src="<?php echo $tool_img; ?>" id="upload_restore_tool_img" style="cursor:pointer;" width="48px" height="48px" title="<?php

								_e( 'Click on the wrench to include the myEASYrestore tool', MEBAK_LOCALE );

							?>" onclick="javascript:var el=document.getElementById('upload_restore_tool_');
													var el2=document.getElementById('upload_restore_tool_exec_');// 0.1.2
													if(el.value==0) {
														el.value=1;
														el2.value=1;// 0.1.2
														this.src='<?php echo MYEASYBACKUP_LINK . 'img/mer-tool.png'; ?>';
													}else{
														el.value=0;
														el2.value=0;// 0.1.2
														this.src='<?php echo MYEASYBACKUP_LINK . 'img/mer-tool-off.png'; ?>';
													};"
								alt="<?php _e( 'WRENCH', MEBAK_LOCALE ); ?>" align="absmiddle" />
				<br /><?php

				echo '<i>'

					. __( 'Note: in order to be able to use the myEASYrestore tool, you need to enable this option: a) when creating a backup; b) when uploading a data set.', MEBAK_LOCALE )
					. '<br />' . __( 'If you include the myEASYrestore tool when creating the data set, the myEASYrestore_ini.php will be included in the data set even if you do not add your WordPress installation folder.', MEBAK_LOCALE )

					. '</i><br /><br />'
				;
*/
#-------------
#	0.0.6: END
#

				if(isPRO==false) {										#	0.1.2

					?><!-- @since 0.1.4 ae_prompt code: beg -->
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

					<table border="1" cellspacing="4" cellpadding="4" width="100%" style="margin-bottom:40px;"><?php

					$totalsize = 0;
					$i = 0;												#	0.1.4

					if(defined('FTP_USER_NAME') && strlen(constant('FTP_USER_NAME'))>0
							&& defined('FTP_SERVER') && strlen(constant('FTP_SERVER'))>0
					) {

						define('FTP_UPLOAD_STRING', __( 'Click to upload this briefcase to: '.FTP_SERVER, MEBAK_LOCALE ));
					}
					else {

						define('FTP_UPLOAD_NOT_AVAILABLE', __( 'To be able to upload your backup, you need to set the FTP info in the settings page', MEBAK_LOCALE ));

						$ftp_upload = ''
							.'<img name="ftpupload" value="ftpupload" '
								.'src="'.MYEASYBACKUP_LINK.'img/upload-stop.png" '
									.'alt="'.FTP_UPLOAD_NOT_AVAILABLE.'" '
									.'title="'.FTP_UPLOAD_NOT_AVAILABLE.'" '
									.'align="absmiddle" />'
						;
					}

#
#   0.9.1: BEG
#-------------
					$allyKEY = get_option('meb_ally_auth_key');

					if(strlen($allyKEY)>0) {

						define('DRESSING_UPLOAD_STRING', __( 'Click to upload this briefcase to your The myEASY Dressing Room&trade; account', MEBAK_LOCALE ));
					}
					else {

						define('DRESSING_UPLOAD_NOT_AVAILABLE', __( 'To be able to upload your backup to The myEASY Dressing Room&trade;, you need to set the KEY in the settings page', MEBAK_LOCALE ));

						$dressing_upload = ''
							.'<img name="dressingupload" value="dressingupload" '
								.'src="'.MYEASYBACKUP_LINK.'img/dr-upload-stop.png" '
									.'alt="'.DRESSING_UPLOAD_NOT_AVAILABLE.'" '
									.'title="'.DRESSING_UPLOAD_NOT_AVAILABLE.'" '
									.'align="absmiddle" />'
						;
					}

					$uploadPath = FTP_REMOTE_PATH;
					$uploadPort = FTP_PORT;
					$alt = 1;
#-------------
#   0.9.1: END
#

					define('BRIEFCASE_DOWNLOAD', __( 'Click to download this briefcase', MEBAK_LOCALE ));
					define('BRIEFCASE_DELETE', __( 'Click to delete this briefcase', MEBAK_LOCALE ));

					foreach($datasets as $ds) {

						$i++;											#	0.1.4
						if($ds!='.' && $ds!='..'
							&& substr($ds,0,12)=='myEASYbackup'			#	0.1.0
							//&& substr($ds,-4)=='.zip'					#	0.0.9
							&& (substr($ds,-4)=='.zip'					#	0.1.4
								|| substr($ds,-4)=='.tar'				#	0.1.4
								|| substr($ds,-4)=='.tgz')				#	0.1.4
						) {

							$filesize = filesize(MEBAK_BACKUP_PATH . '/' . $ds);
							$totalsize += $filesize;

							if($alt%2) { $high = 'background:#f1f1f1;'; } else { $high = 'background:#ffffff;'; }
							$alt++;

							if(defined('FTP_UPLOAD_STRING')) {

								$ftp_upload = ''
									.'<img name="ftpupload" value="ftpupload" '
										.'src="'.MYEASYBACKUP_LINK.'img/upload-off.png" '
										.'style="cursor:pointer;" '
										.'onmouseover="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/upload.png\';" '
										.'onmouseout="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/upload-off.png\';" '
										.'onclick="javascript:'
															.'ask_ftp_password_'.$i.'();'
															.'return false;" '
											.'alt="'.FTP_UPLOAD_STRING.'" '
											.'title="'.FTP_UPLOAD_STRING.'" '
											.'align="absmiddle" />'
								;
							}

#
#   0.9.1: BEG
#-------------
							if(strlen($allyKEY)>0) {

								$dressing_upload = ''
									.'<img name="dressingupload" value="dressingupload" '
										.'src="'.MYEASYBACKUP_LINK.'img/dr-upload-off.png" '
										.'style="cursor:pointer;" '
										.'onmouseover="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/dr-upload.png\';" '
										.'onmouseout="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/dr-upload-off.png\';" '
										.'onclick="javascript:'
															.'ask_dressing_server(\''.$ds.'\',\''.$filesize.'\');'
															.'return false;" '
											.'alt="'.DRESSING_UPLOAD_STRING.'" '
											.'title="'.DRESSING_UPLOAD_STRING.'" '
											.'align="absmiddle" />'
								;
							}
#-------------
#   0.9.1: END
#

							echo '<tr style="'.$high.'">'
									.'<td width="1%">'
										.'<input type="image" name="download" value="download" '
												.'src="'.MYEASYBACKUP_LINK.'img/download-off.png" '
												.'onmouseover="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/download.png\';" '
												.'onmouseout="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/download-off.png\';" '
#
#	0.1.0: BEG
#-------------
												//.'onclick="javascript:window.location=\''.$_SERVER['PHP_SELF'].'?page=myEASYbackup_tools&dwnfile='.$ds.'\';return false;" '
												.'onclick="javascript:'
													.'document.getElementById(\'dwn_action\').value=\'download\';'
													.'document.getElementById(\'dwn_file\').value=\''.$ds.'\';'
													.'document.dwn_backup.submit();'
													.'return false;'
												.'"'
#-------------
#	0.1.0: BEG
#
												.'alt="'.BRIEFCASE_DOWNLOAD.'" '
												.'title="'.BRIEFCASE_DOWNLOAD.'" '
												.'align="absmiddle" />'
									.'</td>'
#
#	0.0.5: BEG
#-------------

#
#	0.1.4: BEG
#-------------
									.'<td width="1%">'
										.'<script type="text/javascript">'
										.'function ask_ftp_password_'.$i.'(){'
											.'ae_prompt( check_ftp_password_'.$i.', \''
													.__( 'Please type the password to upload', MEBAK_LOCALE ).':<br /><b>'.$ds.'</b><br />'.__( 'to', MEBAK_LOCALE ).':<br /><b>'.FTP_SERVER.'</b>'
												.'\', \'\');'
										.'}'
										.'function check_ftp_password_'.$i.'(pwd){'
											.'if(pwd && pwd.length>0){'
#
#   0.9.1: BEG
#-------------
          									.'set_waiting_message(\'up\');'
												.'sndReq(\'ftp_upload\',\'wait_progress\',pwd+\''.AJAX_PARMS_SPLITTER.$ds.AJAX_PARMS_SPLITTER
														.'\'+document.getElementById(\'upload_restore_tool_\').value+\''.AJAX_PARMS_SPLITTER.$uploadPath.AJAX_PARMS_SPLITTER.$uploadPort.'\');'
##												.'sndReq(\'ftp_bgupload\',\'wait_progress\',pwd+\''.AJAX_PARMS_SPLITTER.$ds.AJAX_PARMS_SPLITTER
##														.'\'+document.getElementById(\'upload_restore_tool_\').value+\''.AJAX_PARMS_SPLITTER.$uploadPath.AJAX_PARMS_SPLITTER.$uploadPort.'\');'
#-------------
#   0.9.1: END
#
												.'return false;'
											.'}'
										.'}'
										.'</script>'
										.$ftp_upload
									.'</td>'
#-------------
#	0.1.4: END
#

#-------------
#	0.0.5: END
#

#
#	0.9.1: BEG
#-------------
									.'<td width="1%">'
										.$dressing_upload
									.'</td>'
#-------------
#	0.9.1: END
#

									.'<td width="1%">'
										.'<input type="image" name="delete" value="delete" '
												.'src="'.MYEASYBACKUP_LINK.'img/delete-dataset-off.png" '
												.'onmouseover="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/delete-dataset.png\';" '
												.'onmouseout="javascript:this.src=\''.MYEASYBACKUP_LINK.'img/delete-dataset-off.png\';" '
												.'onclick="javascript:'
															.'if(confirm(\''.__( 'Are you sure that you want to delete this data set?', MEBAK_LOCALE ).'\')==false) {'
																.'return false;'
															.'};'
//															.'window.location=\''.$_SERVER['PHP_SELF'].'?page=myEASYbackup_tools&dltfile='.$ds.'\';return false;" ' # 1.0.2
//															.'window.location=\''. (is_ssl() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'].MEBAK_WPADMIN_PATH .'/tools.php?page=myEASYbackup_tools&dltfile='.$ds.'\';return false;" ' # 1.0.2 - 1.0.5.4
															.'window.location=\''. MEBAK_WPADMIN_PATH .'/tools.php?page=myEASYbackup_tools&dltfile='.$ds.'\';return false;" ' # 1.0.5.4
												.'alt="'.BRIEFCASE_DELETE.'" '
												.'title="'.BRIEFCASE_DELETE.'" '						#	0.1.0
												.'align="absmiddle" />'
									.'</td>'
									.'<td style="padding:0 6px;">'
										.$ds
									.'</td>'
									.'<td align="right" style="padding:0 6px;">'
										.number_format($filesize, 0, '', ',')
									.'</td>'
									.'<td style="padding:0 6px;">'
										.' '
										.__( 'bytes', MEBAK_LOCALE )
									.'</td>'
								.'</tr>'
							;
						}
					}

					echo '<tr style="background:#dfdfdf;">'
							.'<td colspan="4"></td>'				#	0.0.5 - 0.9.1
							.'<td align="right" style="padding:6px;">'
								.__( 'Total space used by data sets', MEBAK_LOCALE )
								.':'
							.'</td>'
							.'<td align="right" style="padding:6px;">'
								.'<b>'
									.number_format($totalsize, 0, '', ',')
								.'</b> '
							.'</td>'
							.'<td style="padding:6px;">'
								.__( 'bytes', MEBAK_LOCALE )
							.'</td>'
						.'</tr>'
					;

					?></table><?php

#
#	0.9.1: BEG
#-------------
					$meb_ally_auth_key = get_option( 'meb_ally_auth_key' ) . get_option( 'myewally_userKey' );

					if(strlen($meb_ally_auth_key)>32) {

						/**
						 * ally keys are available
						 */
						$drooms_file = MEBAK_UPLOAD_PATH . '/myEASYbackup-drooms.txt';
						$fdate = @filemtime($drooms_file);

						if($fdate<(time()-86400)) {

							/**
							 * create the dressing rooms cache file if older than one day
							 */
							$result = @file_get_contents('https://services.myeasywp.com/index.php?page=dressing-get-ftp-list&' . $meb_ally_auth_key);

							@unlink($drooms_file);  # 1.0.5.2

							$fd = @fopen($drooms_file, 'a');
							if(!$fd) {

								echo '<strong style="color:red;">Error 1</strong>';
							}
							else {

								@fwrite($fd, $result);
								@fclose($fd);
							}
							@chmod($drooms_file, 0666);
						}
						else {

							$result = @file_get_contents($drooms_file);
						}

//$result = 'Warning: file_get_contents(https://services.myeasywp.com/index.php?page=dressing-get-ftp-list&@@@) [function.file-get-contents]: failed to open stream: HTTP request failed! HTTP/1.0 500 Internal Server Error in /www/wp-content/plugins/myeasybackup/myeasybackup.php on line 1724';

						if(strpos($result, 'failed', 0)!==false) {

							echo '<div class="dark" style="padding:12px;">'
									. __( 'There was a problem when connecting to the <a href="http://services.myeasywp.com" target="_blank">services.myeasywp.com</a> site, it will not be possible to upload your myEASY Dressing Room&trade; account.<br />Please try again later.', MEBAK_LOCALE )
								. '</div>';
						}
						else {

							$rooms = unserialize($result);
						}

/* // */

						?><script type="text/javascript">
						function checkFTPsize(i,t){
							for(var ii=0;ii<t;ii++) {
								document.getElementById('ftp-availMb-'+ii).style.color='white';
							}
							document.getElementById('dr-upl-btn').disabled=false;
							document.getElementById('ftp-sdname').value=document.getElementById('ftp-sdname-'+i).value;
							var avail = parseInt(document.getElementById('ftp-avail-'+i).innerHTML);
							var req = parseInt(document.getElementById('ftp-requested').innerHTML);//+100000000000;
							if(req>avail) {
								document.getElementById('ftp-availMb-'+i).style.color='red';
								document.getElementById('dr-upl-btn').disabled=true;
							}
							return true;
						}
						function getRadioValue(ID){
							var radio = document.getElementsByName(ID);
							for(var i=0;i<radio.length;i++){
								if(radio[i].checked==true) {
									return radio[i].value;
								}
							}
							return 0;
						}
						function ask_dressing_server(ds,size){
							set_waiting_message('dr');
							var el = document.getElementById('drftp_vars');
							el.innerHTML += '<p align="center" id="ftp_dr_file" style="font-size:16px;font-weight:bold;">'+ds+'</p>'
								+'<p style="font-size:12px;font-weight:bold;"><?php _e( 'File size ', MEBAK_LOCALE ); ?> '+Math.round(size/1048576)+'Mb</p>'
								+'<div id="ftp-requested" style="display:none;">'+size+'</div>'
							;
							var ell = document.getElementById('drftp_select');
							ell.innerHTML = '<div align="center"><table>'
								+'<tbody>'
								+'<tr>'
									+'<th style="font-size:13px;color:#eaeaea;"></th>'
									+'<th style="font-size:13px;color:#eaeaea;" align="left">&nbsp;&nbsp;<?php _e( 'Dressing Room&trade;', MEBAK_LOCALE ); ?>&nbsp;&nbsp;</th>'
									+'<th style="font-size:13px;color:#eaeaea;" align="right">&nbsp;&nbsp;<?php _e( 'Quota', MEBAK_LOCALE ); ?>&nbsp;&nbsp;</th>'
									+'<th style="font-size:13px;color:#eaeaea;" align="right">&nbsp;&nbsp;<?php _e( 'Available', MEBAK_LOCALE ); ?>&nbsp;&nbsp;</th>'
									+'<th style="font-size:13px;color:#eaeaea;" align="left">&nbsp;&nbsp;<?php _e( 'Main blog/site', MEBAK_LOCALE ); ?>&nbsp;&nbsp;</th>'
								+'</tr>'
								<?php

								$t = 0;
								if(is_array($rooms)) {

									$t = count($rooms);

									for($i=0;$i<$t;$i++) {

										$checked = '';
										if($rooms[$i]['domain']==$_SERVER['HTTP_HOST']) {

											$checked = ' checked="checked"';
											$default = $rooms[$i]['subdomain'];
										}

										echo '+\'<tr id="ftp-availMb-'.$i.'" style="color:white;">'
												.'<td align="right"><input type="radio" name="allyDRftp" onclick="checkFTPsize('.$i.','.$t.');" id="drftp'.$i.'" value="'
																		.$rooms[$i]['us_RRN'].'"'.$checked.' /></td>'
												.'<td align="left" nowrap>&nbsp;&nbsp;<label onclick="checkFTPsize('.$i.','.$t.');return true;" for="drftp'.$i.'">' . $rooms[$i]['subdomain'] . '.dr.myeasywp.com</label>&nbsp;&nbsp;</td>'
												.'<td align="right" nowrap>&nbsp;&nbsp;' . round($rooms[$i]['quota']/1048576) . '&nbsp;Mb' . '&nbsp;&nbsp;</td>'
												.'<td align="right" nowrap>&nbsp;&nbsp;'
													.round(($rooms[$i]['quota']-$rooms[$i]['used'])/1048576) . '&nbsp;Mb'
													.'<div id="ftp-avail-'.$i.'" style="display:none;">' . ($rooms[$i]['quota']-$rooms[$i]['used']) . '</div>'
													.'<input id="ftp-sdname-'.$i.'" type="hidden" value="' . $rooms[$i]['subdomain'] . '" />'
												. '&nbsp;&nbsp;</td>'
												.'<td align="left" nowrap>&nbsp;&nbsp;' . $rooms[$i]['domain'] . '</td>'
											.'</tr>\'';
									}
								}
								else {

									echo '+\'<tr id="ftp-availMb-'.$i.'" style="color:white;">'
											.'<td colspan="99"><span style="color:red;">'
												.__( 'No Dressing Rooms found for your account', MEBAK_LOCALE )
											.'</span></td>'
										.'</tr>\'';
								}

							?>+'<tr><td colspan="99" height="12px"></td></tr>'
								+'<tr><td colspan="99" align="right"><input type="button" class="button-secondary" value="<?php _e( 'Close', MEBAK_LOCALE ); ?>" onclick="javascript:hide_waiting_message();" style="margin-right:12px" />'<?php

							if($t>0) {

								?>+'<input type="button" class="button-primary" id="dr-upl-btn" value="<?php _e( 'Upload to your Dressing Room&trade;', MEBAK_LOCALE ); ?>" '
									+'onclick="javascript:document.getElementById(\'drftp_list\').style.display=\'none\';document.getElementById(\'wait_upload\').style.display=\'block\';sndReq(\'ftp_dressing_upload\',\'wait_progress\',document.getElementById(\'ftp_dr_file\').innerHTML+\'<?php echo AJAX_PARMS_SPLITTER?>\'+document.getElementById(\'upload_restore_tool_\').value+\'<?php echo AJAX_PARMS_SPLITTER?>\'+getRadioValue(\'allyDRftp\')+\'<?php echo AJAX_PARMS_SPLITTER?>\'+document.getElementById(\'ftp-sdname\').value);" /></td></tr>'<?php
							}

							?>+'</tbody></table>'
							+'<input id="ftp-sdname" type="hidden" value="<?php echo $default; ?>" />'
							+'</div>';
						}
						</script><?php
					}
					else {

						?><script type="text/javascript">
						function ask_dressing_server(ds,size){
							set_waiting_message('dr');
							var el = document.getElementById('drftp_vars');
							el.innerHTML += '<table align="left" cellpadding="4" cellspacing="8" style="width:50%;margin-left:25%;">'
								+'<tr><td colspan="99" align="center" style="font-size:16px;font-weight:bold;color:red;"><?php _e( 'You must create your personal ally key to be able to upload to a Dressing Room&trade; server.', MEBAK_LOCALE ); ?></td></tr>'<?php

							?>+'<tr><td></td><td align="right"><input type="button" class="button-secondary" value="<?php _e( 'Close', MEBAK_LOCALE ); ?>" onclick="javascript:hide_waiting_message();" style="margin-right:12px" />'
									+'<input type="button" class="button-primary" value="<?php _e( 'Set your ally key', MEBAK_LOCALE ); ?>" onclick="javascript:window.location=\'options-general.php?page=myEASYbackup_options#ally_settings\';" /></td></tr>'
							+'</table>';
						}
						</script><?php
					}
#-------------
#	0.9.1: END
#

				}
			}
		}
		else if($_POST['_action']=='backup') {

			#	perform backup
			#
			$errors = checkvariables();

			if($_POST['filename']=='') {

				echo '<div class="error" style="width:640px;">'
						.__( 'File name is mandatory!', MEBAK_LOCALE )
				.'</div>';
				$errors = true;
			}

			if($errors==true) {

				echo MEBAK_BACK_BUTTON;
				return;
			}

//			if((int)MYEASYBACKUP_FAMILY>0 && file_exists(MEBAK_PRO_PATH.'pro-folders-2backup.php')) {		#	0.1.3
//
//				define('isPRO', true);
//			}
//			else {
//
//				define('isPRO', false);
//			}

			/*[[pro-folders-2backup]]*/
//			if(isPRO==true) {													#	0.1.2
			if(defined('MYEASYBACKUP_FAMILY') && constant('MYEASYBACKUP_FAMILY')==='PRO') {

				include_once(MEBAK_PATH.'inc/pro-folders-2backup.php');
			}

			#
			#	configuration is fine
			#
			if(file_exists(MEBAK_BACKUP_PATH . '/' . $_POST['filename'] . '.sql')) {								#	0.0.4

				echo '<div class="error" style="width:640px;">'
						.__( 'Backup file', MEBAK_LOCALE )
						.' ' . MEBAK_BACKUP_PATH . '/' . $_POST['filename'] . '.sql '							#	0.0.4
						.__( 'does already exists: please try again with a different name.', MEBAK_LOCALE )
				.'</div>';
				echo MEBAK_BACK_BUTTON;

				return;
			}
			else {

/****/

//				if(isPRO==true) {												#	0.1.3
				if(defined('MYEASYBACKUP_FAMILY') && constant('MYEASYBACKUP_FAMILY')==='PRO') {

					echo '<div class="updated">';

					if(defined('isLINUX') && isLINUX==true && isSYSTEM==true && FORCE_PHPCODE==false) {

						/*[[pro-folders-system-compress]]*/
//						if(file_exists(MEBAK_PRO_PATH.'pro-folders-system-compress.php')) {
						if(file_exists(MEBAK_PATH.'inc/pro-folders-system-compress.php')) {

//							include_once(MEBAK_PRO_PATH.'pro-folders-system-compress.php');
							include_once(MEBAK_PATH.'inc/pro-folders-system-compress.php');
						}
					}
					else if((defined('isWINDOWS') && isWINDOWS==true) || isSYSTEM==false || FORCE_PHPCODE==true) {

						/*[[pro-folders-php-compress]]*/
//						if(isPRO==true && file_exists(MEBAK_PRO_PATH.'pro-folders-php-compress.php')) {
						if(file_exists(MEBAK_PATH.'inc/pro-folders-php-compress.php')) {

//							include_once(MEBAK_PRO_PATH.'pro-folders-php-compress.php');
							include_once(MEBAK_PATH.'inc/pro-folders-php-compress.php');
						}

						/*[[pro-folders-php-remove]]*/
//						if(isPRO==true && file_exists(MEBAK_PRO_PATH.'pro-folders-php-remove.php')) {
						if(file_exists(MEBAK_PATH.'inc/pro-folders-php-remove.php')) {

//							include_once(MEBAK_PRO_PATH.'pro-folders-php-remove.php');
							include_once(MEBAK_PATH.'inc/pro-folders-php-remove.php');
						}
					}

					echo '</div>';
					echo MEBAK_BACK_BUTTON;
				}
				else {

					#	meb_free_inline.php
					include_once(MEBAK_PATH . 'inc/backup_exec_single.php');

					$result = myeasybackup_exec('interactive');
					//$result = myeasybackup_exec('scheduled');

					//echo 'myeasybackup_exec result:'.$result.'<br>';	#	debug
				}
/****/
			}
		}

		?></form><?php

//		if(!defined('MYEASYBACKUP_FAMILY') || (int)MYEASYBACKUP_FAMILY<1) {	#	0.1.3
		if(defined('MYEASYBACKUP_FREE')) {

			#	free
			#
			measycom_donate('meb');
		}

		measycom_camaleo_links();

		echo '</div>';
	}

	function meb_install() {

		#
		#	plugin initialization
		#	@since 0.1.3
		#
		#	http://codex.wordpress.org/Creating_Tables_with_Plugins
		#
//		if((int)MYEASYBACKUP_FAMILY<20 || !file_exists(MEBAK_PRO_PATH.'pro-folders-2backup.php')) {
		if(!defined('MYEASYBACKUP_FREE') || constant('MYEASYBACKUP_FAMILY')!=='PRO') {

			return;
		}

		global $wpdb;

		//$table_name = $wpdb->prefix . 'meb_pro_backup_log';

		if($wpdb->get_var('SHOW TABLES LIKE `'.TABLE_PRO_BACKUP_LOG.'`') != TABLE_PRO_BACKUP_LOG) {

			$sql = 'CREATE TABLE `' . TABLE_PRO_BACKUP_LOG . '` ('

					.'`ID` int(12) unsigned NOT NULL AUTO_INCREMENT,'
					.'`folder` text COLLATE utf8_unicode_ci NOT NULL,'

					.'`created` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\','
					.'`updated` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\','

					.'`size` bigint(20) NOT NULL DEFAULT \'0\','
					.'`isFolder` tinyint(1) unsigned DEFAULT \'0\','
					.'`isINI` tinyint(1) unsigned DEFAULT \'0\','
					.'`isMysql` tinyint(1) unsigned DEFAULT \'0\','
					.'`isPassword` tinyint(1) DEFAULT \'0\','

					.'`ftp_server` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,'
					.'`ftp_user` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,'
					.'`ftp_password` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,'

					.'PRIMARY KEY (`ID`)'

			.')  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';	#	AUTO_INCREMENT=1

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			add_option('meb_db_version', MEB_DB_VERSION);
		}
	}

	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	function meb_show_calendar(	$form_name,		#	the form name
								$field_name		#	the field name
	) {
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		#
		#	show a date in a readonly field with a calendar icon letting the user
		#	to enter the date by selecting it from the calendar
		#
		#	@since 0.1.4
		#	@version adapted for myEASYbackup
		#
		#	works on a modified version of DTHMLGoodies Calendar
		#	original version available at:
		#	http://www.dhtmlgoodies.com/scripts/js_calendar/js_calendar.html
		#
		$type = 'yyyy/mm/dd hh:ii';

		$html = ''
			.'<img src="'.MYEASYBACKUP_LINK.'img/calendar/cal.png" style="cursor:pointer;" align="absmiddle" title="'.__( 'Change the date', MEB_LOCALE ).'" '
					.'onclick="javascript:displayCalendar(document.'.$form_name.'.'.$field_name.',\''.$type.'\',this,true);" /> '
			.' <input type="text" readonly id="'.$field_name.'" name="'.$field_name.'" style="cursor:pointer;" '
					.'onclick="javascript:displayCalendar(document.'.$form_name.'.'.$field_name.',\''.$type.'\',this,true);" '
					.'value="'.$_POST[$field_name].'" />'
		;

		return $html;
	}
}

?>