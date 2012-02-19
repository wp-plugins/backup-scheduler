=== Backup Scheduler ===

Author: SedLex
Contributors: SedLex
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/extend/plugins/backup-scheduler/
Tags: backup, schedule, plugin, save, database, zip
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: trunk

With this plugin, you may plan the backup of your entire website (folders, files and/or database).

== Description ==

With this plugin, you may plan the backup of your entire website (folders, files and/or database).

You can choose: 

* which folders you want to save; 
* the frequency of the backup process; 
* whether your database should be saved; 
* whether the backup is stored on the local website, sent by email or stored on a distant FTP (support of multipart zip files)

This plugin is under GPL licence

= Localization =

* German (Switzerland) translation provided by PeterDbbert, BernhardKnab
* English (United States), default language
* Spanish (Spain) translation provided by Javier, AVfoto, charliechin
* French (France) translation provided by SedLex
* Indonesian (Indonesia) translation provided by Faleddo
* Italian (Italy) translation provided by PuntoCon
* Dutch (Netherlands) translation provided by Matrix
* Polish (Poland) translation provided by Opti, Lukasz, pablo
* Portuguese (Portugal) translation provided by FranciscoRocha
* Russian (Russia) translation provided by GerinG, Slawka
* Swedish (Sweden) translation provided by 
* Thai (Thailand) translation provided by 
* Chinese (People's Republic of China) translation provided by YiscaJoe

= Features of the framework =

This plugin uses the SL framework. This framework eases the creation of new plugins by providing incredible tools and frames.

For instance, a new created plugin comes with

* A translation interface to simplify the localization of the text of the plugin ; 
* An embedded SVN client (subversion) to easily commit/update the plugin in wordpress.org repository ; 
* A detailled documentation of all available classes and methodes ; 
* etc.

Have fun !

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

= 1.2.5 =
* Improving the translations management

= 1.2.4 =
* Tuning to be able to work with very huge database

= 1.2.3 =
* Bug with NULL values in the database

= 1.2.1 =
* Portuguese translation added (by FranciscoRocha)

= 1.2.0 =
* FTP support
* Full site backup is now possible
* Bug correction when SQL has NULL value

= 1.1.2 =
* Add a link to delete manually the backup (feature requested by Mirza)
* You can also force a new update without sending the emails

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
* unzip the backup (actually, the zip file comprises a plurality of files i.e. a multi-part zip (zip, z01, z02, etc.). These files should be saved in a same folder and your zip program (such as winzip, winrar, ...) will do the job for you...
* If you have configured to save the entire installation, replace all the wordpress files by the one in the zip file and import the SQL files (at the root of the zip file, the files named *.sql1, *sql2, etc.) in your database (with for instance phpmyadmin). It is recommended to save your database first ;
* In other cases, replace the 'plugins',  'themes', 'uploads' folders (in the wp-content folder) with the one in the archive, replace the wp-config.php (at the root of your wordpress repository) with the one at the root of the zip file and  import the SQL files (at the root of the zip file, the files named *.sql1, *sql2, etc.) in your database (with for instance phpmyadmin). It is recommended to save your database first.

* Where can I read more?

Visit http://www.sedlex.fr/cote_geek/
 
 
InfoVersion:dbe4d98002a25344113bf9fb4fdf9adf