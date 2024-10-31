<?php
/**
 * Initialize configuration variables
 *
 * @package myEASYbackup
 * @author Ugo Grandolini
 */
define('MYEASYBACKUP_VERSION', '1.0.10');
define('MEBAK_LOCALE', 'myEASYbackup');		#	the locale for translations - 1.0.5.1

//define('MYEASYBACKUP_FAMILY', 0);											#	@since 0.1.3: 0:free, 10:lite, 11:litedev, 20:prodev, 30:pro
define('MEB_DB_VERSION', '1.0');											#	@since 0.1.3: plugin own tables version
define('EMAIL_CONTACT', 'info@myeasywp.com');								#	@since 0.1.4: developer contact email

define('myEASYcomCaller', 'myeasybackup');								    #	@since 1.0.2: the plugin install folder

global $wpdb;																#	@since 0.1.3

//if(!defined('isAJAX') || (defined('isAJAX') && isAJAX==false)) {  # @since 1.0.5.4
if(is_object($wpdb)) {                                              # @since 1.0.5.5

	define('TABLE_PRO_BACKUP_LOG', $wpdb->prefix . 'meb_pro_backup_log');		#	@since 0.1.3
}

//ini_set('log_errors','On');				#	0.0.4
//ini_set('display_errors','0');			#	0.0.4


#   0.9.2: BEG
#-------------

/**
 * 1.0.5: BEG
 */
if(isset($_SERVER['SCRIPT_FILENAME'])) {

	if(preg_match("#^[a-z]\\\\\:(?:\\\\[^\/\:\*\?\\\"\<\>\|]+)+$#i", preg_quote($_SERVER['SCRIPT_FILENAME']))) {

		define('isWINDOWS', true);
	}
	else {

		define('isLINUX', true);
	}
}
else {

	if(strpos($_SERVER['DOCUMENT_ROOT'], '/', 0)!==false) {

		define('isLINUX', true);
	}
	else {

		define('isWINDOWS', true);
	}
}
if(defined('isLINUX')) {

	$doc = $_SERVER['DOCUMENT_ROOT'];
	$pth = dirname(__FILE__);
//	echo 'lnx['.$pth.']<br>';
}
else if(defined('isWINDOWS')) {

	$doc = str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']);
	$pth = str_replace('\\','/', dirname(__FILE__));
//	echo 'win['.$pth.']<br>';
}
//echo '$doc['.$doc.'] $pth['.$pth.']';
/**
 * 1.0.5: END
 */


$docAry = explode('/', $doc);
$pthAry = explode('/', $pth);

if($docAry[0]==$pthAry[0]) {

	define('isREALpath', true);
}
else {

	define('isREALpath', false);
}

//$tmp = dirname(__FILE__);

//if(strpos($pthAry, '/', 0)!==false)	{	#	0.0.4
//
//	define('isLINUX', true);
//}
//else {
//
//	define('isWINDOWS', true);
//	$docAry = str_replace('\\','/', $docAry);
//	$pthAry = str_replace('\\','/', $pthAry);
//}

//$isLink = @readlink($wp_path);

//echo '$wp_path['.$wp_path.']<br>'
//	.'$isLink['.$isLink.']<br>';

