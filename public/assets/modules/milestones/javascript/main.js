App.milestones = {
  controllers : {},
  models      : {}
};

/**
 * Main milestones JS file
 */
App.milestones.controllers.milestones = {
  
  /**
   * Prepare stuff on reschedule form
   *
   * @param void
   * @return null
   */
  reschedule : function() {
    $(document).ready(function() {
      
      // Lets hide successive milestone. There must be a better way to do this 
      // but we'll leave it at this for now :)
      $('div.with_successive_milestones input[type=radio]').each(function() {
        if(this.checked && $(this).val() != 'move_selected') {
          $('div.with_successive_milestones div.successive_milestones').hide();
        }
      })
      
      // Click handler for action selectors
      $('div.with_successive_milestones input[type=radio]').click(function() {
        if($(this).val() == 'move_selected') {
          $('div.with_successive_milestones div.successive_milestones').show('fast');
        } else {
          $('div.with_successive_milestones div.successive_milestones').hide('fast');
        }
      });
    });
  }
  
}

/*BOF: task 03 | AD*/
/*function sort_page(order_by){
	try{
		var key_order_by = 'order_by';
		var key_order_by_index = -1;
		var order_by_exists = false;
		var key_sort_order = 'sort_order';
		var key_sort_order_index = -1;
		var sort_order_value = ';'
		var url_str = location.href;
		var url = url_str.substring(0, url_str.indexOf('?'));
		var query_string = url_str.substring(url_str.indexOf('?')+1);
		var values = query_string.split('&');
		for(var i=0; i<values.length; i++){
			if (values[i].indexOf(key_order_by)!=-1){
				order_by_exists = true;
				key_order_by_index = i;
			}
			if (values[i].indexOf(key_sort_order)!=-1){
				sort_order_value = values[i].substring(values[i].indexOf('=')+1);
				key_sort_order_index = i;
			}
		}
		if (order_by_exists){
			sort_order_value = (sort_order_value=='asc' ? 'desc' : 'asc');
		} else {
			sort_order_value = 'asc';
		}
		if (key_order_by_index==-1){
			values.push(key_order_by + '=' + order_by);
		} else {
			values[key_order_by_index] = key_order_by + '=' + order_by;
		}
		if (key_sort_order_index==-1){
			values.push(key_sort_order + '=' + sort_order_value);
		} else {
			values[key_sort_order_index] = key_sort_order + '=' + sort_order_value;
		}
		location.href = url + '?' + values.join('&');
	} catch(e){
		alert(e);
	}
}*/
//commented out as moved to root level
/*EOF: task 03 | AD*/