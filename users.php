  <?php
  /***Bengtson-Fu!!!   13 3 |\| ( ][ 5 () |\| - |= |_|!!! 
  created by Jason Bengtson, MLIS, MA : Available under Creative Commons non-commercial share-alike license****/
  
  /*this script does find and replace on fields in sirsi flat users file to marriage the fields up with expected defaults in Alma. Extensive find and replace necessary, especially with the names and addresses. This script also restructures addresses to straight key value pairs needed for Alma. It also applies heuristics to the names in the file so that they can be accurately dissected into the proper fields and normalized despite data entry/conversion/etc issues*/

/*this script reassigns user_prfile for Tulsa users. In preparation for Peoplesoft it does not add HSC folks to the users2 file. It creates two additional files:patronnotes, which preserves all of the HSC patron records with notes for purposes of adding them manually to Alma; patroncheckouts, which is a list of HSC patrons with active checkouts. Those HSC patron are migrated (so the charges migrate) and the patronscheckout file gives the access services people a list of internal patrons they will want to remove once the outstanding checkouts have been returned.*/

//Bengtson-Fu is the best Kung Fu
  
  //uncomment below for debugging
  ini_set('display_errors',1); 
   error_reporting(E_ALL);

  //set file and path info here
  $filename="users.flat";
  $destination="users2.flat";
  //global variable for holding address number
  //sirsi's address handling and Ex Libris's format are fundamentally incompatible
  $globaladdress=1;
  //for the name madness
  
  
  $recordholding="";
  $iter=0;
  $outcount=0;
  $counter=1;
  $dectest=0;
  //to delete or not to delete
  $deltest=0;
	//is there a note?
	$isnote=0;
	//is there a checkout?
	$ischeckout=0;

  //it's always something
  $tester=0;
  $holdid="";
  $holding="";
  $newcurrent=array();
