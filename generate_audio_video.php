<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// echo "<pre>";
// var_dump($_POST);

$vlog_path = '/usr/local/ccpro/AA/vlog/';
$screen_logger_path = '/usr/local/ccpro/AA/screen_log/';
// $ulaw_path = '/usr/local/ccpro/AA/ulaw/';
$tmp_path = '/usr/local/apache2/htdocs/ccvl/resource/';
$url = 'http://192.168.10.64/ccvl/resource/';
$return = '';

if( (isset($_POST['callid']) && !empty($_POST['callid'])) && (isset($_POST['type']) && !empty($_POST['type'])) ){
	$callid = trim($_POST['callid']);
	$type = trim($_POST['type']);
	$tstamp = substr($callid, 0, 10);
	$year = date('Y', $tstamp);
	$month = date('m', $tstamp);
	$day = date('d', $tstamp);
	
	$ulaw_path = $vlog_path.$year.DIRECTORY_SEPARATOR.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR;
	$vlog_mp3_path = $vlog_path.$year.DIRECTORY_SEPARATOR.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR;
	$screen_logger_zip_path = $screen_logger_path.$year.DIRECTORY_SEPARATOR.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR;
	$generate_wav_path = $tmp_path.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR;
	$generate_mp3_path = $tmp_path.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR;
	$generate_mp4_path = $tmp_path.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR;

	if($type == 'audio'){
		//check mp3 exist or not
		if(file_exists($generate_mp3_path.$callid.'.mp3')){
	        $return['result'] = true;
	        $return['url'] = $url.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR.$callid.'.mp3';
	        die(json_encode($return));
		}else{
			// var_dump($ulaw_path.$callid.'.ulaw');
			// var_dump(file_exists($ulaw_path.$callid.'.ulaw'));
			if(file_exists($ulaw_path.$callid.'.ulaw')){
				if(!file_exists($generate_wav_path))
					mkdir($generate_wav_path, 0700, true);

				if(!file_exists($generate_wav_path.$callid.'.wav')){
					$wav_shell = '/usr/bin/sox -t ul -c 1 -r 8k -b 8 '.$ulaw_path.$callid.'.ulaw -t wav -s '.$generate_wav_path.$callid.".wav";
					$wav_result = exec($wav_shell);
					// var_dump($wav_shell);
					// var_dump($wav_result);
				}

				if(file_exists($generate_wav_path.$callid.'.wav')){
					$mp3_shell = '/usr/bin/lame --quiet -h --scale 2 -b 64 '.$generate_wav_path.$callid.'.wav '.$generate_mp3_path.$callid.".mp3";
					$mp3_result = exec($mp3_shell);
					// var_dump($mp3_shell);
					// var_dump($mp3_result);

					if(file_exists($generate_mp3_path.$callid.".mp3")){
				        $return['result'] = true;
	        			$return['url'] = $url.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR.$callid.'.mp3';
				        die(json_encode($return));
					}
				}
			}else{				
				if(file_exists($vlog_mp3_path.$callid.'.mp3')){
					if(!file_exists($generate_mp3_path))
						mkdir($generate_mp3_path, 0700, true);

					$copy = copy($vlog_mp3_path.$callid.".mp3", $generate_mp3_path.$callid.".mp3");
					// var_dump($copy);

					if($copy){						
						$return['result'] = true;
	    				$return['url'] = $url.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR.$callid.'.mp3';
			        	die(json_encode($return));	
					}
		        }
			}
		}
	}elseif($type == 'video'){
		//check mp4 exist or not
		if(file_exists($generate_mp4_path.$callid.'.mp4')){
	        $return['result'] = true;
	        $return['url'] = $url.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR.$callid.'.mp4';
	        die(json_encode($return));
		}else{
			if(file_exists($generate_wav_path.$callid.'.wav')){
				//unzip screenlog
				$zip_shell ="unzip ".$screen_logger_zip_path.$callid.".zip -d ".$generate_mp4_path;
				$zip_result = shell_exec($zip_shell);

				$mp4_shell = "/usr/bin/ffmpeg -nostats -loglevel 0 -f concat -i ".$generate_mp4_path."screenLog/data.txt -i ".$generate_mp4_path.$callid.".wav -s 1024x768 -crf 25 -vcodec libx264 -pix_fmt yuv420p ".$generate_mp4_path.$callid.".mp4";
				$mp4_result = shell_exec($mp4_shell);

				if(file_exists($generate_mp4_path.$callid.".mp4")){
			        $return['result'] = true;
        			$return['url'] = $url.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR.$callid.'.mp4';
			        die(json_encode($return));
				}
			}else{
				if(file_exists($ulaw_path.$callid.'.ulaw')){
					if(!file_exists($generate_wav_path))
						mkdir($generate_wav_path, 0700, true);
					
					$wav_shell = '/usr/bin/sox -t ul -c 1 -r 8k -b 8 '.$ulaw_path.$callid.'.ulaw -t wav -s '.$generate_wav_path.$callid.".wav";
					$wav_result = exec($wav_shell);

					if(file_exists($generate_wav_path.$callid.'.wav')){
						//unzip screenlog
						$zip_shell ="unzip ".$screen_logger_zip_path.$callid.".zip -d ".$generate_mp4_path;
						$zip_result = shell_exec($zip_shell);

						$mp4_shell = "/usr/bin/ffmpeg -nostats -loglevel 0 -f concat -i ".$generate_mp4_path."screenLog/data.txt -i ".$generate_mp4_path.$callid.".wav -s 1024x768 -crf 25 -vcodec libx264 -pix_fmt yuv420p ".$generate_mp4_path.$callid.".mp4";
						$mp4_result = shell_exec($mp4_shell);

						if(file_exists($generate_mp4_path.$callid.".mp4")){
					        $return['result'] = true;
		        			$return['url'] = $url.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR.$callid.'.mp4';
					        die(json_encode($return));
						}
					}
				}else{
					if(file_exists($vlog_mp3_path.$callid.'.mp3')){
						if(!file_exists($generate_mp3_path))
							mkdir($generate_mp3_path, 0700, true);

						$copy = copy($vlog_mp3_path.$callid.".mp3", $generate_mp3_path.$callid.".mp3");
						// var_dump($copy);

						if($copy){
							//unzip screenlog
							$zip_shell ="unzip ".$screen_logger_zip_path.$callid.".zip -d ".$generate_mp3_path;
							$zip_result = shell_exec($zip_shell);

							$mp3_shell = "/usr/bin/ffmpeg -nostats -loglevel 0 -f concat -i ".$generate_mp3_path."screenLog/data.txt -i ".$generate_mp3_path.$callid.".mp3 -s 1024x768 -crf 25 -vcodec libx264 -pix_fmt yuv420p ".$generate_mp3_path.$callid.".mp4";
							$mp3_result = shell_exec($mp3_shell);

							if(file_exists($generate_mp3_path.$callid.".mp4")){
						        $return['result'] = true;
			        			$return['url'] = $url.date('Y_m_d', $tstamp).DIRECTORY_SEPARATOR.$callid.DIRECTORY_SEPARATOR.$callid.'.mp4';
						        die(json_encode($return));
							}
						}
			        }
				}
			}
		}
	}
}

$return['result'] = false;
$return['url'] = '';
die(json_encode($return));

