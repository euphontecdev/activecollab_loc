{*BOF:mod 20110707 ticketid172*}
{*}<ul class="object_options">{*}
<ul class="object_options" style="margin:0px;">
{*EOF:mod 20110707 ticketid172*}
{foreach from=$_quick_options->data key=_quick_option_name item=_quick_option}
  <li {if isset($_quick_option.class)}class="{$_quick_option.class|clean}"{/if} id="object_quick_option_{$_quick_option_name}">{link href=$_quick_option.url method=$_quick_option.method confirm=$_quick_option.confirm not_lang=yes}<span>{$_quick_option.text|clean}</span>{/link}</li>
{/foreach}
</ul>