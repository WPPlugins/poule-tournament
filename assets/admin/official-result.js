jQuery(document).ready(function() {
    
    var _penalties = null;
    
    
    jQuery('.inputresult').bind('click keyup', function(event) {
        var penalties = false;
        
        if(_penalties == null){
	    
	    for(var i = 0; i < poule.penalties.length; i++){
		if(poule.current_phase == poule.penalties[i].slug){
		    penalties = true;
		}
	    }
            _penalties = penalties;
        
        }else{
            penalties = _penalties; 
        }
        
        if(penalties){

            var match = jQuery(this).attr("match_id");
            var number = jQuery(this).attr("number");

            var id;
            if(number == 1){
                id = "score_" + match + "_" + 2;
            }else{
                id = "score_" + match + "_" + 1;
            }
	    
	    if(jQuery("#"+id).val() == this.value && this.value.length != 0){
		jQuery("#hidden_"+match).removeAttr( "hidden" );
            }else{
		jQuery("#hidden_"+match).attr("hidden","hidden");
	    }

        }
    });
});