<?php 
if (array_key_exists(1,$argv)){
	$dir="fitpress-master";
	$main_name="fitpress.php";
	mkdir($dir);
	$files=array("fitpress-oauth2-client.php","fitpress-settings.php","fitpress.css","fitpress.php","logo.png","readme.txt");
	for ($i=0;$i<count($files);$i++){
		//print $files[$i];
		copy($files[$i],$dir."/".$files[$i]);
	}
	$str=file_get_contents($dir."/".$main_name);
	$str=str_replace("define( 'FITPRESS_CLIENT_STATE_KEY', 'this should be replace prior to uploading' );","define( 'FITPRESS_CLIENT_STATE_KEY', '".$argv[1]."' );",$str);
	file_put_contents($dir."/".$main_name,$str);
	$pattern="/Version: (([0-9]+)\.([0-9]+))/";
	$matches=array();;
	$version="X.X";
	preg_match_all($pattern,$str,$matches);
	print_r($matches);
	if (array_key_exists(1,$matches)){
		$version=$matches[1][0];
	}
	$zip_name="../fitpress-".$version.".zip";
	exec("zip -r ".$zip_name." ".$dir);
	exec("rm -r ".$dir);
}
else{
	print "Secret not provided.";
}
?>
