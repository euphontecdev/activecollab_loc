{title}People{/title}
{add_bread_crumb}All{/add_bread_crumb}

<div id="people">
{if is_foreachable($people)}
{foreach from=$people item=project_company}
  <div class="company">
    <h2 class="section_name"><span class="section_name_span"><a href="{$project_company.company->getViewUrl()}">{$project_company.company->getName()|clean}</a></span></h2>
    <div class="section_container">
      <table class="users">
        <tbody>
        {foreach from=$project_company.users item=user}
          <tr class="{cycle values='odd,even'}">
            <td class="avatar">
              {link href=$user->getViewUrl()}
                <img src="{$user->getAvatarUrl(false)}" alt="" />
              {/link}
            </td>
            <td class="name">
              <h3>{user_link user=$user}</h3>
            </td>
            <td class="meta">
              <dl>
                <dt>{lang}Email{/lang}</dt>
                <dd><a href="mailto:{$user->getEmail()|clean}">{$user->getEmail()|clean}</a></dd>
              {if $user->getConfigValue('im_type') && $user->getConfigValue('im_value')}
                <dt>{$user->getConfigValue('im_type')|clean}</dt>
                <dd>{$user->getConfigValue('im_value')|clean}</dd>
              {/if}
              </dl>
            </td>
            <td class="role">{$user->getVerboseProjectRole($active_project)|clean}</td>
            <td class="options" style="width:100px;">
            <nobr>
            {link href=$active_project->getUserAllocationsUrl($user) title='view all of the milestones, tickets and tasks that are assigned to this user'}Allocations{/link}&nbsp;&nbsp;&nbsp;&nbsp;
            {if $logged_user->canChangeProjectPermissions($user, $active_project)}
              {link href=$active_project->getUserPermissionsUrl($user) title='Change Permissions'}<img src="{image_url name=gray-permissions.gif}" alt="" />{/link}
            {/if}
            {if $logged_user->canRemoveFromProject($user, $active_project)}
              {*}{link href=$active_project->getRemoveUserUrl($user) method=post title='Remove from Project'}<img src="{image_url name=gray-delete.gif}" alt="" />{/link}{*}
			   <a href="{$active_project->getRemoveUserUrl($user)}" title="Remove from Project" onclick="if (confirm('This action will remove the user from the project!'))App.postLink('{$active_project->getRemoveUserUrl($user)}');return false;"><img src="{image_url name=gray-delete.gif}" alt="" /></a>
            {/if}
            <br />
			{*}BOF:mod 20120806{*}
			<a href="{assemble route="goto_user_task_page" project_id=$active_project->getId()}&selected_user_id={$user->getId()}">Task Page</a> | {*}EOF:mod 20120806{*}
			{*}EOF:mod 20120806{*}
            <span>{object_user_star starred_user_id=$user->getId() starred_page_type='projects' starred_by_user_id=$logged_user->getId() project_id=$active_project->getId()}</span>{lang}<a href="{$active_project->getUserAssignedMilestonesUrl($user)}">{lang}Milestones Page{/lang}</a>{/lang} | <span>{object_user_star starred_user_id=$user->getId() starred_page_type='tickets' starred_by_user_id=$logged_user->getId() project_id=$active_project->getId()}</span>{lang}<a href="{$active_project->getUserTodayPageUrl($user)}">Tickets Page</a>{/lang}
            </nobr>
            </td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    </div>
  </div>
{/foreach}
{else}
  <p>{lang url=$active_project->getAddPeopleUrl()}<a href=":url">Click here</a> to add users to this project.{/lang}</p>
{/if}
</div>