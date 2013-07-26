{title}Status{/title}
{add_bread_crumb}Status{/add_bread_crumb}

<div id="status_updates_dialog">
  <div id="status_updates_top">
    <ul class="status_update_top_links">
      <li class="first"></li>
      <li><a href="{assemble route=status_updates}" title="{lang}Browse Archive{/lang}"><img src="{image_url name=archive.gif}" alt="" /></a></li>
      <li><a href="{$rss_url}" title="{lang}Track Using RSS{/lang}"><img src="{image_url name=rss.gif}" alt="" /></a></li>
    </ul>
    <p class="dialog_title">{lang}Recent Messages{/lang}</p>
  </div>

  <div class="table_wrapper">
    <table class="status_updates" id="status_updates_table">
      <tbody class="first_level">
      {if is_foreachable($status_updates)}
        {foreach from=$status_updates item=status_update}
          {include_template name=_status_row controller=status module=status}
        {/foreach}
      {/if}
      </tbody>
    </table>
  </div>
  
  <div id="add_status_message">
    <textarea></textarea>
  </div>
</div>

<script type="text/javascript">
  App.widgets.StatusUpdateDialog.init("{$add_status_message_url}");
</script>