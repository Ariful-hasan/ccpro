<?php

class Erlang
{
	public function __construct(){

	}
	public function numberOfAgentsForSL ($rate, $duration, $interval, $wait_time, $serviceLvlGoal, $givenAgentOccupancy, $givenShrinkage) {
    	$intensity = $this->intensityF($rate, $duration, $interval);
    	$calculatedAgentOccupancy = 0.0;
    	$agents = ceil($intensity);
    	$agents_fl = (float) $agents;
    	//printf("agents before loop = %d  agents_fl =%.2f\n", agents, agents_fl);
	    while(1) {
	        $sl = $this->serviceLevel($agents_fl, $rate, $duration, $interval, $wait_time);
	        //printf(" sl = %.2f > serviLVGOal = %.2f \n", sl, serviceLvlGoal);
	        if( $sl >= $serviceLvlGoal){
	            //printf("inside break\n");
	            break;
	        }
	        $agents_fl = $agents_fl + 1.0;
	        //printf("agents in loop= %.2f  SL = %.2f\n", agents_fl, sl);
	    }
	    $agents = (float) $agents_fl;
	    $calculatedAgentOccupancy = $intensity / $agents_fl;
	    // printf("Before Occupancy Agent %.2f \n", $agents_fl);
	    while($givenAgentOccupancy > $calculatedAgentOccupancy){
	        //printf("calculatedAgentOccupancy %.2f \n", calculatedAgentOccupancy);
	        $agents_fl = $agents_fl - 1;
	        $calculatedAgentOccupancy = $intensity / $agents_fl;
	    }
	    while($calculatedAgentOccupancy > $givenAgentOccupancy){
	        //printf("calculatedAgentOccupancy %.2f \n", calculatedAgentOccupancy);
	        $agents_fl = $agents_fl + 1;
	        $calculatedAgentOccupancy = $intensity / $agents_fl;
	    }
	    // printf("After Occupancy Agent %.2f \n", $agents_fl);
	    // printf("Before shrinkage Agent %.2f \n", $agents_fl);
	    $agents_fl = $agents_fl/(1 - ($givenShrinkage/100));
	    // printf("After shrinkage Agent %.2f \n", $agents_fl);
	    $agents = (float) $agents_fl;
	    return $agents;
	}

	private function intensityF ($rate, $duration, $interval) {
	    $f = (($rate/(60.0*$interval))*$duration);
	    //printf("rate = %.2f, duration = %.2f, interval = %.2f intensityF= %.2f \n",rate, duration, interval, f);
	    return $f;
	}
	private function serviceLevel ($agents, $rate, $duration, $interval, $wait_time) {
     	$intensity = $this->intensityF($rate, $duration, $interval);
     	$erlang = $this->erlang_c($agents, $intensity);
     	$serviceLvl = 1 -  $erlang* exp(-($agents - $intensity)*($wait_time/$duration));
     	//printf("erlang_c %.2f \n",erlang* exp(-(agents - intensity)*(wait_time/duration)));
     	//printf("Service Level %.2f \n", serviceLvl);
     	//printf("Service Level %.2f %.2f %.2f %.2f %.2f  \n", serviceLvl, rate, duration, interval, wait_time);

     	return $serviceLvl;
 	}
 	private function erlang_c ($agents, $intensity) {
	    $erlang_b_inv = 1.0;
	    $erlang_b=0;
	    $erlang_c_f=0;
	    for($i =1; $i<$agents ; $i++) {
	        $erlang_b_inv = 1.0 + $erlang_b_inv * $i / $intensity;
	        //printf("erlang_b_inv= %.2f \n", erlang_b_inv);
	    }
	    $erlang_b = 1.0 /$erlang_b_inv;
	    //(number_of_agents - intensity * (1 - erlang_b(number_of_agents, intensity)))
	    $erlang_c_f = $agents * $erlang_b / ($agents - $intensity * (1 - $erlang_b));
	    return $erlang_c_f;
	}
}