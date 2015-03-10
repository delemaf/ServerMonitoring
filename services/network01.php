<?php
/*
** network01.php for Monig in /services
**
** Network info.
*/

require dirname(__FILE__).DIRECTORY_SEPARATOR.'utils/utils.php';

function get_network()
{
	$card_data = array('name'=>"", 'description'=>"", 'sent'=>0, 'received'=>0, 'total'=>0, 'bandwidth'=>0);
	if(substr(PHP_OS, 0, 3) == 'WIN')
	{
		// Windows
		$wmi = new COM("winmgmts://");
		$card = $wmi->execquery("SELECT *
								 FROM Win32_NetworkAdapter
								 WHERE PhysicalAdapter = true
								 AND NOT PNPDeviceID LIKE 'ROOT\\\%'
								 AND Manufacturer != 'Microsoft' 
								 AND (ConfigManagerErrorCode = 0 
								 OR (ConfigManagerErrorCode = 22 AND NetConnectionStatus = 0))");
		$buffer = $wmi->execquery("SELECT *
								   FROM Win32_PerfFormattedData_Tcpip_NetworkInterface");
		foreach($card as $tmp)
		{
			if ($tmp->speed)
			{
				$descritpion = $tmp->name;
				$id = $tmp->NetConnectionID.PHP_EOL;
			}
		}
		$card_data['name'] = utf8_encode($id);
		$name = str_replace(array("/","#","(",")"),array("_","_","[","]"),$descritpion);
		$card_data['description'] = $descritpion;
		foreach($buffer as $obj)
		{
			if ($obj->name == $name)
			{
				// I/O bytes network
				$card_data['sent'] = intval($obj->bytessentpersec);
				$card_data['received'] = intval($obj->bytesreceivedpersec);
				$card_data['total'] = intval($obj->bytestotalpersec);
				$card_data['bandwidth'] = intval($obj->currentbandwidth) / 1000000;
			}
		}
	}
	else
	{
		// Linux
		$buffer = shell_exec("cat /proc/net/dev | grep -iE 'wlan[0-9]*|eth[0-9]*'");
		$buffer = preg_split("/\n/", $buffer);
		array_pop($buffer);
		$card_data1 = array('sent'=>0, 'received'=>0, 'total'=>0);
		foreach($buffer as $tmp)
		{
			$tmp = preg_split("/[ :]+/", $tmp);
			if ($tmp[0] == "")
				array_shift($tmp);
			$tmp[0] = str_replace(':', '', $tmp[0]);
			$card_data1['sent'] += intval($tmp[9]);
			$card_data1['received'] += intval($tmp[1]);
			$card_data1['total'] += intval($tmp[9]) + intval($tmp[1]);
		}

		sleep(1);
		$buffer = shell_exec("cat /proc/net/dev | grep -iE 'wlan[0-9]*|eth[0-9]*'");
		$buffer = preg_split("/\n/", $buffer);
		array_pop($buffer);
		$i = 0;
		foreach($buffer as $tmp)
		{
			$tmp = preg_split("/[ :]+/", $tmp);
			if ($tmp[0] == "")
				array_shift($tmp);
			$tmp[0] = str_replace(':', '', $tmp[0]);
			$card_data['sent'] += intval($tmp[9]);
			$card_data['received'] += intval($tmp[1]);
			$card_data['total'] += intval($tmp[9]) + intval($tmp[1]);
			if(intval($tmp[1]) > 0 || intval($tmp[9]) > 0)
			{
				if ($i != 0)
				{
					$i++;
					$card_data['name'] .= " ";
				}
				$card_data['name'] .= $tmp[0];
			}
		}
		$card_data['sent'] -= $card_data1['sent'];
		$card_data['received'] -= $card_data1['received'];
		$card_data['total'] = $card_data['sent'] + $card_data['received'];
		if (preg_match("/wlan[0-9]*/", $card_data['name']))
			$card_data['description'] = "Connexion reseau sans fil";
		else if (preg_match("/eth[0-9]*/", $card_data['name']))
			$card_data['description'] = "Connexion reseau local";
		else
			$card_data['description'] = "Pas d'accès réseau";		
	}
	return $card_data;
}

$data = get_service_params($argc, $argv);
$result = get_network();
$result['monig_id'] = $data['monig_id'];
$str = json_encode($result);
post_results($data['monig_post_url'], $data['monig_id'], $str);
?>
