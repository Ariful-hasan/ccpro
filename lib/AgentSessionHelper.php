<?php

class AgentSessionHelper
{
	function getSessionChart($bars, $stime, $etime, $busy_labels)
	{
		$chart = '';
		$chart .= '<table class="chart">';
		foreach ($bars as $bar_type=>$bar_points) {

			$last_draw_point = $stime;
			$bar_name = $bar_type == 'w' ? 'Work' : '';
			if (empty($bar_name)) {
				$_pause_id = substr($bar_type, 1);
				$bar_name = isset($busy_labels[$_pause_id]) ? $busy_labels[$_pause_id] : 'Pause (' . $_pause_id . ')';
			}
			$total_bar_time = 0;
			$chart .= "<tr><td align=right>$bar_name &nbsp;</td><td><div class=\"bar\">";
			foreach ($bar_points as $point) {
				$blank_div_width = $point->x - $last_draw_point;
				$filled_div_width = $point->w - $point->x;
				$last_draw_point = $point->w;
				$total_bar_time += $filled_div_width;
				$blank_div_width = round($blank_div_width/120);
				$filled_div_width = round($filled_div_width/120);
				if ($blank_div_width > 0) $chart .= "<div style=\"width:".$blank_div_width."px;\">&nbsp;</div>";
				if ($filled_div_width > 0) {
					$ttip = AgentSessionHelper::getToolTip($point);
					$chart .= "<div style=\"width:".$filled_div_width."px;\" class=\"filled\" title=\"$ttip\">&nbsp;</div>";
				}
			}
			$total_bar_time = DateHelper::get_formatted_time($total_bar_time);
			$chart .= "</div></td><td>$total_bar_time</td></tr>";
		}

		$chart .= "<tr><td align=right>&nbsp;</td><td><div style=\"border-top:1px solid #454545;height:8px;\">";
		$j = 6 * 30;
		for ($i=0; $i<=17; $i+=6) {
			$chart .= "<div style=\"width:".$j."px;float:left;height:8px;border-left:1px solid #454545;\"></div>";
		}
		$j-=3;
		$chart .= "<div style=\"width:".$j."px;float:left;height:8px;border-left:1px solid blue;border-right:1px solid #454545;\">&nbsp;</div></div>";
		$chart .= "</td><td align=left>&nbsp;</td></tr>";

		$chart .= "<tr><td align=right>&nbsp;</td><td><div style=\"height:8px;text-align:left;\">";
		$j = 6 * 30;
		for ($i=0; $i<=23; $i+=6) {
			$_i = sprintf("%02d", $i);
			$_w = ($i==0) ? $j - 25 : $j;
			$chart .= "<div style=\"width:".$_w."px;float:left;height:8px;\">$_i:00:00</div>";
		}
		$chart .= "<div style=\"width:20px;text-align:right;float:left;\">23:59:59</div></div></td><td align=left>&nbsp;</td></tr>";

		$chart .= '</table>';
		
		return $chart;
	}
	
	function getToolTip($point)
	{
		$ttip = date("H:i:s", $point->x) . ' to ' . date("H:i:s", $point->w);
		$ttip .= ' = ' . DateHelper::get_formatted_time($point->w-$point->x);
		return $ttip;
	}
	
	function getTimeLines($result, $stime, $etime)
	{
		$bars = array();
		$last_event = '';
		$last_event_time = $stime;
		$last_event_type = '';
		$last_pause_type = '';
		//$total_time = 0;
		$work_point = null;
		$pause_point = null;
		$bars['w'] = array();
//var_dump($result);
		foreach ($result as $log) {

			//$ctime = strtotime($log->logdate);
			$ctime = $log->tstamp;

			if (empty($work_point)) {
				if ($log->type == 'I') {
					$work_point->x = $ctime;
				} else {
					$work_point->x = $last_event_time;
				}
			}
	
			if ($log->type == 'I') {
				if (empty($work_point)) $work_point->x = $ctime;
			} else if ($log->type == 'O') {
				$work_point->w = $ctime;
				$bars['w'][] = $work_point;
				$work_point = null;
				
				if (!empty($pause_point)) {
					$pause_point->w = $ctime;
					$ptype = 'p' . $last_pause_type;
					$bars[$ptype][] = $pause_point;
					$pause_point = null;
					$last_pause_type = '';
				}
			} else if ($log->type == 'P' || $log->type == 'X') {
				if (!empty($pause_point)) {
					$pause_point->w = $ctime;
					$ptype = 'p' . $last_pause_type;
					$bars[$ptype][] = $pause_point;
				}
				$pause_point = null;
				$pause_point->x = $ctime;
				$last_pause_type = $log->value;
			} else if ($log->type == 'R') {
				if (empty($pause_point)) {
					$pause_point->x = $last_event_time;
					$last_pause_type = $last_event_type;
				}
				$pause_point->w = $ctime;
				$ptype = 'p' . $last_pause_type;
				$bars[$ptype][] = $pause_point;
				$pause_point = null;
				$last_pause_type = '';
			}
	
			$last_event = $log->type;
			$last_event_time = $ctime;
			$last_event_type = $log->value;
		}

		if (!empty($work_point)) {
			$work_point->w = $etime;
			$bars['w'][] = $work_point;
		}

		if (!empty($pause_point)) {
			$pause_point->w = $etime;
			$ptype = 'p' . $last_pause_type;
			$bars[$ptype][] = $pause_point;
		}

		return $bars;

	}
}

?>