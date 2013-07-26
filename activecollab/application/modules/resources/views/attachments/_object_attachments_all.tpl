<div class="resource object_attachments object_section" id="object_attachments_{$_object_attachments_object->getId()}">

  <div class="body {if $_object_attachments_brief}brief{else}full{/if}">
  {if !is_foreachable($_object_attachments)}
    <p class="details center files_moved_to_trash">{lang}There are no files attached to this object{/lang}</p>
  {/if}       
    <div class="full_files_view" style="display: {if $_object_attachments_brief}none{else}block{/if};">
      <table>
        <tbody>
        {assign_var name='_object_attachments_cycle_name'}object_attachments_cycle_{$_object_attachments_object->getId()}{/assign_var}
        {foreach from=$_object_attachments item=_attachment}
          {include_template name=_object_attachments_row module=resources controller=attachments}
        {/foreach}
        </tbody>
      </table>
    </div>
       {*//BOF-20120228SA*}
   {if $_total_attachments > 0}
    <p id="show_all_ticket_attachments"><a href="{$active_ticket->getAttachmentsUrl()}">{lang total=$_total_attachments}Show all :total attachments{/lang}</a></p>
  {/if}
    {*//EOF-20120228SA*}
    {if $_object_attachments_object->canEdit($logged_user)}
      <div class="actions">
        <div class="attach_another_file" {if $_object_attachments_show_header}style="display: none"{/if}>
          {form action=$_object_attachments_object->getAttachmentsUrl() method=post enctype="multipart/form-data" class=object_resource_form}
            {wrap field=file}
            {if !$_object_attachments_show_header}
              {label}Attach a File{/label}
            {/if}
              {attach_files}
            {/wrap}
            {button type=submit}Submit{/button}
          {/form}
        </div>
      </div>
    {/if}    
  </div>
</div>
<script type="text/javascript">
  App.resources.ObjectAttachments.init('object_attachments_{$_object_attachments_object->getId()}');
</script>