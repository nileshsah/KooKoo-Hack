<?php
    ini_set('display_errors', 'on'); error_reporting(-1);
	session_start();
	
	if( !isset($_SESSION['onGoing']) ) {
	 $_SESSION['files'] = array();
	 $_SESSION['onGoing'] = true;
	}
	
    function isAlive($url){
       $headers=get_headers($url);
       return stripos($headers[0],"200 OK")?true:false;
    }
	
	require_once("response.php");
	
	$r=new Response();
	$cd = new CollectDtmf();

	if (isset($_REQUEST['event']) && $_REQUEST['event'] == 'NewCall')   {

		$r->addPlayText("Hello Kitty welcomes you. Speak now."); 				
		$r->addRecord(md5(time()), "wav", "2", "15");
		$r->addPlayText("Processing.");		

	}
	elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Record')  {          
            $url = urldecode( $_REQUEST['data'] );
			$mp3file = md5(time()) . ".mp3";
			$output = md5(time());
			$output_slow = $output . "_slow.mp3";
			$output = $output . "_op.mp3";
			
			$_SESSION['files'][] = $mp3file;
			$_SESSION['files'][] = $output_slow;
			$_SESSION['files'][] = $output;
									
			while( !isAlive($url) );
						
		    file_put_contents( $mp3file, fopen($url, 'r'));
				
			exec("ffmpeg -i $mp3file -filter:a \"atempo=0.7\" -vn $output_slow 2>&1", $op1);
			exec("ffmpeg -i $output_slow -filter:a \"asetrate=r=12K\" -vn $output 2>&1", $op2);
			
			
			$url = "http://" . $_SERVER['SERVER_NAME'] . "/" . $output;
			$r->addPlayAudio($url);
			$r->addRecord(md5(time()), "wav", "2", "15");
			$r->addPlayText("Processing.");	
	}
	elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Hangup')  {
		
		foreach($_SESSION['files'] as $key=>$value)
			unlink($value);
		
		session_unset();
		session_destroy();
	}

	$r->send();
?>
