  <?php
  /***Bengtson-Fu!!!   13 3 |\| ( ][ 5 () |\| - |= |_|!!! 
  created by Jason Bengtson, MLIS, MA : Available under MIT open source license****/
  
  /*this script adds a v subfield to the 999 feild of marc records. Designed for cases in which the volume info is in the call # but not in a separate subfield. Must be run on a .mrk file; will not work on an .mrc file. Does not check for duplicate $v subfields at this time (I may add this in the future)*/
  
  /*this script fixes legacy bad location information*/
  
  /*this script adds a 001 field to a large number of records that, for some reason, were not assigned system control numbers. The placeholder control number is JBENGTSON followed by an incrementing number (starting at 1000)*/
  
  /*this script attempts to accurately locates system control numbers for serials missing their 001 field by comparing issn, first 10 of call number, and first four of location, to records in the serial control file to find a match. If a good match is found, the SERC_TITLE_KEY from the serial control record is populated into the bib record as the system control number. To run properly this script must run in the same directory as a copy of the serial control file (as serial.flat). */
  
  /*apparently we also have a lot of duplicate system control numbers . . . geez . . . so this script now also loads 001 fields into a variable and compares each successive one, adding a dup+incremented number starting at 1000 suffix to duplicates so that Ex Libris will load them. In coordination with this it generates two output files: numlist is a list of dups, so catalogers can go in and fix them;holdingsfix is a file that needs to sit in the same directory as holdings.php when the holdings file is processed. Uses the 910 field (title control key) to try to disambiguate the dups and match them up with the correct holdings info.*/
  
  /*yes, this script is pretty friggin' awesome*/
  //increase memory limit
  ini_set("memory_limit","5G");
  
  //uncomment below for debugging
  /*ini_set('display_errors',1); 
   error_reporting(E_ALL);*/

  //set file and path info here
  $filename="report.mrk";
  $destination="reportx.mrk";
  $serialfile="serial.flat";
  
  ///for the 001 and 999 holding tanks
  $current001="";
  $current999="";
  $current910="";
  $current852="";

  //global variables for holding info about 001 and 999 fields
  //a bunch of our bib records have no 01 field because of sketchy data entry
  //globalis keeps track of the status of the field in the Record
  //globalincrement keeps up an entry for the field
  //the 999 one and global dup are for the plethora of duped system control numbers
  $globalis=0;
  $globalincrement=1000;
  $dupincrement=1000;
  $global999=0;
  $globalduptest=0;

  $iter=0;
  $outcount=0;
  $counter=1;
  $dectest=0;
  $isserial=0;
  $x=0;
  $thisflag=0;
  
  //global variable for holding the system control numbers to fix duplicates
  //silly rabbit, Trix are for kids!
  $systemcontrolnumbers="";
  
  //open file and save to a variable
  //$daobjectx = file_get_contents($serialfile);
  

  $suba="";
  $subm="";
  $thisissn="";
  $aggregation="";
  
  $holding="";
  $newcurrent=array();
  $GLOBALS ["currentlineo"]=array();
  $GLOBALS ["currentlinei"]=array();
  
  function getHoldings()
  {
  global $currentlineo;
  $daholdings=file_get_contents("holdingsx.mrk","r");
  $currentlineo=str_getcsv($daholdings,"\n");
      
  }
  
  
  function getSerials()
    {
    global $currentlinei;
    $daserialss=file_get_contents("serial.flat","r");
    $currentlinei=str_getcsv($daserialss,"\n");
    
    }
  
  //get serials call numbers into holdings
  function changefield($dacallnumber)
  {
  global $current001; 
  global $current852;
  global $currentlineo;
  global $thisflag;
  
  //if (($daobjecto = fopen("holdingsx.mrk", "r")) !== FALSE) {
  //while (($currentlineo=fgetcsv($daobjecto, 0, "\n"))!==FALSE)
  //{
    //$currentlineo=fgetcsv($daobjecto, 0, "\n");
    
  //switcheroo
  $numberofit=count($currentlineo);
    $z=1;
    $x=0;
    $saveresources=0;
  while ($x<$numberofit && $saveresources<1)
  {
    
    /*if($saveresources<1)
    {*/

  
  if(stripos($currentlineo[$x], "LDR")!==false)
        {
          $current852="";
                      
        }

    /*if(stripos($currentlineo[$x], "=852")!==false && stripos($currentlineo[$x], "=852")<10)
        {
          $current852=$currentlineo[$x];
                      
        }*/
  

  if(stripos($currentlineo[$x], "=852")!==false && stripos($currentlineo[$x], "JOURNALS")!==false)
        {
      
      
       $pattern='/=852.*/';
      preg_match($pattern, $currentlineo[$x], $this852);
          $current852=$this852[0];
      //echo $current852."zzzzzzz\n".$x;
        if(stripos($currentlineo[$x], "=852")<5)
        {
          $currentlineo[$x]="abby normal";
      $thisflag=1;
        }
                      
        }      

  
  if(stripos($currentlineo[$x], "=004")!==false && (stripos($current852, "JOURNALS")!==false))
        {

    
          $coolx=substr($current001, 7);
  /*if()
  {*/
        if(stripos($currentlineo[$x], $coolx)!==false)
        {
          ///
          if($current852!=="")
          {
      $aend=stripos($current852, '$b');
      $astarto=stripos($current852, '$a');
      
            if($aend-$astarto<3) 
                                  {
            
            $start852=substr($current852,0,$aend);
            $end852=substr($current852,$aend);
            $current852=$start852.$dacallnumber.$end852;
            $saveresources=1;
      $currentlineo[$x]=preg_replace('/=852.*\$a\$b.*/','',$currentlineo[$x]);     
      $currentlineo[$x]=str_replace("\n\n","\n",$currentlineo[$x]);
                                  }
                                  else
                      {
            $start852=substr($current852,0,$astarto+2);
            $end852=substr($current852,$aend);
            $current852=$start852.$dacallnumber.$end852;
            $saveresources=1;
      $currentlineo[$x]=preg_replace('/=852.*\$a\$b.*/','',$currentlineo[$x]);     
      $currentlineo[$x]=str_replace("\n\n","\n",$currentlineo[$x]);
                      }
          }
          else
          {
            
                         $current852='=852  \\$a'.$dacallnumber;
                         $saveresources=1;
                      
          }

        /////      
        $currentlineo[$x]=$currentlineo[$x]."\n".$current852;
    $current852=""; 
}

if ($thisflag!=0)
{
$currentlineo[$x]=$currentlineo[$x]."\n".$current852;
$current852="";
$thisflag=0;  
}
        
    $currentlineo[$x]=str_replace("\n\n","\n",$currentlineo[$x]);
      ///end big if
    //}
           
        }
        
           
  
$x++; 
 


    
  } 

  }
  
  //deal with control numbers . . . man, what a mess!
  function controlnumbers($holdingit)
  {
  global $currentlinei;
  global $systemcontrolnumbers;
  global $globalduptest;
  global $dupincrement;
  $testo=substr($holdingit,7);
   
    ////////////////////////

 
    if(stripos($systemcontrolnumbers,$testo)!==false)
    {
    $findo=stripos($systemcontrolnumbers,$testo);
    $findx=stripos($systemcontrolnumbers,",",$findo);
    $lengtho=$findx-$findo;
    echo $findo;
    $thisstring=substr($findo,$lengtho);
      //saves the first copy of the number
        if(stripos($thisstring,"ixi")==false)
        {
          $outputit = fopen("numlist.txt", 'a');
          fwrite($outputit, $holdingit."\n"); 
          fclose($outputit);
    $systemcontrolnumbers=str_replace($testo.",",$testo."ixi,",$systemcontrolnumbers);
        }
      //saves subsequent iterations
      $holdingit=$holdingit."dup".$dupincrement;
      $outputit = fopen("numlist.txt", 'a');
      fwrite($outputit, $holdingit."\n"); 
      fclose($outputit);
    $globalduptest=1; 
    $dupincrement++;
  }

  
/////////////////////////
  ////////////////

  /*if(stripos($systemcontrolnumbers,$testo)!==false)
  {
    $globalduptest=1;
  //generate list of duplicate records
    $outputit = fopen("numlist.txt", 'a');
    fwrite($outputit, $holdingit."\n"); 

    fclose($outputit);  
  $holdingit=$holdingit."9999";
  //add new 9999 file, too
    $outputit = fopen("numlist.txt", 'a');
    fwrite($outputit, $holdingit."\n"); 

    fclose($outputit);
  }*/
  ///////////
  $systemcontrolnumbers=$systemcontrolnumbers.$holdingit.",";

  return $holdingit;
  
  }
  
  ///the money shot; fixing the 001 field for serials
  function serialthis($holdingit,$subm,$suba)
  {
    global $currentlinei;
    global $daobjectx;
    global $serialfile;
    global $currentlinei;
    $lib="";
    $call="";
    $issn="";
    $new001="";
    $titlekey="";
    ///open and parse serial control file
    /////////////////////////////////////
    $loopcount=1;
    /*if (($daobjectx = fopen($serialfile, "r")) !== FALSE) {
      //if ($daobjectx!== FALSE) {
      
      while (($currentlinei=fgetcsv($daobjectx, 0, "\n"))!==FALSE)
      
      {*/
        
      if ($loopcount>0)
      {
        
      //switcheroo
      $numberof=count($currentlinei);
      $x=0;
      while ($x<$numberof)
      {
        //////////
      if(stripos($currentlinei[$x], "BASE_CALLNUM")!==false)
      {
        $exigence=stripos($currentlinei[$x],'|a')+2;
        $call=substr($currentlinei[$x],$exigence,10);
                    
      }
            //////////////////////////////////
            
      ///////////
      if(stripos($currentlinei[$x], "SISAC_ID")!==false)
      {
        $exigence=stripos($currentlinei[$x],'|a')+2;
        $issnit=substr($currentlinei[$x],$exigence);
                          
      }
            /////////////
      if(stripos($currentlinei[$x], "SERC_LIB")!==false)
      {
        $exigence=stripos($currentlinei[$x],'|a')+2;
                    
        $lib=substr($currentlinei[$x],$exigence,4);
                          
      }
                  /////////////////////////////////////
      if(stripos($currentlinei[$x], "SERC_TITLE_KEY")!==false)
      {
        $exigence=stripos($currentlinei[$x],'|a')+2;
        $titlekey=substr($currentlinei[$x],$exigence);
      }
        
        
        
      if(stripos($currentlinei[$x], "DOCUMENT BOUNDARY")!==false)
      {
        if($lib==$subm)
        {
          if($call==$suba)
          {
            if(stripos($issnit,$holdingit)!==false)
                      {
                        $new001='=001  '.$titlekey;
                        return $new001;
                      }
          }
        }
              
      }
      
      ////////////////      
      
      
      $x++;
      //////////////
      }
      }//}}
      ////////////////////
      /////////////////////
    
    return;
  }

  //extract/set volume info in the 999 field
    function farthis($holdingit)
  {
    global $globalis;
    global $global999;
    global $globalduptest;
    global $current999;
    global $current910;
    global $globalincrement;
    global $current001;
    global $suba;
    global $subm;
    global $thisissn;
    global $isserial;
    $exigence=0;
    $exigence2=0;
  /*****find and copy******/
  if(stripos($holdingit,"=910 ")!==false)
  {
    $current910=substr($holdingit, 7);
  }

  if(stripos($holdingit,"=999 ")!==false)
  {
    if($global999==0)
    {
    $current999=$holdingit; 
    }
    else
    {
    $current999=$current999.$holdingit; 
    }
    
    if(stripos($holdingit,'$lJOURNALS')!==false)
        {
          ///fix undocumented problem with Ex Libris SIRSI import
        
        $starto=stripos($holdingit,'$a')+2;
        $temptest=substr($holdingit, $starto);
        $differenceo=strlen($holdingit)-strlen($temptest);
        $endo=strpos($temptest,'$');
        $dacallnumber=substr($holdingit, $starto, $endo);
        //////////////////////
        
        changeField($dacallnumber);
        
        
        ///////////////////////////
        }
    
    
    if(stripos($holdingit,'$v')<=0)
    {
      $findo='/[Vol|vol|V|v]\..?[0-9]+/';
      preg_match($findo, $holdingit, $matches);
      if(empty($matches))
      {
      $matches[0]="";
      }
      
      if (strlen($matches[0])>0)
      {
      
      $holdingit=$holdingit.'$v'.$matches[0];
      

      }
    }

    if(strpos($holdingit,'$mDMEI')>0)
    {
    $holdingit=preg_replace('/\$l.+?\$/','$lDMEI$',$holdingit);   
    }
    else if(strpos($holdingit,'$mSURGERY')>0)
    {
    $holdingit=preg_replace('/\$l.+?\$/','$lSTACKS-SRG$',$holdingit); 
    }
    $global999=1;
  }
      
  if(stripos($holdingit,"LDR ")!==false)
    {
      $global910="";
      $globalis=0;}
  
  if(stripos($holdingit,"=001 ")!==false)
  {
    //this function call commented out at Ex Libris request (they wanted to create their own fix)
    $holdingit=controlnumbers($holdingit);
    $current001=$holdingit;
    $holdingit="x";
    $globalis=1;}
    
    
  
  if(stripos($holdingit,"=022 ")!==false)
  {
    if($globalis!=1)
    {
    
      $exigence=stripos($holdingit,'$a')+2;
      $thisissn=substr($holdingit,$exigence,9);
      
      
    }
  }
  
  if(stripos($holdingit,"=999 ")!==false)
  {
  /////
  $global999=1; 
  $exigence=stripos($holdingit,'$a')+2;
  $suba=substr($holdingit,$exigence,10);
  
  $exigence2=stripos($holdingit,'$m')+2;
  $subm=substr($holdingit,$exigence2,4);
          //////////
  }
  if($holdingit=="")
    {
      
      if($globalis==0 && $thisissn!="")
      {
      
        
      $try001=serialthis($thisissn,$subm,$suba);  
        if (strlen($try001)>8)
        {
        $current001=$try001;
        }
        else
        {
        $current001='=001  JBENGTSON'.$globalincrement."\n".$holdingit;
        $globalincrement++; 
        }
      
      $holdingit=$current001."\n".$holdingit;

      }
      else 
      {
      $holdingit=$current001."\n".$holdingit;
        
      }
      
    $globalis=0;
    //commented out at Ex Libris request (they wanted
    //to create their own fix, so this became irrelevant
    if($globalduptest==1)
    {
    //builds flat file to be applied to correctly associate former dups in holdings file
    //man, this data is a greek tragedy
    $transfer001=substr($current001,7);
    $outputit = fopen("holdingsfix.txt", 'a');
    fwrite($outputit, $current999."|".$current910."#".$transfer001."\n"); 

    fclose($outputit);
    }
    $global999=0;
    $globalduptest=0;
    }
  
  //return $holdingit."\n";
  if ($holdingit!="x")
  {
    return $holdingit."\n";
  }
  else 
  {
  $holdingit="";
  return $holdingit;  
  }
}
getHoldings();
getSerials(); 
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
    //////////
  $currentline[$x]=farthis($currentline[$x]);
  $holding=$holding.$currentline[$x];
  
  $x++;
  //////////////
  }

  
}
/*main loop ends*/



/******appends to the file in chunks to save on system resources*****/
//800 lines is the change for this script, keeping things within a reasonable threshhold for system resources

  $t=0;
  while($t<$iter)
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
}}
///////output the holdings file
///////
$currentlinei="";
$numberofit=count($currentlineo);
$t=0;
  while ($t<$numberofit+1)
  {
  if($currentlineo[$t]!="abby normal")
{
  $aggregation=$aggregation.$currentlineo[$t]."\n";
  $currentlineo[$t]="";
}
  $t++; 
  }
$outputit = fopen("holdingsxy.mrk", 'w');
fwrite($outputit, $aggregation);

fclose($outputit);
$daobjecto="";
$outputit="";
///////////////
//////////////


?>
