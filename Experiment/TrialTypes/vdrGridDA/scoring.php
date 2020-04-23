<?php

$data = $_POST;

require __DIR__ . '/settings.php';

$settings = parse_settings($settings);

$_SESSION['last_grid_summary']   = array();
$_SESSION['last_grid_item_data'] = array();
$_SESSION['last_grid_type']      = $data['Study_Protocol'];

if ($settings['tones'] === 'on') {
    $tone_responses = json_decode($data['Tone_Responses']       , true);
    $tone_summary   = json_decode($data['Tone_Response_Summary'], true);
	
    $_SESSION['last_grid_summary']   = $tone_summary;
    $_SESSION['last_grid_item_data'] = $tone_responses;
}
