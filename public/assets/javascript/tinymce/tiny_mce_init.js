// uniform validation helper
window.tiny_value_present = function(field, caption) {
  if(tinyMCE && tinyMCE.activeEditor) {
    if(tinyMCE.activeEditor.getContent().length < 1) {
      return App.lang('Required');
    } // if
  } else {
    if(field.val() == '') {
      return App.lang('Required');
    } // if
  }
  return true;
}

var tiny_mce_editor;
var tiny_mce_editor_iframe;
var tiny_mce_editor_span_container;

// initialize editor
window.tinyMCE.init({
  mode: "specific_textareas",
  textarea_trigger : 'mce_editable',
  strict_loading_mode: tinymce.isWebKit,
  width: "100%",
  //plugins: "safari,ac_image_dialog,ac_link_dialog,ac_paste_dialog",
  //plugins: "safari,ac_image_dialog,ac_link_dialog,ac_paste_dialog,autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template", 
    plugins: "safari,ac_image_dialog,ac_link_dialog,ac_paste_dialog,autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
  browsers : "msie,gecko,opera,safari",
  /*paste_auto_cleanup_on_paste: true, 
  paste_preprocess : function(pl, o){
	try{
		//tinyMCE.activeEditor.focus();
		tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent() + o.content);
		//tinyMCE.activeEditor.save();
		
	} catch(e){
		alert(e);
	}
  }, */
  accessibility_focus : false,
  gecko_spellcheck: true,
  remove_linebreaks : true,
  apply_source_formatting : false,
  //BOF:mod 20120717
  /*
  //EOF:mod 20120717
  convert_newlines_to_brs : true,
  //BOF:mod 20120717
  */
  convert_newlines_to_brs : false,
  //EOF:mod 20120717
  relative_urls : false,
  absolute_urls : true,
  convert_urls : false,
  init_instance_callback : "tinyMCEPostInit",
  theme : "advanced",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_path : false,
  theme_advanced_statusbar_location : "bottom",
  /*theme_advanced_buttons1 : "undo, redo, separator, formatselect, styleselect, bold, italic, underline, strikethrough, separator, bullist, numlist, separator, outdent, indent, separator, ac_link_dialog_insert, unlink, ac_image_dialog, separator, ac_paste_dialog, removeformat",
  theme_advanced_buttons2 : "",
  theme_advanced_buttons3 : "",*/
		//theme_advanced_buttons1 : "code,separator,save,newdocument,preview,separator,template,separator,cut,copy,paste,pastetext,ac_paste_dialog,pasteword,separator,print,spellchecker,separator,undo,redo,separator,search,replace,separator,removeformat,sub,sup,separator,numlist,bullist,separator,outdent,indent,blockquote,separator,pagebreak",
		theme_advanced_buttons1 : "code,separator,save,newdocument,preview,separator,template,separator,cut,copy,paste,pastetext,ac_paste_dialog,pasteword,separator,print,separator,undo,redo,separator,search,replace,separator,removeformat,sub,sup,separator,numlist,bullist,separator,outdent,indent,blockquote,separator,pagebreak",
		theme_advanced_buttons2 : "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,ac_link_dialog_insert,unlink,anchor,separator,ac_image_dialog,media,tablecontrols,hr,emotions",
		theme_advanced_buttons3 : "styleselect,separator,formatselect,separator,fontselect,separator,fontsizeselect,separator,forecolor,backcolor,separator,fullscreen,help",
		theme_advanced_enable : "spellchecker", 
  theme_advanced_resizing : true,
  theme_advanced_resize_horizontal : false,
  theme_advanced_styles : App.lang('Title') + '=title;' + App.lang('Subtitle') + '=subtitle;' + App.lang('Quote') + '=quote;' + App.lang('Important') + '=important;' + App.lang('Note') + '=note;' + App.lang('Updated') + '=updated',
  valid_elements : "" +
    "#p[id|style|dir|class|align]," + 
    "+a[href|target|title|class]," + 
    "-strong/-b[class|style]," + 
    "-em/-i[class|style]," + 
    "-strike[class|style]," + 
    "-u[class|style]," + 
    "-ol[class|style]," + 
    "-ul[class|style]," + 
    "-li[class|style]," + 
     "br," + 
     "img[id|dir|lang|longdesc|usemap|style|class|src|border|alt=|title|hspace|vspace|width|height|align]," + 
    "-sub[style|class]," + 
    "-sup[style|class]," + 
    "-blockquote[dir|style]," + 
    "-div[id|dir|class|align|style]," + 
    "-span[style|class|align]," + 
    "-pre[class|align|style]," + 
     "address[class|align|style]," + 
    "-h1[id|style|dir|class|align]," + 
    "-h2[id|style|dir|class|align]," + 
    "-h3[id|style|dir|class|align]," + 
    "-h4[id|style|dir|class|align]," + 
    "-h5[id|style|dir|class|align]," + 
    "-h6[id|style|dir|class|align]," + 
     "hr[class|style],"+"-table[border=0|cellspacing|cellpadding|width|frame|rules|"
                                + "height|align|summary|bgcolor|background|bordercolor],-tr[rowspan|width|"
                                + "height|align|valign|bgcolor|background|bordercolor],tbody,thead,tfoot,"
                                + "#td[colspan|rowspan|width|height|align|valign|bgcolor|background|bordercolor"
                                + "|scope],#th[colspan|rowspan|width|height|align|valign|scope]",  
 //BOF:mod 20111220
 setup : function (ed){
	/*ed.onSetContent.add(function(ed, o){
		$('#taskSummary').val(tinyMCE.activeEditor.getContent());
	});*/
	/*ed.onSubmit.add(function(ed, e){
		$('#taskSummary').val(tinyMCE.activeEditor.getContent());
	});*/
	ed.onEvent.add(function(ed, e){
		if (tinyMCE.activeEditor.editorId=='taskSummary'){
			$('#taskSummary').val(tinyMCE.activeEditor.getContent());
		}
	});
     //ed.onGetContent.add(function(ed, o){
     ed.onSaveContent.add(function(ed, o){
		 //if (tinyMCE.activeEditor.editorId=='taskSummary'){
			//alert(tinyMCE.activeEditor.getContent());
			//alert(tinyMCE.editors['taskSummary'].getContent());
			/*var msg = '';
			for (prop in tinyMCE.activeEditor.getBody()){
				msg += prop + ' | ';
			}
			alert(msg);*/
			//alert(tinyMCE.activeEditor.editorId);
		// }
         var cur_content =  o.content;
         if (cur_content.indexOf('base64')!=-1){
            var temp = '';
            var images = new Array();
            var new_images = new Array();
            var split_vals = cur_content.split('<img ');
            if (split_vals.length>0){
                var count = -1;
                for(var i=1; i<split_vals.length; i++){
                    temp = '<img ' + split_vals[i].substring(0, split_vals[i].indexOf('>')+1);
                    if (temp.indexOf('base64')!=-1){
                        count++;
                        images[count] = temp;
                        new_images[count] = images[count].substring(0, images[count].indexOf('src')) + 'src="::IMAGE_URL::"' + images[count].substring(images[count].indexOf('"', images[count].indexOf('src')+5)+1, images[count].length);
                        
                    }
                }
                if (images.length>0){
                    var content_types = new Array();
                    var streams = new Array();
                    var file_names = new Array();
                    var d = new Date();

                    for (i=0; i<images.length; i++){
                        temp = images[i].substring(images[i].indexOf('src="')+5, images[i].indexOf('"', images[i].indexOf('src="')+5));
                        content_types[i] = temp.substring(temp.indexOf(':')+1, temp.indexOf(';'));
                        streams[i] = temp.substring(temp.indexOf(',')+1, temp.length);
                        file_names[i] = d.getTime() + '_' + i + '.' + content_types[i].substring(content_types[i].indexOf('/')+1, content_types[i].length);
                        new_images[i] = new_images[i].replace('::IMAGE_URL::', App.data.homepage_url.replace('public', 'imageuploader') + '/' +  file_names[i]);
                    }
                    
                    for(var i=0; i<images.length; i++){
                        if (cur_content.indexOf(file_names[i])==-1){
                            cur_content = cur_content.replace(images[i], new_images[i]);                            
                            $('#loading-image').show();
                            $.ajax({
                                url: App.extendUrl(App.data.image_uploader_url, {asynch: 0, skip_layout: 1}), 
                                type: 'POST',
                                async: false,
                                data: {content_type : content_types[i], stream : streams[i], file_name: file_names[i]}, 
                                success: function(response){                                    
                                }
                            });
                            
                        }
                    }
                    o.content = cur_content;                    
                }
             }
         }         
     });
 }
 //EOF:mod 20111220
});

