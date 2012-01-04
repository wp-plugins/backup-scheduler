=== Backup Scheduler ===

Author: SedLex
Contributors: SedLex
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/extend/plugins/backup-scheduler/
Tags: plugin, backup, database, schedule
Requires at least: 3.0
Tested up to: 3.2
Stable tag: trunk

With this plugin, you may plan the backup of your website

== Description ==

With this plugin, you may plan the backup of your website.

You can choose: 

* which folders you will save; 
* whether your database should be saved; 
* whether the backup is stored on the local website or sent by email (support of multi part zip files)

This plugin is under GPL licence.

= Localizations =

* Russian translation (by Slawka)
* German-Switzerland translation (by BernhardKnab and PeterDbbert)
* Polish translation (by Pablo)
* Spanish translation (by AVfoto and Javier)
* Italian translation (by PuntoCon)
* French translation (by me)

= Features of the framework = 

This plugin use SL framework.

You may translate this plugin with an embedded feature which is very easy to use by any end-users (without any external tools / knowledge).

You may also create a new plugin. You will download, from the plugin, an "hello World" plugin: You just have to fill the blank and follow the comments.

Moreover, all the plugins developped with this framework is able to use astonishing tools, such as :

* embedded SVN client (subversion) to easily commit/update the plugin in wordpress.org repository ; 
* detailled documentation of all available classes and methodes ; 
* updating the core with a single click ; 
* etc.

== Installation ==

1. Upload this folder to your plugin directory (for instance '/wp-content/plugins/')
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the 'SL plugins' box
4. All plugins developed with the SL core will be listed in this box
5. Enjoy !

== Screenshots ==

1. A list of all backup files
2. The configuration page of the plugin

== Changelog ==

= 1.1.1 =
* Improve error management and memory leakage

= 1.1.0 =
* Bug in the sql file : date and time managements were incorrect

= 1.0.9 =
* Russian translation (by Slawka)
* Add a time option for choosing the best moment to perform an automatic backup
* Display bug correction
* Add instructions to restore the backup :)

= 1.0.8 =
* German-Switzerland translation (by BernhardKnab)
* Improve memory and time management for database extraction
* Add error messages if it is impossible to read/delete/modify files

= 1.0.7 =
* Polish translation (by Pablo)

= 1.0.6 =
* Add time and memory management for constrained configuration

= 1.0.5 =
* Improving zip decompression and path 

= 1.0.4 =
* Spanish translation (by AVfoto)
* Italian translation (by PuntoCon)
* Correction of a bug that occurs when server refuse to access / directory "open_basedir" restriction

= 1.0.3 =
* Improve the English text thanks to Rene 

= 1.0.2 =
* Update of the core

= 1.0.1 =
* First release in the wild web (enjoy)

== Frequently Asked Questions ==

= To restore the backups =
* install a fresh version of Wordpress on your server ; 
* unzip the backup (actually, the zip file comprises a plurality of files i.e. a multip-part zip (zip, z01, z02, etc.). These files should be saved in a same folder and your zip program (such as winzip, winrar, ...) will do the job for you...
* replace the 'plugins' folder (in the wp-content folder) with the one in the archive ; 
* replace the 'themes' folder (in the wp-content folder) with the one in the archive ;
* replace the 'uploads' folder (in the wp-content folder) with the one in the archive ;
* replace the wp-config.php (at the root of your wordpress repository) with the one in the sedlex/backup-scheduler
* import the sql file in your database (with for instance phpmyadmin). It is recommended to save your database first.

* Where can I read more?

Visit http://www.sedlex.fr/cote_geek/
 
 
InfoVersion:b72786b6a092992807917a051994a87e