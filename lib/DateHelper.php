<?php

class DateHelper
{
	static function get_input_time_details($allow_empty_date = false, $_sdate='', $_edate='', $_stime='', $_etime='')
	{
		//$sdate = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
		//$stime = isset($_REQUEST['stime']) ? trim($_REQUEST['stime']) : '';
		//$edate = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
		//$etime = isset($_REQUEST['etime']) ? trim($_REQUEST['etime']) : '';
		$err = '';

		//if (isset($_POST['sdate'])) {

			$sdate = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
			$stime = isset($_REQUEST['stime']) ? trim($_REQUEST['stime']) : '';
			$edate = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
			$etime = isset($_REQUEST['etime']) ? trim($_REQUEST['etime']) : '';

			$isValidSDate = false;
			$isValidEDate = false;
			$isValidSTime = false;
			$isValidETime = false;

			if (!empty($sdate)) {
				$sday = substr($sdate, 8, 2);
				$smonth = substr($sdate, 5, 2);
				$syear = substr($sdate, 0, 4);
				if (checkdate($smonth, $sday, $syear)) {
					$isValidSDate = true;
					if (!empty($stime)) {
						$shr = substr($stime, 0, 2);
						$smin = substr($stime, 3, 2);
						if (DateHelper::checktime($shr, $smin)) {
							$isValidSTime = true;
						}
					}
				}
			}
			
			if (!empty($edate)) {
				$eday = substr($edate, 8, 2);
				$emonth = substr($edate, 5, 2);
				$eyear = substr($edate, 0, 4);
				if (checkdate($emonth, $eday, $eyear)) {
					$isValidEDate = true;
					if (!empty($etime)) {
						$ehr = substr($etime, 0, 2);
						$emin = substr($etime, 3, 2);
						if (DateHelper::checktime($ehr, $emin)) {
							$isValidETime = true;
						}
					}
				}
			}
			
			if (!$isValidSDate) $sdate = '';
			if (!$isValidEDate) $edate = '';
			if (!$isValidSTime || !$isValidETime) {
				$stime = '';
				$etime = '';
			}
		//}

		if (empty($sdate) && empty($edate)) {
			if (!empty($_sdate)) {
				$sdate = $_sdate;
			}
			if (!empty($_edate)) {
				$edate = $_edate;
			}
			if (!empty($_stime)) {
				$stime = $_stime;
			}
			if (!empty($_etime)) {
				$etime = $_etime;
			}
		}
		if (empty($sdate) && !$allow_empty_date) {
			$sdate = date("Y-m-d");
		}
		
		//$sdate = empty($stime) ? '' : date('Y-m-d', $stime);
		//$edate = empty($etime) ? '' : date('Y-m-d', $etime);
		
		$sdate_time = empty($sdate) ? '' : strtotime($sdate);
		$edate_time = empty($edate) ? '' : strtotime($edate);
		if (!empty($sdate_time) && !empty($edate_time)) {
			$date_diff = round( abs($edate_time-$sdate_time) / 86400, 0 );
		} else {
			$date_diff = 0;
		}

		if (!empty($sdate_time) && !empty($edate_time) && $etime-$stime < 0) {
			$err = 'Provide positive date range !!';
		} else if ($date_diff > SUMMARY_REPORT_DAY) {
			$err = 'Date range is too large !!';
		}

		$ststamp = 0;
		$etstamp = 0;
		if (!empty($sdate)) {
			$_sdate_for_tstamp = $sdate;
			if (!empty($stime)) {
				$_sdate_for_tstamp .= "$stime:00";
			} else {
				$_sdate_for_tstamp .= "00:00:00";
			}
			$ststamp = strtotime($_sdate_for_tstamp);
		}

		if (!empty($edate)) {
			$_edate_for_tstamp = $edate;
			if (!empty($etime)) {
				$_edate_for_tstamp .= "$etime:59";
			} else {
				$_edate_for_tstamp .= "23:59:59";
			}
			$etstamp = strtotime($_edate_for_tstamp);
		}

		$dateinfo = new stdClass();
		$dateinfo->stime = $stime;
		$dateinfo->etime = $etime;
		$dateinfo->sdate = $sdate;
		$dateinfo->edate = $edate;
		$dateinfo->ststamp = $ststamp;
		$dateinfo->etstamp = $etstamp;
		
		$dateinfo->errMsg = $err;
		//var_dump($dateinfo);
		return $dateinfo;
	}
	

