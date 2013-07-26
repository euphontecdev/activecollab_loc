App.status = {
  controllers : {},
  models      : {}
};

/**
 * Status update client side behaviour
 */
App.status.controllers.status = { 
  
  /**
   * Index page bahaviour
   */   
  index : function () {
    $(document).ready(function() {
      $('#select_user').change(function() {
        $("#status_update_archive_work_indicator").show();
        window.location = $(this).val();
      });
    });
  },
  
  /**
   * View status message page behavior
   */
  view : function() {
    $(document).ready(function() {
      var initial_status_message_reply = App.lang('Type your reply and hit Enter to post it');
      $('#status_update_details tr.status_update_reply').each(function() {
        var row = $(this);
        
        row.find('td.message textarea').each(function() {
          var status_message_reply = $(this).val(initial_status_message_reply);
          
          // Form
          status_message_reply.focus(function() {
            if(status_message_reply.attr('class').indexOf('focused') == -1) {
              status_message_reply.addClass('focused');
            } // if
            if(status_message_reply.val() == initial_status_message_reply) {
              status_message_reply.addClass('focused').val('');
            } // if
          }).blur(function() {
            if(status_message_reply.val() == '') {
              status_message_reply.removeClass('focused').val(initial_status_message_reply);
            } // if
          }).keydown(function(e) {
            if(e.keyCode == 13) {
              var status_message_reply_value = jQuery.trim(status_message_reply.val());
              if(status_message_reply_value) {
                row.find('#status_update_reply').block();
              
                $.ajax({
                  'url' : App.extendUrl(row.attr('reply_url'), { async : 1}),
                  type : 'POST',
                  data : {
                    'submitted' : 'submitted',
                    'reply[message]' : status_message_reply_value
                  },
                  success : function(response) {
                    row.find('#status_update_reply').unblock();
                    row.before(response);
                    status_message_reply.val('')[0].focus();
                  },
                  error : function(response) {
                    row.find('#status_update_reply').unblock();
                    
                    status_message_reply.val(status_message_reply_value)[0].focus();
                    alert(App.lang('We are sorry, but system failed to save your status message. Please try again later.'));
                  }
                });
              } // if
            } // if
          });
        });
      })
    });
  }
  
};

/**
 * Update dialog behavior
 */