$begtest1=0;
$begtest2=0;
$thislib="";

  
    function farthis($holdingit)
  {
  $namesift=0;
    global $globaladdress;
    global $deltest;
	global $isnote;
	global $ischeckout;
	global $holdid;
	global $thislib;

////normalize all the line feeds
$holdingit=str_replace(chr(10),"\n",$holdingit);
$holdingit=str_replace(chr(13),"\n",$holdingit);
$holdingit=str_replace("\n","\n",$holdingit);

if(stripos($holdingit,"USER_LIBRARY")!==false)
      {
		$starthere=stripos($holdingit,"|a")+2;
		$thislib=substr($holdingit, $starthere);
	}

if(stripos($holdingit,"USER_ID")!==false)
      {
	$holdid=$holdingit;
	$isnote=0; 
}
    ////looking for users to ditch
    ///from user_profile
    if(stripos($holdingit,"USER_PROFILE")!==false)
      {
        if(stripos($holdingit,"LIBFAC")!==false)
        {
        $deltest=1;

			if(stripos($thislib,"OUHSC-T")!==false)
			        {
						$holdingit=$holdingit."-T";
					}
        }
        if(stripos($holdingit,"UNDERGRAD")!==false)
          {
          $deltest=1; 
				if(stripos($thislib,"OUHSC-T")!==false)
        		{
					$holdingit=$holdingit."-T";
					}  
          }
        if(stripos($holdingit,"FACULTYHSC")!==false)
          {
          $deltest=1; 
				if(stripos($thislib,"OUHSC-T")!==false)
				        {
							$holdingit=$holdingit."-T";
						}  
          }
        if(stripos($holdingit,"STAFFLIB")!==false)
          {
          $deltest=1; 
					if(stripos($thislib,"OUHSC-T")!==false)
					        {
								$holdingit=$holdingit."-T";
							}  
          }
        if(stripos($holdingit,"STAFFHSC")!==false)
          {
          $deltest=1;  
					if(stripos($thislib,"OUHSC-T")!==false)
					        {
								$holdingit=$holdingit."-T";
							} 
          }
        if(stripos($holdingit,"GRADSTUDNT")!==false)
          {
          $deltest=1;  
					if(stripos($thislib,"OUHSC-T")!==false)
					        {
								$holdingit=$holdingit."-T";
							} 
          }
          if(stripos($holdingit,"RESIDENT")!==false)
          {
          $deltest=1;   
          }
        if(stripos($holdingit,"FELLOW")!==false)
          {
          $deltest=1;   
          }
        if(stripos($holdingit,"PROFSTUDENT")!==false)
          {
          $deltest=1;   
          }
		if(stripos($holdingit,"ILL")!==false)
		          {
		           
					if(stripos($thislib,"OUHSC-T")!==false)
							{
								$holdingit=$holdingit."-T";
							} 
		          }
		if(stripos($holdingit,"FACULTYOU")!==false)
		          {
		           
					if(stripos($thislib,"OUHSC-T")!==false)
							{
								$holdingit=$holdingit."-T";
								$deltest=1;
							} 
		          }
		if(stripos($holdingit,"STUDENTOU")!==false)
		          {
		           
					if(stripos($thislib,"OUHSC-T")!==false)
							{
								$holdingit=$holdingit."-T";
								$deltest=1;
							} 
		          }
		if(stripos($holdingit,"STAFFOU")!==false)
		          {
		           
					if(stripos($thislib,"OUHSC-T")!==false)
							{
								$holdingit=$holdingit."-T";
								$deltest=1;
							} 
		          }
		if(stripos($holdingit,"PUBLIC")!==false)
		          {
		           
					if(stripos($thislib,"OUHSC-T")!==false)
							{
								$holdingit=$holdingit."-T";
							} 
		          }
        
    
      }
      
          if(stripos($holdingit,"USER_CATEGORY")!==false)
      {
        if(stripos($holdingit,"LIBSTAFF")!==false)
        {
        $deltest=1;

      }

    }
      
    /****setting the address number variable as it changes*****/
    if(stripos($holdingit,"USER_ADDR1_BEGIN")>0)
      {
        $globaladdress=1;
      
        
      }
      else if(stripos($holdingit,"USER_ADDR2_BEGIN")>0)
      {
        $globaladdress=2;
        
        
      }
      else if(stripos($holdingit,"USER_ADDR3_BEGIN")>0)
      {
        $globaladdress=3;
        
        
      }
      
  /*****dealing with names--we got a compound name field and a last name field (usually incorrect)from 
  Sirsi. This multi-layered heuristic uses the output conventions to break the name fairly intelligently
  into the three fields required by Ex Libris. Tailored for our data entry inconsistencies******/
  if(stripos($holdingit,"USER_LAST_NAME")>0)
  {
    $holdingit="";
  }
  else if(stripos($holdingit,"USER_FIRST_NAME")>0)
  {
    $holdingit="";
  } 
  else if(stripos($holdingit,"USER_MIDDLE_NAME")>0) {
    $holdingit="";
  }   
    if(stripos($holdingit,"USER_NAME.")>0)
    {
    
    $commacounter=explode(" ", $holdingit);
    $commacount=count($commacounter);
    $holdingit=preg_replace('/([a-zA-Z])(\s\s+)([a-zA-Z])/','$1 $3',$holdingit);//we don't need extra spaces
    $holdingit=preg_replace('/,(\S)/',', $1',$holdingit);//we need a space after the comma
    
    if(stripos($holdingit,",")>0)
    {$namesift=1;}
    if(stripos($holdingit,"&")>0)
    {$namesift=0;}
    if(stripos($holdingit," ILL ")>0)
    {$namesift=0;}
    if(stripos($holdingit,"Librar")>0)
    {$namesift=0;}
    
      
      if($namesift==1)
    { 
    $starthere=stripos($holdingit,"|a")+2;
    $wholename=substr($holdingit, $starthere);
    //corrects wretchedly inconsistent capitalization in names
    $wholename=preg_replace('/-(\S)/','- $1',$wholename);
    $wholename=strtolower($wholename);
    $wholename=ucwords($wholename);
    $wholename=preg_replace('/- (\S)/','-$1',$wholename);
    ////////////
    $commapos=stripos($wholename, ",");
    $lastname=substr($wholename, 0, $commapos);
    $longfirst=substr($wholename, ($commapos+2));
    $midspace=stripos($longfirst, " ");
    //the middle name problem-lots of folks entered first and middle names in ambiguous ways
    //this heuristic cleans them up as best as can be done on the fly
    if ($midspace>-1)
    {
    $firstname=substr($longfirst, 0, ($midspace));
    $middlename=substr($longfirst, ($midspace+1));    $middlename=preg_replace('/\s*\(.*?\)/',"",$middlename);
    $middlename=str_replace(".","",$middlename);//we don't need these periods
    $middlename=str_replace(",","",$middlename);//or commas
    $middlearr=explode(" ", $middlename);
    $countarr=count($middlearr);
    
    ////////////////////
    if ($countarr>1)
    {
          if (preg_match("/JR/i",$middlearr[$countarr-1])==1)//deal with all the misentered jrs
          {
          $middlename=$middlearr[0];  
          
          }
          else
          {
            $middlename=$middlearr[$countarr-1];
            
            $firstname=$firstname."-".$middlearr[0];
          }
        
        
        }
    
    
    }
    else 
    {
    $firstname=$longfirst;
    $middlename="";
    
    }
    ///////////////////
    }
    else 
    {
    $starthere=stripos($holdingit,"|a")+2;
    $wholename=substr($holdingit, $starthere);
    //removed below due to institution name issues not balancing
    /*$wholename=preg_replace('/-(\S)/','- $1',$wholename);
    $wholename=strtolower($wholename);
    $wholename=ucwords($wholename);
    $wholename=preg_replace('/- (\S)/','-$1',$wholename);*/
    $firstname=$wholename;  
    $lastname=$wholename; 
    $middlename=$wholename; 
    }
    $wholename=preg_replace('/\s*\(.*?\)/',"",$wholename);
    
    $holdingit=".USER_NAME.   |a".$wholename."\n.USER_FIRST_NAME.   |a".$firstname."\n.USER_MIDDLE_NAME.   |a".$middlename."\n.USER_LAST_NAME.   |a".$lastname;
    
    }
  
  else
  {
  /*****general find and replace statements******/
  if(stripos($holdingit,"|a")>0)
  {
  $holdingit=str_replace(".PHONE",".USER_ADDR".$globaladdress.".PHONE",$holdingit);
  $holdingit=str_replace("DAYPHONE","USER_ADDR".$globaladdress.".DAYPHONE",$holdingit);
  $holdingit=str_replace("WORKPHONE","USER_ADDR".$globaladdress.".DAYPHONE",$holdingit);
  $holdingit=str_replace("HOMEPHONE","USER_ADDR".$globaladdress.".DAYPHONE",$holdingit);
  $holdingit=str_replace("FAX","USER_ADDR".$globaladdress.".FAX",$holdingit);
  $holdingit=str_replace("EMAIL","USER_ADDR".$globaladdress.".EMAIL",$holdingit);
  $holdingit=str_replace("ZIP","USER_ADDR".$globaladdress.".POSTALCODE",$holdingit);
  $holdingit=str_replace("CITY/STATE","USER_ADDR".$globaladdress.".LINE4",$holdingit);
  
  $holdingit=str_replace("LINE.","USER_ADDR".$globaladdress.".LINE.",$holdingit);
  $holdingit=str_replace("LINE1.","USER_ADDR".$globaladdress.".LINE1.",$holdingit);
  $holdingit=str_replace("LINE2.","USER_ADDR".$globaladdress.".LINE2.",$holdingit);
  $holdingit=str_replace("LINE3.","USER_ADDR".$globaladdress.".LINE3.",$holdingit);
  $holdingit=str_replace(".STREET",".USER_ADDR".$globaladdress.".LINE3",$holdingit);
  $holdingit=str_replace("PREV_ID","USER_XINFO.PREV_ID",$holdingit);
  $holdingit=str_replace("PREV_ID2","USER_XINFO.PREV_ID2",$holdingit);
  $holdingit=str_replace("INACTVID","USER_XINFO.INACTVID",$holdingit);
  $holdingit=str_replace("ACTIVEID","USER_XINFO.ACTIVEID",$holdingit);
  $holdingit=str_replace(".NOTE.",".LIBRARY_NOTE.",$holdingit);
  $holdingit=str_replace(".COMMENT.",".OTHER_NOTE.",$holdingit);
  }
  else
  {
  
  $holdingit=str_replace(".PHONE.",".USER_ADDR".$globaladdress.".PHONE.   |a",$holdingit);
  $holdingit=str_replace("DAYPHONE.","USER_ADDR".$globaladdress.".DAYPHONE.   |a",$holdingit);
  $holdingit=str_replace("WORKPHONE.","USER_ADDR".$globaladdress.".DAYPHONE.   |a",$holdingit);
  $holdingit=str_replace("HOMEPHONE.","USER_ADDR".$globaladdress.".DAYPHONE.   |a",$holdingit);
  $holdingit=str_replace("FAX.","USER_ADDR".$globaladdress.".FAX.   |a",$holdingit);
  $holdingit=str_replace("EMAIL.","USER_ADDR".$globaladdress.".EMAIL.   |a",$holdingit);
  $holdingit=str_replace("ZIP.","USER_ADDR".$globaladdress.".POSTALCODE.   |a",$holdingit);
  $holdingit=str_replace("CITY/STATE.","USER_ADDR".$globaladdress.".LINE4.   |a",$holdingit);
  
  $holdingit=str_replace("LINE.","USER_ADDR".$globaladdress.".LINE.   |a",$holdingit);
  $holdingit=str_replace("LINE1.","USER_ADDR".$globaladdress.".LINE1.   |a",$holdingit);
  $holdingit=str_replace("LINE2.","USER_ADDR".$globaladdress.".LINE2.   |a",$holdingit);
  $holdingit=str_replace("LINE3.","USER_ADDR".$globaladdress.".LINE3.   |a",$holdingit);
  $holdingit=str_replace(".STREET",".USER_ADDR".$globaladdress.".LINE3",$holdingit);
  $holdingit=str_replace("PREV_ID.","USER_XINFO.PREV_ID.   |a",$holdingit);
  $holdingit=str_replace("PREV_ID2.","USER_XINFO.PREV_ID2.   |a",$holdingit);
  $holdingit=str_replace("INACTVID.","USER_XINFO.INACTVID.   |a",$holdingit); 
  $holdingit=str_replace("ACTIVEID.","USER_XINFO.ACTIVEID.   |a",$holdingit);
  $holdingit=str_replace(".NOTE.",".LIBRARY_NOTE.   |a",$holdingit);
  $holdingit=str_replace(".COMMENT.",".OTHER_NOTE.   |a",$holdingit); 
  }
  
  }

////////////////////check for notes
if(stripos($holdingit,"NOTE.")!==false)
            {
	
            $isnote=1;   
            }

////////////////////////////
////////////////////check for checkouts
if($deltest==1)
{
  
if(stripos($holdingit,"DOCUMENT BOUNDARY")!==false)
 {

///open and parse serial control file
/////////////////////////////////////
$loopcount=1;
if (($daobjectx = fopen("charges.flat", "r")) !== FALSE) {
	//if ($daobjectx!== FALSE) {
	
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

	if(stripos($currentline[$x], ".USER_ID.")!==false)
	{
		
		$finder=stripos($currentline[$x],'|a')+2;
		$exigence=substr($currentline[$x], $finder);
		if(stripos($holdid, $exigence)!==false)
			{
			$ischeckout=1;	
			}
											
	}

	
	////////////////			
	
	
	$x++;
	//////////////
	}
	}}}
	////////////////////
	/////////////////////
}

}

