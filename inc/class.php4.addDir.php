<?php
/**
 * ZipArchive extention class for PHP4
 *
 * not supported anymore since 1.0.6
 *
 * @package myEASYbackup
 * @author Ugo Grandolini
 * @since 0.9.1
 * @version 1.0.5.5
 *
 */
if(class_exists('ZipArchive')) {

	class Zipper extends ZipArchive {
		#
		#	Add directories recursiverly (PHP4)
		#
		function addDir($path, $MODE) {						#	0.0.5 - 0.1.4

			#   for some unknow reasons, the RecursiveIterator syntax is not reckognized on some PHP4 servers
			#   thus I decided its better to go for an alternative way to read a given directory structure
			#
			$path = realpath($path);
			$dirs = scandir($path);

/**
 * @since 1.0.5.5 - beg
 */
			$tmpAry = unserialize(get_option('meb_exclude_folder'));

//$excludesFolders = '';
//foreach($tmpAry as $k => $data) {
//
//	$excludesFolders .= $source . $data . '|';
//}
//echo '$source['.$source.'] $path['.$path.']<br>';
//echo '$excludesFolders['.$excludesFolders.']<br>';
/**
 * @since 1.0.5.5 - end
 */

			foreach($dirs as $file) {

				chdir(MEBAK_WP_PATH);

/**
 * @since 1.0.5.5 - beg
 */
//echo '<br>$file['.$file.']';

				$isOK2include = true;

				if(MYEASYBACKUP_FAMILY=='LITE' || MYEASYBACKUP_FAMILY=='PRO') {
					$fileDir = dirname($file);
					foreach($tmpAry as $k => $data) {

						if($fileDir != $file && strpos($fileDir, $file . $data, 0) !== false) {

							$isOK2include = false;
						}
					}
				}

				if($isOK2include == true) {

//echo ' INCLUDED ';
/**
 * @since 1.0.5.5 - end
 */

					if($file=='.' || $file=='..') {

						/* do nothing */
					}
					else if(is_dir($path . '/' . $file)) {

						if($MODE=='interactive' && defined('ZIP_VERBOSE') && ZIP_VERBOSE==1) {

							//echo '<br />'.__('Adding folder ', MEBAK_LOCALE ) . '<b>' . $file . '</b><br />';
							echo $path . '/' . $file . '<br />';
						}
						$this->addEmptyDir($path);
						$this->addDir(str_replace(MEBAK_WP_PATH, '', $path . '/' . $file), $MODE);
					}
					else if(is_file($path . '/' . $file)) {

						if($MODE=='interactive' && defined('ZIP_VERBOSE') && ZIP_VERBOSE==1) {

							echo $path . '/' . $file . '<br />';
						}
						$this->addFile(str_replace(MEBAK_WP_PATH, '', $path . '/' . $file));
					}
				}
			}
		}
	}
}
?>