App.widgets.StatusUpdateDialog = function() {
  
  /**
   * Submit status update URL
   *
   * @param String
   */
  var submit_status_update_url;
  
  /**
   * Status messages table
   *
   * @var jQuery
   */
  var status_update_table;
  
  /**
   * Status updates table wrapper
   *
   * @var jQuery
   */
  var status_update_table_wrapper;
  
  /**
   * Empty new message textarea value
   *
   * @param jQuery
   */
  var initial_status_message = App.lang('Type your message and hit Enter to post it');
  
  /**
   * New status message textarea
   *
   * @var jQuery
   */
  var status_message;
  
  /**
   * Initialize single status update row
   *
   * @param jQuery row
   */
  var initialize_row = function(row) {
    row.find('td.message a.status_message_reply').click(function() {
      var textarea_row = row.find('td.message tr.status_update_reply_textarea');
      var link_row = row.find('td.message tr.status_update_reply_link');
      
      var reply_textarea = textarea_row.find('textarea').keydown(function(e) {
        if(e.keyCode == 13) {
          var value = jQuery.trim(reply_textarea.val());
          
          if(value) {
            reply_textarea.block();
            submit_status_update(value, row.attr('status_update_id'));
            
            return false;
          } // if
        } // if
      });
      
      link_row.hide();
      textarea_row.show().find('textarea').focus();
      
      return false;
    });
  };
  
  /**
   * Submit status update
   *
   * @param String submit_url
   * @param String text
   * @param Function on_success
   * @param Function on_error
   */
  var submit_status_update = function(text, reply_to_id, on_success, on_error) {
    $.ajax({
      url  : App.extendUrl(submit_status_update_url, { async : 1 }),
      type : 'POST',
      data : {
        'submitted' : 'submitted',
        'status[message]' : text, 
        'status[parent_id]' : reply_to_id
      },
      'success' : function(response) {
        var row = $(response);
        initialize_row(row);
        
        var status_update_id = row.attr('status_update_id');
        
        status_update_table.find('tr[status_update_id=' + status_update_id + ']').remove();
        
        status_update_table_wrapper.show().scrollTo(0);
        status_update_table.prepend(row);
        
        var counter = 1;
        status_update_table.find('tr.status_update').each(function() {
          var new_class = counter % 2 ? 'odd' : 'even';
          $(this).removeClass('odd').removeClass('even').addClass(new_class);
          counter++;
        });
        
        status_update_table.find('tr:first td').highlightFade();
        
        if(typeof(on_success) == 'function') {
          on_success();
        } // if
      },
      'error' : function(response) {
        if(typeof(on_error) == 'function') {
          on_error();
        } // if
        
        alert(App.lang('We are sorry, but system failed to save your status message. Please try again later.'));
      }
    });
  };
  
  // Public interface
  return {
    
    /**
     * Initalize status updates dialog
     *
     * @param String add_message_url
     */
    init : function(add_message_url) {
      submit_status_update_url = add_message_url;
      status_update_table = $("#status_updates_table tbody.first_level");
      status_update_table_wrapper = $("#status_updates_dialog div.table_wrapper");
      status_message = $('#status_updates_dialog #add_status_message textarea').val(initial_status_message);
      
      // Top links
      $('.status_update_top_links li a').hover(function () {
        $('li:eq(0)', $(this).parent().parent()).text($(this).attr('title'));
      }, function () {
        $('li:eq(0)', $(this).parent().parent()).text('');
      });
      
      status_update_table.find('tr.status_update').each(function() {
        initialize_row($(this));
      });
      
      // Form
      status_message.focus(function() {
        if(status_message.attr('class').indexOf('focused') == -1) {
          status_message.addClass('focused');
        } // if
        if(status_message.val() == initial_status_message) {
          status_message.addClass('focused').val('');
        } // if
      }).blur(function() {
        if(status_message.val() == '') {
          status_message.removeClass('focused').val(initial_status_message);
        } // if
      }).keydown(function(e) {
        if(e.keyCode == 13) {
          var status_message_value = jQuery.trim(status_message.val());
          if(status_message_value) {
            $('#status_updates_dialog #add_status_message').block();
            
            submit_status_update(status_message_value, null, function() {
              $('#status_updates_dialog #add_status_message').unblock();
              status_message.val('').focus();
            }, function() {
              $('#status_updates_dialog #add_status_message').unblock();
              status_message.val(status_message_value).focus();
            });
          } // if
          
          return false;
        } // if
      });
    }
  };
  
}();

$(document).ready(function() {
  
  // check if there are new messages
  var update_status_menu_badge_item = function () {
    var count_url = App.data.path_info_through_query_string ? 
      App.extendUrl(App.data.url_base, { path_info : 'status/count-new-messages' }) :
      App.data.url_base + '/status/count-new-messages';
    
    $.ajax({
      type: "GET",
      url: count_url,
      success : function (response) {
        App.MainMenu.setItemBadgeValue('status', 'main', response);
      }
    })
  } // update_status_menu_badge_item
  setInterval(update_status_menu_badge_item, 60000 * 3); 
  
  // init menu item button
  $('#menu_item_status a').click(function() {
    var status_update_url = App.extendUrl($(this).attr('href'), { 
      async : 1 
    });
    
    App.ModalDialog.show('status_updates', App.lang('Status Updates'), $('<p><img src="' + App.data.assets_url + '/images/indicator.gif" alt="" /> ' + App.lang('Loading...') + '</p>').load(status_update_url), {
      buttons : false,
      height : 400,
      width: 600
    });
    App.MainMenu.setItemBadgeValue('status', 'main', 0);
    return false;
  });
})
