{title}Issue Invoice{/title}
{add_bread_crumb}Issue{/add_bread_crumb}

<div id="issue_invoice">
  {form action=$active_invoice->getIssueUrl() method=post}
    <div class="col">
      {wrap field=issued_on}
        {label for=issueFormIssuedOn required=yes}Issued On{/label}
        {select_date name=issue[issued_on] value=$issue_data.issued_on id=issueFormIssuedOn class=required}
      {/wrap}
    </div>
    
    <div class="col">
      {wrap field=issued_on}
        {label for=issueFormDueOn required=yes}Due On{/label}
        {select_date name=issue[due_on] value=$issue_data.due_on id=issueFormDueOn class=required}
      {/wrap}
    </div>
    
    <div class="clear"></div>
    
    {if is_foreachable($company_managers)}
    <p><input type="radio" name="issue[send_emails]" value="1" {if $issue_data.send_emails}checked="checked"{/if} class="inline input_radio" id="issueFormSendEmailsYes" /> {label for="issueFormSendEmailsYes" class=inline}Send email to client{/label}</p>
    <div id="select_invoice_recipients" style="display: none">
      <table>
      {foreach from=$company_managers item=company_manager}
        <tr class="{cycle values='odd,even' name=recipient_rows}">
          <td class="radio"><input type="radio" name="issue[user_id]" value="{$company_manager->getId()}" class="auto input_radio" {if $issue_data.user_id == $company_manager->getId()}checked="checked"{/if} /></td>
          <td class="avatar"><img src="{$company_manager->getAvatarUrl()}" alt="" /></td>
          <td class="name">{$company_manager->getDisplayName()|clean} <span class="details">({$company_manager->getEmail()|clean})</span></td>
        </tr>
      {/foreach}
      </table>
    </div>
    <p><input type="radio" name="issue[send_emails]" value="0" {if !$issue_data.send_emails}checked="checked"{/if} class="inline input_radio" id="issueFormSendEmailsNo" /> {label for="issueFormSendEmailsNo" class=inline}Don't send emails, but mark invoice as issued{/label}</p>
    {else}
<!--    <p>{lang company_name=$company->getName()}Sorry, there's nobody in :company_name to whom we can email the invoice{/lang}</p> -->
    <input type="hidden" name="issue[send_emails]" value="0" />
    {/if}
    
    {wrap_buttons}
      {submit}Submit{/submit}
    {/wrap_buttons}
  {/form}
  
  {empty_slate name=issue module=invoicing}
</div>