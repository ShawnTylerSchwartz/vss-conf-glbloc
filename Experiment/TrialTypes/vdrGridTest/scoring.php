<?php
    $data = array();
    
    // first, do things manually, to properly order them
    for ($i=1; $i<=10; ++$i) {
        $data["Coord$i"]        = $_POST["Coord$i"];
        $data["Value$i"]        = $_POST["Value$i"];
        $data["Correct$i"]      = $_POST["Correct$i"];
        $data["RespOrder$i"]    = $_POST["RespOrder$i"];
        $data["CorrectCoord$i"] = $_POST["CorrectCoord$i"];
    }
    
    // then, make sure we dont miss any inputs
    foreach ($_POST as $key => $val) {
        $data[$key] = $val;
    }
    
    // custom scoring
    $data['Selectivity_Index'] = '';
    $data['SI_Best']           = 0;
    $data['SI_Chance']         = 0;
    
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
