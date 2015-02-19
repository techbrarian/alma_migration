	<?php
	//line 94
	/***Bengtson-Fu!!!   13 3 |\| ( ][ 5 () |\| - |= |_|!!! 
	created by Jason Bengtson, MLIS, MA : Available under MIT open source license****/
	
	/*this script does some find and replace*/
	
	//uncomment below for debugging
	/*ini_set('display_errors',1); 
	 error_reporting(E_ALL);*/

	//set file and path info here
	$filename="holdings.mrk";
	$destination="holdings2.mrk";

	
	$current001="";
	$current035="";
	$current910="";
	
	$iter=0;
	$outcount=0;
	$counter=1;
	$dectest=0;
	$lib="";
	$endo="";
	$titlectl;
	
	$holding="";
	$newcurrent=array();

	//extract/set volume info in the 999 field
		function farthis($holdingit)
	{
		global $lib;
		global $current001;
		global $current035;
		global $endo;
		global $titlectl;
		global $current910;

		if(strpos($holdingit,'LDR ') !== FALSE)
				{
						
						$current910='';
						
					}
		
		if(strpos($holdingit,'=004 ') !== FALSE)
				{
						
						$holdingit='';
						$titlectl=substr($currentline[$x],9);
					}


		if(strpos($holdingit,'=910 ') !== FALSE)
				{
						
						$current910=substr($holdingit, 7);

					}
				
					

	/*****find and copy******/
	if(strpos($holdingit,'=035 ') !== FALSE)
	{
		
			
			$endo= substr($holdingit, 18);
			
		if($endo[0]=="a")
			{
			$endo=ltrim($endo, 'a');
			}

			if($endo[0]=="o")
			{
			$endo=ltrim($endo, 'o');
			}

			if($endo[0]=="i")
			{
			$endo=ltrim($endo, 'i');
			}
			
			$suffix=stripos($endo,"/");
			$endo= substr($endo, $suffix);
			
			
			$holdingit="xce#lrtut";
			$current035='=004  \\\\'.$endo;
		
		}
		
		if($holdingit=="")
			{
					
					$holdingit=$current035."\n".$holdingit;
				
				}
		
	if(stripos($holdingit, "=852")!==false)
		{
			$exigence=stripos($currentline[$x],'$b')+2;
			$lib=substr($currentline[$x],$exigence,4);
														
		//////////////////////////////////////////
		$loopcount=1;
		///to fix the holdings records that need to connect to the duped bib records
		/*if (($daobjectx = fopen("holdingsfix.txt", "r")) !== FALSE) {
					
					
					while (($currentline=fgetcsv($daobjectx, 0, "\n"))!==FALSE)
					
					{
						
					if ($loopcount>0)
					{
						
					//switcheroo
					$numberof=count($currentline);
					$x=0;
					while ($x<$numberof)
					{
						//////////
					
								//////////////////////////////////
					///////////
					
					if(stripos($currentline[$x], "|".$titlectl)!==false)
																{
						
						/*if(stripos($currentline[$x], "$m".$lib)!==false)
											{*/
											$find=stripos($currentline[$x], "#")+1;
											$endo=substr($currentline[$x],$find);
											$current035='=004  \\\\'.$endo;	
											//}
						
									}			
								/////////////
				
					
					////////////////			
					
					
					$x++;
					//////////////
					}
					//}}}*/
					
					
		}
		
		return $holdingit."\n";
			}
	/******fixing data foibles in the 004 field because Ex Libris can't or won't*****/	
	
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
	if(stripos($currentline[$x], "xce#lrtut")===false)
	{
	$holding=$holding.$currentline[$x];
	}
	$x++;
	}
	

	
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
