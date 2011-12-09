<?php
/**
Plugin Name: Backup Scheduler
Description: <p>With this plugin, you may plan the backup of your website.</p><p>You can choose: </p><ul><li>which folders you will save; </li><li>whether your database should be saved; </li><li>whether the backup is stored on the local website or sent by email (support of multipart zip files)
Version: 1.0.2
Framework: SL_Framework
Author: SedLex
Author Email: sedlex@sedlex.fr
Framework Email: sedlex@sedlex.fr
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/extend/plugins/image-zoom/
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
		add_action( "wp_ajax_backupForce",  array($this,"backupForce")) ; 
		add_action( "wp_ajax_updateBackupTable",  array($this,"updateBackupTable")) ;
		add_action( 'wp_ajax_nopriv_checkIfBackupNeeded', array( $this, 'checkIfBackupNeeded'));
		add_action( 'wp_ajax_checkIfBackupNeeded', array( $this, 'checkIfBackupNeeded'));
		add_action('wp_print_scripts', array( $this, 'javascript_checkIfBackupNeeded'));
		
		// Si le dernier backup n'a pas eu lieu, creer le fichier
		@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/", 0777, true) ; 
		if (!is_file(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup")) {
			@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup", date("Y-m-d")) ; 
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
			case 'email' 		: return "" 		; break ; 
			case 'rename' 		: return "" 		; break ; 
			case 'chunk' 		: return 5			; break ; 
			case 'frequency' 		: return 7			; break ; 
			case 'delete_after' 		: return 42			; break ; 
			case 'save_upload' 		: return true				; break ; 
			case 'save_plugin' 		: return false				; break ; 
			case 'save_theme' 		: return false				; break ; 
			case 'save_db' 		: return true				; break ; 
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
			$this->check_folder_rights( array(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ) ; 
			
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
				echo "<p>". __('Here is the backup files. You can force a new backup or download previous backup files.',$this->pluginID)."</p>" ; 
				echo "<p>".sprintf( __('An automatic backup will be launched in %s days.',$this->pluginID), $this->backupInDays())."</p>" ; 
				echo "<div id='zipfile'>" ; 
				$this->displayBackup() ; 
				echo "</div>" ; 
				echo "<p><input type='button' id='backupButton' class='button-primary validButton' onClick='initForceBackup()'  value='". __('Force a new backup',$this->pluginID)."' />" ; 
				echo "<script>jQuery('#backupButton').removeAttr('disabled');</script>" ; 
				echo "<img id='wait_backup' src='".WP_PLUGIN_URL."/".str_replace(basename(__FILE__),"",plugin_basename( __FILE__))."core/img/ajax-loader.gif' style='display: none;'>" ; 
				echo "</p>" ; 
				echo "<div id='backupInfo'>" ; 
				echo "</div>" ; 
				echo "<div id='backupEnd'>" ; 
				echo "</div>" ; 
 
			$tabs->add_tab(__('Backups',  $this->pluginID), ob_get_clean() ) ; 	

			ob_start() ; 
				$params = new parametersSedLex($this, 'tab-parameters') ; 
				
				$params->add_title(sprintf(__('How often do you want to backup your website?',$this->pluginID), $title)) ; 
				$params->add_param('frequency', __('Frequency (in days):',$this->pluginID)) ; 
				$params->add_comment(__('Please note that the backup will occur at midnight to be sure that your website is not overloaded',$this->pluginID)) ; 
				$params->add_param('delete_after', __('Keep the backup files for (in days):',$this->pluginID)) ; 
				
				$params->add_title(sprintf(__('What do you want to save?',$this->pluginID), $title)) ; 
				$params->add_param('save_plugin', __('The plugins directory:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/plugins/")) ; 
				$params->add_param('save_theme', __('The themes directory:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/themes/")) ; 
				$params->add_param('save_upload', __('The upload directory:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('(i.e. %s)',$this->pluginID), WP_CONTENT_DIR."/uploads/")) ; 
				$params->add_param('save_db', __('The SQL database:',$this->pluginID)) ;
				
				$params->add_title(sprintf(__('Do you want that the backup is sent by email?',$this->pluginID), $title)) ; 
				$params->add_param('email', __('If so, please enter your email:',$this->pluginID)) ; 
				$params->add_param('chunk', __('The maximum attachment size (in MB):',$this->pluginID)) ; 
				$params->add_comment(__('Please note that the zip file will be split into multiple files to comply with the maximum attachment size you have indicated',$this->pluginID)) ; 
				
				$params->add_title(sprintf(__('Do you want to rename the files?',$this->pluginID), $title)) ; 
				$params->add_param('rename', __('What is the suffix of the file:',$this->pluginID)) ; 
				$params->add_comment(__('This option allows going round the blocking feature of some mail provider that block the mails with zip attachments (like GMail).',$this->pluginID)) ; 
				
				$params->flush() ; 
			$tabs->add_tab(__('Parameters',  $this->pluginID), ob_get_clean() ) ; 	
			
			
			ob_start() ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new translationSL($this->pluginID, $plugin) ; 
				$trans->enable_translation() ; 
			$tabs->add_tab(__('Manage translations',  $this->pluginID), ob_get_clean() ) ; 	

			ob_start() ; 
				echo "<p>".__('This form is an easy way to contact the author and to discuss issues / incompatibilities / etc.',  $this->pluginID)."</p>" ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new feedbackSL($plugin, $this->pluginID) ; 
				$trans->enable_feedback() ; 
			$tabs->add_tab(__('Give feedback',  $this->pluginID), ob_get_clean() ) ; 	
			
			ob_start() ; 
				echo "<p>".__('Here is the plugins developped by the author:',  $this->pluginID) ."</p>" ; 
				// A list of plugin slug to be excluded
				$exlude = array('wp-pirates-search') ; 
				// Replace sedLex by your own author name
				$trans = new otherPlugins("sedLex", $exlude) ; 
				$trans->list_plugins() ; 
			$tabs->add_tab(__('Other possible plugins',  $this->pluginID), ob_get_clean() ) ; 	
			
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

				$valeur  = "<p>".sprintf(__('Backup completed on %s at %s',  $this->pluginID), $date, $heure)."</p>" ; 
				$valeur .= "<p style='font-size:80%'>".sprintf(__('The total size of the files is %s',  $this->pluginID), Utils::byteSize($size))."</p>" ; 
				$valeur .= "<p style='font-size:80%'>".sprintf(__('These files will be deleted in %s days',  $this->pluginID), $delta)."</p>" ; 
				$cel1 = new adminCell($valeur) ;
				$cel2 = new adminCell($lien) ;
				$table->add_line(array($cel1, $cel2), '1') ;
				$nb++ ; 
			}
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
	
	public function create_zip() {
		$date = date("YmdHis") ; 
		
		// Security
		$rand = Utils::rand_str(10, "abcdefghijklmnopqrstuvwxyz0123456789") ; 
		$name = WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$date."_".$rand.".zip" ; 
		@mkdir(WP_CONTENT_DIR."/sedlex/backup-scheduler/", 0777, true) ; 
		
		$z = new SL_Zip;
		
		$ip = $z->is_inProgress(WP_CONTENT_DIR."/sedlex/backup-scheduler/") ; 
		
		if ($ip['step'] == "in progress") {
			return array('finished'=>false, 'error'=>__("An other backup is still in progress... Please wait!", $this->pluginID)) ; 
		} else if ($ip['step'] == "nothing") {
			$ok = false ; 
			if ($this->get_param('save_plugin')) {
				$z -> addDir(WP_CONTENT_DIR."/plugins/");
				$ok = true ; 
			}
			if ($this->get_param('save_theme')) {
				$z -> addDir(WP_CONTENT_DIR."/themes/");
				$ok = true ; 
			}
			if ($this->get_param('save_upload')) {
				$z -> addDir(WP_CONTENT_DIR."/uploads/");
				$ok = true ; 
			}
			if ($this->get_param('save_db')) {
				$this->dumpMySQL(WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$date."_".$rand.".sql") ; 
				$z -> addFile(WP_CONTENT_DIR."/sedlex/backup-scheduler/BackupScheduler_".$date."_".$rand.".sql");
				$ok = true ; 
			}
			
			@unlink(WP_CONTENT_DIR."/sedlex/backup-scheduler/wp-config.php") ; 
			@copy(ABSPATH."/wp-config.php", WP_CONTENT_DIR."/sedlex/backup-scheduler/wp-config.php") ; 
			$z -> addFile(WP_CONTENT_DIR."/sedlex/backup-scheduler/wp-config.php");
				
			if ($ok) {
				$z -> removePath(WP_CONTENT_DIR."/") ; 
				$z -> addPath("/backup_".$date."/") ; 
				$path = $z -> createZip($name,$this->get_param('chunk')*1024*1024, 5);
				if ($path['finished']==true) {
					// We rename the zip file if needed
					if ($this->get_param('rename')!="") {
						$path2 = array() ; 
						foreach ($path['path'] as $f) {
							@rename($f, $f.$this->get_param('rename')) ; 
							$path2[] = $f.$this->get_param('rename') ; 
						}
						$path['path'] = $path2 ; 
					}
					
					@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup", date("Y-m-d")) ; 
					if (!$this->sendEmail($path['path'])) {
						$path['error'] = __("An error occured while sending mail!", $this->pluginID) ; 
						$path['finished'] = false ;  
					}
				}
				return $path ; 
			}
		} else if ($ip['step'] == "to be completed") {
			$name = $ip['name_zip']  ; 
			$path = $z -> createZip(WP_CONTENT_DIR."/sedlex/backup-scheduler/".$name,$this->get_param('chunk')*1024*1024, 10);
			if ($path['finished']==true) {
				@file_put_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup", date("Y-m-d")) ; 
				// We rename the zip file if needed
				if ($this->get_param('rename')!="") {
					$path2 = array() ; 
					foreach ($path['path'] as $f) {
						@rename($f, $f.$this->get_param('rename')) ; 
						$path2[] = $f.$this->get_param('rename') ; 
					}
					$path['path'] = $path2 ; 
				}
				
				if (!$this->sendEmail($path['path'])) {
					$path['error'] = __("An error occured while sending mail!", $this->pluginID) ; 
					$path['finished'] = false ;  
				}
			}
			return $path ; 
		}
		
		return false ;
	}
	
	/** ====================================================================================================================================================
	* Callback for displaying the progress bar
	*
	* @return void
	*/
	function initBackupForce() {	
		$pb = new progressBarAdmin(300, 20, 0, "") ; 
		$pb->flush() ;
		die() ; 
	}
	
	/** ====================================================================================================================================================
	* Callback for the button to force a new backup
	*
	* @return void
	*/
	function backupForce() {
		$result = $this->create_zip() ;
		if ($result!==false) {
			if ($result['finished']==true) {
				echo "<div class='updated fade'><p class='backupEnd'>".__("A new backup have been generated!", $this->pluginID)."</p></div>" ; 
			} else {
				if (isset($result['error'])) {
					echo "<div class='error fade'><p class='backupError'>".$result['error']."</p></div>" ; 
				} else {
					echo $result['nb_finished']."/".($result['nb_finished']+$result['nb_to_finished']) ; 
				}
			}
		} else {
			echo "<p class='backupError'>".__("An error occurred", $this->pluginID)."</p>" ; 
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
		if ($this->backupInDays()<=0) {
			$this->create_zip() ;
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
	* Tell in how many days the backup will be launched
	*
	* @return integer the number of days
	*/
	
	function backupInDays() {
		// On regarde depuis quand date  la derniere sauvegarde
		$date = @file_get_contents(WP_CONTENT_DIR."/sedlex/backup-scheduler/last_backup") ; 
		$new_date = date("Y-m-d") ; 
		$s = strtotime($new_date)-strtotime($date);
		$delta = intval($s/86400);   
		
		
		return $this->get_param("frequency")-$delta;

	}
	/** ====================================================================================================================================================
	* Callback updating the table with zip files
	*
	* @return void
	*/
	
	function javascript_checkIfBackupNeeded() {	
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
	
	/** ====================================================================================================================================================
	* Send Email with the backupfiles
	*
	* @param $attach the bachup file paths
	* @return void
	*/
	
	function sendEmail($attach) {
		if ($this->get_param('email')=='') 
			return true ;
			
		for ($i=0 ; $i<count($attach) ; $i++) {
			$message = "" ; 
			$message .= "<p>".__("Dear sirs,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
			$message .= "<p>".sprintf(__("Here is attached the %s on %s backup files for today", $this->pluginID), $i+1, count($attach))."</p><p>&nbsp;</p>" ; 
			$message .= "<p>".__("Best regards,", $this->pluginID)."</p><p>&nbsp;</p>" ; 
			
			$headers= "MIME-Version: 1.0\n" .
					"Content-Type: text/html; charset=\"" .
					get_option('blog_charset') . "\"\n";
					
			$attachments = array($attach[$i]);
			$subject = sprintf(__("Backup of %s (%s)", $this->pluginID), get_bloginfo('name') , ($i+1)."/".count($attach)) ; 
						
			// send the email
			if (wp_mail($this->get_param('email'), $subject, $message, $headers, $attachments )) {
			} else {
				return false ; 			
			} 
		}
		return true ; 
	}
	
	/** ====================================================================================================================================================
	* Export the DB in a sql file
	*
	* @param $sqlPath the path to SQL file to save
	* @return void
	*/
	function dumpMySQL($sqlPath) {
		global $wpdb ; 
		
		@file_put_contents($sqlPath, "") ; 
		
		$entete = "-- -------------------------------------------------\n";
		$entete .= "-- ".DB_NAME." - ".date("d-M-Y")."\n";
		$entete .= "-- -----------------------------------------------\n";
		
		@file_put_contents($sqlPath, $entete, FILE_APPEND) ; 
		
		$creations = "";
		
	 	$tables = $wpdb->get_results("show tables", ARRAY_N);
		foreach ($tables as $table) {
			
			$creations = "\n\n";
			$creations .= "-- -----------------------------\n";
			$creations .= "-- CREATE ".$table[0]."\n";
			$creations .= "-- -----------------------------\n";
			$creations .= $wpdb->get_var("show create table ".$table[0], 1);
			
			@file_put_contents($sqlPath, $creations, FILE_APPEND) ; 
		
			$insertions = "\n\n";
	 		$insertions .= "-- -----------------------------\n";
			$insertions .= "-- INSERT INTO ".$table[0]."\n";
			$insertions .= "-- -----------------------------\n\n";
					
			$lignes = $wpdb->get_results("SELECT * FROM ".$table[0], ARRAY_N);
			foreach ( $lignes as $ligne ) {
				$insertions .= "INSERT INTO ".$table[0]." VALUES(";
				for($i=0; $i < count($ligne); $i++) {
					if($i != 0) 
						$insertions .=  ", ";
					if ( ($wpdb->get_col_info('type', $i) == "string") || ($wpdb->get_col_info('type', $i) == "blob") )
						$insertions .=  "'";
					$insertions .= addslashes($ligne[$i]);
					if ( ($wpdb->get_col_info('type', $i) == "string") || ($wpdb->get_col_info('type', $i) == "blob") )
						$insertions .=  "'";
						
					// Si c'est trop grand on flush
					if (strlen($insertions)>4000000) {
						@file_put_contents($sqlPath, $insertions, FILE_APPEND) ; 
						$insertions = "" ; 
					}
		
				}
				$insertions .=  ");\n";
				
			}
			@file_put_contents($sqlPath, $insertions, FILE_APPEND) ; 
		}
	}
	
}

$backup_scheduler = backup_scheduler::getInstance();

?>