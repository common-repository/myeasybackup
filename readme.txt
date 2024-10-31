=== myEASYbackup ===
Contributors: camaleo
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7P3YH6G2MLTVU
Tags: myeasy, backup, migrate, admin, administration, ajax, comments, google, facebook, image, images, links, jquery, plugin, plugins, post, posts, rss, seo, sidebar, social, twitter, video, widget, wordpress, youtube
Requires at least: 2.5
Tested up to: 3.3.*
Stable tag: 1.0.11

Backup, restore, migrate your WP installation, both code and MySQL tables, with a single click. <a href="http://is.gd/hUwBx" target="_blank">Screen shots</a>

== Description ==
Backup, restore, migrate your WordPress installation, both code and MySQL tables, with a single click.

Check out the [myEASYbackup for WordPress video](http://www.youtube.com/watch?v=sDMiIJhapKE):

http://www.youtube.com/watch?v=sDMiIJhapKE&hd=1

Sometimes screen shots are not shown on this site. While the problem is being fixed you can have a look by [clicking here](http://myeasywp.com/plugins/myeasybackup/).

When performing a backup, myEASYbackup creates a single file, called "<b>data set</b>", that includes your data in compressed format (.zip).

Data sets are saved <b>outside</b> the WordPress installation directory to avoid someone else discover the links and get them.

A list of all data sets with the ability to download, upload (FTP) and delete each of them is also included to keep everything under control.

Whether you are a WordPress user or a developer, this plugin is sure to bring you peace of mind and added security in the event of data loss.

Another plugin in the myEASY series, conceived to make your life easier!

myEASYbackup comes handy before upgrading WordPress or one of the plugins you have installed.

This version allows to backup, upload/migrate and a beta of the myEASYrestore tool allowing to restore data sets after having uploaded.

Related Links:

* <a href="http://myeasywp.com/" title="myEASYwp: WordPress plugins created to make your life easier">myEASYwp plugin series homepage</a>
* To stay tuned and get live news about what's going on at myeasywp.com follow me on <a href="http://twitter.com/camaleo" target="_blank" title="myEASY live news">Twitter</a>
* Subscribe to the <a href="http://eepurl.com/bt9rD" target="_blank" title="myEASY newsletter">myeasywp.com newsletter</a>
* myEASYbackup is the perfect companion to <a href="http://myeasywp.com/plugins/myeasyhider/" target="_blank">myEASYhider</a> and <a href="http://myeasywp.com/plugins/myeasywebally/" target="_blank">myEASYwebally</a>, two other plugins in the myEASY serie.
* The full set of icons used in this plugin is <a href="http://myeasywp.com/redirect/themee.php" target="_blank">available here for free</a>


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the full directory into your `wp-content/plugins` directory
1. Activate the plugin through the 'Plugins' menu in the WordPress Administration page
1. Open the plugin settings page, which is located under `Settings -> myEASYbackup`
1. Set the options as you like
1. Open the plugin tools page, which is located under `Tools -> myEASYbackup`
1. Click on the backup button and wait myEASYbackup to prepare the backup data set
1. When the backup data set is ready click on the save button to get it on your system


== Frequently Asked Questions ==

= Where can I get some help if anything goes wrong? =

* Please check the <a href="http://myeasywp.com/plugins/myeasybackup/faq/" target="_blank">myEASYbackup F.A.Q. page</a> to get the latest answers, if you cannot find the answer please send me an email through <a href="http://myeasywp.com/contact/" target="_blank">the contact form</a>.


== Screenshots ==

1. (Tools) You can select to backup the MySQL tables, the Wordpress installation directory or both.
2. (Settings) The main options options panel.
3. (System Settings) The system settings panel let's you customize the plugin as you like and making it adapting to a great variety of servers.


== Changelog ==

= 1.0.11 (27 January 2012) =
Improves the fix applied with version 1.0.9 and fixes the possibility for malicious user to discover the directory structure of the target site as kindly reported by Pavel Komisarchuk of <a href="http://6scan.com/" target="_blank">6scan.com</a>.

= 1.0.9 (17 January 2012) =
Fixes the exploit described at <a href="http://packetstormsecurity.org/files/108711/" target="_blank">Packet Storm</a>.

= 1.0.8.1 (24 July 2011) =
Replaced few lines of a Creative Commons licensed code used to handle the mailing list subscription as per kind request from wordpress.org

= 1.0.8 (23 July 2011) =
All the images and javascript code is now loaded from the same server where the plugin is installed.
Last year I tought it might be useful to have the myeasy common images and code loaded from a CDN to avoid having to update all the plugins in the series each time an image changes and to load pages faster; so I moved all the common items to a CDN.
Today I received a kind email from wordpress.org letting me know that "there a potential malicious intent issue here as you {me} could change the files to embed malicious code and nobody would be the wiser" and asking me to change the code.
I promptly reacted to show everyone that I am 101% in bona fide and here is a new version.

= 1.0.7 (12 July 2011) =
Fixed some issues caused by other plugins preventing the js code to be properly loaded.

= 1.0.6 (2 July 2011) =
Now fully compatible with WordPress 3.2.

Important! New system requirements:
* PHP5 is now required to use this plugin for two reasons: 1) PHP4 was discontinued by the PHP development team on December, 31 2007 and now also WordPress does not support PHP4 anymore; 2) the plugin is now using some commands available only with PHP5 and later.
* Linux server.

