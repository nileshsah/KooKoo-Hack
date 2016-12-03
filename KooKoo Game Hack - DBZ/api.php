<?php

    function url_exists($url){
       $headers=get_headers($url);
       return stripos($headers[0],"200 OK")?true:false;
    }
    
	include("DSP.php");
	include("Num2Words.php");

	session_start();
	
	require_once("response.php");//include KooKoo library
	
	$r=new Response(); //create a response object
	$cd = new CollectDtmf();

	if (isset($_REQUEST['event']) && $_REQUEST['event'] == 'NewCall')   {
		$url = "http://" . $_SERVER['SERVER_NAME'] . "/data/final.mp3";
		$r->addPlayAudio($url);
		
		$r->addPlayText("Welcome to the Dragon Ball Zee Arena. Its time to power up."); 
		
		$url = "http://" . $_SERVER['SERVER_NAME'] . "/data/GokuPowerUp.mp3";
		$r->addPlayAudio($url);
		
		$r->addRecord(md5(time()), "wav", "3", "7");
		$r->maxduration = 7;
		
		$r->addPlayText("Calculating power level."); 
		

	}
	elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Record') //recording completed
	{
			//$r->addPlayText("Calculating power level."); 
            
            $url = $_REQUEST['data'];
			$no  = $_REQUEST['cid'];
			
			$wav = str_replace(".mp3", ".wav", $url );
			$wav = urldecode($wav);
			
			#echo $wav . "<br>";
			
			$temp = md5(time()) . ".wav";
			
			while( url_exists($wav) == false );
			
			
		    file_put_contents( $temp, fopen($wav, 'r'));
		
			$powerlvl = abs( floor( getPower( $temp ) ) );
			$words = convert_number_to_words( $powerlvl );
			
			#echo $words;
			
			$r->addPlayText("Your power level is $words. Impressive.");
			$r->addHangup();
	}

	$r->send();
?>
