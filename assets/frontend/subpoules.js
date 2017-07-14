jQuery(document).ready(function() {
    
    jQuery('#member_of_subpoules tbody').sortable({
	update: function(event, ui) {
	    var $table = jQuery('#member_of_subpoules tbody');

	    $table.sortable();
	    $table.sortable('serialize');
	    console.log($table);
	   // alert(jQuery("#member_of_subpoules tbody").sortable().sortable("serialize"));
	    
	    var reorder = "";
	    
	    jQuery('#member_of_subpoules>tbody>tr').each(function () {
		//processing this row
		reorder += jQuery(this).attr('poule-id') + ',';
	    });
	    
	    var data = {
		action: "poule_subpoule_reorder",
		order: reorder
	    }
	    
	    jQuery.ajax({
		type: "POST",
		url: Ajax.url,
		data: data,
                dataType : 'json',
	        //contentType: 'application/json; charset=utf-8',
//		dataType: 'json',
		success: function (response) {
		    console.log(response);
		    if(response.code == 1){
			
		    }else{
			
		    }
		},
		error: function (response) {
		    console.log(response);
		}
	    });
	    //ajax om poule volgorde op te slaan
	    
//	    //When the item postion is changed while dragging a item, get it's position
//	    var start_pos = ui.item.data('start_pos');
//
//	    //get the direction of the drag motion
//	    var dragDirection = start_pos < ui.placeholder.index() ? "down" : "up";
//	    ui.item.data('drag_direction', dragDirection);
	},
	
    });
    
    jQuery( ".subpoules tbody" ).disableSelection();
    
    var poule_id = 0;
    
    jQuery( "#subpoule_add" ).click(function() {
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: jQuery('#subpoule_form_add').serialize(),
//            dataType : 'json',
           // contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (response) {
                console.log(response);
		console.log(jQuery('#subpoule_form_add').serialize());
                if(response.code == 1){
                    window.location="?subpouleid="+response.pouleid;
                }else{
                    jQuery.each (response.fields, function (index) {
                        jQuery("#own_"+response.fields[index]).addClass("has-error");
                        if(response.fields[index] == "undefined"){
                            jQuery("#own_name").addclass("has-error");
                        }
                    });
                    
//                    if(response.description == "undefined"){
//                        jQuery("#own_description").addclass("has-error");
//                    }
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    });
    
    jQuery( "#subpoule_edit" ).click(function() {
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: jQuery('#subpoule_form_edit').serialize(),
//            dataType : 'json',
           // contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (response) {
                console.log(response.code);
                if(response.code == "1"){
                    //window.location="?subpouleid="+response.pouleid;
                }else{
                    jQuery.each (response.fields, function (index) {
                        jQuery("#own_"+response.fields[index]).addClass("has-error");
//                        if(response.fields[index] == "undefined"){
//                            jQuery("#own_name").addclass("has-error");
//                        }
                    });
                    
//                    if(response.description == "undefined"){
//                        jQuery("#own_description").addclass("has-error");
//                    }
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    });
    
    jQuery( "#accept_subpoule" ).click(function() {
    	var data = {
    		action: "poule_subpoule_invitations",
    		pouleid: jQuery(this).attr("pouleid"),
    		key: jQuery(this).attr("key"),
    		type: "accept"
    	}
        
	var row = this;
	
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: data,
            dataType: 'json',
            success: function (response) {
                
		
		jQuery("#member_of_subpoules").find('tbody')
                    .append(jQuery('<tr>')
                        .append(jQuery('<td>').text(jQuery("#invitation_name").text()))
                        .append(jQuery('<td>').text(jQuery("#invitation_inviter").text()))
			.append(jQuery('<td>').html('<a class="btn btn-default" poule-id="'+jQuery(row).attr("pouleid")+'" id="deletesubpoule" hash="'+response.deleteurl+'" href="#">'+response.deletetitle+'</a>'))
                    );
                
		//_e('Delete')?></a>
		
		var tableRow = jQuery(row).closest('tr');
                tableRow.remove();
		
            },
            error: function (response) {
            }
        });
    });
    
    jQuery( "#delete_subpoule" ).click(function() {
    	var data = {
    		action: "poule_subpoule_invitations",
    		pouleid: jQuery(this).attr("pouleid"),
    		key: jQuery(this).attr("key"),
    		type: "delete"
    	}
        
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: data,
            dataType: 'json',
            success: function (response) {
                console.log(response);
                
		jQuery('#delete_subpoule').parent().parent().remove();
            },
            error: function (response) {
            }
        });
    });
    
    jQuery( "#edit_group" ).click(function() {
        var data = {
            action: "poule_get_subpoule_info",
            pouleid: jQuery(this).attr("sub-poule-id")
    	}
    	
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: data,
            dataType: 'json',
            success: function (response) {
                console.log(response);
                
                if(response.code == 1){
                    
                    //plaats de gegevens
                    jQuery("#edit_name").val(response.name);
                    jQuery("#poule_id").val(response.poule_id);
                    jQuery("#edit_description").val(response.description);
                    jQuery("#add_user_edit").attr('poule-id', response.poule_id);
                    poule_id = response.poule_id;
                    jQuery("#users > tbody").html("");
                    jQuery.each(response.users, function (index) {
                        jQuery("#users").find('tbody')
                            .append(jQuery('<tr>')
                                .append(jQuery('<td>').text(response.users[index].name))
                                .append(jQuery('<td>').text(response.users[index].status))
                            );
                    });
                    
                }
            },
            error: function (response) {
            }
        });
                        
        //modal vullen dat ajax data
        
        //data-toggle="modal" data-target="#edit_subpoule"
        jQuery('#edit_subpoule').modal('show');
    });
    
    jQuery( "#add_user_edit" ).click(function() {
        var data = {
            action: "poule_add_user_to_subpoule",
            pouleid: poule_id,
            user: jQuery("#user").val()
    	}
    	
        console.log(data);
        
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: data,
            dataType: 'json',
            success: function (response) {
                console.log(response);
                
                if(response.code == 1){
                    jQuery("#users").find('tbody')
                        .append(jQuery('<tr>')
                            .append(jQuery('<td>').text(response.user))
                            .append(jQuery('<td>').text(response.status))
                        );
                }else if(response.code == 2){
                    jQuery("#message_add_user").text(response.message);
                    jQuery('#add_user').modal('show');
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    });
    
    jQuery("#subpoule_add_user").click(function(){
        
        var data = {
            action: "poule_add_user",
            poule_id: jQuery("#add_user_edit").attr("poule-id"),
            full_name: jQuery("#full_name").val(),
            user_email: jQuery("#user_email").val(),
            email_message: jQuery("#email_message").val()
    	}
    	
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: data,
            dataType: 'json',
            success: function (response) {
                console.log(response);
            },
            error: function (response) {
                console.log(response);
            }
        });
        
    });
    
    jQuery("#change_podium").change(function () {   
//	alert(jQuery(this).val());
    	location.href = poule_url+jQuery(this).val();
    });
    
    jQuery("#deletesubpoule").click(function(){
	var data = {
	    action: "poule_subpoule_delete",
	    pouleid: jQuery(this).attr("poule-id"),
	    type: "delete",
	    hash: jQuery(this).attr("hash")
    	}
        
        jQuery.ajax({
            type: "POST",
            url: Ajax.url,
            data: data,
            dataType: 'json',
            success: function (response) {
                console.log(response);
		
		if(response.code == 1){
		    jQuery('#deletesubpoule').parent().parent().remove();
		}
            },
            error: function (response) {
            }
        });
    });
});