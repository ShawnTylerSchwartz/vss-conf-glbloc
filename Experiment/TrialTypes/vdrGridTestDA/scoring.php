<?php
    $data = $_POST;
    
    // custom scoring
    $data['Selectivity_Index'] = '';
    $data['SI_Best']           = 0;
    $data['SI_Chance']         = 0;

    $combinedJOLs = array();
    
    $valueCopy    = explode('|', $value);
    $correctCount = 0;
    $possibleVal  = 0;
    $receivedVal  = 0;
    for ($i=1; $i<=10; ++$i) {
        $possibleVal += $data["Value$i"];
        if ($data["Correct$i"] == 1) {
            ++$correctCount;
            $receivedVal += $data["Value$i"];
        }
        
    }

    for ($i = 0; $i < 10; $i++) {
        $data["JOLItem$i"] = $_POST["JOLItem$i"];
        
        array_push($combinedJOLs, $data["JOLItem$i"]);
    }


    
    $siChance = $possibleVal / 10 * $correctCount;
    $siBest = 0;
    sort($valueCopy);
    for ($i=0; $i<$correctCount; ++$i) {
        $siBest += array_pop($valueCopy);
    }
    $data['SI_Best']   = $siBest;
    $data['SI_Chance'] = $siChance;
    
    if ($siBest != $siChance) {
        $data['Selectivity_Index'] = ($receivedVal-$siChance)/($siBest-$siChance);
    }
    
    $data['CorrectCount'] = $correctCount;
    $data['PossibleVal']  = $possibleVal;
    $data['ReceivedVal']  = $receivedVal;
    
    if (isset($_SESSION['last_grid_summary'])) {
        foreach ($_SESSION['last_grid_summary'] as $col => $val) {
            $data[$col] = $val;
        }
    }
	
	// sort stimuli into rows
	$stim_rows = array();
	
	foreach ($currentStimuli as $column => $vals) {
		$val_array = explode('|', $vals);
		foreach ($val_array as $i => $val) {
			$stim_rows[$i][$column] = $val;
		}
	}
    
    // take out individual data points, will be added later, line by line
    $grid_info = array(
        'Coord'        => array(),
        'Value'        => array(),
        'Correct'      => array(),
        'RespOrder'    => array(),
        'CorrectCoord' => array(),
        'JOL' => $combinedJOLs
    );
    
    for ($i=1; $i<=10; ++$i) {
        foreach ($grid_info as $category => $info) {
            $grid_info[$category][] = $data[$category . $i];
            unset($data[$category . $i]);
        }
    }
    
    foreach ($grid_info as $category => $values) {
        $data[$category] = implode(',', $values);
    }
    
	// prepare summary tone data to be added to every row of output
    if (isset($_SESSION['last_grid_summary'])) {
        foreach ($_SESSION['last_grid_summary'] as $category => $summary) {
            $data["All_Tones_$category"] = $summary;
        }
    }
    
    $currentTrial['Response'] = placeData($data, $currentTrial['Response'], $keyMod);
    
    $responses = array();
    
    foreach ($grid_info['Value'] as $i => $val) {
        $item_info = array('Presentation Order' => $i + 1);
        
        foreach ($grid_info as $category => $data_list) {
            $item_info[$category] = $data_list[$i];
        }
		
		// add stim info
		foreach ($stim_rows[$i] as $col => $stim_val) {
			$item_info["Stim_$col"] = $stim_val;
		}
        
        if (isset($_SESSION['last_grid_item_data'])) {
			// merge tone data in, converting arrays to strings when necessary
			foreach ($_SESSION['last_grid_item_data'][$i] as $col => $val) {
				$item_info["Tone_$col"] = is_array($val) ? implode('|', $val) : $val;
			}
        }
        
        $responses[] = $item_info;
    }
    
    // sort items by value
    $comparison_function = function($a, $b) {
        if ($a['Stim_Value'] == $b['Stim_Value']) return 0;
        
        return ($a['Stim_Value'] < $b['Stim_Value']) ? -1 : 1;
    };
	
    usort($responses, $comparison_function);
	
	$last_response_set = end($responses);
	
	foreach ($responses as $response_set) {
		$is_last_response = $last_response_set === $response_set;
		
		$extra_data = array();
		
		foreach ($response_set as $col => $val) {
			$extra_data["Grid_Item_$col"] = $val;
		}
		
		recordTrial(
			$extra_data,
			$is_last_response,
			$is_last_response
		);
	}
	
	header('Location: ' . $_PATH->get('Experiment Page'));
	exit;
