jQuery(document).ready(function() {
    jQuery('#pouledelete').click(function(e){
        var data = {
            action: 'poule_delete_group',
            phase: poule_phase,
            group: poule_group
        };
        
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            //contentType: "application/x-www-form-urlencoded",
//            dataType: "json",
            success: function (response) {
                window.location="?post_type=country&page=matches&phase="+poule_phase;
//                alert(response.result);
            },
            error: function (response) {
//                alert(response.result);
            }
        });
    });
});