<?php
Function workdays($days, $uom , $working){

	$no_full_weeks = floor($days / 7);  
	$no_remaining_days = fmod($days, 7); 
	if ( $working == 1 ) {
		$workingDays = $no_full_weeks * 5;   
	} else {
		$workingDays = $no_full_weeks * 7; 
	}
	if ($no_remaining_days > 0 )    {      
		$workingDays += $no_remaining_days;    
	}    

	if ( $uom  == 'H' ) {
		$workingDays = $workingDays *8;
	}
	if ( $uom  == 'M' ) {
		$workingDays = $workingDays *480;
	}
	return $workingDays;
}