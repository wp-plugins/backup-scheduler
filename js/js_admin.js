/* =====================================================================================
*
*  Init a backup
*
*/

function initForceBackup(only) {
	jQuery("#wait_backup").show();
	jQuery("#backupButton").attr('disabled', 'disabled');
	jQuery("#backupButton2").attr('disabled', 'disabled');
	
	var arguments = {
		action: 'initBackupForce'
	} 
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		jQuery("#backupInfo").html(response);
		forceBackup(only) ; 
	});    
}

/* =====================================================================================
*
*  Force a backup
*
*/

function forceBackup(only) {
	
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
				no_mail: only,
				action: 'updateBackupTable'
			} 	
			jQuery.post(ajaxurl, arguments2, function(response) {
				jQuery("#backupInfo").html("");
				jQuery("#zipfile").html(response);
				jQuery("#backupButton").removeAttr('disabled');
				jQuery("#backupButton2").removeAttr('disabled');
				jQuery("#wait_backup").hide();
			}) ; 
		} else if ((""+response+ "").indexOf("backupError") !=-1) {
			jQuery("#backupInfo").html(response);
			jQuery("#backupButton").removeAttr('disabled');
			jQuery("#backupButton2").removeAttr('disabled');
			jQuery("#wait_backup").hide();
		} else if ((""+response+ "").indexOf("error") !=-1) {
			jQuery("#backupInfo").html(response);
			jQuery("#backupButton").removeAttr('disabled');
			jQuery("#backupButton2").removeAttr('disabled');
			jQuery("#wait_backup").hide();
		} else if ((""+response+ "").indexOf("Error") !=-1) {
			jQuery("#backupInfo").html(response);
			jQuery("#backupButton").removeAttr('disabled');
			jQuery("#backupButton2").removeAttr('disabled');
			jQuery("#wait_backup").hide();
		} else {
			if (typeof(response)=='string') {
				valeur = response.split(" ") ; 
				valeur = valeur[0].split("/") ; 
				progressBar_modifyProgression(Math.floor(valeur[0]/valeur[1]*100));
				progressBar_modifyText(response);
				window.setTimeout(function() { 	forceBackup(only)  }, 200);
			} else {
				jQuery("#backupInfo").html("TimeOut problem");
				jQuery("#backupButton").removeAttr('disabled');
				jQuery("#backupButton2").removeAttr('disabled');
				jQuery("#wait_backup").hide();
			}
		}
	}).error(function(jqXHR, textStatus, errorThrown) { 
		jQuery("#backupInfo").html(textStatus+" " + errorThrown);
		jQuery("#backupButton").removeAttr('disabled');
		jQuery("#backupButton2").removeAttr('disabled');
		jQuery("#wait_backup").hide();
	});    
}

/* =====================================================================================
*
*  Delete a backup
*
*/

function deleteBackup(racineF) {	
	var arguments = {
		action: 'deleteBackup',
		racine: racineF
	} 
	
	//POST the data and append the results to the results div
	jQuery.post(ajaxurl, arguments, function(response) {
		if (((""+response+ "").indexOf("error") !=-1)||((""+response+ "").indexOf("Error") !=-1)) {
			alert(response);
		} else {
			jQuery("#zipfile").html(response);
		}
	});    
}