if(isREALpath==false) {

	/**
	 * if the hosting service setup symbolic paths we need to use them rather than the real path
	 */
	define('MEBAK_PATH', $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/myeasybackup/' );

	$wp_path = $_SERVER['DOCUMENT_ROOT'] . '/';
	$backup_path = $_SERVER['DOCUMENT_ROOT'] . '/';
}
else {

	define('MEBAK_PATH', dirname(__FILE__) . '/');

	//	$tpath = explode('/',$pthAry);
	$t = count($pthAry) - 3;
	$wp_path = '';
	for($i=0;$i<$t;$i++) {

//		$wp_path .= $tpath[$i] . '/';
		$wp_path .= $pthAry[$i] . '/';
	}

//$pth = measycom_get_real_path($_SERVER['DOCUMENT_ROOT'], dirname(__FILE__));
//echo $pth.'wp-content/plugins/myeasybackup/';

	$t = $t - 1;
	$backup_path = '';
	for($i=0;$i<$t;$i++) {

//		$backup_path .= $tpath[$i] . '/';
		$backup_path .= $pthAry[$i] . '/';
	}
}

//echo 'ABSPATH='.ABSPATH.' $wp_path='.$wp_path.'|||';
//echo 'MEBAK_PATH='.MEBAK_PATH.'<br>';
//echo '$tmp='.$tmp.'<br>';
//echo '$wp_path['.$wp_path.']<br>';
//echo '$backup_path['.$backup_path.']<br>';
//die();
/*
MEBAK_BACKUP_PATH:      /hermes/web05/b1154/
MEBAK_WP_PARENT_PATH:   /home/users/web/b1154/moo.barbaracounselscom
MEBAK_WP_PATH:          /hermes/web05/b1154/moo.barbaracounselscom/
*/

#-------------
#   0.9.2: END

if($_SERVER['DOCUMENT_ROOT']!='')										#	0.1.3
{
	define('MEBAK_WP_PARENT_PATH', $_SERVER['DOCUMENT_ROOT']);			#	0.1.3
}
else
{
	define('MEBAK_WP_PARENT_PATH', $backup_path);						#	0.1.1
}
define('MEBAK_WP_PATH', $wp_path);

//echo 'meb-config.php (a) '.time();


/**
 * moved here @since 1.0.5.2
 */
if(defined('MEBAK_ISDOWNLOAD') && MEBAK_ISDOWNLOAD==true) {

	if(file_exists(MEBAK_WP_PATH . '/wp-load.php')) {

		require_once( MEBAK_WP_PATH . '/wp-load.php' );
	}
	else if(file_exists('../../../wp-load.php')) {

		require_once( '../../../wp-load.php' );
	}
}


if(defined('AJAX_CALLER') && AJAX_CALLER==true)	{							# 0.1.3

	require( $wp_path . 'wp-load.php' );
}

/**
 * text used to split ajax parameters	@since 0.1.3
 */
define('AJAX_PARMS_SPLITTER', '|-ajax-parms-|');

$isSYSTEM = true;								#	0.0.5
$disabled = ini_get('disable_functions');		#	0.1.3

if(strpos($disabled, 'system', 0)!==false) {	#	0.0.5

	$isSYSTEM = false;
}
define('isSYSTEM', $isSYSTEM);					#	0.0.5


/**
 * @since 1.0.5.2
 */
if(strlen(get_option( 'upload_path' ))>0) {

//	define('MEBAK_UPLOAD_PATH', get_option( 'upload_path' ) );  # 1.0.5.4

	$tmp = get_option( 'upload_path' );  # 1.0.5.4
	if(stripos($tmp,ABSPATH,0)===false) {

		$tmp = ABSPATH . $tmp;
	}
	define('MEBAK_UPLOAD_PATH', $tmp );
}
else {

	define('MEBAK_UPLOAD_PATH', ABSPATH . 'wp-content/uploads');
}
//echo ' <code>'. MEBAK_UPLOAD_PATH .'</code><br>';


/**
 * @since 1.0.4: BEG
 */
if(!defined('MEBAK_ISDOWNLOAD') || MEBAK_ISDOWNLOAD==false) { 	# @since 1.0.5

	if(strpos($disabled, 'system', 0)===false) {

		/**
		 * get path to zip
		 */
//echo 'ABSPATH['.ABSPATH.'wp-content/uploads/]';

		$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );
		$cmd = 'which zip' . ' 1> ' . escapeshellarg($tmpfile) .' 2>&1';
		system( $cmd, $cmd_result );

		$cmd_output = @file_get_contents($tmpfile);

		$PATH_ZIP = 'zip';
		if($cmd_result==0) {

			$PATH_ZIP = trim($cmd_output);
		}
		@unlink($tmpfile);

		/**
		 * get path to tar
		 */
		$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );
		$cmd = 'which tar' . ' 1> ' . escapeshellarg($tmpfile) .' 2>&1';
		system( $cmd, $cmd_result );

		$cmd_output = @file_get_contents($tmpfile);

		$PATH_TAR = 'tar';
		if($cmd_result==0) {

			$PATH_TAR = trim($cmd_output);
		}
		@unlink($tmpfile);

		/**
		 * get path to mysqldump
		 */
		if(strlen(get_option( 'meb_mysqldump_path' ))>0) {

			$PATH_MYSQLDUMP = get_option( 'meb_mysqldump_path' ) . '/mysqldump';
		}
		else {

			$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );
			$cmd = 'which mysqldump' . ' 1> ' . escapeshellarg($tmpfile) .' 2>&1';
			system( $cmd, $cmd_result );

			$cmd_output = @file_get_contents($tmpfile);

			$PATH_MYSQLDUMP = 'mysqldump';
			if($cmd_result==0) {

				$PATH_MYSQLDUMP = trim($cmd_output);
			}
			@unlink($tmpfile);
		}

