	<?php
	/***Bengtson-Fu!!!   13 3 |\| ( ][ 5 () |\| - |= |_|!!! 
	created by Jason Bengtson, MLIS, MA : Available under a creative commons noncommercial, attribution, share-alike license****/
	
	/*this script does basic find and replace on fields in sirsi falt charges file to marriage the fields up with expected defaults in Alma. Number of recall notices sent are substituted for number of recalls*/
	
	//uncomment below for debugging
	/*ini_set('display_errors',1); 
	 error_reporting(E_ALL);*/

	//set file and path info here
	$filename="charges.flat";
	$destination="charges2.flat";

	

	$iter=0;
	$outcount=0;
	$counter=1;
	$dectest=0;

	
	$holding="";
	$newcurrent=array();

	
		function farthis($holdingit)
	{

	/*****find and replace statements******/
	$holdingit=str_replace("CHRG_LIBRARY","LIBRARY",$holdingit);
	$holdingit=str_replace("CHRG_DATEDUE","DUE_DATE",$holdingit);
	$holdingit=str_replace("CHRG_DC","CHARGE_DATE",$holdingit);
	$holdingit=str_replace("CHRG_DATE_CLMRET","CLAIMED_RETURNED_DATE",$holdingit);
	$holdingit=str_replace("CHRG_NBROVERNTC","NUM_OVERDUE_NOTICES",$holdingit);
	$holdingit=str_replace("CHRG_NBRRECLNTC","NUM_RECALLS",$holdingit);
	

	/////////////////////////////////
			
			return $holdingit."\n";
	}
	
/*main loop*/
	$loopcount=1;

	if (($daobject = fopen($filename, "r")) !== FALSE) {
	while (($currentline=fgetcsv($daobject, 0, "\n"))!==FALSE)
	{
	if ($loopcount>0)
	{
	//switcheroo
	$numberof=count($currentline);
	$x=0;
	while ($x<$numberof)
	{
	$currentline[$x]=farthis($currentline[$x]);
	$holding=$holding.$currentline[$x];
	
	$x++;
	}
	
	/*$newcurrent[$counter]=$newcurrent[$counter].$currentline[$x];
		print($newcurrent[$counter]."   ".$currentline[$x]."   ".$counter."   ".$x);

	$counter++;*/
	
}
/*main loop ends*/

	


/******appends to the file in chunks to save on system resources*****/
//800 lines seems to work well, keeping things within a reasonable threshhold for system resources

	$t=1;
	while($t<$iter+$dectest)
	{
	
	$holding=$holding.$newcurrent[(800)+$t];
	$newcurrent[(800)+$t]="";
	
	$t++;
	}

/***append to the file***/
$outer = fopen($destination, 'a');
fwrite($outer, $holding);	

fclose($outer);	
$holding="";
$iter=0;	
$outcount++;	

$loopcount++;
$iter++;
$dectest=0;
}

	
}
?>