	static function get_date_title($dateinfo=null)
	{
		$title = '';
		if (empty($dateinfo)) return $title;
		$sdate = $dateinfo->sdate;
		$edate = $dateinfo->edate;
		$month_name = array('01'=>'JAN','02'=>'FEB','03'=>'MAR','04'=>'APR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AUG','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DEC');
		if (!empty($dateinfo->sdate) && !empty($dateinfo->edate)) {
			$sm = isset($month_name[substr($sdate, 5, 2)]) ? $month_name[substr($sdate, 5, 2)] : '';
			$em = isset($month_name[substr($edate, 5, 2)]) ? $month_name[substr($edate, 5, 2)] : '';
			$title = $sm.' '.substr($sdate, 8, 2).', ' .substr($sdate, 0, 4).' - '.$em.' '.substr($edate, 8, 2).', ' .substr($edate, 0, 4);
		} else if (!empty($dateinfo->sdate)) {
			$sm = isset($month_name[substr($sdate, 5, 2)]) ? $month_name[substr($sdate, 5, 2)] : '';
			$title = $sm.' '.substr($sdate, 8, 2).', ' .substr($sdate, 0, 4);
		} else if (!empty($dateinfo->edate)) {
			$em = isset($month_name[substr($edate, 5, 2)]) ? $month_name[substr($edate, 5, 2)] : '';
			$title = $em.' '.substr($edate, 8, 2).', ' .substr($edate, 0, 4);
		}
		
		return $title;
	}

	static function get_date_log($dateinfo=null)
	{
		$cond = '';
		if (empty($dateinfo)) return $cond;
		$sdate = $dateinfo->sdate;
		$edate = $dateinfo->edate;
		$stime = $dateinfo->stime;
		$etime = $dateinfo->etime;
		
		if (!empty($sdate) && !empty($edate)) {
			if (empty($stime) || empty($etime)) {
				$cond = "Date between $sdate and $edate";
			} else {
				$_stime = strtotime("$sdate $stime:00");
				$_etime = strtotime("$edate $etime:59");
				$cond = "Time between $_stime and $_etime";
			}
		} else if (!empty($sdate)) {
			$cond = "Date=$sdate";
		} else {
			$cond = "Date=$edate";
		}

		return $cond;
	}
	
	static function get_date_condition($field='', $sdate='', $edate='')
	{
		$cond = '';
		
		if (!empty($sdate) && !empty($edate)) {
			$cond = "$field BETWEEN '$sdate' AND '$edate'";
		} else if (!empty($sdate)) {
			$cond = "$field='$sdate'";
		} else if (!empty($edate)) {
			$cond = "$field='$edate'";
		}
		
		return $cond;
	}