//echo '$PATH_ZIP:'.$PATH_ZIP.'<br>$cmd_result:'.$cmd_result.'<hr>';
//echo '$PATH_TAR:'.$PATH_TAR.'<br>$cmd_result:'.$cmd_result.'<hr>';
//echo '$PATH_MYSQLDUMP:'.$PATH_MYSQLDUMP.'<br>$cmd_result:'.$cmd_result.'<hr>';

		define('PATH_ZIP', $PATH_ZIP);
		define('PATH_TAR', $PATH_TAR);
		define('PATH_MYSQLDUMP', $PATH_MYSQLDUMP);
	}
	else {

		define('PATH_ZIP', 'zip');
		define('PATH_TAR', 'tar');
		define('PATH_MYSQLDUMP', 'mysqldump');
	}

	/**
	 * @since 1.0.5.5: BEG
	 */
	$tmpAry = unserialize(get_option('meb_exclude_folder'));
	$tmpTar = ' ';
	$tmpZip = ' ';

	if(is_array($tmpAry)) {

		$tmpZip = ' -x ';

		foreach($tmpAry as $k => $dir) {

			if(substr($dir, 0, 1)==DIRECTORY_SEPARATOR) {

				$dir = substr($dir, 1);
			}
			$tmpTar .= '--exclude="' . $dir . DIRECTORY_SEPARATOR . '*" ';
			$tmpZip .= " '" . $dir . DIRECTORY_SEPARATOR . "*' ";
		}
	}
	define('TAR_EXCLUDE_FOLDERS', $tmpTar);
	define('ZIP_EXCLUDE_FOLDERS', $tmpZip);
	/**
	 * @since 1.0.5.5: END
	 */
}
else {

	/**
	 * @since 1.0.5.1
	 */
	define('PATH_ZIP', 'zip');
	define('PATH_TAR', 'tar');
	define('PATH_MYSQLDUMP', 'mysqldump');

	define('TAR_EXCLUDE_FOLDERS', ' ');  #   1.0.5.5
	define('ZIP_EXCLUDE_FOLDERS', ' ');  #   1.0.5.5
}
/**
 * @since 1.0.4: END
 */
//echo 'TAR_EXCLUDE_FOLDERS['.TAR_EXCLUDE_FOLDERS.']<br>';


