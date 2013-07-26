<div id="editor_picker_dialog">
  <ul class="top_tabs">
  {if !$disable_upload}
    <li class="selected"><a href="#" id="tab_upload_image"><span>{lang}Upload Image{/lang}</span></a></li>
  {/if}
    <li><a href="#" id="tab_link_image" class="{if $disable_upload}selected{/if}"><span>{lang}Link Existing{/lang}</span></a></li>
    <li><a href="#" id="tab_paste_image" class=""><span>{lang}Paste Image{/lang}</span></a></li>
  </ul>
  
  <div class="top_tabs_object_list dialog_tabs_content">
    {if !$disable_upload}
    <div id="container_tab_upload_image">
      {form enctype="multipart/form-data" action="$image_picker_url" method="post" id="upload_image_form"}
      
        {wrap field=image}
          {label for=image required=yes}Name{/label}
          {file_field name='image' id=image class='title required'}
        {/wrap}
               
        <input type="hidden" value="upload" name="widget_action"/>
          
        {wrap_buttons}
          {submit}Upload and Insert{/submit}
        {/wrap_buttons}
      {/form}
    </div>
    {/if}
    
    <div id="container_tab_link_image">
      {form enctype="multipart/form-data" action="$image_picker_url" method="post" id="link_image_form"}
        {wrap field=image}
          {label for=image required=yes}Image URL{/label}
          {text_field name='image' id=image class='title required'}
        {/wrap}
        
        {wrap_buttons}
          {submit}Insert Image{/submit}
        {/wrap_buttons}      
      {/form}
    </div>
    
    <div id="container_tab_paste_image">
		<applet code="Main" name="Image Uploader" width="440" height="200" hspace="0" vspace="0" align="middle" archive="http://www.projects.ffbh.org/imageuploader/incero-dot-com-uploader.jar" id="Image Uploader">
          <!-- Session string -->
          <param name="session_string" value="" />
          <!-- The applet will redirect the user to this page when upload completes. To disable redirection, set value="" -->
          <param name="external_redir" value="" />
          <param name="upload_url" value="http://www.projects.ffbh.org/imageuploader/simpleupload.php" />
        </applet>
      {form enctype="multipart/form-data" method="post" id="paste_image_form"}
        {wrap_buttons}
          {submit}Insert Latest Image{/submit}
        {/wrap_buttons}      
      {/form}
    </div>
  </div>
</div>

<script type="text/javascript">
  App.widgets.EditorImagePicker.init('#editor_picker_dialog');
</script>