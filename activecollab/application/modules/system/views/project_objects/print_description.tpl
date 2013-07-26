{* 20 April 2012 (SA) Ticket #794: add print button to description section on tickets *}
{title}Print - {$name}{/title}
{add_bread_crumb}Print - {$name}{/add_bread_crumb}
	<div class="body content">
		<p style="font-weight:bold">{$name}</p>
		<p><br/></p>
		<p>{$desc}</p>
	</div>
<script type="text/javascript">window.print();</script>