if((!defined('MEBAK_ISDOWNLOAD') || MEBAK_ISDOWNLOAD==false)) {				# @since 0.0.5

	#
	#	on the production server avoid to show errors, etc. (@since 0.0.5)
	#
	$tmp = get_option( 'meb_isPRODUCTION' );
	if($tmp=='') { $tmp = 1; }
	define('mebak_PRODSERVER', $tmp);

	#
	#	switch to show/hide debug code (@since 0.0.5)
	#
	$tmp = get_option( 'meb_isDEBUG' );
	if($tmp=='') { $tmp = 0; }
	define('mebak_DEBUG', $tmp);

	#
	#	switch to show/hide debug code (@since 0.0.5)
	#
	$tmp = get_option( 'meb_compression' );
	if($tmp=='') { $tmp = 6; }
	define('COMPRESSION_LEVEL', $tmp);

	#
	#	FTP server (@since 0.0.5)
	#
	$tmp = get_option( 'meb_ftp_server' );
	if($tmp=='') { $tmp = ''; }
	define('FTP_SERVER', $tmp);

	#
	#	FTP user name (@since 0.0.5)
	#
	$tmp = get_option( 'meb_ftp_user_name' );
	if($tmp=='') { $tmp = ''; }
	define('FTP_USER_NAME', $tmp);

	#
	#	FTP user password (@since 0.0.5)
	#
	//$tmp = get_option( 'meb_ftp_user_pass' );
	//if($tmp=='') { $tmp = ''; }
	//define('FTP_USER_PASS', $tmp);

	#
	#	FTP remote path (@since 0.9.1)
	#
	$tmp = get_option( 'meb_ftp_remote_path' );
	if($tmp=='') { $tmp = ''; }
	define('FTP_REMOTE_PATH', $tmp);

	#
	#	FTP port (@since 0.9.1)
	#
	$tmp = get_option( 'meb_ftp_port' );
	if($tmp=='') { $tmp = '21'; }
	define('FTP_PORT', $tmp);

	#
	#	FTP timeout (@since 1.0.5.7)
	#
	$tmp = get_option( 'meb_ftp_timeout' );
	if($tmp=='') { $tmp = '90'; }
	define('FTP_TIMEOUT', $tmp);

	#
	#	FTP passive mode (@since 1.0.5.7)
	#
	$tmp = get_option( 'meb_ftp_pasv' );
	if($tmp=='') { $tmp = 0; }
	define('FTP_PASSIVE', $tmp);

	#
	#	Use PHP code rather than system() (@since 0.0.5)
	#
#
#	1.0.0: BEG
#-------------
//	$tmp = get_option( 'meb_force_phpcode' );
//	//if($tmp=='') { $tmp = false; }				#	0.0.9
//	if($tmp=='') { $tmp = true; }					#	0.0.9
//	define('FORCE_PHPCODE', $tmp);
	$tmp = get_option( 'meb_force_phpcode' );
	if($tmp=='') {

		if(isSYSTEM==1) {   //  1.0.0

			$tmp = false;   //  1.0.0
		}
		else {

			$tmp = true;
		}
	}
	define('FORCE_PHPCODE', $tmp);
#-------------
#	1.0.0: END
#

	#
	#	Ask for a password when compressing the data set using system() (@since 0.1.3)
	#
	$tmp = get_option( 'meb_zip_pass' );
	if($tmp=='') { $tmp = false; }
	define('ASK_ZIP_PWD', $tmp);

	#
	#	List all the files while compressing - verbose (@since 0.1.4)
	#
	$tmp = get_option( 'meb_zip_verbose' );
	if($tmp=='') { $tmp = false; }
	define('ZIP_VERBOSE', $tmp);

	#
	#	System archiving tool (@since 0.1.4)
	#
	$tmp = get_option( 'meb_sys_archiving_tool' );
	if($tmp=='') { $tmp = 'z'; }
	define('SYS_ARCHIVING_TOOL', $tmp);

	#
	#	Compress with tar? (@since 0.1.4)
	#
	$tmp = get_option( 'meb_tar_compress' );
	if($tmp=='') { $tmp = false; }
	define('TAR_COMPRESS', $tmp);

	#
	#	Memory to allocate to PHP (@since 0.1.4)
	#
	$tmp = get_option( 'meb_php_ram' );
	if($tmp!='') {

		define('PHP_RAM', $tmp.'M');
	}
	else {

		define('PHP_RAM', ini_get('memory_limit'));
	}

	#
	#	Folder where to save the data set (@since 0.1.1)
	#
	$meb_backup_root = get_option( 'meb_backup_root' );

	#
	#	Email where to send the data set (@since 0.9.1)
	#
	$tmp = get_option( 'meb_email_backup' );
	if($tmp=='') { $tmp = ''; }
	define('MEBAK_MAIL_TO', $tmp);

	#
	#	Remove the data set on successfull email send? (@since 0.9.1)
	#
	$tmp = get_option( 'meb_email_backup_remove' );
	if($tmp=='') { $tmp = 0; }
	define('MEBAK_MAIL_TO_REMOVE', $tmp);

	#
	#	The wp-admin path (@since 1.0.2)
	#
	$tmp = get_option( 'meb_wpadmin_path' );
	if($tmp=='') { $tmp = '/wp-admin'; }

	if(substr($tmp,0,1)!='/') { $tmp = '/'.$tmp; }    #   @since 1.0.5.4

	define('MEBAK_WPADMIN_PATH', $tmp);

}
else {

	define('mebak_PRODSERVER', true);
	define('mebak_DEBUG', false);
}