////////////////////////////
  if(preg_match("/USER_ADDR._END/",$holdingit)==1)
  {
                
  $holdingit="";
                
  }
  else if(preg_match("/USER_ADDR._BEGIN/",$holdingit)==1)
  {
  $holdingit="";  
  }
  else if(preg_match("/USER_ALT_ID/",$holdingit)==1)
  {
  $holdingit="";  
  }
  else if(preg_match("/USER_XINFO_BEGIN/",$holdingit)==1)
  {
  $holdingit="";  
  }
  else if(preg_match("/USER_XINFO_END/",$holdingit)==1)
  {
  $holdingit="";  
  }
  if ($holdingit!="") 
  { 
  return $holdingit."\n"; 
  }
  else
  {
  return; 
  } 
      
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

	///////////////////////////////////////////////load each record into a a variable to delete peoplesoft users after generating a report containing them. Allows other selective deletes as appropriate
  //uncomment the line below and the one prior to catch last record
//subroutine to deactivate deletion protocol
  //$deltest=0;
  if(stripos($currentline[$x],"DOCUMENT BOUNDARY")==false)
  {
    $recordholding=$recordholding.$currentline[$x];
    
  }
  /////////////////////////////////////////////
  else
    {
 
    $recordholding=$recordholding.$currentline[$x];
    
    //removes selected records
    if($deltest!==1)
    {
	
    $holding=$holding.$recordholding; 
    $recordholding="";

    }
    else
    {
	
      //generate files of patron records to check
		//for notes
		if($isnote==1)
		    {
			
          		if($begtest1==0)
				{
					$recordholding="*** DOCUMENT BOUNDARY ***\n".$recordholding;
				}
		       $outputit = fopen("patronnotes.txt", 'a');
		        fwrite($outputit, $recordholding);
		        fclose($outputit); 
		    	$isnote=0;
				$begtest1=1;
				    }
		//for charges
		if($ischeckout==1)
		    {
          
			//we still want these in the import so the checkputs don't bounce
		    $holding=$holding.$recordholding; 
			if($begtest2==0)
						{
							$recordholding="*** DOCUMENT BOUNDARY ***\n".$recordholding;
						}
			$outputit = fopen("patroncheckouts.txt", 'a');
			fwrite($outputit, $recordholding);
			fclose($outputit);
			$ischeckout=0;
		    $begtest2=1;
		    }
        else
        {

          $outputit = fopen("deleted.txt", 'a');
      fwrite($outputit, $recordholding);
      fclose($outputit);
      $ischeckout=0;
        $begtest2=1;


        }
$recordholding=""; 
$deltest=0;
    }
 
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
$holding=str_replace("*** DOCUMENT BOUNDARY ***\n*** DOCUMENT BOUNDARY ***","*** DOCUMENT BOUNDARY ***",$holding);
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
//catches the final record of the user file
/////////////////////////////////////
//uncomment the line below in addition to the one earlier in the script to
//deactivate the deletion protocol
 //$deltest=0;
	     if($deltest!==1)
	    {
		
	    $holding=$holding.$recordholding; 
	    $recordholding="";
	    }
	    else
	    {
		
	      //generate files of patron records to check
			//for notes
			if($isnote==1)
			    {
				
	          
			       $outputit = fopen("patronnotes.txt", 'a');
			        fwrite($outputit, $recordholding);
			        fclose($outputit); 
			    	$isnote=0;
					    }
			//for charges
			if($ischeckout==1)
			    {
	          
				//we still want these in the import so the checkputs don't bounce
			    $holding=$holding.$recordholding; 
				$outputit = fopen("patroncheckouts.txt", 'a');
				fwrite($outputit, $recordholding);
				fclose($outputit);
				$ischeckout=0;
			    
			    }
	$recordholding=""; 
	$deltest=0;
	}
   ///////////////////////////////////////  
  }

?>
