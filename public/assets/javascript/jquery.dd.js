$(document).ready(function(){
        $('select.imageList').each(function(){
            var sid = $(this).attr('id');
	// The select element to be replaced:        
	var select = $(this);
	var selectBoxContainer = $('<span>');       
        selectBoxContainer.html('<span class="selectBox" style="cursor:pointer;"></span>');
	var dropDown = $('<ul>',{className:'dropDown'});
        dropDown.attr('style','text-align:right;');
	var selectBox = selectBoxContainer.find('.selectBox');
	//BOF:mod 20120816
	/*
	//EOF:mod 20120816
         selectBox.html('<img src="'+select.attr('sel-image')+'"/>');
	//BOF:mod 20120816
	*/	
	hover_text = '';
	var split_path = select.attr('sel-image').split('/');
	switch(split_path[split_path.length-1]){
		case 'highest.gif':
			hover_text = 'Today or Tomorrow';
			break;
		case 'high.gif':
			hover_text = 'This Week';
			break;
		case 'normal.gif':
			hover_text = 'This Month';
			break;
		case 'low.gif':
			hover_text = 'This Quarter';
			break;
		case 'lowest.gif':
			hover_text = 'This Year';
			break;
		case 'ongoing.png':
			hover_text = 'Recurring Task';
			break;
		case 'hold.png':
			hover_text = 'On Hold';
			break;
	}
	selectBox.html('<img src="'+select.attr('sel-image')+'" title="' + hover_text + '" />');
	//EOF:mod 20120816

	// Looping though the options of the original select element
	select.find('option').each(function(i){            
		var optionRow = $(this);       
               
               if(optionRow.attr('data-skip')=='true'){
                return true;
                }
		var li = $('<li>');
                li.attr('style','border:0;cursor:pointer;');
                li.html(optionRow.text()+'<img src="'+optionRow.attr('data-icon')+'" />');
		li.click(function(){

			//selectBox.html(optionRow.text());
			dropDown.trigger('hide');
			// When a click occurs, we are also reflecting
			// the change on the original select element:
			select.val(optionRow.val());                        
                        $.ajax({
                            url: select.attr('url')+'&skip_layout=1', 
                            type: 'POST',
                            async: false,
                            data: {priority: optionRow.val()}, 
                            success: function(response){  
                                //alert(response);                              
                               selectBox.html('<img src="'+optionRow.attr('data-icon')+'" />');
                            }
                        });
			return false;
		});

		dropDown.append(li);
	});

	selectBoxContainer.append(dropDown.hide());
	select.hide().after(selectBoxContainer);
	// Binding custom show and hide events on the dropDown:

	dropDown.bind('show',function(){

		if(dropDown.is(':animated')){
			return false;
		}
                selectBox.addClass('expanded');
		dropDown.slideDown();

	}).bind('hide',function(){

		if(dropDown.is(':animated')){
			return false;
		}
                selectBox.removeClass('expanded');
		dropDown.slideUp();

	}).bind('toggle',function(){
		if(selectBox.hasClass('expanded')){
			dropDown.trigger('hide');
		}
		else dropDown.trigger('show');
	});
         
	selectBox.click(function(){
		dropDown.trigger('toggle');
		return false;
	});

	// If we click anywhere on the page, while the
	// dropdown is shown, it is going to be hidden:

	$(document).click(function(){
		dropDown.trigger('hide');
	});
    });
});