//echo '['.mebak_PRODSERVER.'|'.mebak_DEBUG.']';

//$isSYSTEM = true;								#	0.0.5
//$disabled = ini_get('disable_functions');		#	0.1.3
//if(strpos($disabled, 'system', 0)!==false)		#	0.0.5
//{
//	$isSYSTEM = false;
//}
//define('isSYSTEM', $isSYSTEM);					#	0.0.5


/**
 * check that the upload folder exists
 * @since 1.0.5
 */
$tmp = '';
if((!defined('MEBAK_ISDOWNLOAD') || MEBAK_ISDOWNLOAD==false)) { # @since 1.0.5

/* 1.0.5.1
	if(strlen(get_option( 'upload_path' ))>0) {

		define('MEBAK_UPLOAD_PATH', ABSPATH . get_option( 'upload_path' ) );
	}
	else {

		define('MEBAK_UPLOAD_PATH', ABSPATH . 'wp-content/uploads');
	}
*/

	if(!is_dir(MEBAK_UPLOAD_PATH)) {

		/**
		 * show an error message in the admn footer if the upload folder does not exists
		 * @since 1.0.5
		 */
		$tmp .= __('The upload folder:', MEBAK_LOCALE )
						.' <code>'. MEBAK_UPLOAD_PATH .'</code> '
						.__('does not exhist on the server.', MEBAK_LOCALE )
						.'<br />'
		;
		$tmp .= '<p><strong>'. __('You must create the upload folder and assign it writing privileges to be able to use the myEASYbackup plugin and to upload media!', MEBAK_LOCALE ) . '</strong></p>';
//		$tmp .= '<p><a href="options-general.php?page=myEASYbackup_options">'. __('Click here to edit the upload folder name', MEBAK_LOCALE ) . '</a></p>';

		define('MEBAK_MISSING_UPLOAD_PATH_MSG', $tmp);
		define('MEBAK_MISSING_UPLOAD_PATH', '<div class="warning">' . $tmp . '</div>');

//	if(!defined('MYEASY_SHOW_MISSING_UPLOAD_PATH')) {
//
//		add_action('admin_footer', 'myeasy_missing_upload');
//		function myeasy_missing_upload() {
//
//			echo MEBAK_MISSING_UPLOAD_PATH;
//			define('MYEASY_SHOW_MISSING_UPLOAD_PATH', true);
//		}
//	}

	}
//echo MEBAK_UPLOAD_PATH.'<br>'.MEBAK_MISSING_UPLOAD_PATH;
}

