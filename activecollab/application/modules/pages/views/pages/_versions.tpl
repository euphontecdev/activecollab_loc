{* 5/5/2012 (SA) Ticket #825: limit number of versions visible*}
{if is_foreachable($_versions)}
<div class="resource object_revisions object_section">
  <div class="head">
    <h2 class="section_name"><span class="section_name_span">
      <span class="section_name_span_span">{lang}Older Versions{/lang} ({$_versions|@count})</span>
      <ul class="section_options">
		<li>{link href=javascript:void(0) id=lnk_show}Show{/link}</li>
        <li>{link href=$_versions_page->getCompareVersionsUrl()}Compare Versions{/link}</li>
      </ul>
      <div class="clear"></div>
    </span></h2>
  </div>
  
  
  <div class="body old_versions" style="display:none;">
    <table class="revisions_table" id="page_versions">
      <tbody>
      {foreach from=$_versions item=_version name=ver}
      	{if $smarty.foreach.ver.index < 10}
        <tr class="{cycle values='odd,even'}">
		  {*}BOF:mod 20121010
          <td class="revision_num">
		  <a href="{$_version->getViewUrl()}">#{$_version->getVersion()}</a>
		  EOF:mod 20121010{*}
		  {*}BOF:mod 20121010{*}
		  <td class="revision_num" style="width:200px;">
		  #{$_version->getVersion()}
		  &nbsp;
		  <a href="{assemble route=show_description project_id=$active_project->getId() object_id=$active_page->getId() version_id=$_version->getVersion() }">View</a>
		  &nbsp;
		  <a href="{$_version->getViewUrl()}">Compare</a>
		  &nbsp;
		  <a href="{assemble route=print_old_versions project_id=$active_project->getId() object_id=$active_page->getId() version_id=$_version->getVersion() }">Print</a>
		  {*}EOF:mod 20121010{*}
		  </td>
          <td class="author">{action_on_by user=$_version->getCreatedBy() datetime=$_version->getCreatedOn()}</td>
          <td class="options">
          {if $_versions_page->canEdit($logged_user)}
            {link href=$_versions_page->getRevertUrl($_version) title='Revert to this version' confirm='Are you sure that you want to revert back to this version?' method=post}<img src="{image_url name=revert-gray.gif module=pages}" alt="" />{/link}
          {/if}
          {if $_version->canDelete($logged_user)}
            {link href=$_version->getDeleteUrl() title='Permanently delete version' class=remove_revision}<img src="{image_url name=gray-delete.gif}" alt="" />{/link}
          {/if}
          </td>
        </tr>
        {/if}
        {/foreach}
        {if $_versions|@count  >= 10}
        	<tr><td align="right" url="{$_version->getShowAllUrl()}" colspan="3" id="showAllVersionsLink" style="cursor:pointer;"><u>Show All Versions</u></td></tr>
        {/if}
      
      </tbody>
    </table>
  </div>
</div>
{/if}
<script type="text/javascript">
	$("#lnk_show").click(function(){ldelim}
		var link_text = $(this).html().toLowerCase();
		if (link_text=='show'){ldelim}
			$(this).html('Hide');
			$('div.old_versions').show('slow');
		{rdelim} else if (link_text=='hide'){ldelim}
			$(this).html('Show');
			$('div.old_versions').hide('slow');
		{rdelim}
	{rdelim});
</script>