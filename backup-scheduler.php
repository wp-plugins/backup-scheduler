<?php
/**
Plugin Name: Backup Scheduler
Plugin Tag: backup, schedule, plugin, save, database, zip
Description: <p>With this plugin, you may plan the backup of your entire website (folders, files and/or database).</p><p>You can choose: </p><ul><li>which folders you want to save; </li><li>the frequency of the backup process; </li><li>whether your database should be saved; </li><li>whether the backup is stored on the local website, sent by email or stored on a distant FTP (support of multipart zip files)</li></ul><p>This plugin is under GPL licence</p>
Version: 1.2.6
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
		$this->table_sql = '' ; 
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
		add_action( 'wp_print_scripts', array( $this, 'javascript_checkIfBackupNeeded'));
		
		// Si le dernier backup n'a pas eu lieu, creer le fichier
		@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/", 0777, true) ; 
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup")) {
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup", date("Y-m-d")) ; 
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
		register_deactivation_hook(__FILE__, array($this,'uninstall'));
	}

	/**====================================================================================================================================================
	* Function called when the plugin is activated
	* For instance, you can do stuff regarding the update of the format of the database if needed
	* If you do not need this function, you may delete it.
	*
	* @return void
	*/
	
	public function _update() {
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
			case 'save_plugin' 		: return false				; break ; 
			case 'save_theme' 		: return false				; break ; 
			case 'save_all' 		: return false				; break ; 
			case 'save_db' 		: return true				; break ; 
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
		$table_name = $wpdb->prefix . $this->pluginID;
	
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
				$params->add_param('save_all', __('All directories (the full Wordpress installation):',$this->pluginID),"", "", array('!save_upload', '!save_theme', '!save_plugin')) ;
				$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), ABSPATH)) ; 
				$params->add_comment(__('Check this option if you want to save everything. Be careful, because the backup could be quite huge!',$this->pluginID)) ; 
				$params->add_param('save_plugin', __('The plugins directory:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/plugins/")) ; 
				$params->add_comment(__('Check this option if you want to save all plugins that you have installed and that you use on this website.',$this->pluginID)) ; 
				$params->add_param('save_theme', __('The themes directory:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/themes/")) ; 
				$params->add_comment(__('Check this option if you want to save all themes that you have installed and that you use on this website.',$this->pluginID)) ; 
				$params->add_param('save_upload', __('The upload directory:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/uploads/")) ; 
				$params->add_comment(__('Check this option if you want to save the images, the files, etc. that you have uploaded on your website to create your articles/posts/pages.',$this->pluginID)) ; 
				$params->add_param('save_db', __('The SQL database:',$this->pluginID)) ;
				$params->add_comment(__('Check this option if you want to save the text of your posts, your configurations, etc.',$this->pluginID)) ; 
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
 
			$tabs->add_tab(__('Backups',  $this->pluginID), ob_get_clean() ) ; 	

			$tabs->add_tab(__('Parameters',  $this->pluginID), $parameters , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_param.png") ; 	
			
			ob_start() ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new translationSL($this->pluginID, $plugin) ; 
				$trans->enable_translation() ; 
			$tabs->add_tab(__('Manage translations',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_trad.png") ; 	

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
		$table = new adminTable() ;
		$table->title(array(__('Date of the backup',  $this->pluginID), __('Backup files',  $this->pluginID)) ) ;
		// List zip files
		$files = scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 
		$nb = 0 ; 
		foreach ($files as $f) {
			if (preg_match("/^BackupScheduler.*zip/i", $f)) {
				if ((!preg_match("/^BackupScheduler.*tmp$/i", $f))&&(!preg_match("/^BackupScheduler.*tmp2$/i", $f))) {
					$date = explode("_", $f) ; 
					$date = $date[1] ; 
					$date = date_i18n(get_option('date_format') ,mktime(0, 0, 0, substr($date, 4, 2), substr($date, 6, 2), substr($date, 0, 4)));
					$heure = date ("H:i:s.", @filemtime(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f)) ; 
					
					$lien = "<p>" ; 
					$i = 1 ; 
					$size = 0 ; 
					$racine = str_replace(".zip",".z". sprintf("%02d",$i), $f) ; 
					
					while (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$racine)) {
						$lien .= "<a href='".WP_CONTENT_URL."/sedlex/backup-scheduler/".$racine."'>".sprintf(__('Part %s',  $this->pluginID), sprintf("%02d",$i))."</a> (".Utils::byteSize(filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$racine)).") | "  ; 
						$size += filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$racine) ; 
						//MAJ
						$i++ ; 
						$racine = str_replace(".zip",".z". sprintf("%02d",$i), $f) ; 
					}
					$size += filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f) ; 
					$lien .= "<a href='".WP_CONTENT_URL."/sedlex/backup-scheduler/".$f."'>".sprintf(__('Part %s',  $this->pluginID), sprintf("%02d",$i))."</a> (".Utils::byteSize(filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f)).")"  ; 
					$lien .= "<p>" ; 
					
					// We compute in how many days the backup will be deleted
					$name_file = explode("_", $f, 3) ; 
					$new_date = date("Ymd") ; 
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
		if  ((is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step"))||(is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock"))) {
			$sec_rand = "" ; 
			$files = scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 
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
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="SQL") {
				$sql = new SL_Database() ; 
				$progress = $sql->progress(WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$sec_rand.".sql") ; 
				$valeur  .= "<p style='font-size:80%'>".sprintf(__('The SQL extraction is in progress (%s tables extracted).',  $this->pluginID), $progress)."</p>" ;
			}
			// STEP ZIP
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="ZIP") {
				// We create the zip file
				$z = new SL_Zip;
				$progress = $z->progress(WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$sec_rand.".zip") ; 
				$size = @filesize(WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$sec_rand.".zip.data_segment.tmp") ; 
				$valeur  .= "<p style='font-size:80%'>".sprintf(__('The ZIP creation is in progress (%s files has been added in the zip file and the current size of the zip is %s).',  $this->pluginID), $progress, Utils::byteSize($size))."</p>" ;				
			}
			
			// STEP FTP
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="FTP") {	
				$files_to_sent = $this->get_param('ftp_to_be_sent') ; 
				$files_sent = $this->get_param('ftp_sent') ; 
				$progress = count($files_sent)."/".(count($files_to_sent)+count($files_sent)) ; 
				$valeur  .= "<p style='font-size:80%'>".sprintf(__('The FTP sending is in progress (%s files has been stored in the FTP).',  $this->pluginID), $progress)."</p>" ;				
			}
			
			// STEP MAIL
			if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="MAIL") {	
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
		// We check if the process is in progress
		clearstatcache() ; 
		if (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock")) {
			return array('finished'=>false, 'error'=>__("Please wait, a backup is in progress! If you want to force a new backup, refresh this page and end the current backup first.", $this->pluginID)) ; 
		}
		@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock", 'lock') ; 
		clearstatcache() ; 
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock")) {
			return array('finished'=>false, 'error'=>sprintf(__("It is impossible to create the %s file in the %s folder. Please check folder/file permissions.", $this->pluginID), "<code>.lock</code>", "<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/"."</code>")) ; 
		}
		
		
		@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/", 0777, true) ; 
		if (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.htaccess")) {
			@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/.htaccess") ; 
		}
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/index.php")) {
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/index.php", "You are not allowed here!") ; 
		}

	
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step") ) {	
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step", "SQL") ; 
		}
		
		// Memory limit upgrade
		$current_use    = ceil( memory_get_usage() / (1024*1024) );
		$limit  = ((int)ini_get('memory_limit'));
		if ( $current_use + $this->get_param('max_allocated') + 20 >= $limit ){
			@ini_set('memory_limit', sprintf('%dM', ($current_use + $this->get_param('max_allocated') + 20) ));
		}
		
		// Security
		$sec_rand = "" ; 
		$files = scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 
		foreach ($files as $f) {
			if (preg_match("/sec_rand/i", $f)) {
				list($nothing1, $nothing2, $sec_rand) = explode("_", $f, 3) ; 
			}
		}
		$rand = $sec_rand ; 
		if ($rand=="") {
			$rand = date("YmdHis")."_".Utils::rand_str(10, "abcdefghijklmnopqrstuvwxyz0123456789") ; 
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.sec_rand_".$rand, "ok") ; 
		}
		
		// STEP SQL
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="SQL") {
			if ($this->get_param('save_db')) {
				// We create the SQL file
				$sql = new SL_Database() ; 
				$ip = $sql->is_inProgress(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 

				if ($ip['step'] == "in progress") {
					return array('finished'=>false, 'error'=>sprintf(__("An other SQL extraction is still in progress (for %s seconds)... Please wait!", $this->pluginID),$ip['for'])) ; 
				} else if ($ip['step'] == "error") {
					return array('finished'=>false, 'error'=>$ip['error']) ; 			
				} else if ($ip['step'] == "nothing") {
					$name = "BackupScheduler_".$rand.".sql" ; 
				} else if ($ip['step'] == "to be completed") {
					$name = $ip['name_sql']  ; 
				}
				$res = $sql->createSQL(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$name, $this->get_param('max_time'),$this->get_param('max_allocated')*1024*1024);
				
				// Check if the step should be modified
				if ($res['finished']==true) {
					@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step", "ZIP") ; 
					$sqlfiles =  $res['path'] ; 
				} else {
					$res['text'] = ' '.sprintf(__('(SQL extraction)', $this->pluginID), $res['info']) ; 	
					return $res ; 
				}
			} else {
				// Nothing should be done, thus we go directly at the next step
				@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step", "ZIP") ; 
			}
		}
		
		// STEP ZIP
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="ZIP") {
			// We create the zip file
			$z = new SL_Zip;
			$ip = $z->is_inProgress(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 
			
			if ($ip['step'] == "in progress") {
				return array('finished'=>false, 'error'=>sprintf(__("An other backup is still in progress (for %s seconds)... Please wait!", $this->pluginID),$ip['for'])) ; 
			} else if ($ip['step'] == "error") {
				return array('finished'=>false, 'error'=>$ip['error']) ; 			
			} else if ($ip['step'] == "nothing") {
				if ($this->get_param('save_all')) {
					$z -> addDir(ABSPATH, ABSPATH, "backup_".date("Ymd")."/", array(WP_CONTENT_DIR."/sedlex/"));
				} else {
					if ($this->get_param('save_plugin')) {
						$z -> addDir(WP_CONTENT_DIR."/plugins/", WP_CONTENT_DIR."/", "backup_".date("Ymd")."/");
					}
					if ($this->get_param('save_theme')) {
						$z -> addDir(WP_CONTENT_DIR."/themes/", WP_CONTENT_DIR."/", "backup_".date("Ymd")."/");
					}
					if ($this->get_param('save_upload')) {
						$z -> addDir(WP_CONTENT_DIR."/uploads/", WP_CONTENT_DIR."/", "backup_".date("Ymd")."/");
					}
					@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/wp-config.php") ; 
					@copy(ABSPATH."/wp-config.php", WP_CONTENT_DIR."/sedlex/backup-scheduler/wp-config.php") ; 
					$z -> addFile(WP_CONTENT_DIR."/sedlex/backup-scheduler/wp-config.php", WP_CONTENT_DIR."/sedlex/backup-scheduler/", "backup_".date("Ymd")."/");
				}
				if ($this->get_param('save_db')) {
					foreach($sqlfiles as $f) {
						$z -> addFile($f, WP_CONTENT_DIR."/sedlex/backup-scheduler/", "backup_".date("Ymd")."/");
					}
				}
					
				$name = WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$rand.".zip" ; 
			} else if ($ip['step'] == "to be completed") {
				$name = WP_CONTENT_DIR."/sedlex/backup-scheduler/".$ip['name_zip']  ; 
			}
			
			$path = $z -> createZip($name,$this->get_param('chunk')*1024*1024, $this->get_param('max_time'),$this->get_param('max_allocated')*1024*1024);
			
			// Check if the step should be modified
			if ($path['finished']==true) {
				@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step", "FTP") ; 
				// We delete the possible SQL file and config file
				$num_i = 1 ; 
				while (true) {
					if (is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$rand.".sql".$num_i)) {
						@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$rand.".sql".$num_i) ; 
						$num_i ++ ; 
					} else {
						break ; 
					}
				}
				@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/wp-config.php") ; 
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
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="FTP") {	
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
					$res = $this->sendFTP(array($file_to_sent)) ; 
					if ($res['transfer']) {
						$res['text'] = ' '.__('(FTP sending)', $this->pluginID) ; 	
						$res['nb_finished'] = count($files_sent) ; 
						$res['nb_to_finished'] = count($files_to_sent) ; 
					} 
					return $res ; 
				} else {
					$this->sendFTPEmail(count($files_sent)) ; 
					@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step", "MAIL") ; 
				}
			} else {
				// Nothing should be done, thus we go directly at the next step
				@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step", "MAIL") ; 			
			}
		}
		
		// STEP MAIL
		if (@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")=="MAIL") {	
			if (($this->get_param('email_check'))&&($type_backup=="external")) {
				// On envoie le premier fichier en mail
				$files_to_sent = $this->get_param('mail_to_be_sent') ; 
				$files_sent = $this->get_param('mail_sent') ; 
				$file_to_sent = array_pop ($files_to_sent) ; 
				array_push($files_sent, $file_to_sent ) ; 
				// Mise à jour 
				$this->set_param('mail_to_be_sent', $files_to_sent) ; 
				$this->set_param('mail_sent', $files_sent) ; 
				
				if ($file_to_sent!=NULL) {
					$subject = sprintf(__("Backup of %s on %s (%s)", $this->pluginID), get_bloginfo('name') , date('Y-m-d'), count($files_sent)."/".(count($files_to_sent)+count($files_sent)) ) ; 
					$res = $this->sendEmail(array($file_to_sent), $subject) ; 
					if ($res===true) {
						$path['text'] = ' '.__('(MAIL sending)', $this->pluginID) ; 	
						$path['nb_finished'] = count($files_sent) ; 
						$path['nb_to_finished'] = count($files_to_sent) ; 
					} else {
						$path['error'] = __("Your Wordpress installation cannot send emails (with heavy attachments)!", $this->pluginID)  ; 
					}
					return $path ; 
				} else {
					if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")) {
						return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/.step"."</code>")) ; 
					}
				}
			} else {
				// Nothing should be done, thus we go directly at the next step
				if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")) {
					return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/.step"."</code>")) ; 
				}
			}
		}
		
		// STEP END
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step") ) {	
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup", date("Y-m-d")) ; 	
			return  ; 		
		}

		return array('finished'=>false, 'error'=>__("An unknown error occured!", $this->pluginID)) ; 
	}
	
	/** ====================================================================================================================================================
	* Callback for displaying the progress bar
	*
	* @return void
	*/
	function initBackupForce() {	
		$pb = new progressBarAdmin(500, 20, 0, "") ; 
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
		$type_backup = $_POST['type_backup'] ;

		$result = $this->create_zip($type_backup) ;
		if (isset($result['error'])) {
			echo "<div class='error fade'><p class='backupError'>".$result['error']."</p></div>" ; 
			if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")) {
				return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/.step"."</code>")) ; 
			}
			if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock")) {
				return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock"."</code>")) ; 
			}
			die() ; 
		}
		
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock")) {
			$this->only_cancelBackup() ; 
			$result = array('finished'=>false, 'error'=>__("The process has been canceled by a third person", $this->pluginID)) ; 
		}
		

		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")) {
			echo "<div class='updated fade'><p class='backupEnd'>".__("A new backup has been generated!", $this->pluginID)."</p>" ; 
			echo "</div>" ; 
			$this->only_cancelBackup() ; 
		} else {
			echo $result['nb_finished']."/".($result['nb_finished']+$result['nb_to_finished']).$result['text'] ; 
			//echo print_r($result) ;// DEBUG
			if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock")) {
				echo "<div class='error fade'><p class='backupError'>".sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock"."</code>")."</p></div>" ; 
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
		// si le nb de jours dans laquel on doit faire un backup est inferieur ou egal a 0, on sauve
		if ($this->backupInHours()<0){	
			// We check dead lock
			$filemtime = @filemtime(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock");  // returns FALSE if file does not exist
			if ($filemtime>0 && (time() - $filemtime >= 200)){
				if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock")) {
					return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock"."</code>")) ; 
				}
			}
			// Create backup
			$result = $this->create_zip("external") ;
			if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step")) {
				// New backup ended ; 
				$this->only_cancelBackup() ; 
			} else {
				if (!isset($result['error'])) {
					if (!Utils::rm_rec(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock")) {
						return array("step"=>"error", "error"=>sprintf(__('The file %s cannot be deleted. You should have a problem with file permissions or security restrictions.', $this->pluginID),"<code>".WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock"."</code>")) ; 
					}
					echo ">".@file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step") ; 
				} else {
					//echo $result['error'] ; // DEBUG
				}
			}
			
		} else {
			echo "No Backup Needed" ; 
			$this->only_cancelBackup() ; 
		}
		// On parcours les fichier de sauvegarde et on les supprime si trop vieux
		$files = scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ;
		foreach ($files as $f) {
			if (preg_match("/^BackupScheduler/i", $f)) {
				$name_file = explode("_", $f, 3) ; 
				$new_date = date("Ymd") ; 
				$date = substr($name_file[1], 0, 8) ; 
				$s = strtotime($new_date)-strtotime($date);
				$delta = intval($s/86400);   
				if ($delta >= $this->get_param("delete_after")) {
					@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f) ; 
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
		// On regarde depuis quand date  la derniere sauvegarde
		$dateOfLastBackup = @file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup") ; 
		$dateOfNextBackup = strtotime($dateOfLastBackup) + $this->get_param("frequency")*86400 + $this->get_param("save_time")*3600 ; 
		
		$DateNow = strtotime(date("Y-m-d H:0:0")) ; 

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
		$racine = $_POST['racine'] ;
		$files = scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 
		$nb = 0 ; 
		foreach ($files as $f) {
			if (preg_match("/^".$racine."/i", $f)) {
				$res = @unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f) ; 
				if ($res===false) {
					echo "Error: ".WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f." can not be deleted. Checks rights!" ; 
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
		$files = scandir(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 
		$nb = 0 ; 
		@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/.lock") ; 
		@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/.step") ; 
		foreach ($files as $f) {
			if (preg_match("/sec_rand/i", $f)) {
				@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f) ; 
			}
		}
		foreach ($files as $f) {
			if (preg_match("/tmp$/i", $f)) {
				@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$f) ; 
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
				return false ; 			
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
	
	function sendFTPEmail($nb) {
		if (trim($this->get_param('ftp_mail'))=="")
			return ;
			
		$message = "" ; 
		$message .= "<p>".__("Dear sirs,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
		$message .= "<p>".sprintf(__("%s backup files has been successfully saved on your FTP (%s)", $this->pluginID), $nb, $this->get_param('ftp_host'))."</p><p>&nbsp;</p>" ; 
		$message .= "<p>".__("Best regards,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
		
		$headers= "MIME-Version: 1.0\n" .
				"Content-Type: text/html; charset=\"" .
				get_option('blog_charset') . "\"\n";
		
		$subject = sprintf(__("Backup of %s on %s - FTP confirmation", $this->pluginID), get_bloginfo('name') , date('Y-m-d') ) ; 
		
		// send the email
		$res = wp_mail($this->get_param('ftp_mail'), $subject, $message, $headers) ; 
	}	
	
	/** ====================================================================================================================================================
	* Send backup files to ftp host
	*
	* @param $attach the bachup file paths
	* @return void
	*/
	
	function sendFTP($attach) {
		if ($this->get_param('ftp_host')=='') 
			return array("transfer"=>false, "error"=>__('No host has been defined', $this->pluginID)) ; 
		
		$conn=false ; 
		
		if (preg_match("/ftp:\/\/([^\/]*?)(\/.*)/i", $this->get_param('ftp_host'), $match)) {
			$conn = @ftp_connect($match[1]); 
		} else {
			if (!function_exists('ftp_ssl_connect')) {
				return array("transfer"=>false, "error"=>sprintf(__('Your PHP installation does not support SSL features... Thus, please use a standard FTP and not a FTPS!', $this->pluginID),  "<code>".$match[1] ."</code>")) ; 
			}
			if (preg_match("/ftps:\/\/([^\/]*?)(\/.*)/i", $this->get_param('ftp_host'), $match)) {
				$conn = @ftp_ssl_connect($match[1]); 
			}
		}
		if ($conn===false) {
			return array("transfer"=>false, "error"=>sprintf(__('The host %s cannot be resolved!', $this->pluginID),  "<code>".$match[1] ."</code>")) ; 
		} else {
			if (@ftp_login($conn, $this->get_param('ftp_login'), $this->get_param('ftp_pass'))) {
				if (@ftp_chdir($conn, $match[2])) {
					for ($i=0 ; $i<count($attach) ; $i++) {
						ob_start() ; 
						$res = ftp_put($conn, basename($attach[$i]), $attach[$i], FTP_BINARY);
						if (!$res) {
							return array("transfer"=>false, "error"=>sprintf(__('The file %s cannot be transfered to the FTP repository! The ftp_put function returns: %s', $this->pluginID), "<code>".$attach[$i]."</code>", "<code>".ob_get_clean()."</code>")) ; 
						}
						$vide = ob_get_clean() ; 
					}
					@ftp_close($conn) ; 
					return array("transfer"=>true) ; 
				} else {
				 	@ftp_close($conn) ; 
					return array("transfer"=>false, "error"=>sprintf(__('The specified folder %s does not exists. Please create it so that the transfer may start!', $this->pluginID), $match[2])) ; 
				}
			} else {
				@ftp_close($conn) ; 
				return array("transfer"=>false, "error"=>__('The login/password does not seems valid!', $this->pluginID)) ; 
			}
		}
		
		return array("transfer"=>true) ; 
	}	
}

$backup_scheduler = backup_scheduler::getInstance();

?>