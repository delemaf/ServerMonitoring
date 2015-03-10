<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function post_results($url, $instanceId, $data)
{
    $ch = curl_init();

    echo("sending to $url: $data".PHP_EOL);
        
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL,            $url.$instanceId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST,           1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $data ); 
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));
    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);    
}

function get_service_params($argc, $argv)
{
    ($argc>1) or die("Ce service nécessite un fichier d'option en argument.");
    $file_content = file_get_contents($argv[1]);
    $data = NULL;
    if ($file_content !== false) { $data = json_decode($file_content, TRUE); }
    ($data !== NULL) or die("Le parametre '$argv[1]' ne semble pas être un fichier"
                    . " JSON valide."); 

    return $data;
}


?>


