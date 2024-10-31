<?php
/**
 * @package myEASYbackup
 * @author Ugo Grandolini
 * @version 1.0.6.1
 *
 * return codes
 *
 * string	= *OK*{full path to the backup file}
 * 1		= error (interactive mode)
 * string	= error (scheduled mode)
 *
 */
function myeasybackup_exec($MODE)
{
	set_time_limit(0);  #   1.0.5.9

	//if($MODE!='interactive' && $MODE!='scheduled')
	//{
	//	$tmp = __('Missing the mode type in ' . __FUNCTION__, MEBAK_LOCALE );
	//	return $tmp;
	//}

	switch($MODE)
	{
		case 'scheduled':

			$_POST['filename']					= 'myEASYbackup__scheduled__'.date('Y-m-d_H-i-s', time());
			$_POST['cdatabase']					= get_option('meb_cron_inc_db');
			$_POST['cwordpress']				= get_option('meb_cron_inc_wp');
			$_POST['upload_restore_tool_exec']	= get_option('meb_cron_inc_restore');
			$_POST['z_password']				= md5(session_id());
			break;
			#
		case 'interactive':
			break;
			#
		default:

			$tmp = __('Missing the mode type in ' . __FUNCTION__, MEBAK_LOCALE );
			return $tmp;
	}

	#
	#	show/hide the list of files compressed with 'zip'
	#
	if(defined('ZIP_VERBOSE') && ZIP_VERBOSE==1)
	{
		$zip_verbose = '';
		$tar_verbose = 'v';
	}
	else
	{
		$zip_verbose = 'q';
		$tar_verbose = '';
	}

	if($MODE=='interactive')
	{
		echo '<div id="backingup">'
				.__( 'Backing up...', MEBAK_LOCALE )
			.'</div>'

			.'<div class="updated">'
			.'<h3>Info about your backup</h3>'
		;
	}

	$result = 0;

	if(!is_dir(MEBAK_BACKUP_PATH)) {

		/**
		 * @since 1.0.0
		 */
		$tmp = '<div class="warning">'

				. __( 'The backup path is not present on the server: ', MEBAK_LOCALE )
				. MEBAK_BACKUP_PATH . '<br />'
				. __( 'Please choose a <a href="options-general.php?page=myEASYbackup_options">valid backup path</a> before tyring to create a data set!', MEBAK_LOCALE )

		.'</div>';

		if($MODE=='interactive') {

			echo $tmp . MEBAK_BACK_BUTTON . '</div>';
			return 1;
		}
		else {

			return $tmp;
		}
	}

	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	#	backup the database - beg
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if($_POST['cdatabase'])
	{
		#	extracting the tables data
		#
		if($MODE=='interactive')
		{
			echo __( 'Extracting database tables...', MEBAK_LOCALE ) . '<br /><br />';
		}

		$file = MEBAK_BACKUP_PATH .'/' . $_POST['filename'] . '.sql';

		if(isSYSTEM==true && FORCE_PHPCODE==false)
		{
			#	using mysqldump
			#
			$tmpfile = tempnam( MEBAK_BACKUP_PATH, 'mebak_plugin' );

//			$mysqldump = 'mysqldump --opt --no-create-db '         # 1.0.2
			$mysqldump = PATH_MYSQLDUMP.' --opt --no-create-db '   # 1.0.2
					.' -u ' . escapeshellarg(DB_USER)
					.' -p'  . escapeshellarg(DB_PASSWORD)
					.' -h ' . escapeshellarg(DB_HOST)
					.' -r ' . escapeshellarg($file) . ' ' . escapeshellarg(DB_NAME) . ' 1> ' . escapeshellarg($tmpfile) .' 2>&1';

//die($mysqldump);

			//echo '<pre>';
				system( $mysqldump, $result );
			//echo '</pre><br />';

			$mysqldump_error  = file_get_contents($tmpfile);        #   0.9.3
			$mysqldump_result = $result;                            #   0.9.3

			unlink($tmpfile);
		}
		else
		{
			#	getting database data with queries
			#
			$query = mysql_query('SHOW TABLES');
			$table = '';
			$i = 0;
			while($tables = mysql_fetch_array($query, MYSQL_ASSOC))
			{
				list(,$table) = each($tables);
				$table_select[$i] = $table;
				$i++;
			}

			$fp = fopen($file, 'w');

			if(!$fp)
			{
				$tmp = '<div class="error">'
						.__( 'Impossible to create', MEBAK_LOCALE )
						.': <b>' . $file . '</b> '
					.'</div>'
				;

				if($MODE=='interactive')
				{
					echo $tmp . MEBAK_BACK_BUTTON . '</div>';
					return 1;
				}
				else
				{
					return $tmp;
				}
			}

			$data =  '/*'
					.' MySQL backup'
					.' Database: '. DB_NAME
					.' Site: ' . str_replace('www.', '', $_SERVER['HTTP_HOST'])
					.' Backup started: ' . date('r U', time())
					.' Host: '. mysql_get_host_info()
					.' MySQL server version: '. mysql_get_server_info()
					.' MySQL protocol: '. mysql_get_proto_info()
					.' Created with myEASYbackup version ' . MYEASYBACKUP_VERSION
					.' http://myeasywp.com'
					.' */' . "/*[[EOR]]*/\n"
			;
			fwrite($fp, $data);

			$i = 0;
			foreach($table_select as $table)
			{
				$i++;

				if($MODE=='interactive' && defined('ZIP_VERBOSE') && ZIP_VERBOSE==1)
				{
					echo __('Getting contents of table ') . ' <b>' . $table .'</b>...<br />';
				}

				$data =  "\n" . '/******'
						  .' * Structure for table `' . $table . '`'
						  .' */' . "/*[[EOR]]*/\n"

							. 'DROP TABLE IF EXISTS `' . $table . '`;' . "/*[[EOR]]*/\n\n";

				$rows_query = mysql_query('SHOW CREATE TABLE `' . $table . '`');
				$tables = mysql_fetch_array($rows_query);

				$data .= str_replace("\n", ' ', $tables[1]) . ";/*[[EOR]]*/\n"
						."\n" . '/******'
						  .' * Contents for table `' . $table . '`'
						  .' */' . "/*[[EOR]]*/\n";

				fwrite($fp, $data);

				$table_list = array();
				$fields_query = mysql_query('SHOW FIELDS FROM  `' . $table . '`');

				while($fields = mysql_fetch_array($fields_query))
				{
					$table_list[] = $fields['Field'];
				}

				$rows_query = mysql_query('SELECT * FROM `' . $table . '` ');
				$tr = 0;

				while($rows = mysql_fetch_array($rows_query))
				{
					$tr++;
					$data = 'INSERT INTO `' . $table . '` (`' . implode('`, `', $table_list) . '`) VALUES (';

					fwrite($fp, $data);
					$comma = '';

					reset($table_list);
					while(list(,$i) = each($table_list))
					{
						$data = '';

						if(!isset($rows[$i]))
						{
							$data .= 'NULL ';
						}
						elseif(has_data($rows[$i]))
						{
							$row = addslashes($rows[$i]);
							$row = str_replace("\n#", "\n".'\#', $row);

							$data .= '\'' . $row . '\'';
						}
						else
						{
							$data .= '\'\'';
						}

						fwrite($fp, $comma . $data);
						$comma = ', ';
					}

					fwrite($fp, ");/*[[EOR]]*/\n");
				}
				if($tr<1)
				{
					fwrite($fp, '/* Table `' . $table . '` is empty' .' */' . "/*[[EOR]]*/\n");
				}
			}

			$data = '/* Backup ended: ' . date('r U', time()) . ' */' . "/*[[EOR]]*/\n";

			fwrite($fp, $data);
			fclose($fp);
		}
	}

#   0.9.3: BEG
#-------------
	$finfo = array();
	if(file_exists($file)) {

		$finfo = stat($file);
	}

//	if(file_exists($file) && $finfo['size']>0)
	if($finfo['size']>0)
	{
#-------------
#   0.9.3: END

		if($MODE=='interactive')
		{
			echo '<br />'
				.__( 'Your database has been saved as', MEBAK_LOCALE )
				.': <b>'
					.$file
				. '</b><br />';
		}

		chmod($file, 0755);
	}
	else
	{
		if($_POST['cdatabase'])
		{
			$tmp = '<div class="error">'
					.'<b>'
						.__( 'Error while trying to extract database data', MEBAK_LOCALE )
					.'</b>, '
			;

			if(isSYSTEM==true && FORCE_PHPCODE==false)
			{
				$tmp .= ''
					.__( 'using the following command', MEBAK_LOCALE )

						.':<br /><code>'.$mysqldump.'</code>'
						.'<br /><b>'.$mysqldump_error.' ['.$mysqldump_result.']</b>'
					;
			}
			else
			{
				$tmp .= ''
					.__( 'extracting data with queries', MEBAK_LOCALE );
			}

			//echo '</div>'
			//	.MEBAK_BACK_BUTTON;
			//return -1;
			$tmp .= '</div>';

			if($MODE=='interactive')
			{
				echo $tmp . MEBAK_BACK_BUTTON . '</div>';
				return 1;
			}
			else
			{
				return $tmp;
			}
		}
	}
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	#	backup the database - end
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	#	create the restore
	#	configuration file - beg
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if($_POST['cdatabase'] && $_POST['upload_restore_tool_exec']==1)
	{
		#	create the restore configuration file
		#
		$ini_file = MEBAK_BACKUP_PATH .'/myEASYrestore_ini.php';

		$fp = fopen($ini_file, 'w');
		if($fp)
		{
			$data = '<?php return; ?>' . "\n"
					.';---------------------------------------------------------' . "\n"
					.'; myEASYbackup data set restore/migrate configuration file' . "\n"
					.'; do NOT touch to avoid issues with myEASYrestore!' . "\n"
					.';' . "\n"
					.'; Created by myEASYbackup version ' . MYEASYBACKUP_VERSION . "\n"
					.'; http://myeasywp.com' . "\n"
					.';---------------------------------------------------------' . "\n\n"

					.'[mysql_src]' . "\n"
					.'DB_DB = "' .  DB_NAME . '"' . "\n"
					.'DB_USER = "' .  DB_USER . '"' . "\n"
					.'DB_PASSWORD = "' .  DB_PASSWORD . '"' . "\n"
					.'DB_CHARSET = "' .  DB_CHARSET . '"' . "\n"
					.'DB_COLLATE = "' .  DB_COLLATE . '"' . "\n"
					.'TBL_PREFIX = "' .  $table_prefix . '"' . "\n"
					."\n"

					.'[siteinfo_src]' . "\n"
					.'Root_path = "' . ABSPATH . '"' . "\n"
					.'Site = "' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . '"' . "\n"
					.'Host = "'. mysql_get_host_info() . '"' . "\n"
					.'Server_info = "'. mysql_get_server_info() . '"' . "\n"
					.'Server_protocol = "'. mysql_get_proto_info() . '"' . "\n"
					."\n"

					.'[options]' . "\n"
					.'Use_PHP_code = "' . FORCE_PHPCODE . '"' . "\n"
					.'Use_ZIP_password = "' . ASK_ZIP_PWD . '"' . "\n"

					.'; END OF DATA' . "\n"
					.';---------------------------------------------------------' . "\n"
			;
			fwrite($fp, $data);
			fclose($fp);

			if($MODE=='interactive')
			{
				echo ''
					. __( 'The restore configuration file was successfully created.', MEBAK_LOCALE )
					.'<br /><br />';
			}
		}
		else
		{
			if($MODE=='interactive')
			{
				echo '<p style="color:red;font-weight:bold;">'

					. __( 'Unable to create the restore configuration file, it will NOT be possible to use the myEASYrestore tool with this data set.', MEBAK_LOCALE )
					. __( 'The full path where the myEASYrestore tool was trying to be created is:', MEBAK_LOCALE )
					. '<br />' . $ini_file . '<br /><br />'

				.'</p><p>'

					. __( 'However you will be able to manually restore the data set.', MEBAK_LOCALE )

				.'</p><br /><br />';
			}
		}
	}
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	#	create the restore
	#	configuration file - end
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	#	compress the data set
	#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	chdir(MEBAK_BACKUP_PATH);

	$dwnfilename	= '';
	$dest_file_zip	= $_POST['filename']  . '.zip';

	$dest_file_tar	= $_POST['filename']  . '.tar';					#	0.1.4
	$dest_file_tgz	= $_POST['filename']  . '.tgz';					#	0.1.4

	if(defined('isLINUX') && isLINUX==true && isSYSTEM==true && FORCE_PHPCODE==false)
	{
		#	Linux & system() is allowed
		#
		if($_POST['cdatabase'])
		{
			switch(SYS_ARCHIVING_TOOL)
			{
				#--------------------------
				case 'z':
				#--------------------------
					//echo '<pre>';
					if($_POST['z_password']!='') {

//						system( 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '
//									. '-P '.$_POST['z_password'] . ' '
//									. MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//									. ' ' . $_POST['filename'] . '.sql',
//									$result );

//						$zip_command = 'zip -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '         # 1.0.2
//						$zip_command = PATH_ZIP.' -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '   # 1.0.2
						$zip_command = PATH_ZIP.' -r' . $zip_verbose . COMPRESSION_LEVEL . ' '                   # 1.0.5.3
										. '-P ' . escapeshellarg($_POST['z_password']) . ' '
										. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_zip) . ' '
										. escapeshellarg($_POST['filename'] . '.sql');
					}
					else {

//						system( 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '
//									. MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//									. ' ' . $_POST['filename'] . '.sql',
//									$result );

//						$zip_command = 'zip -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '         # 1.0.2
//						$zip_command = PATH_ZIP.' -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '   # 1.0.2
						$zip_command = PATH_ZIP.' -r' . $zip_verbose . COMPRESSION_LEVEL . ' '                   # 1.0.5.3
										. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_zip) . ' '
										. escapeshellarg($_POST['filename'] . '.sql');
					}

					$zip_error = system( $zip_command, $result );               #   0.9.3

					//echo '</pre>';

					if($result==0) {

						if($MODE=='interactive') {

							echo ' <span style="color:green;font-weight:bold;">' . __( 'Your database has been added to', MEBAK_LOCALE )
									.': <b>' . MEBAK_BACKUP_PATH . '/' . $dest_file_zip . '</b></span>'
									. '<br /><br />';
						}
					}
					else {

						$tmp = '<br /><span style="color:red;font-weight:bold;">' . __( 'Error [' . $result . '] while executing the following command', MEBAK_LOCALE ) . '</span>:<br />'

								.'<code style="font-size:13px;">'
//									.'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' ';
//
//										if($_POST['z_password']!='') {
//
//											$tmp .= '-P '.$_POST['z_password'] . ' ';
//										}
//
//										$tmp .= MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//												. ' ' . $_POST['filename'] . '.sql'
									. $zip_command . '<br />'
								.'</code>'

								.__( 'You might try to compress data by using the TAR system command in the settings page.', MEBAK_LOCALE ) . MEBAK_SETTINGS_BUTTON  #   1.0.5.3

								.'<br /><br />';

						if($MODE=='interactive') {

							echo $tmp . MEBAK_BACK_BUTTON . '</div>';
							return 1;
						}
						else {

							return $tmp;
						}
					}
					break;
					#
				#--------------------------
				case 't':
				#--------------------------
					if(!file_exists(MEBAK_BACKUP_PATH . '/' . $dest_file_tar)) {

						touch(MEBAK_BACKUP_PATH . '/' . $dest_file_tar);
					}

					//echo '<pre>';
//					system( 'tar -r'.$tar_verbose.'pf '
//								. MEBAK_BACKUP_PATH . '/' . $dest_file_tar . ' '
//								. $_POST['filename'] . '.sql ',
//								$result );

//					$tar_command = 'tar -r'.$tar_verbose.'pf '           # 1.0.2
					$tar_command = PATH_TAR
								. ' -r'.$tar_verbose.'pf '     # 1.0.2
								. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_tar) . ' '
								. escapeshellarg($_POST['filename'] . '.sql');

					$tar_error = system( $tar_command, $result );               #   0.9.3

					//echo '</pre>';

//echo '$result:'.$result.'<br>';

					if(substr($result, -1)==0) {

						if($MODE=='interactive') {
							echo ' <span style="color:green;font-weight:bold;">' . __( 'Your database has been added to', MEBAK_LOCALE )
									.': <b>' . MEBAK_BACKUP_PATH . '/' . $dest_file_tar . '</b></span>'
									. '<br /><br />';
						}
					}
					else {

						$tmp = '<br /><span style="color:red;font-weight:bold;">' . __( 'Error [' . $result . '] while executing the following command', MEBAK_LOCALE ) . '</span>:<br />'

								.'<code style="font-size:13px;">'

//									.'tar -r'.$tar_verbose.'pf ' . MEBAK_BACKUP_PATH . '/' . $dest_file_tar
//										. ' ' . $_POST['filename'] . '.sql '
									. $tar_command

								.'</code>'

								.__( 'You might try to compress data by using the ZIP system command in the settings page.', MEBAK_LOCALE ) . MEBAK_SETTINGS_BUTTON  #   1.0.5.3

								.'<br /><br />';

						unlink(MEBAK_BACKUP_PATH . '/' . $dest_file_tar);   #   0.9.3

						if($MODE=='interactive') {

							echo $tmp . MEBAK_BACK_BUTTON . '</div>';
							return 1;
						}
						else {

							return $tmp;
						}
					}
					break;
					#
				#--------------------------
				default:
				#--------------------------
					$tmp = __( 'You are not using PHP to create the data set but I do not have instructions about what system command to use: please check the Settings page and set Zip or Tar in the "What system command do you like to use to create the data set?" option.', MEBAK_LOCALE );

					if($MODE=='interactive') {

						echo $tmp . MEBAK_BACK_BUTTON . '</div>';
						return 1;
					}
					else {

						return $tmp;
					}
			}

			if($_POST['upload_restore_tool_exec']==1)
			{
				chdir(MEBAK_BACKUP_PATH);

				switch(SYS_ARCHIVING_TOOL) {

					#--------------------------
					case 'z':
					#--------------------------
						//echo '<pre>';
						if($_POST['z_password']!='') {

//							system( 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '
//										. '-P '.$_POST['z_password'] . ' '
//										. MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//										. ' ' . basename($ini_file),
//										$result );

//							$zip_command = 'zip -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '         # 1.0.2
//							$zip_command = PATH_ZIP.' -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '   # 1.0.2
							$zip_command = PATH_ZIP.' -r' . $zip_verbose . COMPRESSION_LEVEL . ' '                   # 1.0.5.3
										. '-P '.escapeshellarg($_POST['z_password']) . ' '
										. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_zip) . ' '
										. escapeshellarg(basename($ini_file));
						}
						else {

//							system( 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '
//										. MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//										. ' ' . basename($ini_file),
//										$result );

//							$zip_command = 'zip -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '         # 1.0.2
//							$zip_command = PATH_ZIP.' -r' . escapeshellarg($zip_verbose . COMPRESSION_LEVEL) . ' '   # 1.0.2
							$zip_command = PATH_ZIP.' -r' . $zip_verbose . COMPRESSION_LEVEL . ' '                   # 1.0.5.3
										. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_zip) . ' '
										. escapeshellarg(basename($ini_file));
						}

						system( $zip_command, $result );          #   0.9.3

						//echo '</pre>';

						if($result==0)
						{
							if($MODE=='interactive') {

								echo ' <span style="color:green;font-weight:bold;">' . __( 'The restore ini file has been added to', MEBAK_LOCALE )
										.': <b>' . MEBAK_BACKUP_PATH . '/' . $dest_file_zip . '</b></span>'
										. '<br /><br />';
							}
						}
						else {

/* 06/07/2011: BEG */
							$zipErr = array();
							$zipErr[0] = 'Normal; no errors or warnings detected.';
							$zipErr[2] = 'The zipfile is either truncated or damaged in some way (e.g., bogus internal offsets) that makes it appear to be truncated.';
							$zipErr[3] = 'A generic error in the zipfile format was detected. Processing may have completed successfully anyway; some broken zipfiles created by other archivers have simple workarounds.';
							$zipErr[4] = 'Zip was unable to allocate memory for one or more buffers during program initialization.';
							$zipErr[5] = 'A severe error in the zipfile format was detected. Processing probably failed immediately.';
							$zipErr[6] = 'Entry too large to be processed (such as input files larger than 2 GB when not using Zip64 or trying to read an existing archive that is too large) or entry too large to be split with ZipSplit.';
							$zipErr[7] = 'Invalid comment format.';
							$zipErr[8] = 'Testing (-T option) failed due to errors in the archive, insufficient memory to spawn UnZip, or inability to find UnZip.';
							$zipErr[9] = 'The user aborted Zip prematurely with control-C (or similar).';
							$zipErr[10] = 'Zip encountered an error while using a temp file.';
							$zipErr[11] = 'Read or seek error.';
							$zipErr[12] = 'Zip has nothing to do.';
							$zipErr[13] = 'The zipfile was missing or empty (typically when updating or freshening).';
							$zipErr[14] = 'Zip encountered an error writing to an output file (typically the archive); for example, the disk may be full.';
							$zipErr[15] = 'Zip could not open an output file (typically the archive) for writing.';
							$zipErr[16] = 'Bad command line parameters.';
							$zipErr[18] = 'Zip could not open a specified file for reading; either it doesn\'t exist or the user running Zip doesn\'t have permission to read it.';
							$zipErr[19] = 'Zip was compiled with options not supported on this system.';
/* 06/07/2011: END */

							$tmp = '<br /><span style="color:red;font-weight:bold;">' . __( 'Error [' . $result . '] while executing the following command', MEBAK_LOCALE ) . '</span>:<br />'

									.'<code style="font-size:13px;">'
//										.'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' ';
//
//											if($_POST['z_password']!='')
//											{
//												$tmp .= '-P '.$_POST['z_password'] . ' ';
//											}
//
//											$tmp .= MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//													. ' ' . basename($ini_file)
										. $zip_command
									.'</code>'

									.$zipErr[$result] /* 06/07/2011 */

									.__( 'You might try to compress data by using the TAR system command in the settings page.', MEBAK_LOCALE ) . MEBAK_SETTINGS_BUTTON  #   1.0.5.3

									.'<br /><br />';

							if($MODE=='interactive') {

								echo $tmp . MEBAK_BACK_BUTTON . '</div>';
								return 1;
							}
							else {

								return $tmp;
							}
						}
						break;
						#
					#--------------------------
					case 't':
					#--------------------------
						if(!file_exists(MEBAK_BACKUP_PATH . '/' . $dest_file_tar)) {

							touch(MEBAK_BACKUP_PATH . '/' . $dest_file_tar);
						}

						//echo '<pre>';