adjustHeight = function () {
  if (!window.tinyMCE.activeEditor._doc_element || !window.tinyMCE.activeEditor._iframe_element) {
    return false;
  } // if
  
  var inner_body_height;
  var iframe_height = window.tinyMCE.activeEditor._iframe_element.height();
  if ($.browser.msie) {
    // IE
    inner_body_height = window.tinyMCE.activeEditor._body_element.attr('scrollHeight');
  } else if ($.browser.safari && (App.compareVersions('530', $.browser.version) == 1)) {
    // SAFARI AND CHROME (webkit < 530)
    var last_element = window.tinyMCE.activeEditor._body_element.find('> *:last');
    if (last_element.length > 0) {
      var last_element_position = last_element.position();
      inner_body_height = last_element_position.top + last_element.height() + parseInt(last_element.css('marginBottom')) + parseInt(last_element.css('paddingBottom')) + 20;
    } else {

      inner_body_height = 0;
    } // if
  } else {
    // OTHERS
    inner_body_height = window.tinyMCE.activeEditor._body_element.height();
  } // if
   
  var new_height = inner_body_height + 25;   
  //if ( inner_body_height > iframe_height ) {
  var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
  if (!is_chrome){
      if ( inner_body_height + 20 > iframe_height ) {
        window.tinyMCE.activeEditor._iframe_element.css('height', new_height + 'px');
		//BOF:mod 20130128
		var reply_mode = parseInt($('div.quick_comment_form').css('top'))>0 ? true : false;
		if (reply_mode) $('div.quick_comment_form').css('top', parseInt($('div.quick_comment_form').css('top'))+25);
		//EOF:mod 20130128
      } // if
      setTimeout("adjustHeight()",250);
  }
};

