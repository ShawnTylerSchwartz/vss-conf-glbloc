<?php

function parse_settings($settings) {
    $settings = json_decode('{' . $settings . '}', true);
    
    if ($settings === null) exit('Error: Settings defined incorrectly, cannot parse.');
    
    $settings = array_change_key_case($settings, CASE_LOWER);
    
    $defaults = array(
        'study protocol' => 'simultaneous',
        'tones' => 'on',
        'study time per item' => 3
    );
    
    $settings += $defaults;
    
    foreach ($settings as $key => $setting) {
        $settings[$key] = strtolower(trim($setting));
    }
    
    $settings['study time per item'] = (float) $settings['study time per item'];
    
    if ($settings['study protocol'] !== 'simultaneous') $settings['study protocol'] = 'sequential';
    
    if ($settings['tones'] !== 'on') $settings['tones'] = 'off';
    
    return $settings;
}