//						system( 'tar -r'.$tar_verbose.'pf ' . MEBAK_BACKUP_PATH . '/' . $dest_file_tar
//									. ' ' . basename($ini_file) . ' ',
//									$result );

//						$tar_command = 'tar -r'.$tar_verbose.'pf '               # 1.0.2
						$tar_command = PATH_TAR.' -r'.$tar_verbose.'pf '         # 1.0.2
										. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_tar) . ' '
										. escapeshellarg(basename($ini_file));

						$tar_error = system( $tar_command, $result );               #   0.9.3

						//echo '</pre>';

//echo '$result:'.$result.'<br>';

						if(substr($result, -1)==0) {

							if($MODE=='interactive') {

								echo ' <span style="color:green;font-weight:bold;">' . __( 'The restore ini file has been added to', MEBAK_LOCALE )
										.': <b>' . MEBAK_BACKUP_PATH . '/' . $dest_file_tar . '</b></span>'
										. '<br /><br />';
							}
						}
						else {

							$tmp = ' <span style="color:red;font-weight:bold;">' . __( 'Error [' . $result . '] while executing the following command', MEBAK_LOCALE ) . '</span>:<br />'

									.'<code style="font-size:13px;">'

//										.'tar -r'.$tar_verbose.'pf ' . MEBAK_BACKUP_PATH . '/' . $dest_file_tar
//											. ' ' . basename($ini_file) . ' '
										. $tar_command

									.'</code>'

									.__( 'You might try to compress data by using the ZIP system command in the settings page.', MEBAK_LOCALE ) . MEBAK_SETTINGS_BUTTON  #   1.0.5.3

									.'<br /><br />';

							unlink(MEBAK_BACKUP_PATH . '/' . $dest_file_tar);   #   0.9.3

							if($MODE=='interactive') {

								echo $tmp . MEBAK_BACK_BUTTON . '</div>';
								return 1;
							}
							else {

								return $tmp;
							}
						}
						break;
						#
					#--------------------------
					default:
					#--------------------------
						$tmp = __( 'You are not using PHP to create the data set but I do not have instructions about what system command to use: please check the Settings page and set Zip or Tar in the "What system command do you like to use to create the data set?" option.', MEBAK_LOCALE );

						if($MODE=='interactive') {

							echo $tmp . MEBAK_BACK_BUTTON . '</div>';
							return 1;
						}
						else {

							return $tmp;
						}
				}
			}

			if($MODE=='interactive') {

				echo '<br />';
			}

			if(file_exists(MEBAK_BACKUP_PATH . '/' . $_POST['filename'] . '.sql')) {

				unlink(MEBAK_BACKUP_PATH . '/' . $_POST['filename'] . '.sql');

				if($MODE=='interactive') {

					echo __( 'The uncompressed MySQL backup file has been removed &ndash; it\'s ok, just to let you know!', MEBAK_LOCALE )
							.'<br /><br />';
				}
			}
		}


		if($_POST['cwordpress']) {

			if($MODE=='interactive') {

				echo '<br />' . __( 'Saving your WordPress installation...', MEBAK_LOCALE ) . '<br />';
			}

			chdir(MEBAK_WP_PATH);

//echo 'MEBAK_WP_PATH:' . MEBAK_WP_PATH . '<br>';
//echo 'SYS_ARCHIVING_TOOL:' . SYS_ARCHIVING_TOOL . '<br>';

			switch(SYS_ARCHIVING_TOOL) {

				#--------------------------
				case 'z':
				#--------------------------
					//echo '<pre>';
					if($_POST['z_password']!='') {

//						system( 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '
//									. '-P '.$_POST['z_password'] . ' '
//									. MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//									. ' . ',
//									$result );

//						$zip_command = 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '             # 1.0.2
						$zip_command = PATH_ZIP
										. ' -r' . $zip_verbose . COMPRESSION_LEVEL . ' '       # 1.0.2
										. '-P ' . escapeshellarg($_POST['z_password']) . ' '
										. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_zip)
										. ' . '
										. ZIP_EXCLUDE_FOLDERS          # 1.0.5.5
						;
					}
					else {

//						system( 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '
//									. MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//									. ' . ',
//									$result );

//						$zip_command = 'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' '             # 1.0.2
						$zip_command = PATH_ZIP
										. ' -r' . $zip_verbose . COMPRESSION_LEVEL . ' '       # 1.0.2
										. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_zip)
										. ' . '
										. ZIP_EXCLUDE_FOLDERS          # 1.0.5.5
						;
					}

