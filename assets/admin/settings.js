jQuery(document).ready(function() {
    
    jQuery( "#reset_delete_phases" ).click(function() {
	
	var r=confirm("Are you sure?");
	if (r==true){
	    //ajax
	    var data = {
		action: "poule_reset_phase_delete"
	    }

	    jQuery.ajax({
		type: "POST",
		url: Ajax.url,
		data: data,
		dataType: 'json',
		success: function (response) {
		},
		error: function (response) {
		}
	    });
	}
	
    });
    
    jQuery( "#reset_add_wk_phases" ).click(function() {
	
	var r=confirm("Are you sure?");
	if (r==true){
	    //ajax
	    var data = {
		action: "poule_reset_add_phases_wk"
	    }

	    jQuery.ajax({
		type: "POST",
		url: Ajax.url,
		data: data,
		dataType: 'json',
		success: function (response) {
		},
		error: function (response) {
		}
	    });
	}
	
    });
    
    jQuery( "#reset_add_ek_phases" ).click(function() {
	
	var r=confirm("Are you sure?");
	if (r==true){
	    //ajax
	    var data = {
		action: "poule_reset_add_phases_ek"
	    }

	    jQuery.ajax({
		type: "POST",
		url: Ajax.url,
		data: data,
		dataType: 'json',
		success: function (response) {
		},
		error: function (response) {
		}
	    });
	}
	
    });
    
    jQuery( "#reset_delete_official_result" ).click(function() {
	
	var r=confirm("Are you sure?");
	if (r==true){
	    //ajax
	    var data = {
		action: "poule_reset_delete_official_result"
	    }

	    jQuery.ajax({
		type: "POST",
		url: Ajax.url,
		data: data,
		dataType: 'json',
		success: function (response) {
		},
		error: function (response) {
		}
	    });
	}
	
    });
});