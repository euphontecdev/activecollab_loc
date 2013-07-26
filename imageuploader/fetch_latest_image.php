<?php
$dir = '/home/ffbhpro/public_html/imageuploader/';
/*if ($dir){
	while($dh = opendir($dir)){
		while (($file=readdir($dh)) !== false){
			
		}
		closedir($dh);
	}
}*/
exec('cd ' . $dir . ' | ls -t', $output);
foreach($output as $file){
	$filetype=strtolower(strrchr($file, '.'));
	if($filetype==".jpg" || $filetype==".jpeg" || $filetype==".png" || $filetype==".png"){
		echo 'http://' . $_SERVER['SERVER_NAME'] . '/imageuploader/' . $file;
		break;
	}
}

?>