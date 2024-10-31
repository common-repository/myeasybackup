<?php
/**
 * Show debug info on request
 *
 * @package myEASYbackup
 * @author Ugo Grandolini
 * @since 0.0.5
 * @version 1.0.5
 *
 */

require('class.phpextensions.php');										#	0.1.1
$modules = new moduleCheck();											#	0.1.1


if(defined('mebak_DEBUG') && mebak_DEBUG==true) {

	/**
	 * debug info
	 */
	$html = '<div style="background-color:#DFDFDF;border:1px solid #CFCFCF;margin:8px;padding:8px;-moz-border-radius:6px;border-radius:6px;">'
				.'<h3 style="margin-top:0;">myEASYbackup debug info</h3>'
				.'<p>Plugin version: <b>'.MYEASYBACKUP_VERSION.'</b></p>'
				.'<p>Family: <b>'.MYEASYBACKUP_FAMILY.'</b></p>'
				.'<p>DB version: <b>'.MEB_DB_VERSION.'</b></p>'
	;

	/**
	 * options
	 */
	if(mebak_PRODSERVER==1) {

		$mebak_PRODSERVER = 'YES';
	}
	else {

		$mebak_PRODSERVER = 'NO';
	}

	if(FORCE_PHPCODE==1) {

		$FORCE_PHPCODE = 'YES';
	}
	else {

		$FORCE_PHPCODE = 'NO';
	}

	$html .= '<div style="background-color:#EAEAEA;border:1px solid #CFCFCF;margin:8px;padding:6px;-moz-border-radius:6px;border-radius:6px;">'
			.'<h3>Options</h3>'
			.'<p>Production server: <b>'.$mebak_PRODSERVER.'</b></p>'
			.'<p>Compression: <b>'.COMPRESSION_LEVEL.'</b></p>'
			.'<p>Force PHP code: <b>'.$FORCE_PHPCODE.'</b></p>'
//			.'<p>Backup path: <b>'.MEBAK_BACKUP_PATH.'</b></p>'
		.'</div>'
	;

	/**
	 * MySQL
	 */
	if(strlen(PATH_MYSQLDUMP)==0) {

		$tmp = __('Unable to determine the mysqldump command path', MEBAK_LOCALE);
	}
	else {

		$tmp = PATH_MYSQLDUMP;
	}

	$tmpfile = tempnam( MEBAK_UPLOAD_PATH, 'mebak_plugin' );    #   1.0.5
	$cmd = PATH_MYSQLDUMP . ' -V 1> ' . escapeshellarg($tmpfile) .' 2>&1';
	@system( $cmd, $result );

	$cmd_output = file_get_contents($tmpfile);
	unlink($tmpfile);

//echo $cmd.'<br>'.$result;

	if($result==0) {

		$DUMPcheck = __('Available', MEBAK_LOCALE );
	}
	else {

		$DUMPcheck = __('NOT available', MEBAK_LOCALE );
	}

	$html .= '<div style="background-color:#EAEAEA;border:1px solid #CFCFCF;margin:8px;padding:6px;-moz-border-radius:6px;border-radius:6px;">'
			.'<h3>MySQL</h3>'
			.'<p>Server: <b>'.mysql_get_server_info().'</b></p>'
			.'<p>Client: <b>'.mysql_get_client_info().'</b></p>'
			.'<p>Host: <b>'.mysql_get_host_info().'</b></p>'
			.'<p>Protocol: <b>'.mysql_get_proto_info().'</b></p>'
			.'<p>User: <b>'.DB_USER.'@'.DB_HOST.'</b></p>'
			.'<p>mysqldump: <b>'.$DUMPcheck.'</b><br />'  // 1.0.4
				.'<b>'.$cmd_output.'</b></p>'             // 1.0.4
			.'<p>Path to mysqldump: <b>'.$tmp.'</b></p>'
		.'</div>'
	;

	/**
	 * server settings
	 */
	$disabled = ini_get('disable_functions');

	if(strlen($disabled)==0) { $disabled = __('None', MEBAK_LOCALE ); }

	if(defined('isSAFEMODE') && isSAFEMODE==false) {

		$safe = __('Off', MEBAK_LOCALE );
	}
	else {

		$safe = __('On', MEBAK_LOCALE );
	}

	if(class_exists(ZipArchive)) {

		$isZipArchive = 'YES';
	}
	else {

		$isZipArchive = 'NO';
	}

	if(class_exists(RecursiveIteratorIterator)) {

		$isRecursiveIteratorIterator = 'YES';
	}
	else {

		$isRecursiveIteratorIterator = 'NO';
	}

	if(isSYSTEM==1) {

		$isSYSTEM = 'YES';
	}
	else {

		$isSYSTEM = 'NO';
	}

	if(isOPEN_BASEDIR=='') {

		$isOPEN_BASEDIR = '<b>DISABLED</b>';
	}
	else {

		$isOPEN_BASEDIR = '<b>'.isOPEN_BASEDIR.'</b>';
	}

	if(defined('isLINUX') && isLINUX==true) {

		$os_desc = 'LINUX';
	}
	else if(defined('isWINDOWS') && isWINDOWS==true) {

		$os_desc = 'WINDOWS';
	}
	else {

		$os_desc = 'UNKNOWN';
	}

	if($modules->isLoaded('zlib')) {

		$zlib_desc = 'Loaded';
	}
	else {

		$zlib_desc = 'Not loaded';
	}


	if($modules->isLoaded('ftp')) {

		$ftp_desc = 'Loaded';
	}
	else {

		$ftp_desc = 'Not loaded';
	}

	//if(isSYSTEM==1)
	//{
	//	exec( 'zip -h', $output, $result );
	//	if($result==0)
	//	{
	//		$ZIPcheck = __('Available', MEBAK_LOCALE );
	//	}
	//	else
	//	{
	//		$ZIPcheck = __('NOT available', MEBAK_LOCALE );
	//	}
	//
	//	exec( 'tar --help', $output, $result );
	//	if($result==0)
	//	{
	//		$TARcheck = __('Available', MEBAK_LOCALE );
	//	}
	//	else
	//	{
	//		$TARcheck = __('NOT available', MEBAK_LOCALE );
	//	}
	//}
	//else
	//{
	//	$ZIPcheck = __('NOT available', MEBAK_LOCALE );
	//	$TARcheck = __('NOT available', MEBAK_LOCALE );
	//}

	if(isSYSzip==true) {

		$ZIPcheck = __('Available', MEBAK_LOCALE );
	}
	else {

		$ZIPcheck = __('NOT available', MEBAK_LOCALE );
	}

	if(isSYStar==true) {

		$TARcheck = __('Available', MEBAK_LOCALE );
	}
	else {

		$TARcheck = __('NOT available', MEBAK_LOCALE );
	}

	if(isREALpath==true) {

		$LINKcheck = __('Yes', MEBAK_LOCALE );
	}
	else {

		$LINKcheck = __('No', MEBAK_LOCALE );
	}

	if(strlen(PATH_ZIP)==0) {

		$tmpz = __('Not available', MEBAK_LOCALE);
	}
	else {

		$tmpz = PATH_ZIP;
	}

	if(strlen(PATH_TAR)==0) {

		$tmp = __('Not available', MEBAK_LOCALE);
	}
	else {

		$tmp = PATH_TAR;
	}

	$html .= '<div style="background-color:#EAEAEA;border:1px solid #CFCFCF;margin:8px;padding:6px;-moz-border-radius:6px;border-radius:6px;">'
		.'<h3>Server settings</h3>'
			.'<p>OS: <b>'.$os_desc.'</b></p>'
			.'<p>Is the hosting service using a real path for the document root? <b>'.$LINKcheck.'</b></p>'
			.'<p>Document root: <b>'.$_SERVER['DOCUMENT_ROOT'].'</b></p>'
			.'<p>WordPress parent path: <b>'.MEBAK_WP_PARENT_PATH.'</b></p>'
			.'<p>WordPress install path: <b>'.MEBAK_WP_PATH.'</b></p>'
			.'<p>Plugin path: <b>'.MEBAK_PATH.'</b></p>'
			.'<p>Backup path: <b>'.MEBAK_BACKUP_PATH.'</b></p>'
			.'<p>System commands allowed: <b>'.$isSYSTEM.'</b></p>'
			.'<p>Zip: <b>'.$ZIPcheck.'</b></p>'
			.'<p>Path to zip: <b>'.$tmpz.'</b></p>'
			.'<p>Tar: <b>'.$TARcheck.'</b></p>'
			.'<p>Path to tar: <b>'.$tmp.'</b></p>'
		.'</div>'
	;

	$html .= '<div style="background-color:#EAEAEA;border:1px solid #CFCFCF;margin:8px;padding:6px;-moz-border-radius:6px;border-radius:6px;">'
		.'<h3>PHP settings</h3>'
			.'<p>PHP Version: <b>'.PHP_VERSION.'</b></p>'
			.'<p>Disabled functions: <b>'.$disabled.'</b></p>'
			.'<p>Safe mode: <b>'.$safe.'</b></p>'
			.'<p>Is ZipArchive available: <b>'.$isZipArchive.'</b></p>'
			.'<p>Is RecursiveIteratorIterator available: <b>'.$isRecursiveIteratorIterator.'</b></p>'
			.'<p>`open_basedir` restriction: <b>'.$isOPEN_BASEDIR.'</b></p>'
			.'<p>Zlib: <b>'.$zlib_desc.'</b></p>'
			.'<p>FTP: <b>'.$ftp_desc.'</b></p>'
		.'</div>'
	;

//echo phpinfo();
//echo 'zend_loader_enabled['.zend_loader_enabled().']';


	/**
	 * http://www.ianr.unl.edu/internet/mailto.html
	 */
	$body = str_replace('</p>', '%0A', $html);
	$body = str_replace('</h3>', '%0A', $body);
	$body = str_replace('</div>', '%0A%0A', $body);
	$body = strip_tags($body);

	//echo $html
	$html .= ''
			.'<div align="center" style="padding:8px 0;">'
				.'<a class="button-primary" href="mailto:info@myeasywp.com?subject=myEASYbackup debug info&body='.$body.'">'
					.__('Send the debug info to the developer', MEBAK_LOCALE )
				.'</a>'
			.'</div>'
		.'</div>'
	;
}

?>