if(isSYSTEM==1 && (!defined('MEBAK_ISDOWNLOAD') || MEBAK_ISDOWNLOAD==false)) {

	/**
	 * @since 0.1.4
	 *
	 * 1.0.4: changed exec to system
	 */
	$result = 1;
//	@exec( 'zip -h', $output, $result );               # 1.0.2
//	@exec( PATH_ZIP.' -h', $output, $result );         # 1.0.2

	$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );
	$cmd = PATH_ZIP . ' -h 1> ' . escapeshellarg($tmpfile) .' 2>&1';
	@system( $cmd, $result );

//	$cmd_output  = file_get_contents($tmpfile);
	@unlink($tmpfile);

//echo $cmd.'<br>'.$result;

	if($result==0) {

		$ZIPcheck = __('Available', MEBAK_LOCALE );
		define('isSYSzip', true);
	}
	else {

		$ZIPcheck = __('NOT available', MEBAK_LOCALE );
		define('isSYSzip', false);
	}

	$result = 1;
//	@exec( 'tar --help', $output, $result );           # 1.0.2
//	@exec( PATH_TAR.' --help', $output, $result );     # 1.0.2

	$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );
	$cmd = PATH_TAR . ' --help 1> ' . escapeshellarg($tmpfile) .' 2>&1';
	@system( $cmd, $result );

//	$cmd_output  = file_get_contents($tmpfile);
	@unlink($tmpfile);

//echo $cmd.'<br>'.$result;

	if($result==0) {

		$TARcheck = __('Available', MEBAK_LOCALE );
		define('isSYStar', true);
	}
	else {

		$TARcheck = __('NOT available', MEBAK_LOCALE );
		define('isSYStar', false);
	}
}
else
{
	define('isSYSzip', false);
	define('isSYStar', false);
}

#
#	0.1.2: BEG
#-------------
$open_basedir = ini_get('open_basedir');
define('isOPEN_BASEDIR', $open_basedir);
#-------------
#	0.1.2: BEG
#

#
#	handling error messages
#
if(!defined('mebak_PRODSERVER') || (defined('mebak_PRODSERVER') && mebak_PRODSERVER==true))	#	0.0.5
{
	#	on the production server
	#
	ini_set('log_errors','On');			#	0.0.4
	ini_set('display_errors','0');		#	0.0.4
}
else
{
	#	on the development server
	#
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set('log_errors','Off');		#	0.0.5
	ini_set('display_errors','1');		#	0.0.5
}


@set_time_limit(0);						#	0.0.5

//if(MYEASYBACKUP_FAMILY==11) {
//
//	define('MEBAK_LITE_PATH', $backup_path . 'myEASYbackup-LITE/inc/');	#	0.1.4 - development
//}
//else if(MYEASYBACKUP_FAMILY==20) {
//
//	define('MEBAK_PRO_PATH', $backup_path . 'myEASYbackup-PRO/inc/');	#	0.1.2 - development
//}

//define('MEBAK_LITE_PATH', dirname(__FILE__).'/inc/');					#	0.1.4
//define('MEBAK_PRO_PATH', dirname(__FILE__).'/inc/');					#	0.1.2

//echo 'WP_PLUGIN_DIR['.WP_PLUGIN_DIR.']<br>';	#	debug

define('MEBAK_LITE_PATH', WP_PLUGIN_DIR.'/myeasybackup-lite/');			#	1.0.6
define('MEBAK_PRO_PATH', WP_PLUGIN_DIR.'/myeasybackup-pro/');			#	1.0.6

define('MEBAK_INC_PATH', dirname(__FILE__).'/inc/');


/**
 * force free version - debug
 */
define('MYEASYBACKUP_FAMILY', false);
define('MYEASYBACKUP_FREE', true);


if(file_exists(MEBAK_PRO_PATH.'pro-folders-2backup.php')) {

	define('MYEASYBACKUP_FAMILY', 'PRO');
}
else if(file_exists(MEBAK_LITE_PATH.'lite-cron-init.php')) {

	define('MYEASYBACKUP_FAMILY', 'LITE');
}
else {

	define('MYEASYBACKUP_FAMILY', false);
	define('MYEASYBACKUP_FREE', true);
}

