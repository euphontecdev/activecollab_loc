{title}Print Comment{/title}
{add_bread_crumb}Print Comment{/add_bread_crumb}
<div style="font-size:14px;font-weight:bold;">{$comment_data.ticketName}</div>
<div><br/></div>
<div>Comment by {$comment_data.userName} on {$comment_data.date}</div>
<div><br/></div>
<div style="vertical-align:middle;">{$comment_data.body|@nl2br}</div>
<script type="text/javascript">window.print();</script>