Fixed:

* The upload path is now correctly checked even when it is defined in the Media -> Settings page.
* Small issue with AJAX when the errors are shown on the screen rather than written in the log file.
* Fixed a series of problems arising only when the url was starting with `www`.
* Fixed the path calculation used to execute the Ajax calls - Note: the plugin will not work if you rename its folder name (/myeasybackup).
* Its now possible to download even large data sets (tested with some 6Gb files).
* Clicking on the "Generate the myEASYbackup KEY" didn't show the "myEASYwebally PRIVATE API key" request dialog anymore since some mods in version 1.0.5.
* Now its possible to pull data from the services server even on servers where `fopen wrappers` have been disabled.

Changed:

* Memory issues with the ZipArchive class: changed the code to try to keep memory as clean as possible.

Added:

* Tool to remove ALL the plugin settings. For usage instructions please see the /wp-content/plugins/myeasybackup/myeasybackup-reset file.
* Ability to set the timeout and passive mode for the FTP connections.

= 1.0.5.3 (16 December 2010) =

Fixed:

* Fixed a number of issues when WordPress is installed in a sub folder.

= 1.0.5.2 (14 December 2010) =

Fixed:

* Issue when downloading a data set right after has been created.

= 1.0.5.1 (13 December 2010) =

Fixed:

* Long delay in building the settings page under certain conditions.
* Moved some definitions in a better place to avoid warnings messages.

= 1.0.5 (11 December 2010) =

Fixed:

* Temporary files are now written in the media upload directory (the default directory is: `/wp-content/uploads/`) to avoid "open_basedir restrictions".
* Validation of mysqldump was not fully correct.
* On new installation there is no need to remove all the data sets older than a certain number of days.
* If the backup folder name is not set, the plugin does not try to create it anymore.

Changed:

* The "» System settings" collapsed section in the settings page is now automatically opened when there is one or more configuration setting error(s) to be fixed within the section itself.
* The value inserted as "The path to the WordPress administration path" is now checked and must exists on the server.
* Windows servers recognition.

= 1.0.4 (28 November 2010) =
Fixed:

