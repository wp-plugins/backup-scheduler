/* =====================================================================================
*
*  Init a backup
*
*/

function initForceBackup() {
	jQuery("#wait_backup").show();
	jQuery("#backupButton").attr('disabled', 'disabled');
	
	var arguments = {
		action: 'initBackupForce'
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#backupInfo").html(response);
		forceBackup() ; 
	});    
}
/* =====================================================================================
*
*  Force a backup
*
*/

function forceBackup() {
	
	var arguments = {
		action: 'backupForce'
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		if ((""+response+ "").indexOf("backupEnd") !=-1) {
			progressBar_modifyProgression(100);
			progressBar_modifyText("");
			jQuery("#backupEnd").html(response);
			var arguments2 = {
				action: 'updateBackupTable'
			} 	
			jQuery.post(ajaxurl, arguments2, function(response) {
				jQuery("#backupInfo").html("");
				jQuery("#zipfile").html(response);
				jQuery("#backupButton").removeAttr('disabled');
				jQuery("#wait_backup").hide();
			}) ; 
		} else if ((""+response+ "").indexOf("backupError") !=-1) {
			jQuery("#backupInfo").html(response);
			jQuery("#backupButton").removeAttr('disabled');
			jQuery("#wait_backup").hide();
		} else {
			valeur = response.split("/") ; 
			progressBar_modifyProgression(Math.floor(valeur[0]/valeur[1]*100));
			progressBar_modifyText(response);
			window.setTimeout(function() { 	forceBackup()  }, 200);
		}
	});    
}