//echo 'MYEASYBACKUP_FAMILY['.MYEASYBACKUP_FAMILY.']<br>';

/* 0.9.1 */
if(!defined('MEBAK_ISDOWNLOAD') || MEBAK_ISDOWNLOAD==false) {

	if(MYEASYBACKUP_FAMILY=='LITE') {

		require_once(MEBAK_LITE_PATH.'lite-cron-init.php');
	}
}

#
#	0.1.1: BEG
#-------------
if(defined('MEBAK_ISDOWNLOAD') && MEBAK_ISDOWNLOAD==true) {

// moved @since 1.0.5.2
//	if(file_exists(MEBAK_WP_PATH . '/wp-load.php')) {
//
//		require_once( MEBAK_WP_PATH . '/wp-load.php' );
//	}
//	else if(file_exists('../../../wp-load.php')) {
//
//		require_once( '../../../wp-load.php' );                     #   0.9.2
//	}

	$meb_backup_root = get_option( 'meb_backup_root' );
}

if($meb_backup_root!=''
	&& is_dir($meb_backup_root))
	//&& is_writable($meb_backup_root))
{
	$backup_path = $meb_backup_root;
}
#-------------
#	0.1.1: END
#

define('isSAFEMODE', ini_get('safe_mode'));								#	0.0.5

define('MEBAK_BACKUP_ROOT', $backup_path);								#	0.1.0
define('MEBAK_BACKUP_PATH', $backup_path);								#	0.1.1

//echo '@<b>'.__FILE__.'</b><br>';
//echo '$backup_path = '.$backup_path.'<br>';
//echo '$meb_backup_root = '.$meb_backup_root.'<br>';
//echo 'MEBAK_BACKUP_PATH = '.MEBAK_BACKUP_PATH.'<br>';
//echo 'meb_backup_root = '.get_option( 'meb_backup_root' ).'<br>';

$myEASYbackup_dir = basename(dirname(__FILE__));

//define('MEBAK_LOCALE', 'myEASYbackup');	#	the locale for translations - 1.0.5.1


if((!defined('MEBAK_ISDOWNLOAD') || MEBAK_ISDOWNLOAD==false)) {

	#
	#	link to the plugin folder (eg. http://example.com/wordpress-2.9.1/wp-content/plugins/MyPlugin/)
	#
	define('MYEASYBACKUP_LINK', get_option('siteurl').'/wp-content/plugins/' . $myEASYbackup_dir . '/');

	#
	#	meb_settings buttons (@since 0.0.5)
	#
	define('SAVE_BTN', __('Update Options', MEBAK_LOCALE ));

	define('CHANGE_HOST',											#	0.0.5

		 '<p>'
			. __( 'If your provider does not allow you to change the server configuration, please let me suggest a very good, professional and serious hosting service provider.', MEBAK_LOCALE ) .' '
			. __( 'I am using their services since 2006 and', MEBAK_LOCALE )
			. ' <b>'
			. __( 'never had problem without a prompt solution', MEBAK_LOCALE )
			. '</b>.'
		. '</p>'

		. '<p>'
			. __( 'Prices start at $4.95/month with', MEBAK_LOCALE )
			. ':<br />&raquo; '
			. __( 'UNLIMITED Disk Space', MEBAK_LOCALE )

			. '<br />&raquo; '
			. __( 'UNLIMITED Bandwidth', MEBAK_LOCALE )

			. '<br />&raquo; '
			. __( 'Powered by 130% wind energy!', MEBAK_LOCALE )
		. '</p>'

		. '<p>'
			. ' <a href="http://myeasywp.com/redirect/hosting.php" target="_blank"><b>'

				. __( 'Click here to see all the offers', MEBAK_LOCALE )

			. '</b></a>'
		. '</p>'

		. '<p>'
				. __( 'If you buy the hosting service by using the link provided here above, you will automatically reward my effort', MEBAK_LOCALE )
				.' <u>'
					. __( 'at no extra cost', MEBAK_LOCALE )
				.'</u>!'
		. '</p>'
	);
}