//echo '$zip_command:' . $zip_command . '<br>';//TODO

					$zip_error = system( $zip_command, $result );               #   0.9.3

//echo '$zip_error:' . $zip_error . '<br>';
//echo '$result:' . $result . '<br>';

					//echo '</pre><br />';

					if($result==0) {

						if($MODE=='interactive') {

							echo ' <span style="color:green;font-weight:bold;">' . __( 'Your WordPress installation has been added to', MEBAK_LOCALE )
									.': <b>' . $dest_file_zip . '</b></span>'
									. '<br /><br />';
						}
					}
					else {

						$tmp = ' <span style="color:red;font-weight:bold;">' . __( 'Error [' . $result . '] while executing the following command', MEBAK_LOCALE ) . '</span>:<br />'

								.'<code style="font-size:13px;">'
//									.'zip -r' . $zip_verbose . COMPRESSION_LEVEL . ' ';
//
//										if($_POST['z_password']!='')
//										{
//											$tmp .= '-P '.$_POST['z_password'] . ' ';
//										}
//
//										$tmp .= MEBAK_BACKUP_PATH . '/' . $dest_file_zip
//												. ' . '
									. $zip_command
								.'</code>'

								.__( 'You might try to compress data by using the TAR system command in the settings page.', MEBAK_LOCALE ) . MEBAK_SETTINGS_BUTTON  #   1.0.5.3

								.'<br /><br />';

						if($MODE=='interactive') {

							echo $tmp . MEBAK_BACK_BUTTON . '</div>';
							return 1;
						}
						else {

							return $tmp;
						}
					}
					break;
					#
				#--------------------------
				case 't':
				#--------------------------
					if(!file_exists(MEBAK_BACKUP_PATH . '/' . $dest_file_tar)) {

						touch(MEBAK_BACKUP_PATH . '/' . $dest_file_tar);
					}

					//echo '<pre><br />';
