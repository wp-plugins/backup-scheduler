<?php
/**
Plugin Name: Backup Scheduler
Plugin Tag: backup, schedule, plugin, save, database, zip
Description: <p>With this plugin, you may plan the backup of your entire website (folders, files and/or database).</p><p>You can choose: </p><ul><li>which folders you want to save; </li><li>the frequency of the backup process; </li><li>whether your database should be saved; </li><li>whether the backup is stored on the local website, sent by email or stored on a distant FTP (support of multipart zip files)</li></ul><p>This plugin is under GPL licence</p>
Version: 1.3.3


Framework: SL_Framework
Author: SedLex
Author Email: sedlex@sedlex.fr
Framework Email: sedlex@sedlex.fr
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/extend/plugins/backup-scheduler/
License: GPL3
*/

//Including the framework in order to make the plugin work

require_once('core.php') ; 

/** ====================================================================================================================================================
* This class has to be extended from the pluginSedLex class which is defined in the framework
*/
class backup_scheduler extends pluginSedLex {
	

	/** ====================================================================================================================================================
	* Plugin initialization
	* 
	* @return void
	*/
	static $instance = false;

	protected function _init() {
		global $wpdb ; 
		
		// Name of the plugin (Please modify)
		$this->pluginName = 'Backup Scheduler' ; 
		
		// The structure of the SQL table if needed (for instance, 'id_post mediumint(9) NOT NULL, short_url TEXT DEFAULT '', UNIQUE KEY id_post (id_post)') 
		$this->tableSQL = "" ; 
		// The name of the SQL table (Do no modify except if you know what you do)
		$this->table_name = $wpdb->prefix . "pluginSL_" . get_class() ; 

		//Configuration of callbacks, shortcode, ... (Please modify)
		// For instance, see 
		//	- add_shortcode (http://codex.wordpress.org/Function_Reference/add_shortcode)
		//	- add_action 
		//		- http://codex.wordpress.org/Function_Reference/add_action
		//		- http://codex.wordpress.org/Plugin_API/Action_Reference
		//	- add_filter 
		//		- http://codex.wordpress.org/Function_Reference/add_filter
		//		- http://codex.wordpress.org/Plugin_API/Filter_Reference
		// Be aware that the second argument should be of the form of array($this,"the_function")
		// For instance add_action( "the_content",  array($this,"modify_content")) : this function will call the function 'modify_content' when the content of a post is displayed
		
		add_action( "wp_ajax_initBackupForce",  array($this,"initBackupForce")) ; 
		add_action( "wp_ajax_deleteBackup",  array($this,"deleteBackup")) ; 
		add_action( "wp_ajax_cancelBackup",  array($this,"cancelBackup")) ; 
		add_action( "wp_ajax_backupForce",  array($this,"backupForce")) ; 
		add_action( "wp_ajax_updateBackupTable",  array($this,"updateBackupTable")) ;
		add_action( 'wp_ajax_nopriv_checkIfBackupNeeded', array( $this, 'checkIfBackupNeeded'));
		add_action( 'wp_ajax_checkIfBackupNeeded', array( $this, 'checkIfBackupNeeded'));
		add_action( 'wp_ajax_testFTP', array( $this, 'testFTP'));
		add_action( 'wp_print_scripts', array( $this, 'javascript_checkIfBackupNeeded'));
		
		// Si le dernier backup n'a pas eu lieu, creer le fichier
		@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/", 0777, true) ; 
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup")) {
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup", date_i18n("Y-m-d")) ; 
		}
		if (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.htaccess")) {
			@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/.htaccess") ; 
		}
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/index.php")) {
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/index.php", "You are not allowed here!") ; 
		}
		
		// Important variables initialisation (Do not modify)
		$this->path = __FILE__ ; 
		$this->pluginID = get_class() ; 
		
		// activation and deactivation functions (Do not modify)
		register_activation_hook(__FILE__, array($this,'install'));
		register_deactivation_hook(__FILE__, array($this,'deactivate'));
		register_uninstall_hook(__FILE__, array($this,'uninstall_removedata'));
	}

	/**====================================================================================================================================================
	* Function called when the plugin is activated
	* For instance, you can do stuff regarding the update of the format of the database if needed
	* If you do not need this function, you may delete it.
	*
	* @return void
	*/
	
	public function _update() {
		SL_Debug::log(get_class(), "Update the plugin" , 4) ; 
	}
	
	/**====================================================================================================================================================
	* Function called to return a number of notification of this plugin
	* This number will be displayed in the admin menu
	*
	* @return int the number of notifications available
	*/
	 
	public function _notify() {
		return 0 ; 
	}
	
	/**====================================================================================================================================================
	* Function to instantiate the class and make it a singleton
	* This function is not supposed to be modified or called (the only call is declared at the end of this file)
	*
	* @return void
	*/
	
	public static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/** ====================================================================================================================================================
	* Define the default option values of the plugin
	* This function is called when the $this->get_param function do not find any value fo the given option
	* Please note that the default return value will define the type of input form: if the default return value is a: 
	* 	- string, the input form will be an input text
	*	- integer, the input form will be an input text accepting only integer
	*	- string beggining with a '*', the input form will be a textarea
	* 	- boolean, the input form will be a checkbox 
	* 
	* @param string $option the name of the option
	* @return variant of the option
	*/
	public function get_default_option($option) {
		switch ($option) {
			// Alternative default return values (Please modify)
			case 'save_time' 		: return 0 		; break ; 
			
			case 'ftp' 		: return false 		; break ; 
			case 'ftp_host' 		: return "" 		; break ; 
			case 'ftp_login' 		: return "" 		; break ; 
			case 'ftp_pass' 		: return "[password]" 		; break ; 
			case 'ftp_mail' 		: return "" 		; break ; 
			case 'ftp_to_be_sent' 		: return array()		; break ; 
			case 'ftp_sent' 		: return array()		; break ; 

			case 'email_check' 		: return true 		; break ; 
			case 'email' 		: return "" 		; break ; 
			
			case 'rename' 		: return "" 		; break ; 
			case 'chunk' 		: return 5			; break ; 
			case 'frequency' 		: return 7			; break ; 
			case 'delete_after' 		: return 42			; break ; 
			case 'save_upload' 		: return true				; break ; 
			case 'save_upload_all' 		: return false				; break ; 
			case 'save_plugin' 		: return false				; break ; 
			case 'save_theme' 		: return false				; break ; 
			case 'save_all' 		: return false				; break ; 
			case 'save_db' 		: return true				; break ; 
			case 'save_db_all' 		: return false				; break ; 
			case 'max_allocated' 		: return 5				; break ;
			case 'max_time' 		: return 15				; break ;
		}
		return null ;
	}

	/** ====================================================================================================================================================
	* The admin configuration page
	* This function will be called when you select the plugin in the admin backend 
	*
	* @return void
	*/
	
	public function configuration_page() {
		
		global $wpdb;
		global $blog_id;
		
		$table_name = $wpdb->prefix . $this->pluginID;
		SL_Debug::log(get_class(), "Display the configuration page" , 4) ;
		
		?>
		<div class="wrap">
			<div id="icon-themes" class="icon32"><br></div>
			<h2><?php echo $this->pluginName ?></h2>
		</div>
		<div style="padding:20px;">
			<?php echo $this->signature ; ?>
			<p><?php echo __('This plugin enables scheduled backup of important part of your website : simple to use and efficient !', $this->pluginID) ; ?></p>
		<?php
		
			// On verifie que les droits sont corrects
			$this->check_folder_rights( array(array(WP_CONTENT_DIR."/sedlex/backup-scheduler/", "rw")) ) ; 
			
			// On verifie que la fonction exist
			if (!@function_exists('gzcompress')) {
				SL_Debug::log(get_class(), "GZCompress function is not supported on this server.", 1) ; 
				echo "<div class='error fade'><p>".sprintf(__('Sorry, but you should install/activate %s on your website. Otherwise, this plugin will not work properly!', $this->pluginID), "<code>gzcompress()</code>")."</p><div>";
			}
			
			//==========================================================================================
			//
			// Mise en place du systeme d'onglet
			//		(bien mettre a jour les liens contenu dans les <li> qui suivent)
			//
			//==========================================================================================
			$tabs = new adminTabs() ; 
			
			ob_start() ; 
				$params = new parametersSedLex($this) ; 
				
				$params->add_title(sprintf(__('How often do you want to backup your website?',$this->pluginID), $title)) ; 
				$params->add_param('frequency', __('Frequency (in days):',$this->pluginID)) ; 
				$params->add_param('save_time', __('Time of the backups:',$this->pluginID)) ; 
				$params->add_comment(__('Please note that 0 means midnight, 1 means 1am, 13 means 1pm, etc. The backup will occur at that time (server time) so make sure that your website is not too overloaded at that time.',$this->pluginID)) ; 
				$params->add_comment(__("Please also note that the backup won't be end exactly at that time. The backup process could take up to 6h especially if you do not have a lot of traffic on your website and/or if the backup is quite huge.",$this->pluginID)) ; 
				$params->add_param('delete_after', __('Keep the backup files for (in days):',$this->pluginID)) ; 
				
				$params->add_title(sprintf(__('What do you want to save?',$this->pluginID), $title)) ; 
				
				if ((!is_multisite())||((is_multisite())&&($blog_id == 1))) {
					$params->add_param('save_all', __('All directories (the full Wordpress installation):',$this->pluginID),"", "", array('!save_upload', '!save_upload_all', '!save_theme', '!save_plugin')) ;
					$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), ABSPATH)) ; 
					$params->add_comment(__('Check this option if you want to save everything. Be careful, because the backup could be quite huge!',$this->pluginID)) ; 
					$params->add_param('save_plugin', __('The plugins directory:',$this->pluginID)) ; 
					$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/plugins/")) ; 
					$params->add_comment(__('Check this option if you want to save all plugins that you have installed and that you use on this website.',$this->pluginID)) ; 
					$params->add_param('save_theme', __('The themes directory:',$this->pluginID)) ; 
					$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/themes/")) ; 
					$params->add_comment(__('Check this option if you want to save all themes that you have installed and that you use on this website.',$this->pluginID)) ; 
				}
				
				if (is_multisite()&&($blog_id != 1)) {
					$params->add_param('save_upload', __('The upload directory for this blog:',$this->pluginID)) ; 
					$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/blogs.dir/".$blog_id)) ; 
					$params->add_comment(__('Check this option if you want to save the images, the files, etc. that you have uploaded on your website to create your articles/posts/pages.',$this->pluginID)) ; 					
				} else if (is_multisite()&&($blog_id == 1)) {
					$params->add_param('save_upload_all', __('All upload directories (for this site and the sub-blogs):',$this->pluginID), "", "", array("!save_upload")) ; 
					$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/blogs.dir/")) ; 
					$params->add_comment(__('Check this option if you want to save the images, the files, etc. that people have uploaded on their websites to create articles/posts/pages.',$this->pluginID)) ; 					
					$params->add_param('save_upload', __('The upload directory for the main site:',$this->pluginID)) ; 
					$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/blogs.dir/".$blog_id)) ; 
					$params->add_comment(__('Check this option if you want to save the images, the files, etc. that you have uploaded on your main website to create your articles/posts/pages.',$this->pluginID)) ; 					
				} else {
					$params->add_param('save_upload', __('The upload directory:',$this->pluginID)) ; 
					$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/uploads/")) ; 
					$params->add_comment(__('Check this option if you want to save the images, the files, etc. that you have uploaded on your website to create your articles/posts/pages.',$this->pluginID)) ; 
				}
				
				if (is_multisite()&&($blog_id != 1)) {
					$params->add_param('save_db', __('The SQL database:',$this->pluginID)) ;
					$params->add_comment(__('Check this option if you want to save the text of your posts, your configurations, etc. for this blog',$this->pluginID)) ; 
				} else if (is_multisite()&&($blog_id == 1)) {
					$params->add_param('save_db_all', __('All SQL databases:',$this->pluginID), "", "", array("!save_db")) ; 
					$params->add_comment(__('Check this option if you want to save all texts of posts, configurations, etc. for all blogs in this website',$this->pluginID)) ; 
					$params->add_param('save_db', __('Only your SQL database:',$this->pluginID)) ;
					$params->add_comment(__('Check this option if you want to save the text of your posts, your configurations, etc. for the main website',$this->pluginID)) ; 
				} else {
					$params->add_param('save_db', __('The SQL database:',$this->pluginID)) ;
					$params->add_comment(__('Check this option if you want to save the text of your posts, your configurations, etc.',$this->pluginID)) ; 
				}

				$params->add_param('chunk', __('The maximum file size (in MB):',$this->pluginID)) ; 
				$params->add_comment(__('Please note that the zip file will be split into multiple files to comply with the maximum file size you have indicated',$this->pluginID)) ; 

				$params->add_title(sprintf(__('Do you want that the backup is sent by email?',$this->pluginID), $title)) ; 
				$params->add_param('email_check', __('Send the backup files by email:',$this->pluginID), '', '', array('email', 'rename')) ; 
				$params->add_param('email', __('If so, please enter your email:',$this->pluginID)) ; 
				$params->add_param('rename', __('Do you want to add a suffix to sent files:',$this->pluginID)) ; 
				$params->add_comment(__('This option allows going round the blocking feature of some mail provider that block the mails with zip attachments (like GMail).',$this->pluginID)) ; 
				$params->add_comment(__('You do not need to fill this field if no mail is to be sent.',$this->pluginID)) ; 

				$params->add_title(sprintf(__('Do you want that the backup is stored on a FTP?',$this->pluginID), $title)) ;
				if (function_exists("ftp_connect")) {
					$params->add_param('ftp', __('Save the backup files on a FTP?',$this->pluginID), '', '', array('ftp_host', 'ftp_login', 'ftp_pass', 'ftp_root')) ; 
					$params->add_param('ftp_host', __('FTP host:',$this->pluginID)) ; 
					$params->add_comment(sprintf(__('Should be at the form %s or %s',$this->pluginID), '<code>ftp://domain.tld/root_folder/</code>', '<code>ftps://domain.tld/root_folder/</code>')) ; 
					$params->add_param('ftp_login', __('Your FTP login:',$this->pluginID)) ; 
					$params->add_param('ftp_pass', __('Your FTP pass:',$this->pluginID)) ; 
					$params->add_comment(sprintf(__('Click on that button %s to test if the above information is correct',$this->pluginID)."<span id='testFTP_info'></span>", "<input type='button' id='testFTP_button' class='button validButton' onClick='testFTP();'  value='". __('Test',$this->pluginID)."' /><img id='wait_testFTP' src='".WP_PLUGIN_URL."/".str_replace(basename(__FILE__),"",plugin_basename( __FILE__))."core/img/ajax-loader.gif' style='display: none;'>")) ; 			
					$params->add_param('ftp_mail', __('If you want to be notify when the FTP storage is finished, please enter your email:',$this->pluginID)) ; 
				} else {
					$params->add_comment(__('Your PHP installation does not support FTP features, thus this option has been disabled! Sorry...',$this->pluginID)) ; 
				}
								
				$params->add_title(sprintf(__('Advanced - Memory and time management',$this->pluginID), $title)) ; 
				$params->add_param('max_allocated', __('What is the maximum size of allocated memory (in MB):',$this->pluginID)) ; 
				$params->add_comment(__('On some Wordpress installation, you may have memory issues. Thus, try to reduce this number if you face such error.',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('For your information, the memory limit of your webserver is %s whereas the present memory usage is %s.',$this->pluginID), ini_get('memory_limit'), Utils::byteSize(memory_get_usage()))) ; 
				$params->add_comment(__('It is recommended that the maximum attachment size is not set to a value higher than this one.',$this->pluginID)) ; 
				$params->add_comment(__("Please note that the files greater than this limit won't be included in the zip file!",$this->pluginID)) ; 
				$params->add_param('max_time', __('What is the maximum time for the php scripts execution (in seconds):',$this->pluginID)) ; 
				$params->add_comment(__('Even if you do not have time restriction, it is recommended to set this value to 15sec in order to avoid any killing of the php scripts by your web hoster.',$this->pluginID)) ; 

				$params->flush() ;
			$parameters = ob_get_clean() ; 
			
			ob_start() ; 
				echo "<p>". __('Here is the backup files. You can force a new backup or download previous backup files.',$this->pluginID)."</p>" ; 
				echo "<p>".sprintf(__('Please note that the current GMT time of the server is %s. If it is not correct, please configure the Wordpress installation correctly.', $this->pluginID), "<strong>".date_i18n('Y-m-d H:i:s')."</strong>")."</p>" ; 
							
				$hours = $this->backupInHours() ; 
				if ($hours>0) {
					$days = floor($hours/24) ; 
					$hours = $hours - 24*$days ; 
					echo "<p>".sprintf( __('An automatic backup will be launched in %s days and %s hours.',$this->pluginID), $days, $hours)."</p>" ; 
				} else {
					echo "<p>".sprintf( __('The backup process has started %s hours ago but has not finished yet.',$this->pluginID), -$hours)."</p>" ; 
				}
				echo "<div id='zipfile'>" ; 
				$this->displayBackup() ; 
				echo "</div>" ; 
				
				
				echo "<p><input type='button' id='backupButton' class='button-primary validButton' onClick='initForceBackup(\"external\")'  value='". __('Force a new backup (with Mail/FTP)',$this->pluginID)."' />" ; 
				echo "<script>jQuery('#backupButton').removeAttr('disabled');</script>" ; 
				echo "<input type='button' id='backupButton2' class='button validButton' onClick='initForceBackup(\"internal\")'  value='". __('Force a new backup (without any external storage or sending)',$this->pluginID)."' />" ; 
				echo "<script>jQuery('#backupButton2').removeAttr('disabled');</script>" ; 
				echo "<img id='wait_backup' src='".WP_PLUGIN_URL."/".str_replace(basename(__FILE__),"",plugin_basename( __FILE__))."core/img/ajax-loader.gif' style='display: none;'>" ; 
				echo "</p>" ; 
				echo "<div id='backupInfo'>" ; 
				echo "</div>" ; 
				echo "<div id='backupEnd'>" ; 
				echo "</div>" ; 
				
				echo "<br/><h3>".__("How to restore the backup files?", $this->pluginID)."</h3>" ; 
				
				echo "<p>".__("To restore the backups, and if you have backuped the full installation, you will have to execute the following steps:", $this->pluginID)."</p>" ; 
				echo "<ul>" ; 
				echo "<li style='padding-left:50px;'>".__("Save all zip files (i.e. *.zip, *.z01, *.z02, etc.) in a single folder on your hard disk.", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Unzip these files by using IZArc, Winzip, or Winrar (others software could not support these multipart zip and consider that the archive is corrupted).", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Save the extracted files on your webserver.", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Reimport the SQL files (i.e. *.sql1, *sql2, etc.) with phpmyadmin (it is recommended to save your database first).", $this->pluginID)."</li>" ; 
				echo "</ul>" ; 
				echo "<p>".__("To restore the backups, and if you have backuped only some folders, you will have to execute the following steps:", $this->pluginID)."</p>" ; 
				echo "<ul>" ; 
				echo "<li style='padding-left:50px;'>".__("Install a fresh version of Wordpress on your webserver.", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Save all zip files (i.e. *.zip, *.z01, *.z02, etc.) in a single folder on your hard disk.", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Unzip these files by using IZArc, Winzip, or Winrar (others software could not support these multipart zip and consider that the archive is corrupted).", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Replace the folders (i.e. 'plugins',  'themes', and/or 'uploads') of the root of your webserver by the extracted folders.", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Reimport the SQL files (i.e. *.sql1, *sql2, etc.) with phpmyadmin (it is recommended to save your database first).", $this->pluginID)."</li>" ; 
				echo "<li style='padding-left:50px;'>".__("Replace the wp-config.php (at the root of your webserver) with the extracted one.", $this->pluginID)."</li>" ; 
				echo "</ul>" ; 

			$tabs->add_tab(__('Backups',  $this->pluginID), ob_get_clean() ) ; 	

			$tabs->add_tab(__('Parameters',  $this->pluginID), $parameters , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_param.png") ; 	
			
			$frmk = new coreSLframework() ; 
			if ( ((is_multisite())&&($blog_id == 1)) || (!is_multisite()) || ($frmk->get_param('global_allow_translation_by_blogs'))) {
				ob_start() ; 
					$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
					$trans = new translationSL($this->pluginID, $plugin) ; 
					$trans->enable_translation() ; 
				$tabs->add_tab(__('Manage translations',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_trad.png") ; 	
			}
			
			ob_start() ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new feedbackSL($plugin, $this->pluginID) ; 
				$trans->enable_feedback() ; 
			$tabs->add_tab(__('Give feedback',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_mail.png") ; 	
			
			ob_start() ; 
				// A list of plugin slug to be excluded
				$exlude = array('wp-pirates-search') ; 
				// Replace sedLex by your own author name
				$trans = new otherPlugins("sedLex", $exlude) ; 
				$trans->list_plugins() ; 
			$tabs->add_tab(__('Other plugins',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_plug.png") ; 	
			
			echo $tabs->flush() ; 
			
			
			// Before this comment, you may modify whatever you want
			//===============================================================================================
			?>
			<?php echo $this->signature ; ?>
		</div>
		<?php
	}
	
	
	/** ====================================================================================================================================================
	* Create a table which summarize all the backup files
	*
	* @return void
	*/
	
	function displayBackup() {
		global $blog_id ; 
		// We create the folder for the backup files
		$blog_fold = "" ; 
		if (is_multisite()) {
			$blog_fold = $blog_id."/" ; 
		}
	
		$table = new adminTable() ;
		$table->title(array(__('Date of the backup',  $this->pluginID), __('Backup files',  $this->pluginID)) ) ;
		
		if (!is_dir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold)) {
			@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold, 0777, true) ; 
		}
		
		// List zip files
		$files = @scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ; 
		$nb = 0 ; 
		foreach ($files as $f) {
			if (preg_match("/^BackupScheduler.*zip/i", $f)) {
				SL_Debug::log(get_class(), "The backup ".$f." has been found" , 5) ; 
				if ((!preg_match("/^BackupScheduler.*tmp$/i", $f))&&(!preg_match("/^BackupScheduler.*tmp2$/i", $f))) {
					$date = explode("_", $f) ; 
					$date = $date[1] ; 
					$date = date_i18n(get_option('date_format') ,mktime(0, 0, 0, substr($date, 4, 2), substr($date, 6, 2), substr($date, 0, 4)));
					$heure = date ("H:i:s.", @filemtime(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f)) ; 
					
					$lien = "<p>" ; 
					$i = 1 ; 
					$size = 0 ; 
					$racine = str_replace(".zip",".z". sprintf("%02d",$i), $f) ; 
					
					while (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$racine)) {
						SL_Debug::log(get_class(), "The sub-backup ".$racine." has been found" , 5) ; 
						$lien .= "<a href='".WP_CONTENT_URL."/sedlex/backup-scheduler/".$blog_fold.$racine."'>".sprintf(__('Part %s',  $this->pluginID), sprintf("%02d",$i))."</a> (".Utils::byteSize(filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$racine)).") | "  ; 
						$size += filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$racine) ; 
						//MAJ
						$i++ ; 
						$racine = str_replace(".zip",".z". sprintf("%02d",$i), $f) ; 
					}
					$size += filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f) ; 
					$lien .= "<a href='".WP_CONTENT_URL."/sedlex/backup-scheduler/".$blog_fold.$f."'>".sprintf(__('Part %s',  $this->pluginID), sprintf("%02d",$i))."</a> (".Utils::byteSize(filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f)).")"  ; 
					$lien .= "<p>" ; 
					
					// We compute in how many days the backup will be deleted
					$name_file = explode("_", $f, 3) ; 
					$new_date = date_i18n("Ymd") ; 
					$date2 = substr($name_file[1], 0, 8) ; 
					$s = strtotime($new_date)-strtotime($date2);
					$delta = $this->get_param("delete_after")-intval($s/86400);   

					$valeur  = "<p>".sprintf(__('Backup finished on %s at %s',  $this->pluginID), $date, $heure)."</p>" ; 
					$valeur .= "<p style='font-size:80%'>".sprintf(__('The total size of the files is %s',  $this->pluginID), Utils::byteSize($size))."</p>" ; 
					$valeur .= "<p style='font-size:80%'>".sprintf(__('These files will be deleted in %s days',  $this->pluginID), $delta)."</p>" ; 
					$cel1 = new adminCell($valeur) ;
					$racinefichier = explode(".", $f) ; 
					$cel1->add_action(__("Delete these backup files", $this->pluginID), "deleteBackup('".$racinefichier[0]."')") ; 
					$cel2 = new adminCell($lien) ;
					$table->add_line(array($cel1, $cel2), '1') ;
					$nb++ ; 
				} 
			}
		}
		if  ((is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step"))||(is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock"))) {
			$sec_rand = "" ; 
			$files = @scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ; 
			foreach ($files as $f) {
				if (preg_match("/sec_rand/i", $f)) {
					list($nothing1, $nothing2, $sec_rand) = explode("_", $f, 3) ; 
				}
			}

			$date_tmp = explode("_", $sec_rand) ; 
			$date_tmp = $date_tmp[0] ; 
			$date = date_i18n(get_option('date_format') ,mktime(0, 0, 0, substr($date_tmp, 4, 2), substr($date_tmp, 6, 2), substr($date_tmp, 0, 4)));
			$heure = date ("H:i:s.", mktime(substr($date_tmp, 8, 2), substr($date_tmp, 10, 2), substr($date_tmp, 12, 2), substr($date_tmp, 4, 2), substr($date_tmp, 6, 2), substr($date_tmp, 0, 4))) ; 
			$valeur  = "<p>".sprintf(__('The process is still in progress for this backup (begun %s at %s).',  $this->pluginID), $date, $heure)."</p>" ;
			// STEP SQL
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="SQL") {
				$sql = new SL_Database() ; 
				$progress = $sql->progress(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."BackupScheduler_".$sec_rand.".sql") ; 
				$valeur  .= "<p style='font-size:80%'>".sprintf(__('The SQL extraction is in progress (%s tables extracted).',  $this->pluginID), $progress)."</p>" ;
			}
			// STEP ZIP
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="ZIP") {
				// We create the zip file
				$z = new SL_Zip;
				$progress = $z->progress(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."BackupScheduler_".$sec_rand.".zip") ; 
				$size = @filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."BackupScheduler_".$sec_rand.".zip.data_segment.tmp") ; 
				$valeur  .= "<p style='font-size:80%'>".sprintf(__('The ZIP creation is in progress (%s files has been added in the zip file and the current size of the zip is %s).',  $this->pluginID), $progress, Utils::byteSize($size))."</p>" ;				
			}
			
			// STEP FTP
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="FTP") {	
				$files_to_sent = $this->get_param('ftp_to_be_sent') ; 
				$files_sent = $this->get_param('ftp_sent') ; 
				$progress = count($files_sent)."/".(count($files_to_sent)+count($files_sent)) ; 
				$valeur  .= "<p style='font-size:80%'>".sprintf(__('The FTP sending is in progress (%s files has been stored in the FTP).',  $this->pluginID), $progress)."</p>" ;				
			}
			
			// STEP MAIL
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="MAIL") {	
				$files_to_sent = $this->get_param('mail_to_be_sent') ; 
				$files_sent = $this->get_param('mail_sent') ; 
				$progress = count($files_sent)."/".(count($files_to_sent)+count($files_sent)) ; 
				$valeur  .= "<p style='font-size:80%'>".sprintf(__('The MAIL sending is in progress (%s files has been sent).',  $this->pluginID), $progress)."</p>" ;				
			}
			
			$cel1 = new adminCell($valeur) ;
			$cel1->add_action(__("Cancel this process", $this->pluginID), "cancelBackup()") ; 
			$valeur  = "<p>".__('Please wait...',  $this->pluginID)."</p>" ; 
			$cel2 = new adminCell($valeur) ;
			$table->add_line(array($cel1, $cel2), '1') ;
			$nb++ ; 
		}
		if ($nb==0) {
			$cel1 = new adminCell("<p>".__('(For now, there is no backup files... You should wait or force a backup (see below) )',  $this->pluginID)."</p>") ;
			$cel2 = new adminCell("") ;
			$table->add_line(array($cel1, $cel2), '1') ;
			$nb++ ; 			
		}

		echo $table->flush() ;
	}
	/** ====================================================================================================================================================
	* Create the zip file
	*
	* @return boolean if it works
	*/
	
	public function create_zip($type_backup) {
		global $blog_id ; 
		global $wpdb ; 
		
		// We create the folder for the backup files
		$blog_fold = "" ; 
		if (is_multisite()) {
			$blog_fold = $blog_id."/" ; 
		}
		
		// We check if the process is in progress
		clearstatcache() ; 
		if (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock")) {
			SL_Debug::log(get_class(), "A lock file is still here." , 2) ; 
			return array('finished'=>false, 'error'=>__("Please wait, a backup is in progress! If you want to force a new backup, refresh this page and end the current backup first.", $this->pluginID)) ; 
		}
		@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock", 'lock') ; 
		clearstatcache() ; 
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock")) {
			SL_Debug::log(get_class(), "Impossible to write in the ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold." folder the lock file" , 2) ; 
			return array('finished'=>false, 'error'=>sprintf(__("It is impossible to create the %s file in the %s folder. Please check folder/file permissions.", $this->pluginID), "<code>.lock</code>", "<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."</code>")) ; 
		}
		
		@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold, 0777, true) ; 
		if (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".htaccess")) {
			@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".htaccess") ; 
		}
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."index.php")) {
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."index.php", "You are not allowed here!") ; 
			SL_Debug::log(get_class(), "Create the index.php file in the ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold." to avoid any listing of the directory." , 5) ; 
		}

	
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step") ) {	
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step", "SQL") ; 
		}
		
		// Memory limit upgrade
		$current_use = ceil( memory_get_usage() / (1024*1024) );
		$limit  = ((int)ini_get('memory_limit'));
		if ( $current_use + $this->get_param('max_allocated') + 20 >= $limit ){
			@ini_set('memory_limit', sprintf('%dM', ($current_use + $this->get_param('max_allocated') + 20) ));
		}
		
		// Security
		$sec_rand = "" ; 
		$files = @scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ; 
		foreach ($files as $f) {
			if (preg_match("/sec_rand/i", $f)) {
				list($nothing1, $nothing2, $sec_rand) = explode("_", $f, 3) ; 
			}
		}
		$rand = $sec_rand ; 
		if ($rand=="") {
			$rand = date_i18n("YmdHis")."_".Utils::rand_str(10, "abcdefghijklmnopqrstuvwxyz0123456789") ; 
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".sec_rand_".$rand, "ok") ; 
		}
		
		// STEP SQL
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="SQL") {
			if ($this->get_param('save_db')||$this->get_param('save_db_all')) {
				
				if (!is_multisite()) {
					// We create the SQL file
					$sql = new SL_Database() ; 	
				} else if (is_multisite()&&($blog_id == 1)) {
					if ($this->get_param('save_db_all')) {
						$sql = new SL_Database() ; 
					} else {
						$sql = new SL_Database($wpdb->prefix) ; 
					}
				} else {
					$sql = new SL_Database($wpdb->prefix) ; 
				}
				$ip = $sql->is_inProgress(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ; 

				if ($ip['step'] == "in progress") {
					SL_Debug::log(get_class(), "An other SQL extraction is still in progress (for ".$ip['for']." seconds).", 2) ; 
					return array('finished'=>false, 'error'=>sprintf(__("An other SQL extraction is still in progress (for %s seconds)... Please wait!", $this->pluginID),$ip['for'])) ; 
				} else if ($ip['step'] == "error") {
					SL_Debug::log(get_class(), strip_tags("SQL: " .$ip['error']), 2) ; 
					return array('finished'=>false, 'error'=>$ip['error']) ; 			
				} else if ($ip['step'] == "nothing") {
					SL_Debug::log(get_class(), "SQL extraction: BackupScheduler_".$rand.".sql", 4) ; 
					$name = "BackupScheduler_".$rand.".sql" ; 
				} else if ($ip['step'] == "to be completed") {
					SL_Debug::log(get_class(), "SQL extraction to be completed: ".$ip['name_sql'], 4) ; 
					$name = $ip['name_sql']  ; 
				}
				$res = $sql->createSQL(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$name, $this->get_param('max_time'),$this->get_param('max_allocated')*1024*1024);
				
				// Check if the step should be modified
				if ($res['finished']==true) {
					SL_Debug::log(get_class(), "SQL extraction finished", 4) ; 
					@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step", "ZIP") ; 
					$sqlfiles =  $res['path'] ; 
				} else {
					$res['text'] = ' '.__('(SQL extraction)', $this->pluginID) ; 	
					return $res ; 
				}
			} else {
				// Nothing should be done, thus we go directly at the next step
				@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step", "ZIP") ; 
			}
		}
		
		// STEP ZIP
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="ZIP") {
			// We create the zip file
			$z = new SL_Zip;
			$ip = $z->is_inProgress(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ; 
			
			if ($ip['step'] == "in progress") {
				SL_Debug::log(get_class(), "ZIP backup is still in progress (for ".$ip['for']." seconds)", 2) ; 
				return array('finished'=>false, 'error'=>sprintf(__("An other backup is still in progress (for %s seconds)... Please wait!", $this->pluginID),$ip['for'])) ; 
			} else if ($ip['step'] == "error") {
				SL_Debug::log(get_class(), strip_tags("ZIP: " .$ip['error']), 2) ; 
				return array('finished'=>false, 'error'=>$ip['error']) ; 			
			} else if ($ip['step'] == "nothing") {
				if ( ( (is_multisite()&&($blog_id == 1))||(!is_multisite()) ) && ($this->get_param('save_all')) ) {
					SL_Debug::log(get_class(), "ZIP backup of " .ABSPATH, 4) ; 
					$z -> addDir(ABSPATH, ABSPATH, "backup_".date_i18n("Ymd")."/", array(WP_CONTENT_DIR."/sedlex/"));
				} else {
					if  ( ( (is_multisite()&&($blog_id == 1))||(!is_multisite()) ) && ($this->get_param('save_plugin')) ) {
						SL_Debug::log(get_class(), "ZIP backup of " .WP_CONTENT_DIR."/plugins/", 4) ; 
						$z -> addDir(WP_CONTENT_DIR."/plugins/", WP_CONTENT_DIR."/", "backup_".date_i18n("Ymd")."/");
					}
					if  ( ( (is_multisite()&&($blog_id == 1))||(!is_multisite()) ) && ($this->get_param('save_theme')) ) {
						SL_Debug::log(get_class(), "ZIP backup of " .WP_CONTENT_DIR."/themes/", 4) ; 
						$z -> addDir(WP_CONTENT_DIR."/themes/", WP_CONTENT_DIR."/", "backup_".date_i18n("Ymd")."/");
					}
					if  ( (!is_multisite()) && ($this->get_param('save_upload')) ) {
						SL_Debug::log(get_class(), "ZIP backup of " .WP_CONTENT_DIR."/uploads/", 4) ; 
						$z -> addDir(WP_CONTENT_DIR."/uploads/", WP_CONTENT_DIR."/", "backup_".date_i18n("Ymd")."/");
					}
					if  ( is_multisite() && ($this->get_param('save_upload')) ) {
						SL_Debug::log(get_class(), "ZIP backup of " .WP_CONTENT_DIR."/blogs.dir/".$blog_id."/", 4) ; 
						$z -> addDir(WP_CONTENT_DIR."/blogs.dir/".$blog_id."/", WP_CONTENT_DIR."/", "backup_".date_i18n("Ymd")."/");
					}
					if  ( is_multisite() && ($blog_id == 1) && ($this->get_param('save_upload_all')) ) {
						SL_Debug::log(get_class(), "ZIP backup of " .WP_CONTENT_DIR."/blogs.dir/", 4) ; 
						$z -> addDir(WP_CONTENT_DIR."/blogs.dir/", WP_CONTENT_DIR."/", "backup_".date_i18n("Ymd")."/");
					}
					if  ( (is_multisite()&&($blog_id == 1))||(!is_multisite()) ) {
						SL_Debug::log(get_class(), "ZIP backup of " .ABSPATH."/wp-config.php", 4) ; 
						$z -> addFile(ABSPATH."/wp-config.php", ABSPATH, "backup_".date_i18n("Ymd")."/");
					}
				}
				if ($this->get_param('save_db')||$this->get_param('save_db_all')) {
					foreach($sqlfiles as $f) {
						$z -> addFile($f, WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold, "backup_".date_i18n("Ymd")."/");
						SL_Debug::log(get_class(), "ZIP backup of " .$f, 4) ; 
					}
				}
				$name = WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."BackupScheduler_".$rand.".zip" ; 
				SL_Debug::log(get_class(), "ZIP backup name is defined as " .$name, 4) ; 
			} else if ($ip['step'] == "to be completed") {
				$name = WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$ip['name_zip']  ; 
				SL_Debug::log(get_class(), "ZIP backup name to be finished is " .$name, 4) ; 
			}
			
			$path = $z -> createZip($name,$this->get_param('chunk')*1024*1024, $this->get_param('max_time'),$this->get_param('max_allocated')*1024*1024);
			
			// Check if the step should be modified
			if ($path['finished']==true) {
				@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step", "FTP") ; 
				// We delete the possible SQL file and config file
				$num_i = 1 ; 
				while (true) {
					if (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."BackupScheduler_".$rand.".sql".$num_i)) {
						@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."BackupScheduler_".$rand.".sql".$num_i) ; 
						SL_Debug::log(get_class(), "SQL file is deleted: " .WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."BackupScheduler_".$rand.".sql".$num_i, 4) ; 
						$num_i ++ ; 
					} else {
						break ; 
					}
				}
				$files_to_sent = $path['path'] ; 
				// Reset this variable to avoid any conflicts
				$this->set_param('ftp_to_be_sent', $files_to_sent) ; 
				$this->set_param('mail_to_be_sent', $files_to_sent) ; 
				$this->set_param('ftp_sent', array()) ; 
				$this->set_param('mail_sent', array()) ; 
			}
			$path['text'] = ' '.__('(ZIP creation)', $this->pluginID) ; 	
			return $path ; 
		}
		
		// STEP FTP
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="FTP") {	
			
			if (($this->get_param('ftp'))&&($type_backup=="external")) {
			
				// On envoie le premier fichier en FTP
				$files_to_sent = $this->get_param('ftp_to_be_sent') ; 
				$files_sent = $this->get_param('ftp_sent') ; 
				$file_to_sent = array_pop ($files_to_sent) ; 
				array_push($files_sent, $file_to_sent) ; 
				// Mise ˆ jour 
				$this->set_param('ftp_to_be_sent', $files_to_sent) ; 
				$this->set_param('ftp_sent', $files_sent) ; 
				
				if ($file_to_sent!=NULL) {
					SL_Debug::log(get_class(), "FTP file to be sent: " .$file_to_sent, 4) ; 
					$res = $this->sendFTP(array($file_to_sent)) ; 
					if ($res['transfer']) {
						$res['text'] = ' '.__('(FTP sending)', $this->pluginID); 	
						$res['nb_finished'] = count($files_sent) ; 
						$res['nb_to_finished'] = count($files_to_sent) ; 
					} 
					return $res ; 
				} else {
					SL_Debug::log(get_class(), "Email to summarize the number of FTP files sent.", 4) ; 
					$this->sendFTPEmail($files_sent) ; 
					@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step", "MAIL") ; 
				}
			} else {
				// Nothing should be done, thus we go directly at the next step
				@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step", "MAIL") ; 			
			}
		}
		
		// STEP MAIL
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")=="MAIL") {	
			if (($this->get_param('email_check'))&&($type_backup=="external")) {

				// On envoie le premier fichier en mail
				$files_to_sent = $this->get_param('mail_to_be_sent') ; 
				$files_sent = $this->get_param('mail_sent') ; 
				$file_to_sent = array_pop ($files_to_sent) ; 
				array_push($files_sent, $file_to_sent ) ; 
				// Mise a jour 
				$this->set_param('mail_to_be_sent', $files_to_sent) ; 
				$this->set_param('mail_sent', $files_sent) ; 
				
				if ($file_to_sent!=NULL) {
					SL_Debug::log(get_class(), "Email the backup file: ".file_to_sent , 4) ; 
					$subject = sprintf(__("Backup of %s on %s (%s)", $this->pluginID), get_bloginfo('name') , date_i18n('Y-m-d'), count($files_sent)."/".(count($files_to_sent)+count($files_sent)) ) ; 
					$res = $this->sendEmail(array($file_to_sent), $subject) ; 
					if ($res===true) {
						$path['text'] = ' '.__('(MAIL sending)', $this->pluginID) ; 	
						$path['nb_finished'] = count($files_sent) ; 
						$path['nb_to_finished'] = count($files_to_sent) ; 
						SL_Debug::log(get_class(), "Email sent.", 4) ; 
					} else {
						SL_Debug::log(get_class(), "Email failed to be sent.", 2) ; 
						$path['error'] = __("Your Wordpress installation cannot send emails (with heavy attachments)!", $this->pluginID)  ; 
					}
					return $path ; 
				} else {
					if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")) {
						SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step cannot be deleted." , 2) ; 
						return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step"."</code>")) ; 
					}
				}
			} else {
				// Nothing should be done, thus we go directly at the next step
				if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")) {
					SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step cannot be deleted." , 2) ; 
					return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step"."</code>")) ; 
				}
			}
		}
		
		// STEP END
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step") ) {	
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."last_backup", date_i18n("Y-m-d")) ; 	
			return  ; 		
		}
		
		SL_Debug::log(get_class(), "An unknown error occured!" , 2) ; 
		return array('finished'=>false, 'error'=>__("An unknown error occured!", $this->pluginID)) ; 
	}
	
	/** ====================================================================================================================================================
	* Callback for displaying the progress bar
	*
	* @return void
	*/
	function initBackupForce() {	
		$pb = new progressBarAdmin(500, 20, 0, "") ; 
		SL_Debug::log(get_class(), "Init force a new backup." , 4) ; 
		$this->only_cancelBackup() ; 
		$pb->flush() ;
		die() ; 
	}
	
	/** ====================================================================================================================================================
	* Callback for the button to force a new backup
	*
	* @return void
	*/
	function backupForce() {
		global $blog_id ; 
		// We create the folder for the backup files
		$blog_fold = "" ; 
		if (is_multisite()) {
			$blog_fold = $blog_id."/" ; 
		}
		
		$type_backup = $_POST['type_backup'] ;
		SL_Debug::log(get_class(), "Force a new backup." , 4) ; 

		$result = $this->create_zip($type_backup) ;
		if (isset($result['error'])) {
			echo "<div class='error fade'><p class='backupError'>".$result['error']."</p></div>" ; 
			if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")) {
				SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step cannot be deleted." , 2) ; 
				return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step"."</code>")) ; 
			}
			if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock")) {
				SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock cannot be deleted." , 2) ; 
				return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock"."</code>")) ; 
			}
			die() ; 
		}
		
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock")) {
			$this->only_cancelBackup() ; 
			SL_Debug::log(get_class(), "The process has been canceled by a third person (i.e. the lock file does not exist anymore)" , 2) ; 
			$result = array('finished'=>false, 'error'=>__("The process has been canceled by a third person", $this->pluginID)) ; 
		}
		

		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")) {
			SL_Debug::log(get_class(), "The backup process has end." , 4) ; 
			echo "<div class='updated fade'><p class='backupEnd'>".__("A new backup has been generated!", $this->pluginID)."</p>" ; 
			echo "</div>" ; 
			$this->only_cancelBackup() ; 
		} else {
			echo $result['nb_finished']."/".($result['nb_finished']+$result['nb_to_finished']).$result['text'] ; 
			//echo print_r($result) ;// DEBUG
			if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock")) {
				SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock cannot be deleted." , 2) ; 
				echo "<div class='error fade'><p class='backupError'>".sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock"."</code>")."</p></div>" ; 
			}
		}
		
		die() ; 
	}
	
	/** ====================================================================================================================================================
	* Callback updating the table with zip files
	*
	* @return void
	*/
	function updateBackupTable() {
		$this->displayBackup() ; 
		die() ; 
	}
	
	/** ====================================================================================================================================================
	* Callback updating the table with zip files
	*
	* @return void
	*/
	
	function checkIfBackupNeeded() {
		global $blog_id ; 
		// We create the folder for the backup files
		$blog_fold = "" ; 
		if (is_multisite()) {
			$blog_fold = $blog_id."/" ; 
		}
		
		// si le nb de jours dans laquel on doit faire un backup est inferieur ou egal a 0, on sauve
		if ($this->backupInHours()<0){	
			// We check dead lock
			$filemtime = @filemtime(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock");  // returns FALSE if file does not exist
			if ($filemtime>0 && (time() - $filemtime >= 200)){
				if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock")) {
					SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock cannot be deleted." , 2) ; 
					return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock"."</code>")) ; 
				}
			}
			// Create backup
			SL_Debug::log(get_class(), "A new backup has to be created." , 5) ; 
			$result = $this->create_zip("external") ;
			if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step")) {
				// New backup ended ; 
				$this->only_cancelBackup() ; 
			} else {
				if (!isset($result['error'])) {
					if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock")) {
						SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock cannot be deleted." , 2) ; 
						return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock"."</code>")) ; 
					}
					SL_Debug::log(get_class(), "The content of the .step file is :".@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step") , 5) ; 
				} 
			}
			
		} else {
			SL_Debug::log(get_class(), "No backup needed" , 5) ; 
			echo "No Backup Needed" ; 
			$this->only_cancelBackup() ; 
		}
		// On parcours les fichier de sauvegarde et on les supprime si trop vieux
		$files = @scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ;
		foreach ($files as $f) {
			if (preg_match("/^BackupScheduler/i", $f)) {
				$name_file = explode("_", $f, 3) ; 
				$new_date = date_i18n("Ymd") ; 
				$date = substr($name_file[1], 0, 8) ; 
				$s = strtotime($new_date)-strtotime($date);
				$delta = intval($s/86400);   
				if ($delta >= $this->get_param("delete_after")) {
					@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f) ; 
					SL_Debug::log(get_class(), "The backup file ".$f." has been deleted because it is to old" , 4) ; 
				}
			} 
		}
		die() ; 
	}
	
	/** ====================================================================================================================================================
	* Tell in how many hours the backup will be launched
	*
	* @return integer the number of days
	*/
	
	function backupInHours() {
		global $blog_id ; 
		// We create the folder for the backup files
		$blog_fold = "" ; 
		if (is_multisite()) {
			$blog_fold = $blog_id."/" ; 
		}
		
		// On regarde depuis quand date  la derniere sauvegarde
		$dateOfLastBackup = @file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold."last_backup") ; 
		$dateOfNextBackup = strtotime($dateOfLastBackup) + $this->get_param("frequency")*86400 + $this->get_param("save_time")*3600 ; 
		
		$DateNow = strtotime(date_i18n("Y-m-d H:0:0")) ; 

		$delta = ceil(($dateOfNextBackup-$DateNow)/3600);   

		return $delta;

	}
	/** ====================================================================================================================================================
	* Callback updating the table with zip files
	*
	* @return void
	*/
	
	function javascript_checkIfBackupNeeded() {	
		if ($this->backupInHours()<0)  {
			ob_start() ; 
			?>
				function checkIfBackupNeeded() {
					
					var arguments = {
						action: 'checkIfBackupNeeded'
					} 
					var ajaxurl2 = "<?php echo admin_url()."admin-ajax.php"?>" ; 
					jQuery.post(ajaxurl2, arguments, function(response) {
						// We do nothing as the process should be as silent as possible
					});    
				}
				
				// We launch the callback
				if (window.attachEvent) {window.attachEvent('onload', checkIfBackupNeeded);}
				else if (window.addEventListener) {window.addEventListener('load', checkIfBackupNeeded, false);}
				else {document.addEventListener('load', checkIfBackupNeeded, false);} 
							
			<?php 
			
			$java = ob_get_clean() ; 
			$this->add_inline_js($java) ; 
		}
	}
	
	/** ====================================================================================================================================================
	* Callback deleting backup files
	*
	* @return void
	*/
	
	function deleteBackup() {	
		global $blog_id ; 
		// We create the folder for the backup files
		$blog_fold = "" ; 
		if (is_multisite()) {
			$blog_fold = $blog_id."/" ; 
		}
		
		$racine = $_POST['racine'] ;
		$files = @scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ; 
		$nb = 0 ; 
		
		SL_Debug::log(get_class(), "The backup files ".$racine." is asked to be deleted" , 4) ; 

		foreach ($files as $f) {
			if (preg_match("/^".$racine."/i", $f)) {
				$res = @unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f) ; 
				if ($res===false) {
					SL_Debug::log(get_class(), "The file ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f." cannot be deleted." , 2) ; 
					echo "Error: ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f." can not be deleted. Checks rights!" ; 
					die() ; 
				}
			}
		}
		$this->displayBackup() ; 
		die() ; 
	}
	
	/** ====================================================================================================================================================
	* Callback cancelling backup files
	*
	* @return void
	*/
	
	function cancelBackup() {	
		$this->only_cancelBackup() ; 
		$this->displayBackup() ; 
		die() ; 
	}	

	function only_cancelBackup() {	
		global $blog_id ; 
		// We create the folder for the backup files
		$blog_fold = "" ; 
		if (is_multisite()) {
			$blog_fold = $blog_id."/" ; 
		}

		SL_Debug::log(get_class(), "Cancel any previous backup process if exists" , 4) ; 
		
		if (!is_dir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold)) {
			@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold, 0777, true) ; 
		}

		$files = @scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold) ; 
		$nb = 0 ; 
		@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".lock") ; 
		@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.".step") ; 
		foreach ($files as $f) {
			if (preg_match("/sec_rand/i", $f)) {
				@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f) ; 
			}
		}
		foreach ($files as $f) {
			if (preg_match("/tmp$/i", $f)) {
				@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$blog_fold.$f) ; 
			}
		}
	}	

	/** ====================================================================================================================================================
	* Send Email with the backup files
	*
	* @param $attach the backup file paths
	* @return void
	*/
	
	function sendEmail($attach, $subject="Backup") {

		for ($i=0 ; $i<count($attach) ; $i++) {
			$message = "" ; 
			$message .= "<p>".__("Dear sirs,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
			$message .= "<p>".sprintf(__("Here is attached the %s on %s backup files for today", $this->pluginID), $i+1, count($attach))."</p><p>&nbsp;</p>" ; 
			$message .= "<p>".__("Best regards,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
			
			$headers= "MIME-Version: 1.0\n" .
					"Content-Type: text/html; charset=\"" .
					get_option('blog_charset') . "\"\n";
					
			// We rename the zip files if needed
			if ($this->get_param('rename')!="") {
				@rename($attach[$i], $attach[$i].$this->get_param('rename')) ; 
				$attachments = array($attach[$i].$this->get_param('rename'));
			} else {
				$attachments = array($attach[$i]);
			}
			
						
			// send the email
			$res = wp_mail($this->get_param('email'), $subject, $message, $headers, $attachments ) ; 
			
			// We unrename the file 
			if ($this->get_param('rename')!="") {
				@rename($attach[$i].$this->get_param('rename'), $attach[$i]) ; 
			}
			
			if (!$res) {
				SL_Debug::log(get_class(), "An error occurred sending the mail to ".$this->get_param('email')." with ".$attach[$i] , 2) ; 
				return false ; 			
			} else {
				SL_Debug::log(get_class(), "The email has been successfully sent to ".$this->get_param('email')." with ".$attach[$i] , 4) ; 
			}

		}
		return true ; 
	}	
	
	/** ====================================================================================================================================================
	* Send Email with the backup files
	*
	* @param $attach the backup file paths
	* @return void
	*/
	
	function sendFTPEmail($files) {
		$nb = count($files) ; 
		if (trim($this->get_param('ftp_mail'))=="")
			return ;
			
		$message = "" ; 
		$message .= "<p>".__("Dear sirs,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
		$message .= "<p>".sprintf(__("%s backup files has been successfully saved on your FTP (%s)", $this->pluginID), $nb, $this->get_param('ftp_host'))."</p>" ; 
		$message .= "<p>".__("To download them, please click on the following links:", $this->pluginID) ;
		$message .= "<ul>" ; 
		preg_match("/ftp:\/\/([^\/]*)(\/*.*)/i", $this->get_param('ftp_host'), $match) ; 
		foreach ($files as $f) {
			if (trim($f)!="") {
				$link = str_replace(":/", "://", str_replace("//", "/", $this->get_param('ftp_host')."/".basename($f))) ; 
				$message .= "<li><a href='$link'>$link</a></li>" ; 
			}
		}
		$message .= "</ul></p>" ; 
		$message .= "</p><p>&nbsp;</p>" ; 
		$message .= "<p>".__("Best regards,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
		
		$headers= "MIME-Version: 1.0\n" .
				"Content-Type: text/html; charset=\"" .
				get_option('blog_charset') . "\"\n";
		
		$subject = sprintf(__("Backup of %s on %s - FTP confirmation", $this->pluginID), get_bloginfo('name') , date_i18n('Y-m-d') ) ; 
		
		// send the email
		$res = wp_mail($this->get_param('ftp_mail'), $subject, $message, $headers) ; 
		if (!$res) {
			SL_Debug::log(get_class(), "An error occurred sending the mail to ".$this->get_param('ftp_mail') , 2) ; 
		} else {
			SL_Debug::log(get_class(), "The email has been successfully sent  to ".$this->get_param('ftp_mail') , 4) ; 
		}
		
	}	
	
	/** ====================================================================================================================================================
	* Send backup files to ftp host
	*
	* @param $attach the bachup file paths
	* @return void
	*/
	
	function sendFTP($attach) {
		$pasv = false ; 
		if ($this->get_param('ftp_host')=='') 
			return array("transfer"=>false, "error"=>__('No host has been defined', $this->pluginID)) ; 
		
		$conn=false ; 
		
		if (preg_match("/ftp:\/\/([^\/]*)(\/*.*)/i", $this->get_param('ftp_host'), $match)) {
			$conn = @ftp_connect($match[1], 21, 50); 
		} else {
			if (!function_exists('ftp_ssl_connect')) {
				SL_Debug::log(get_class(), "The PHP installation does not support SSL features" , 1) ; 
				return array("transfer"=>false, "error"=>sprintf(__('Your PHP installation does not support SSL features... Thus, please use a standard FTP and not a FTPS!', $this->pluginID),  "<code>".$match[1] ."</code>")) ; 
			}
			if (preg_match("/ftps:\/\/([^\/]*)(\/*.*)/i", $this->get_param('ftp_host'), $match)) {
				$conn = @ftp_ssl_connect($match[1], 21, 50); 
			}
		}
		if ($conn===false) {
			SL_Debug::log(get_class(), sprintf("Problem with host %s", $match[1]) , 2) ; 
			return array("transfer"=>false, "error"=>sprintf(__('The host %s cannot be resolved!', $this->pluginID),  "<code>".$match[1] ."</code>")) ; 
		} else {
			if (@ftp_login($conn, $this->get_param('ftp_login'), $this->get_param('ftp_pass'))) {
				if (@ftp_chdir($conn, $match[2])) {
					for ($i=0 ; $i<count($attach) ; $i++) {
						ob_start() ; 
						$res = ftp_put($conn, basename($attach[$i]), $attach[$i], FTP_BINARY);
						if (!$res) {
							$pasv = true ; 
							if (@ftp_pasv($conn, true)) {
								$res = ftp_put($conn, basename($attach[$i]), $attach[$i], FTP_BINARY);
								if (!$res) {
									$value = ob_get_clean() ; 
									SL_Debug::log(get_class(), "Problem with FTP transferring: ".$value , 2) ; 
									return array("transfer"=>false, "error"=>sprintf(__('The file %s cannot be transfered to the FTP repository! The ftp_put function returns: %s', $this->pluginID), "<code>".$attach[$i]."</code>", "<code>".$value."</code>")) ; 
								} else {
									SL_Debug::log(get_class(), "FTP transfer OK of ".$attach[$i].' to '.$this->get_param('ftp_host'), 4) ; 
								}								
							} else {
								$value = ob_get_clean() ; 
								SL_Debug::log(get_class(), "Problem with PASV mode: ".$value , 2) ; 
								return array("transfer"=>false, "error"=>sprintf(__('The file %s cannot be transfered to the FTP repository and PASV mode cannot be entered : %s', $this->pluginID), "<code>".$attach[$i]."</code>", "<code>".$value."</code>")) ; 
							}						
						} else {
							SL_Debug::log(get_class(), "FTP transfer OK of ".$attach[$i].' to '.$this->get_param('ftp_host'), 4) ; 
						}
						$vide = ob_get_clean() ; 
					}
					@ftp_close($conn) ; 
					return array("transfer"=>true, 'pasv'=>$pasv, 'file'=>$attach, 'ftp_host'=>$match[1], 'ftp_dir'=>$match[2]) ; 
				} else {
				 	@ftp_close($conn) ; 
					SL_Debug::log(get_class(), "Problem with FTP chdir to ".$match[2] , 2) ; 
					return array("transfer"=>false, "error"=>sprintf(__('The specified folder %s does not exists. Please create it so that the transfer may start!', $this->pluginID), $match[2])) ; 
				}
			} else {
				@ftp_close($conn) ; 
				SL_Debug::log(get_class(), "The login (i.e. ".$this->get_param('ftp_login').")  and the password (i.e. ".$this->get_param('ftp_pass').") do not seem to be valid!" , 2) ; 
				return array("transfer"=>false, "error"=>__('The login/password does not seems valid!', $this->pluginID)) ; 
			}
		}
		return array("transfer"=>true, 'pasv'=>$pasv, 'file'=>$attach, 'ftp_host'=>$match[1], 'ftp_dir'=>$match[2]) ; 
	}	
	
	/** ====================================================================================================================================================
	* Test FTP
	*
	* @access private
	* @return void
	*/
	
	function testFTP() {

		$ftp_host =  $_POST['ftp_host'] ;
		$ftp_login =  $_POST['ftp_login'] ;
		$ftp_pass =  $_POST['ftp_pass'] ;
		
		if ($ftp_host=='') {
			echo "<p style='color:red;'>".__('No host has been defined', $this->pluginID)."</p>" ; 
			die() ; 
		}
		
		$conn=false ; 
		
		if (preg_match("/ftp:\/\/([^\/]*)(\/*.*)/i", $ftp_host, $match)) {
			$conn = ftp_connect($match[1], 21, 50); 
		} else {
			if (!function_exists('ftp_ssl_connect')) {
				echo "<p style='color:red;'>".__('Your PHP installation does not support SSL features... Thus, please use a standard FTP and not a FTPS!', $this->pluginID)."</p>" ; 
				die() ; 
			}
			if (preg_match("/ftps:\/\/([^\/]*)(\/*.*)/i", $ftp_host, $match)) {
				$conn = ftp_ssl_connect($match[1], 21, 50); 
			}
		}
		if ($conn===false) {
			echo "<p style='color:red;'>".sprintf(__('The host %s cannot be resolved!', $this->pluginID),  "<code>".$match[1] ."</code>")."</p>" ; 
			die() ; 
		} else {
			if (@ftp_login($conn, $ftp_login, $ftp_pass)) {
				if (@ftp_chdir($conn, $match[2])) {
					$res = @ftp_put($conn, "test_write.txt", WP_CONTENT_DIR."/index.php", FTP_BINARY);
					if (!$res) {
						if (@ftp_pasv($conn, true)) {
							$res = @ftp_put($conn, "test_write.txt", WP_CONTENT_DIR."/index.php", FTP_BINARY);
							if (!$res) {
								echo "<p style='color:red;'>".sprintf(__('The folder %s does not seems to be writable', $this->pluginID), $match[2])."</p>" ; 
								die() ; 
							}								
						} else {
							echo "<p style='color:red;'>".__('It seems impossible to switch to PASV mode', $this->pluginID)."</p>" ; 
							die() ; 						
						}
					} 
					$res = @ftp_delete($conn, "test_write.txt");
					@ftp_close($conn) ; 
					echo "<p style='color:green;'>".sprintf(__('Everything OK!', $this->pluginID), $match[2])."</p>" ; 
					die() ; 
				} else {
				 	@ftp_close($conn) ; 
					echo "<p style='color:red;'>".sprintf(__('The specified folder %s does not exists. Please create it so that the transfer may start!', $this->pluginID), $match[2])."</p>" ; 
					die() ; 
				}
			} else {
				@ftp_close($conn) ; 
				echo "<p style='color:red;'>".__('The login/password does not seems valid!', $this->pluginID)."</p>" ; 
				die() ; 
			}
		}
		die() ; 
	}	
}

$backup_scheduler = backup_scheduler::getInstance();

?>