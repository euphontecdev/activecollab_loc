<?php
$command = "grep -ri 'menu_item_milestones' ./*";
$output = shell_exec($command);
echo "$output";
echo "Grep job over.";
?>