//					system( 'tar -r'.$tar_verbose.'pf ' . MEBAK_BACKUP_PATH . '/' . $dest_file_tar
//								. ' * ',
//								$result );
					//echo '</pre><br />';

//					$tar_command = 'tar -r'.$tar_verbose.'pf '               # 1.0.2
					$tar_command = PATH_TAR
									. TAR_EXCLUDE_FOLDERS          # 1.0.5.5
									.' -r'.$tar_verbose.'pf '      # 1.0.2
									. escapeshellarg(MEBAK_BACKUP_PATH . '/' . $dest_file_tar) . ' '
//									. ' * '      #   1.0.1
									. ' . '      #   1.0.1
					;

//echo $tar_command . '<br>';//TODO

					$tar_error = system( $tar_command, $result );               #   0.9.3

					if(substr($result, -1)==0) {

						if($MODE=='interactive') {

							echo ' <span style="color:green;font-weight:bold;">' . __( 'Your WordPress installation has been added to', MEBAK_LOCALE )
									.': <b>' . $dest_file_tar . '</b></span>'
									. '<br /><br />';
						}
					}
					else {

						$tmp = ' <span style="color:red;font-weight:bold;">' . __( 'Error [' . $result . '] while executing the following command', MEBAK_LOCALE ) . '</span>:<br />'

								.'<code style="font-size:13px;">'

//									.'tar -r'.$tar_verbose.'pf ' . MEBAK_BACKUP_PATH . '/' . $dest_file_tar
//										. ' * '
									. $tar_command

								.'</code>'

								.__( 'You might try to compress data by using the ZIP system command in the settings page.', MEBAK_LOCALE ) . MEBAK_SETTINGS_BUTTON  #   1.0.5.3

								.'<br /><br />';

						unlink(MEBAK_BACKUP_PATH . '/' . $dest_file_tar);   #   0.9.3

						if($MODE=='interactive') {

							echo $tmp . MEBAK_BACK_BUTTON . '</div>';
							return 1;
						}
						else {

							return $tmp;
						}
					}
					#
					break;
				#--------------------------
				default:
				#--------------------------
					$tmp = __( 'You are not using PHP to create the data set but I do not have instructions about what system command to use: please check the Settings page and set Zip or Tar in the "What system command do you like to use to create the data set?" option.', MEBAK_LOCALE );

					if($MODE=='interactive') {

						echo $tmp . MEBAK_BACK_BUTTON . '</div>';
						return 1;
					}
					else {

						return $tmp;
					}
			}
		}

		$dwnfilename = $dest_file_tar;

