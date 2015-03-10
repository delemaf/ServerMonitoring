<?php
/*
** users01.php for Monig in /services
**
** Number of users connected.
*/

require dirname(__FILE__).DIRECTORY_SEPARATOR.'utils/utils.php';

function get_users()
{
   if(substr(PHP_OS, 0, 3) == 'WIN')
	{ 
		// Windows
		$wmi = new COM("Winmgmts://");
		$buffer = $wmi->execquery("SELECT Caption
									FROM Win32_Process");
		$users = 0;
		foreach($buffer as $process)
		{
            if (strtoupper($process->caption) == strtoupper('explorer.exe'))
				$users++;
		}
	}
   else
	{
		// Linux
		$users = shell_exec('who | wc -l');
	}
	return intval($users);
}

$data = get_service_params($argc, $argv);
$result['nb_users'] = get_users();
$result['monig_id'] = $data['monig_id'];
$str = json_encode($result);
post_results($data['monig_post_url'], $data['monig_id'], $str);
?>