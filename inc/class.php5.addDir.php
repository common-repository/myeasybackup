<?php
/**
 * ZipArchive extention class for PHP5
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
		#	Add directories recursiverly (PHP5)
		#
		public function addDir($path, $MODE) {				#	0.0.4 - 0.1.4

			if($MODE=='interactive' && defined('ZIP_VERBOSE') && ZIP_VERBOSE==1) {

				echo '<br />'
					.__('Adding folder ', MEBAK_LOCALE ) . '<b>' . $path . '</b><br />';
			}

			#   1.0.5.9: BEG
			#
			$source = null;
			$tmpAry = null;
			$dir = null;
			$files = null;

			unset($source);
			unset($tmpAry);
			unset($dir);
			unset($files);
			#
			#   1.0.5.9: END


			$source = realpath($path);

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

			if(is_dir($source) === true) {

				//$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);	#	0.1.4

				$dir = new RecursiveDirectoryIterator($source);											#	0.1.4
				$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);	#	0.1.4

				foreach($files as $file) {

					$file = realpath($file);

/**
 * @since 1.0.5.5 - beg
 */
//echo '<br>$file['.$file.']';

					$isOK2include = true;

					if(MYEASYBACKUP_FAMILY=='LITE' || MYEASYBACKUP_FAMILY=='PRO') {

						$fileDir = dirname($file);
						foreach($tmpAry as $k => $data) {

							if($fileDir != $source && strpos($fileDir, $source . $data, 0) !== false) {

								$isOK2include = false;
							}
						}
					}

					if($isOK2include == true) {

//echo ' INCLUDED ';
/**
 * @since 1.0.5.5 - end
 */


/**
 * @since 1.0.5.9 - beg
 */
					$availableRAM = (ini_get('memory_limit') * 1048576) / 2;

//echo 'memory check '.__LINE__ .'>'. number_format(memory_get_peak_usage(),0).' '.number_format($availableRAM,0).'<br />'; # 1.0.5.9 debug

					if(memory_get_peak_usage() > $availableRAM) {

						$actualLimit = ini_get('memory_limit');
						$increment = 8;
						$newLimit = $actualLimit + $increment;
						ini_set('memory_limit',	$newLimit);
						echo '<p style="color:red;">new memory limit='.number_format(ini_get('memory_limit'),0).'</p>';
					}

//echo 'memory usage '.__LINE__ .'>'. number_format(memory_get_peak_usage(),0).' '.number_format(PHP_RAM,0).' '.number_format(ini_get('memory_limit'),0).' '.number_format($availableRAM,0).'<br />'; # 1.0.5.9 debug
//echo 'memory usage '.__LINE__ .'>'. number_format(memory_get_peak_usage(),0).' '.number_format((ini_get('memory_limit')*1048576),0).'<br />'; # 1.0.5.9 debug
/**
 * @since 1.0.5.9 - end
 */

						if(is_dir($file) === true) {

							if($MODE=='interactive' && defined('ZIP_VERBOSE') && ZIP_VERBOSE==1) {

								echo '<br />'.__('Adding folder ', MEBAK_LOCALE ) . '<b>' . $file . '</b><br />';
							}
							$this->addEmptyDir(str_replace($source . '/', '', $file . '/'));
						}
						else if(is_file($file) === true) {

							if($MODE=='interactive' && defined('ZIP_VERBOSE') && ZIP_VERBOSE==1) {

								echo $file . '<br />';
							}

//							$this->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));   #   1.0.5.9
							$this->addFile($file, str_replace($source . '/', '', $file));   #   1.0.5.9
						}
					}
				}
			}
			else if(is_file($source) === true) {

/**
 * @since 1.0.5.5 - beg
 */
//echo '<br>$source['.$source.']';

				$isOK2include = true;
				if(MYEASYBACKUP_FAMILY=='LITE' || MYEASYBACKUP_FAMILY=='PRO') {

					$fileDir = dirname($source);
					foreach($tmpAry as $k => $data) {

						if($fileDir != $source && strpos($fileDir, $source . $data, 0) !== false) {

							$isOK2include = false;
						}
					}
				}

				if($isOK2include == true) {

//echo ' INCLUDED ';
/**
 * @since 1.0.5.5 - beg
 */
//echo 'memory usage '.__LINE__ .'>'. number_format(memory_get_peak_usage(),0).' '.number_format(PHP_RAM,0).' '.number_format(ini_get('memory_limit'),0).' '.number_format($availableRAM,0).'<br />'; # 1.0.5.9 debug
//echo 'memory usage '.__LINE__ .'>'. number_format(memory_get_peak_usage(),0).' '.number_format((ini_get('memory_limit')*1048576),0).'<br />'; # 1.0.5.9 debug

					$this->addFromString(basename($source), file_get_contents($source));
				}
			}
		}
	}
}
?>