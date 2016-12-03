<?php
  
  ini_set("max_execution_time", "30000");

  // how much detail we want. Larger number means less detail
  // (basically, how many bytes/frames to skip processing)
  // the lower the number means longer processing time
  define("DETAIL", 1);
  
  /**
   * GENERAL FUNCTIONS
   */
  function findValues($byte1, $byte2){
    $byte1 = hexdec(bin2hex($byte1));                        
    $byte2 = hexdec(bin2hex($byte2));                        
    return ($byte1 + ($byte2*256));
  }
  
  function func( $val ) {
	return ( $val * $val * $val )*0.05 - ( $val * $val );
  }
  
  function getPower( $filename ) {   
	   
      $width = 500;
      $height = 500;
      
      $samples = 0;
      $amp = 0;

      $img = false;

      $handle = fopen($filename, "r");
      // wav file header retrieval
      $heading[] = fread($handle, 4);
      $heading[] = bin2hex(fread($handle, 4));
      $heading[] = fread($handle, 4);
      $heading[] = fread($handle, 4);
      $heading[] = bin2hex(fread($handle, 4));
      $heading[] = bin2hex(fread($handle, 2));
      $heading[] = bin2hex(fread($handle, 2));
      $heading[] = bin2hex(fread($handle, 4));
      $heading[] = bin2hex(fread($handle, 4));
      $heading[] = bin2hex(fread($handle, 2));
      $heading[] = bin2hex(fread($handle, 2));
      $heading[] = fread($handle, 4);
      $heading[] = bin2hex(fread($handle, 4));
      
      // wav bitrate 
      $peek = hexdec(substr($heading[10], 0, 2));
      $byte = $peek / 8;
      
      // checking whether a mono or stereo wav
      $channel = hexdec(substr($heading[6], 0, 2));
      
      $ratio = ($channel == 2 ? 40 : 80);
      
      // start putting together the initial canvas
      // $data_size = (size_of_file - header_bytes_read) / skipped_bytes + 1
      $data_size = floor((filesize($filename) - 44) / ($ratio + $byte) + 1);
      $data_point = 0;
      
      // now that we have the data_size for a single channel (they both will be the same)
     
      while(!feof($handle) && $data_point < $data_size){
        if ($data_point++ % DETAIL == 0) {
          $bytes = array();
          
          // get number of bytes depending on bitrate
          for ($i = 0; $i < $byte; $i++)
            $bytes[$i] = fgetc($handle);
          
          switch($byte){
            // get value for 8-bit wav
            case 1:
              $data = findValues($bytes[0], $bytes[1]);
              break;
            // get value for 16-bit wav
            case 2:
              if(ord($bytes[1]) & 128)
                $temp = 0;
              else
                $temp = 128;
              $temp = chr((ord($bytes[1]) & 127) + $temp);
              $data = floor(findValues($bytes[0], $temp) / 256);
              break;
          }
          
          // skip bytes for memory optimization
          fseek($handle, $ratio, SEEK_CUR);
          
          // data values can range between 0 and 255
          $v = (int) ($data / 255 * $height);
          
          #echo $height - $v - ( $height - ($height - $v) ), "<br/>";
          $val = $height - $v - ( $height - ($height - $v) );
          $amp += $val * $val;
          $samples++;
          
        } else {
          // skip this one due to lack of detail
          fseek($handle, $ratio + $byte, SEEK_CUR);
        }
      }
      
      // close and cleanup
      fclose($handle);
	  
	  $rms = sqrt( $amp / $samples );
	  return func( $rms );  
    }
  
	#die( getPower( $_GET['name'] ) );
?>