	static function get_date_attributes($field='', $sdate='', $edate='', $stime='', $etime='', $is_timestamp=true)
	{
		$attr = new stdClass();
		$attr->condition = '';
		$attr->yy = '';
		$cond = '';
		$yy = '';
		if (empty($field)) return $attr;
		if (empty($sdate) && empty($edate)) return $attr;
		
		//$date_field_name = $is_field_type_time ? "FROM_UNIXTIME($field,'%Y-%m-%d')" : "$field";
		$date_field_name = "FROM_UNIXTIME($field,'%Y-%m-%d')";
		
		if (!empty($sdate) && !empty($edate)) {
			if (empty($stime) || empty($etime)) {
				//$cond = "$date_field_name BETWEEN '$sdate' AND '$edate'";
				if ($is_timestamp) {
				    $_stime = strtotime("$sdate 00:00:00");
				    $_etime = strtotime("$edate 23:59:59");
				} else {
				    $_stime = "$sdate 00:00:00";
				    $_etime = "$edate 23:59:59";
				}
				$cond = "$field BETWEEN '$_stime' AND '$_etime'";

			} else {
			        if ($is_timestamp) {
        				$_stime = strtotime("$sdate $stime:00");
	        			$_etime = strtotime("$edate $etime:59");
				} else {
				    $_stime = "$sdate $stime:00";
				    $_etime = "$edate $etime:59";
				}
				$cond = "$field BETWEEN '$_stime' AND '$_etime'";
			}
			$yy = substr($sdate, 2, 2); 
		} else if (!empty($sdate)) {
		        if ($is_timestamp) {
			    $_stime = strtotime("$sdate 00:00:00");
			    $_etime = strtotime("$sdate 23:59:59");
			} else {
			    $_stime = "$sdate 00:00:00";
			    $_etime = "$sdate 23:59:59";
			}
			$cond = "$field BETWEEN '$_stime' AND '$_etime'";
			//$cond = "'$sdate'=$date_field_name";
			$yy = substr($sdate, 2, 2);
		} else {
		        if ($is_timestamp) {
			    $_stime = strtotime("$edate 00:00:00");
			    $_etime = strtotime("$edate 23:59:59");
			} else {
			    $_stime = "$edate 00:00:00";
			    $_etime = "$edate 23:59:59";
			}
			$cond = "$field BETWEEN '$_stime' AND '$_etime'";
//			$cond = "'$edate'=$date_field_name";
			$yy = substr($edate, 2, 2);
		}
		$attr->condition = $cond;
		$attr->yy = $yy;
		return $attr;
	}
	
	static function get_date_attributes_new($field='', $sdate='', $edate='', $stime='', $etime='')
        {
                $attr = new stdClass();
                $attr->condition = '';
                $attr->yy = '';
                $cond = '';
                $yy = '';
                if (empty($field)) return $attr;
                if (empty($sdate) && empty($edate)) return $attr;

                //if ($stime == '00:00') $stime = '';
                //if ($etime == '00:00') $etime = '';
                //$date_field_name = $is_field_type_time ? "FROM_UNIXTIME($field,'%Y-%m-%d')" : "$field";
                $date_field_name = "FROM_UNIXTIME($field,'%Y-%m-%d')";

                if (!empty($sdate) && !empty($edate)) {
                        if (empty($stime) || empty($etime)) {
                                //$cond = "$date_field_name BETWEEN '$sdate' AND '$edate'";
                                if (!empty($stime)) {
                                    $_stime = strtotime("$sdate $stime:00");
                                    list($shour, $smin) = explode(":", $stime);
                                    $_etime = strtotime("$edate ".$shour.":59:59");
                                } elseif (!empty($etime)) {
                                    $_etime = strtotime("$edate $etime:00");
                                    list($shour, $smin) = explode(":", $etime);
                                    $_stime = strtotime("$sdate ".$shour.":00:59");
                                } else {
                                    $_stime = strtotime("$sdate 00:00:00");
                                    $_etime = strtotime("$edate 23:59:59");
                                }
                                $cond = "$field BETWEEN '$_stime' AND '$_etime'";
                        } else {
                                $_stime = strtotime("$sdate $stime:00");
                                $_etime = strtotime("$edate $etime:59");
                                $cond = "$field BETWEEN '$_stime' AND '$_etime'";
                        }
                        $yy = substr($sdate, 2, 2);
                } else if (!empty($sdate)) {
                        if (empty($stime)) {
                            $_stime = strtotime("$sdate 00:00:00");
                            $_etime = strtotime("$sdate 23:59:59");
                        } else {
                            $_stime = strtotime("$sdate $stime:00");
                            list($shour, $smin) = explode(":", $stime);
                            if ($shour == '00') $shour = '23'; 
                            $_etime = strtotime("$sdate ".$shour.":59:59");
                        }
                        //$_etime = strtotime("$sdate 23:59:59");
                        $cond = "$field BETWEEN '$_stime' AND '$_etime'";
                        //$cond = "'$sdate'=$date_field_name";
                        $yy = substr($sdate, 2, 2);
                } else {
                        if (empty($etime)) {
                            $_stime = strtotime("$edate 00:00:00");
                            $_etime = strtotime("$edate 23:59:59");
                        } else {
                            //$_stime = strtotime("$edate 00:00:00");
                            list($shour, $smin) = explode(":", $etime);
                            $_stime = strtotime("$edate ".$shour.":00:00");
                            $_etime = strtotime("$edate ".$shour.":59:59");
                        }
                        $cond = "$field BETWEEN '$_stime' AND '$_etime'";
//                      $cond = "'$edate'=$date_field_name";
                        $yy = substr($edate, 2, 2);
                }
                $attr->condition = $cond;
                $attr->yy = $yy;
                return $attr;
	}