function format_permissions($perms) {

	if (($perms & 0xC000) == 0xC000) {
		// Socket
		$info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
		// Symbolic Link
		$info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
		// Regular
		$info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
		// Block special
		$info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
		// Directory
		$info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
		// Character special
		$info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
		// FIFO pipe
		$info = 'p';
	} else {
		// Unknown
		$info = 'u';
	}

	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
				(($perms & 0x0800) ? 's' : 'x' ) :
				(($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
				(($perms & 0x0400) ? 's' : 'x' ) :
				(($perms & 0x0400) ? 'S' : '-'));

	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
				(($perms & 0x0200) ? 't' : 'x' ) :
				(($perms & 0x0200) ? 'T' : '-'));

	$info .= ' ' . substr(decoct($perms), 2);

	return $info;
}

function shutdown() {

	#
	#	@since 0.1.4 - requires php 5
	#
	$isError = false;
	if($error = error_get_last()) {

		//echo E_ERROR.','.E_CORE_ERROR.','.E_COMPILE_ERROR.','.E_USER_ERROR;

		switch($error['type']) {

			case E_ERROR:			#	1
			case E_CORE_ERROR:		#	16
			case E_COMPILE_ERROR:	#	64
			case E_USER_ERROR:		#	256
				$isError = true;
				break;
		}
	}

	if($isError) {

		$tmp = '';
		$p = strpos($error['message'], 'Allowed memory size of', 0);
		if($p!==false) {

			echo '<div style="background-color:#F1F1F1;border:1px solid #DFDFDF;margin:8px;padding:6px;-moz-border-radius:6px;border-radius:6px;">'

				.'<p style="color:red;font-weight:bold;">'
					. __('Script execution halted: ', MEBAK_LOCALE ) . $error['message']
				. '</p>'

				. '<p>' . __( 'It looks like that your data set is big enough to need more memory than the actually allocated to your account', MEBAK_LOCALE )
						.' (' . str_replace('M', '', ini_get('memory_limit')) . __('Mb', MEBAK_LOCALE ) . ').</p>'

				. '<p>'
					. __( 'I can try to help you out, providing your hosting service does not prevent me to allocate more memory for your PHP scripts!', MEBAK_LOCALE ) . '</p>'

				. '<p>'
					. __( 'Please try the following:', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'click on the button here below to open the settings page', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'scroll the available settings until you find the one labeled "Memory available to PHP"', MEBAK_LOCALE ) . '</p>'

				. '<p>&raquo; '
					. __( 'change the value to a bigger amount, the value you enter here represent the number of megabytes that the plugin will try to allocate when creating a data set &mdash; such amount will be allocated only while creating a data set.', MEBAK_LOCALE ) . '</p>'

				. MEBAK_SETTINGS_BUTTON

				. '<p>'
					. __( 'Note: remember to save the new setting by clicking on the "Update Options" button once you have changed the setting!', MEBAK_LOCALE ) . '</p>'

			.'</div>';
		}
		else {

			echo '<p style="color:red;font-weight:bold;">'
					. __('Script execution halted: ', MEBAK_LOCALE ) . $error['message'] . ' &mdash; ' . $error['type']
				. '</p>';
		}
	}
	else {

		//echo '<p>' . __('Script completed', MEBAK_LOCALE ) . '</p>';
	}
}

if(version_compare(PHP_VERSION, '5', '>=')) {

	/**
	 * @since 0.1.4
	 */
	register_shutdown_function('shutdown');
}

//echo 'MEBAK_WP_PATH='.MEBAK_WP_PATH.'<br>';
//echo 'MEBAK_PATH='.MEBAK_PATH.'<br>';
//echo 'MYEASYBACKUP_LINK='.MYEASYBACKUP_LINK.'<br>';

?>