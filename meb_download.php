<?php
/**
 * Download a data set
 *
 * @package myEASYbackup
 * @author Ugo Grandolini
 * @version 1.0.9
 */

#
#	0.0.3: BEG
#-------------
#
#	1.0.0: BEG
#-------------
//if(	($_SERVER['HTTP_HOST'] != $_SERVER['SERVER_NAME'])
//		||
//	($_SERVER['HTTP_HOST'] != $referer)
//		||
//	($_SERVER['SERVER_NAME'] != $referer)
//) {
if(stripos($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME'])!==false
	&& stripos($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER'])!==false) {
#-------------
#	1.0.0: END
#
	echo 'Nice try, you cheeky monkey!';	#	0.0.5
	return;
}
#-------------
#	0.0.3: END
#

//echo '(0) meb_backup_root='.$meb_backup_root.'<br>';

define('MEBAK_ISDOWNLOAD', true);
require_once('meb-config.php');

//echo '(1) meb_backup_root='.$meb_backup_root.'<br>';

//$file_name = $_GET['dwnfile'];	#	0.0.5
//$file_name = $_POST['dwn_file'];	#	0.0.5
$file_name = basename($_POST['dwn_file']);	#	1.0.9: fixes the exploit http://packetstormsecurity.org/files/108711/

$file = MEBAK_BACKUP_PATH . '/' . $file_name;

//if(file_exists($file))					// 1.0.9
if(file_exists($file) && (!is_dir($file)))	// 1.0.11
{
	$bytes = filesize($file);

	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private',false);
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

	//header('Content-Type: application/x-download');		#	0.0.5: IE fix
	header('Content-Type: application/zip');				#	0.0.5: IE fix
	header('Content-Type: application/force-download');		#	0.0.5: IE fix

	header('Content-Transfer-Encoding: binary');
	header('Content-Length: '.(string) $bytes);
	header('Content-Disposition: attachment; filename="'.$file_name.'"');

	#
	#	start the download
	#
	//set_time_limit(0);			#	0.0.4: moved to meb-config.php

	/**
	 * 1.0.5.5: beg
	 */
	//readfile($file);

	$chunksize = 1 * (1024 * 1024);
	$handle = fopen($file, 'rb');
	$buffer = '';
	while(!feof($handle)) {

		$buffer = fread($handle, $chunksize);
		echo $buffer;
		ob_flush();
		flush();
	}
	fclose($handle);
	/**
	 * 1.0.5.5: end
	 */
}
else {

	echo 'sorry, cannot find the download file at: '.$file.'<br>';
	exit();
}
?>