	static function checktime($hour, $minute)
	{
		if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60) {
			return true;
		}
		return false;
	}
	
	static function get_formatted_time($string=0, $format='h:m:s')
	{
		if (empty($string)) $string = 0;
		$h = 0;
		$m = 0;
		$s = $string;
	
		$is_minute = strpos($format, 'm');
		$is_hour = strpos($format, 'h');
		$is_minute = $is_minute === false ? false : true;
		$is_hour = $is_hour === false ? false : true;
	
		if ($is_minute || $is_hour) {
			$m = (int)($s/60);
			$s = $s%60;
		}
		if ($is_hour) {
			$h = (int)($m/60);
			$m = $m%60;
		}
	
		$h = sprintf("%02d", $h);
		$m = sprintf("%02d", $m);
		$s = sprintf("%02d", $s);

		$return = $format;
		$return = str_replace("s", $s, $return);
		$return = str_replace("m", $m, $return);
		$return = str_replace("h", $h, $return);

		return $return;
	}
	
	static function get_cc_time_details($dateTimeArr=array(), $allow_empty_date = false, $_sdate='', $_edate='', $_stime='', $_etime='')
	{
	    $err = '';
	
	    //$dateTimeArr['from'] = '2015-09-20 00:00';
	    //$dateTimeArr['to'] = '2015-10-10 23:29';
	    $sdate = isset($dateTimeArr['from']) && !empty($dateTimeArr['from']) ? trim($dateTimeArr['from']) : $_sdate;
	    $stime = !empty($sdate) ? date('H:i', strtotime($sdate)) : $_stime;
	    $sdate = !empty($sdate) ? date('Y-m-d', strtotime($sdate)) : '';
	    
	    $edate = isset($dateTimeArr['to']) && !empty($dateTimeArr['to']) ? trim($dateTimeArr['to']) : ($_edate);
	    if(empty($edate) && !empty($sdate)){
	    	$stime="00:00";
	    	$edate=$sdate;
	    	$etime="23:59";
	    }else{
	    	$etime = !empty($edate) ? date('H:59', strtotime($edate)) : $_etime;
	    	$edate = !empty($edate) ? date('Y-m-d', strtotime($edate)) : '';
	    }
	
	    $isValidSDate = false;
	    $isValidEDate = false;
	    $isValidSTime = false;
	    $isValidETime = false;
	
	    if (!empty($sdate)) {
	        $sday = substr($sdate, 8, 2);
	        $smonth = substr($sdate, 5, 2);
	        $syear = substr($sdate, 0, 4);
	        if (checkdate($smonth, $sday, $syear)) {
	            $isValidSDate = true;
	            if (!empty($stime)) {
	                $shr = substr($stime, 0, 2);
	                $smin = substr($stime, 3, 2);
	                if (DateHelper::checktime($shr, $smin)) {
	                    $isValidSTime = true;
	                }
	            }
	        }
	    }
	    	
	    if (!empty($edate)) {
	        $eday = substr($edate, 8, 2);
	        $emonth = substr($edate, 5, 2);
	        $eyear = substr($edate, 0, 4);
	        if (checkdate($emonth, $eday, $eyear)) {
	            $isValidEDate = true;
	            if (!empty($etime)) {
	                $ehr = substr($etime, 0, 2);
	                $emin = substr($etime, 3, 2);
	                if (DateHelper::checktime($ehr, $emin)) {
	                    $isValidETime = true;
	                }
	            }
	        }
	    }
	    	
	    if (!$isValidSDate) $sdate = '';
	    if (!$isValidEDate) $edate = '';
	    if (!$isValidSTime || !$isValidETime) {
	        $stime = '';
	        $etime = '';
	    }
	
	    if (empty($sdate) && empty($edate)) {
	        if (!empty($_sdate)) {
	            $sdate = $_sdate;
	        }
	        if (!empty($_edate)) {
	            $edate = $_edate;
	        }
	        if (!empty($_stime)) {
	            $stime = $_stime;
	        }
	        if (!empty($_etime)) {
	            $etime = $_etime;
	        }
	    }
	    if (empty($sdate) && !$allow_empty_date) {
	        $sdate = date("Y-m-d");
	    }

	    $sdate_time = empty($sdate) ? '' : strtotime($sdate);
	    $edate_time = empty($edate) ? '' : strtotime($edate);
	    if (!empty($sdate_time) && !empty($edate_time)) {
	        $date_diff = round( abs($edate_time-$sdate_time) / 86400, 0 );
	    } else {
	        $date_diff = 0;
	    }
	
	    if (!empty($sdate_time) && !empty($edate_time) && $etime-$stime < 0) {
	        $err = 'Provide positive date range !!';
	    } else if ($date_diff > SUMMARY_REPORT_DAY) {
	        $err = 'Date range is too large !!';
	    }
	
	    $ststamp = 0;
	    $etstamp = 0;
	    if (!empty($sdate)) {
	        $_sdate_for_tstamp = $sdate;
	        if (!empty($stime)) {
	            $_sdate_for_tstamp .= "$stime:00";
	        } else {
	            $_sdate_for_tstamp .= "00:00:00";
	        }
	        $ststamp = strtotime($_sdate_for_tstamp);
	    }
	
	    if (!empty($edate)) {
	        $_edate_for_tstamp = $edate;
	        if (!empty($etime)) {
	            $_edate_for_tstamp .= "$etime:59";
	        } else {
	            $_edate_for_tstamp .= "23:59:59";
	        }
	        $etstamp = strtotime($_edate_for_tstamp);
	    }
	
	    $dateinfo = new stdClass();
	    $dateinfo->stime = $stime;
	    $dateinfo->etime = $etime;
	    $dateinfo->sdate = $sdate;
	    $dateinfo->edate = $edate;
	    $dateinfo->ststamp = $ststamp;
	    $dateinfo->etstamp = $etstamp;
	
	    $dateinfo->errMsg = $err;
	    //var_dump($dateinfo);
	    return $dateinfo;
	}
	static function get_input_report_time_details($allow_empty_date = false, $_sdate='', $_edate='', $_stime='', $_etime='', $sdate_pm_string='', $edate_pm_string='', $date_range='')
	{
		$err = '';
		$date_range = empty($date_range) ? SUMMARY_REPORT_DAY : $date_range;
		
		$sdate = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
		$stime = isset($_REQUEST['stime']) ? trim($_REQUEST['stime']) : '';
		$edate = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
		$etime = isset($_REQUEST['etime']) ? trim($_REQUEST['etime']) : '';

		$isValidSDate = false;
		$isValidEDate = false;
		$isValidSTime = false;
		$isValidETime = false;

		if (!empty($sdate)) {
			$sday = substr($sdate, 8, 2);
			$smonth = substr($sdate, 5, 2);
			$syear = substr($sdate, 0, 4);
			if (checkdate($smonth, $sday, $syear)) {
				$isValidSDate = true;
				if (!empty($stime)) {
					$shr = substr($stime, 0, 2);
					$smin = substr($stime, 3, 2);
					if (DateHelper::checktime($shr, $smin)) {
						$isValidSTime = true;
					}
				}
			}
		}
		
		if (!empty($edate)) {
			$eday = substr($edate, 8, 2);
			$emonth = substr($edate, 5, 2);
			$eyear = substr($edate, 0, 4);
			if (checkdate($emonth, $eday, $eyear)) {
				$isValidEDate = true;
				if (!empty($etime)) {
					$ehr = substr($etime, 0, 2);
					$emin = substr($etime, 3, 2);
					if (DateHelper::checktime($ehr, $emin)) {
						$isValidETime = true;
					}
				}
			}
		}
		
		if (!$isValidSDate) $sdate = '';
		if (!$isValidEDate) $edate = '';
		if (!$isValidSTime || !$isValidETime) {
			$stime = '';
			$etime = '';
		}

		if (empty($sdate) && empty($edate)) {
			if (!empty($_sdate)) {
				$sdate = $_sdate;
			}
			if (!empty($_edate)) {
				$edate = $_edate;
			}
			if (!empty($_stime)) {
				$stime = $_stime;
			}
			if (!empty($_etime)) {
				$etime = $_etime;
			}
		}
		if (empty($sdate) && !$allow_empty_date) {
			$sdate = date("Y-m-d");
		}
		
		$sdate_time = empty($sdate) ? '' : strtotime($sdate);
		$edate_time = empty($edate) ? '' : strtotime($edate);
		if (!empty($sdate_time) && !empty($edate_time)) {
			$date_diff = round( abs($edate_time-$sdate_time) / 86400, 0 );
		} else {
			$date_diff = 0;
		}

		if (!empty($sdate_time) && !empty($edate_time) && $etime-$stime < 0) {
			$err = 'Provide positive date range !!';
		} else if ($date_diff > $date_range) {
			$err = 'Date range is too large !!';
		}

		$ststamp = 0;
		$etstamp = 0;
		if (!empty($sdate)) {
			$_sdate_for_tstamp = $sdate;
			if (!empty($stime)) {
				$_sdate_for_tstamp .= "$stime:00";
			} else {
				$_sdate_for_tstamp .= "00:00:00";
			}
			$ststamp = strtotime($_sdate_for_tstamp);
		}

		if (!empty($edate)) {
			$_edate_for_tstamp = $edate;
			if (!empty($etime)) {
				$_edate_for_tstamp .= "$etime:59";
			} else {
				$_edate_for_tstamp .= "23:59:59";
			}
			$etstamp = strtotime($_edate_for_tstamp);
		}

		$dateinfo = new stdClass();
		$dateinfo->stime = $stime;
		$dateinfo->etime = $etime;
		$dateinfo->sdate = $sdate;
		$dateinfo->edate = $edate;
		$dateinfo->ststamp = $ststamp;
		$dateinfo->etstamp = $etstamp;		
		$dateinfo->errMsg = $err;

		if(!empty($edate_pm_string)){
			$etime = date('H', strtotime($dateinfo->edate.' '.$dateinfo->etime.':00 '.$edate_pm_string));
        	$edate = date('Y-m-d', strtotime($dateinfo->edate.' '.$dateinfo->etime.':00 '.$edate_pm_string));
        	$etstamp = strtotime($dateinfo->edate.' '.$dateinfo->etime.':00 '.$edate_pm_string);

			$dateinfo->etime = $etime;
			$dateinfo->edate = $edate;
			$dateinfo->etstamp = $etstamp;
		}
		if ((int)$dateinfo->etstamp-(int)$dateinfo->ststamp > 0 && $dateinfo->errMsg == 'Provide positive date range !!') {
			$dateinfo->errMsg = '';
		}

		return $dateinfo;
	}
}
//echo DateHelper::get_formatted_time(581869, 'h:m');
?>