* To get the full path to system commands, prior versions were using the `exec()` command that created issues on some servers: now the plugin is using the `system()` command that seems more welcomed by most hosting providers.
* Common images to all the myEASY series plugins are now served by a [Content Delivery Network](http://is.gd/hUAEb): pages will load much faster and with no interruptions.

= 1.0.3 (14 November 2010) =
This is the full 1.0.2 that was also tested and approved by some of our best beta testers.

Fixed:

* On some servers the system commands must be called with their explicit full path (eg: `/usr/bin/mysqldump` and not simply `mysqldump`): you can now set the mysqldump path in the settings page.
* A path issue not allowing to backup, download a dataset and upload a dataset when WordPress is installed in a sub folder: you can now set the wp-admin path in the settings page.

Changed:

* To keep the settings page clean, the settings that are usually set once are now grouped and only shown on request - click on the triangle on the right in the "» System settings" line.
* Changed the <a href="http://eepurl.com/bt8f1" target="_blank">newsletter provider</a> as the previous one is going to close his service by the end of 2010.

= 1.0.2 (11 November 2010) =
Sorry guy I messed up with the update system :/
This release it totally unstable as its missing a number of checks and file updates.

= 1.0.1 (4 October 2010) =
Fixed:

* When creating data sets using the system() command "Tar" hidden files - files whose name starts with a dot, like `.htaccess` - were not included in the backup.

= 1.0.0 (2 October 2010) =
Now fully compatible with WordPress 3.0.1.

Fixed:

* Now able to run also on servers configured with a linked home path.
* Some minor issues.

Changed:

* On new installations the option to use PHP code rather than system commands is selected by default only if system commands are not available.
* Reduced the number of connections when uploading to the FTP server to save time.
* The layout of the main plugin page.
* Rearranged the order of the settings page.
* Some icons to give a better clue on their meaning.

Added, now you can:

* Choose the destination directory and the server port when uploading your data sets to a remote FTP server.
* Validate your FTP credentials and check the remote contents.
* Send the data set to your email address.
* Upload your data set to your Dressing Room&trade; &mdash; requires a <a href="https://services.myeasywp.com/?page=account-add" target="_blank">free account</a> on our dedicated server.
* Automatically remove all the data sets older than a number of days you decide.

= 0.9.0 (2 June 2010) =
The interface in this version was changed to the new myEASY standard, this way you will get the same feeling with every myEASY plugin.
As the code is now stable enough, the version number had a big jump.

Fixed:

* The entire code is executed only when its called from the administration pages.
* It is not anymore possible to click on the briefcase if both the database and WordPress folder options are unselected.
* It is not anymore possible to click on the FTP upload icon if the FTP server settings are set.
* Fixed the weird "Parse error: syntax error, unexpected ')', expecting '(' in /home/username/public_html/wp-content/plugins/myeasybackup/myeasybackup.php on line 146" on PHP4 servers
* Minor issues preventing proper backup under some specific conditions.

Changed:

* Contextual help: help and debug info (when enabled) is now available though the help tab (right below the Log Out link on top right of the screen).
* Prepared the structure for the PRO version that will support scheduled backups.
* The password required to upload is not visible on the screen anymore.
* Centralized common myEASY CSS code.
* Replaced some instructions deprecated as PHP 5.3

Added:

* System compression: is now possible to use the TAR system tool to create/restore the data set. ZIP is still present as it allow to password protect the data set.
* Memory issues with the ZipArchive class: you can change the memory allocated to PHP; the value you set is only used when performing a backup through the ZipArchive class.
* Verbose mode: now its possible to avoid displaying the complete list of files when creating the data set. The result screen is more compact and readable.

= 0.1.3 (21 April 2010) =
Fixed &mdash; myEASYbackup:

* If you selected to save only the database &mdash; without the WordPress folder &mdash; the ini file was not added to the data set regardless how the wrench option was set.
* Under certain circumstances, on `open_basedir` enabled systems, it was not possible to create the data set.

Changed &mdash; myEASYbackup &amp; myEASYrestore:

* Increased security by adding an user selected password to the data set.
* Edited the layout to make it easier to use and more consistent.

Added &mdash; myEASYbackup:
* Ability to choose the folder where to save the data set as well as to create if directly from the Settings page.
* Link to the FAQ page on the official site.
* Common code that will be used by the PRO version.

= 0.1.2 (10 April 2010) =
Fixed &mdash; myEASYbackup:

* Fixed a bug where a message reporting an error while trying to extract database data was given by mistake even if the user did not choose to save the database.
* Moved the change permission command on the .sql to its proper position (related to the previous bug).
* Fixed the code to show errors eventually shown by the ZipArchive class.
* After a backup was performed, it was not possible to download it from the result page.
* The myEASYrestore ini file is now saved in the folder choosen folder.
* When you deselect the wrench (making it grey) to choose not to upload the myEASYrestore tool, the the myEASYrestore.ini and the wp-config.php files are not included anymore in the backup. Note that the wrench is enabled (colored) my default.

Changed &mdash; myEASYbackup:

* Added an option to let the user choose the folder where to save the data sets (backups).

Added &mdash; myEASYbackup:

* It is now possible to select the folder where to save the datasets from a drop down menu. The restore configuration file as well as the MySQL extracted database (.sql) are also temporarily created in this folder.
* Pending on the server configuration, a number of new messages are now shown on the screen with tips about how to better configure the plugin for better performances.

Note: version 0.1.1 was not published as it was released only in private to the beta testers.

= 0.1.0 (5 April 2010) =
Fixed &mdash; myEASYbackup:

* Changed the code to allow IE users to download data sets.
* Fixed a security issue with the myEASYrestore.ini file, now renamed myEASYrestore_ini.php and added a some code to prevent showing its contents from a browser.

= 0.0.9 (3 April 2010) =
Fixed &mdash; myEASYbackup:

* When saving only the MySQL tables, the myEASYrestore.ini and the wp-config.php files were not included in the data set, making it impossible to restore the data with the restore tool and to amend, if needed, the WordPress configuration.

Changed &mdash; myEASYbackup &amp; myEASYrestore:

* Until now most of the support requests were coming from users unable to backup/restore as their providers blocked the use of the PHP <code>system()</code> command. For this reason I decided to change the behaviour of the plugin that now uses PHP code rather than the <code>system()</code> by default. If you know that <code>system()</code> is enabled on your servers you can still use it by unselecting the 'Use PHP code rather than system() commands' checkbox through the `Setting -> myEASYbackup` menu. Using the <code>system()</code> let's you backup/restore faster as relies on the server commands to perform operations on the MySQL database and compressing/uncompressing the data set. Using PHP code can create issues when the MySQL database is very big &mdash; rest assured that I will do my best to avoid putting limits on your databases size when/if, in the future, the problem will arise ;-)

Added &mdash; myEASYrestore:

* Ability to restore data sets created with the 'Use PHP code rather than system() commands' option enabled. <span style="color:red;font-weight:bold;">Warning</span>: you <span style="color:red;font-weight:bold;">MUST</span> save your data set with myEASYbackup 0.0.9 <span style="color:red;font-weight:bold;">AND</span> using PHP code in order to be able to restore using PHP code.
* Now changing paths in the `option_value` field (`options` table) only if the path is changed.
* Now showing the number of records updated by the queries replacing values in the tables.
* Some additional classes in the style header.
* Ability to remove also the .zip file (only the one selected in the 'Backups available on this server in the following folder' section).
* When the restore is completed successfully the data set ini file is automatically removed.


= 0.0.8 (23 March 2010) =
Fixed:

* Added a query to modify the `guid` field in the `posts` table when migrating/restoring a database.

= 0.0.7 (14 March 2010) =
Fixed:

* When uploading 20Mb and larger data sets, setting the FTP connection in passive mode was not enough to get green light on the upload. To be sure the upload went correctly through I have added a check on the size of the local data set against the uploaded file, if the size are the same I can issue the ok message.
* Added indications about the percentage and estimated remaining time for the upload.

= 0.0.6 (13 March 2010) =
Fixed:

* When compressing with the system tools, the zipped file does not include the complete path anymore.
* When uploading 20Mb and larger data sets, sometimes the upload was reported unsuccessfull even if was completed succesfully. Setting the FTP connection in passive mode seems to fix the problem.
* In some cases the wait message (the semi-transparent black screen) did not show the images.

Added:

* When preparing a data set, create an .ini file used by the myEASYrestore tool.
* When uploading you can choose to upload the myEASYrestore script that will let you easily restore your data set.
* Russian and Spanish translations.

= 0.0.5 (6 March 2010) =
Fixed:

* Avoid to issue a warning message when setting infinite time limit, needed to avoid possible interruption while creating/saving big data sets.
* Now working even on `system()` disabled servers.
* Blocked the execution on safe_mode enabled servers, it will soon be deprecated so there is no need to loose time on that.

Added:

* Settings page with a number of options like, for example: show/hide debug info, compression level (only on `system()` enabled servers), etc.
* Ability to upload a data set to the same or a different server.
* Italian translation.

Note: once the upload code will be stabilized I will add some more features like, for example, the ability to restore the tables data and the WordPress installation folder in the proper locations.

= 0.0.4 (1 March 2010) =

* Now faster as, on Linux servers, does use the zip command to compress. On Windows servers it requires the ZipArchive extension, however I changed the code to create a better zip file.
* Info about installing the ZipArchive extention are provided by the plugin if it does not find it installed, however you can have a look at the <a href="http://php.net/manual/zip.installation.php" target="_blank">ZipArchive page on the PHP site</a>

= 0.0.3 (28 February 2010) =

* Fixed a security issue where a direct call of meb_download.php may have allowed an intruder to get the full blog archive without any permissions.

= 0.0.2 (28 February 2010) =

* Added a check on the installed PHP version as well as the required ZipArchive class.
* If the PHP version is too old or the ZipArchive class is not installed instruction are provided through a number of useful links.

= 0.0.1 (27 February 2010) =
The first release.


== Upgrade Notice ==

= 1.0.5 =
A must if you are using version 1.0.2, 1.0.3 or 1.0.4.

= 1.0.3 =
After you install this version be sure to refresh the settings page at least a couple of times to load the latest css.
If you get the "Please be sure to setup the plugin options before tyring to use it!" when trying to backup: open the setting page, choose a folder to backup (possibly above the public_html/www folder) and save.

= 1.0.1 =
Not needed if you do not have hidden files (example: .htaccess) to backup.

= 1.0.0 =
Better user interface and additional options. Share its structure with the PRO version.

= 0.1.3 =
Password protect your backups.

= 0.1.2 =
Bugs fix and ability to choose your backup folder.

= 0.1.0 =
Mandatory to fix a security issue.

= 0.0.9 =
Needed if your hosting provider does not allow to use PHP <code>system()</code> commands.

= 0.0.8 =
Needed to be sure to change all the references to the new host in case its name is different than the host where you backed up the MySQL database.

= 0.0.7 =
If you are uploading the data set with this plugin it is warmly recommended to upgrade.
Tested only on Linux based hosting. Please let me know your comments and suggestions.

= 0.0.6 =
This is the first version allowing to restore a data set after having uploaded to your FTP server.

= 0.0.5 =
Needed on servers where some system commands are disabled in the configuration ad to start uploading your data sets in the easiest way.

= 0.0.4 =
Warmly suggested to upgrade for better performances.

= 0.0.3 =
Fixed a sever security issue, it is warmly suggested to upgrade.

= 0.0.2 =
If the initial release does work for your server configuration, there is no need to upgrade.

= 0.0.1 =
This is the first release.


== Translations ==
* Italian: [myEASYbackup author](http://myeasywp.com/)
* Russian: [ShinePHP](http://shinephp.com/)
* Spanish: [Cela](http://unfuturosicuro.com/)

Dear myEASYbackup plugin User,
if you wish to help me with this plugin translation I will really appreciate it.
Please send your language .po and .mo files to info@myeasywp.com &mdash; also please do not forget include a link to your site
so that I can show it with greetings for the translation help at [myeasywp.com](http://myeasywp.com/) and in this document.

One useful links if you like to help with the translation but do not know how to do it:
[Creating POT Files](http://codex.wordpress.org/User:Skippy/Creating_POT_Files)


== Special Thanks to ==
* [ShinePHP](http://shinephp.com/) for the help with the security issues, ideas and new versions testing.