//echo 'TAR_COMPRESS:'.TAR_COMPRESS.'<br>';
//echo 'COMPRESSION_LEVEL:'.COMPRESSION_LEVEL.'<br>';

		if(SYS_ARCHIVING_TOOL=='t' && defined('TAR_COMPRESS') && TAR_COMPRESS>0 && file_exists(MEBAK_BACKUP_PATH . '/' . $dest_file_tar))
		{
			#	the data set was prepared with tar and it was requested to compress it
			#	@since 0.1.4
			#
			$dwnfilename = $dest_file_tgz;

			chdir(MEBAK_BACKUP_PATH);

//  		if(ini_get('memory_limit')!=PHP_RAM)
			if((int)str_replace('M','',ini_get('memory_limit')) < (int)str_replace('M','',PHP_RAM))  #	1.0.5.9
			{
				ini_set('memory_limit',	PHP_RAM);
			}

			$fgz = gzopen(MEBAK_BACKUP_PATH . '/' . $dest_file_tgz, 'w'.COMPRESSION_LEVEL);
			$fp = fopen(MEBAK_BACKUP_PATH . '/' . $dest_file_tar, 'r');

			if($fgz && $fp)
			{
				while(!feof($fp))
				{
					$content = fread($fp, 16384);			#	8192	16384
					$result  = gzwrite($fgz, $content);
				}

				fclose($fp);
				gzclose($fgz);

				unlink(MEBAK_BACKUP_PATH . '/' . $dest_file_tar);


				if($MODE=='interactive')
				{
					echo __( 'The data set prepared with Tar was successfully compressed to', MEBAK_LOCALE ) . ':<br />'
							. '<b>' . MEBAK_BACKUP_PATH . '/' . $dest_file_tgz . '</b><br /><br />'
					;
				}
			}
			else
			{
				$tmp = __( 'Unable to compress the data set prepared with Tar.', MEBAK_LOCALE );

				if($MODE=='interactive')
				{
					echo $tmp . MEBAK_BACK_BUTTON . '</div>';
					return 1;
				}
				else
				{
					return $tmp;
				}
			}
		}

		switch(SYS_ARCHIVING_TOOL)
		{
			case 't': break;
			#
			case 'z':
			default:
				$dwnfilename = $dest_file_zip;
		}
	}
	else if((defined('isWINDOWS') && isWINDOWS==true) || isSYSTEM==false || FORCE_PHPCODE==true && class_exists('Zipper'))
	{
		#	Windows or system() is not allowed
		#
		chdir(MEBAK_BACKUP_PATH);


//echo 'ini_get('. ini_get('memory_limit').') PHP_RAM('.PHP_RAM.')<br />'; # 1.0.5.9 debug


//		if(ini_get('memory_limit')!=PHP_RAM) #	0.1.4
		if((int)str_replace('M','',ini_get('memory_limit')) < (int)str_replace('M','',PHP_RAM))  #	1.0.5.9
		{
			ini_set('memory_limit',	PHP_RAM);
		}

//ini_set('memory_limit',	'24'); # 1.0.5.9 debug
//echo 'ini_get('. ini_get('memory_limit').') PHP_RAM('.PHP_RAM.')<br />'; # 1.0.5.9 debug


		//$zip = new ZipArchive();
		$zip = new Zipper();

		if($zip->open(MEBAK_BACKUP_PATH . '/' . $dest_file_zip, ZIPARCHIVE::OVERWRITE) === true)
		{
			if($_POST['cdatabase'])
			{
				if(file_exists($_POST['filename'] . '.sql'))
				{

					chdir(MEBAK_BACKUP_PATH);

					$zip->addFile(basename($_POST['filename'] . '.sql'));

					if($MODE=='interactive')
					{
						echo '<span style="color:green;font-weight:bold;">'
							.__( 'Your database has been added to', MEBAK_LOCALE )
							.': <b>'.MEBAK_BACKUP_PATH.'/'.$dest_file_zip
							. '</b></span><br />';
					}

					if($_POST['upload_restore_tool_exec']==1)
					{
						chdir(MEBAK_BACKUP_PATH);
						$zip->addFile(basename($ini_file));

						if($MODE=='interactive')
						{
							echo '<span style="color:green;font-weight:bold;">'
								.__( 'The restore configuration file has been added to', MEBAK_LOCALE )
								.': <b>'.MEBAK_BACKUP_PATH.'/'.$dest_file_zip
								. '</b></span><br />';
						}
					}
				}
			}

			if($_POST['cwordpress'])
			{
				if($MODE=='interactive')
				{
					echo '<br />' . __( 'Saving your WordPress installation...', MEBAK_LOCALE ) . '<br />';
				}

# 1.0.5.9: BEG
#-------------
//				$zip->addDir(substr(MEBAK_WP_PATH, 0, -1), $MODE);

				if(strlen(constant('MEBAK_WP_PATH'))>1) {

					$zip->addDir(substr(MEBAK_WP_PATH, 0, -1), $MODE);
				}
				else {

					$zip->addDir(MEBAK_WP_PATH, $MODE);
				}
#-------------
# 1.0.5.9: END

				if($MODE=='interactive')
				{
					echo '<br /><span style="color:green;font-weight:bold;">'
						.__( 'Your WordPress installation has been added to', MEBAK_LOCALE )
						.': <b>'.MEBAK_BACKUP_PATH.'/'.$dest_file_zip
						. '</b></span><br /><br />';
				}
			}

			$zip->close();

			if($MODE=='interactive') {

# 1.0.5.9: BEG
#-------------
//				$finfo = stat(MEBAK_BACKUP_PATH . '/' . $dest_file_zip);

				$finfo = array();
				if(file_exists(MEBAK_BACKUP_PATH . '/' . $dest_file_zip)) {

					$finfo = stat(MEBAK_BACKUP_PATH . '/' . $dest_file_zip);
				}

				if((int)$finfo['size']>0) {
#-------------
# 1.0.5.9: END
					echo '<br /><b>'
						.__( 'Your backup data set was successfully compressed.', MEBAK_LOCALE )
						.'</b><br />';

					echo __( 'The compressed file size is', MEBAK_LOCALE )
								.': <b>'.number_format($finfo['size']).'</b> bytes<br /><br />';
				}
				else {

					/**
					 * @since  1.0.5.9
					 */
					echo __( 'It was not possible to create the compressed backup using the ZipArchive class, the backup file path was', MEBAK_LOCALE )
								.': <b>'.MEBAK_BACKUP_PATH . '/' . $dest_file_zip.'</b><br /><br />';
				}
			}
		}
		else
		{
			$tmp = '<div class="error">'
					.__( 'Error when trying to compress your backup', MEBAK_LOCALE )
					.':<br />'

					.$zip->getStatusString()

					. '<br />' . __( 'While trying to create the file', MEBAK_LOCALE ) . ':<br />'
					.MEBAK_BACKUP_PATH . '/' . $dest_file_zip

				.'</div>';

			if($MODE=='interactive')
			{
				echo $tmp . MEBAK_BACK_BUTTON . '</div>';
				return 1;
			}
			else
			{
				return $tmp;
			}
		}

		$dwnfilename = $dest_file_zip;
	}


	if(file_exists(MEBAK_BACKUP_PATH . '/' . $_POST['filename'] . '.sql'))
	{
		unlink(MEBAK_BACKUP_PATH . '/' . $_POST['filename'] . '.sql');

		if($MODE=='interactive')
		{
			echo __( 'The uncompressed MySQL backup file has been removed &ndash; it\'s ok, just to let you know!', MEBAK_LOCALE )
					.'<br />';
		}
	}

	if(file_exists($ini_file))
	{
		unlink($ini_file);

		if($MODE=='interactive')
		{
			echo __( 'The restore configuration file has been removed &ndash; it\'s ok, just to let you know!', MEBAK_LOCALE )
					.'<br /><br />';
		}
	}

	if(defined('MEBAK_MAIL_TO') && strlen(MEBAK_MAIL_TO)>0)
	{
		#   send the data set by email
		#   @since 0.9.1
		#
		$mailto = MEBAK_MAIL_TO;
		$datetime = get_option('date_format') . ' ' . get_option('time_format');
		$now = time();

		$subject = 'myEASYbackup@'. $_SERVER['HTTP_HOST'] . ': ' . __( 'your data set dated', MEBAK_LOCALE ) . ' '. date($datetime, $now);

		$message = __( 'Hallo, please find here attached your data set.', MEBAK_LOCALE )

					. "\n\n" . __( 'Backup details', MEBAK_LOCALE ) . "\n\n"

					. __( 'Site: ', MEBAK_LOCALE )
						. 'http://' . $_SERVER['HTTP_HOST'] . "\n\n"

					. __( 'Executed at: ', MEBAK_LOCALE ) . date($datetime, $now) . "\n\n"

					. __( 'Have a nice day,', MEBAK_LOCALE ) . "\n"
					. __( 'Your loyal myEASYbackup plugin', MEBAK_LOCALE ) . "\n"
		;

		$sendemail_result = measycom_emailer($mailto, $subject, $message, '', '', '', '', 'plain', '1', MEBAK_BACKUP_PATH . '/', $dwnfilename);

		if($sendemail_result!='*OK*')
		{
			if($MODE=='interactive')
			{
				echo '<div class="error">'

						. '<h2>' . __( 'Something went wrong!', MEBAK_LOCALE ) . '</h2>'
						. $sendemail_result
					.'</div>'
				;
			}
			else
			{
				$tmp = __( 'I cannot email the data set', MEBAK_LOCALE )
						. ':<br /><b>' . MEBAK_BACKUP_PATH . '/' . $dwnfilename .'</b><br />'
						. __( 'to', MEBAK_LOCALE ) . ': <b>' . $mailto . '</b><br />'
				;
				return $tmp;
			}
		}
		else
		{
			if($MODE=='interactive')
			{
				echo __( 'The data set was successfully sent by email to', MEBAK_LOCALE ) . ' <b>' . MEBAK_MAIL_TO . '</b><br />';
			}
		}
	}

	if($MODE=='interactive')
	{
		echo '</div>';
	}

	clearstatcache();

	if(file_exists(MEBAK_BACKUP_PATH . '/' . $dwnfilename)
		//&& 1==2		#debug
	) {

		if($MODE=='interactive')
		{
			echo '<div class="ok" style="margin:0 0 20px 0;">'
					. __( 'The data set was created successfully!', MEBAK_LOCALE )
				.'</div>';
		}


		if(defined('MEBAK_MAIL_TO_REMOVE') && MEBAK_MAIL_TO_REMOVE==1 && $sendemail_result=='*OK*')
		{
			#   remove the data set on demand
			#   @since 0.9.1
			#
			unlink(MEBAK_BACKUP_PATH . '/' . $dwnfilename);

			if($MODE=='interactive')
			{
				echo __( 'The data set was removed from this server.', MEBAK_LOCALE )

					.'<br /><i>'
						. __( 'Note: you can change this option in the', MEBAK_LOCALE ) . ' "'
						. '<a href="options-general.php?page=myEASYbackup_options#email_settings">'
							. __( 'Settings', MEBAK_LOCALE )
						. '</a>" '
						. __( 'menu.', MEBAK_LOCALE )
					.'</i><br /><br />';
			}
		}
		else
		{
			chmod(MEBAK_BACKUP_PATH . '/' . $dwnfilename, 0755);

			if($MODE=='interactive')
			{
				echo __( 'Click to download', MEBAK_LOCALE );

				?> &raquo;
					<input type="image" name="download" value="download"
							src="<?php echo MYEASYBACKUP_LINK; ?>img/download-off.png"
							onmouseover="javascript:this.src='<?php echo MYEASYBACKUP_LINK; ?>img/download.png';"
							onmouseout="javascript:this.src='<?php echo MYEASYBACKUP_LINK; ?>img/download-off.png';"
							onclick="javascript:
								document.getElementById('dwn_action').value='download';
								document.getElementById('dwn_file').value='<?php echo $dwnfilename; ?>';
								document.dwn_backup.submit();
								return false;"
							alt="DOWNLOAD"
							align="absmiddle" />

				<br /><br /><?php
			}

		}
	}
	else
	{
		if(isPRO==false)
		{
			if($MODE=='interactive')
			{
				echo '<div class="error">'

						. '<h2>' . __( 'Something went wrong!', MEBAK_LOCALE ) . '</h2>'

						. __( 'I cannot find the data set', MEBAK_LOCALE )
						. ':<br /><b>' . MEBAK_BACKUP_PATH . '/' . $dwnfilename .'</b><br /><br />'

						. __( 'Please open the plugin settings page then', MEBAK_LOCALE )

							. ':<br /><br />&raquo; ' . __( 'enable the show debug option', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'disable the prodution server option', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'save the options', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'copy the information shown in the "myEASYbackup debug info" box', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'paste the information in a new email', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'try again to create a data set', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'copy the information shown in the "Info about your backup" box, the yellow box above this one', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'paste the contents in the email you have just created', MEBAK_LOCALE )
							. '<br />&raquo; ' . __( 'send the email to', MEBAK_LOCALE )
								.': <a href="mailto:'.EMAIL_CONTACT.'">'.EMAIL_CONTACT.'</a>'
								.'<br /><br />'

						. __( 'The author will get in touch if he needs further info to be able to fix the problem.', MEBAK_LOCALE )
						. '<br />' . __( 'Thank you!', MEBAK_LOCALE )
						. '<br /><br />'
					.'</div>'
				;
			}
			else
			{
				$tmp = __( 'I cannot find the data set', MEBAK_LOCALE )
						. ':<br /><b>' . MEBAK_BACKUP_PATH . '/' . $dwnfilename .'</b><br /><br />'
				;
				return $tmp;
			}
		}
	}

	if($MODE=='interactive')
	{
		echo MEBAK_BACK_BUTTON;
	}
	return '*OK*' . MEBAK_BACKUP_PATH . '/' . $dwnfilename . '[[meb_splitter]]' . $_POST['z_password'];
}

?>