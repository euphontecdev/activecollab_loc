<?php
$file=$_FILES['userfile']['name'];

/*$split_vals = explode('.', $file);
if (count($split_vals)>2){
	array_pop($split_vals);
	array_pop($split_vals);
}
$file = implode('.', $split_vals);*/

$fullFilePath=$_FILES['userfile']['tmp_name'];
$move_image_to_dir = '/home/ffbhpro/public_html/imageuploader/';
$filename = $move_image_to_dir . $file;

$filetype=strtolower(strrchr($file, '.'));
if($filetype==".jpg" || $filetype==".jpeg" || $filetype==".png" || $filetype==".png"){
	//copy ($fullFilePath, $filename) or die ("couldnot copy");
	move_uploaded_file($fullFilePath, $filename);
} else {
	echo 'Error';
}
/*
//$redirectkey=strip_tags($_REQUEST['sessionstring']);
$file=$_FILES['userfile']['name'];
$redirectkey=$file;
//print "file is $file<br>";
$fullFilePath=$_FILES['userfile']['tmp_name'];//$tempDirectory . "/" . $file;
$move_image_to_dir = '/home/ffbhpro/public_html/imageuploader/';
$filename = $move_image_to_dir . $file;

$filetype=strtolower(strrchr($file, '.'));//grab the file extension
//fwrite($fp, "file is $file, filetype is $filetype, fullFilePath is $fullFilePath \n");
if($filetype==".jpg" || $filetype==".jpeg" || $filetype==".png" || $filetype==".png"){
	srand((double)microtime()*1000000);
	$temp1 = TIME();
								
	$originalFile="$redirectkey";
	$mainFile="$redirectkey-$temp1-main";
	$thumbFile="$redirectkey-$temp1-thumb";
	$send="";
								
	$jpg=0;
								
	//$fileMime=system('file -ib '. $fullFilePath);
	$parts = getimagesize($fullFilePath);
	$fileMime=$parts['mime'];
								
	if($fileMime=="image/pjpg" || $fileMime=="image/pjpeg" || $fileMime=="image/jpeg" || $fileMime=="image/jpeg" || $fileMime=="image/jfif" || $fileMime=="image/pjfif"){
		$jpg=1;
		$type="jpg";
		$allowedFileType=1;
		$filename="$originalFile.jpg";
		while(file_exists($move_image_to_dir . $file)){
			$filename=$move_image_to_dir ."Z" . $file;	
		}
		copy ($fullFilePath, $filename) or die ("couldnot copy");
	}
	if($fileMime=="image/gif"){
		$allowedFileType=1;
		$filename="$originalFile.gif";
		$type="gif";
		while(file_exists($move_image_to_dir . $file)){
			$filename=$move_image_to_dir ."Z" . $file;	
		}
		copy ($fullFilePath, $filename) or die ("couldnot copy");
	}
	if($fileMime=="image/png" || $fileMime=="image/x-png"){
		$allowedFileType=1;
		$filename="$originalFile.png";
		$type="png";
		while(file_exists($move_image_to_dir . $file)){
			$filename=$move_image_to_dir ."Z" . $file;	
		}
		copy ($fullFilePath, $filename) or die ("couldnot copy");
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}//end of filetype check.
*/
?>