function tinyMCEPostInit(ed) {
  tiny_mce_editor = $(ed);
  tiny_mce_editor_iframe = $('#' + tiny_mce_editor.attr('id') + '_ifr');
  
  // object on which we hook blur i focus events
  var hook_nod = ed.settings.content_editable ? ed.getBody() : (tinymce.isGecko ? ed.getDoc() : ed.getWin());
  
  var tiny_mce_editor_span_container = $(ed.contentAreaContainer).parents('span.mceEditor');
  
  // find objects that are important for uniform validation
  var parent_form = $(ed.contentAreaContainer);
  while(parent_form[0].nodeName != 'FORM') {
    parent_form = parent_form.parent();
  } // if
  var textarea = $(ed.getElement());
  
  // variables needed for resizing
  ed._doc_element = ed.getDoc();
  ed._body_element = $(ed.getDoc()).find('body:first');
  ed._iframe_element = $(ed.contentAreaContainer).find('iframe:first');
  
  // hook events
  tinymce.dom.Event.add(hook_nod, 'focus', function(e) {
    UniForm.focus_field(parent_form,textarea);
    if (!tiny_mce_editor_span_container.is('expanded')) {
      tiny_mce_editor_span_container.addClass('expanded');
    } // if
  });
  tinymce.dom.Event.add(hook_nod, 'blur', function(e) {
    UniForm.validate(parent_form, false);
  });
  tinymce.dom.Event.add(hook_nod, 'keypress', function(event) {
    if ((event.keyCode == 37) && (event.metaKey == true)) {
      return false;
    } // if
  });
  
  if ((textarea.attr('auto_expand') && (textarea.attr('auto_expand') != 'no'))) {
    ed._iframe_element.css('overflow-y', 'hidden');
    adjustHeight();
  } // if
} // tinyMCEPostInit