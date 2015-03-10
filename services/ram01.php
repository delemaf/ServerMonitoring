<?php
/*
** ram01.php for Monig in /services
**
** Random access memory load.
*/

require dirname(__FILE__).DIRECTORY_SEPARATOR.'utils/utils.php';

function get_memory()
{
	if(substr(PHP_OS, 0, 3) == 'WIN')
	{
		// Windows
		$wmi = new COM("Winmgmts://");
		$obj = $wmi->execquery("SELECT TotalVisibleMemorySize, FreePhysicalMemory
									FROM Win32_OperatingSystem");
		foreach($obj as $mem)
		{
			$memtotal = $mem->totalvisiblememorysize;
			$memfree = $mem->freephysicalmemory;
		}
   	}
   	else
	{
		// Unix
		foreach (file('/proc/meminfo') as $ri)
			$memfile[strtok($ri, ':')] = strtok('');
		$memfree = $memfile['MemFree'] + $memfile['Buffers'] + $memfile['Cached'];
		$memtotal = $memfile['MemTotal'];
	}
	$percent = round((100 - $memfree / $memtotal * 100), 1);
	$result['memtotal'] = round($memtotal / (1024 * 1024), 1);
	$result['memfree'] = round($memfree / (1024 * 1024), 1);
	$result['memused'] = round(($memtotal - $memfree) / (1024 * 1024), 1);;
	$result['ram_load'] = round($percent, 1);
	return $result;
}

$data = get_service_params($argc, $argv);
$result = get_memory();
$result['monig_id'] = $data['monig_id'];
$str = json_encode($result);
post_results($data['monig_post_url'], $data['monig_id'], $str);
?>