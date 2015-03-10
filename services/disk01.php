<?php
/*
** disk01.php for Monig in /services
**
** Hard disk drive usage.
*/

require dirname(__FILE__).DIRECTORY_SEPARATOR.'utils/utils.php';

function convert($bytes, $unit)
{
	// Convert bytes to gigabytes
	if ($unit == 'gb')
		return round(($bytes / 1073741824), 2);
	else if ($unit == 'mb')
		return round(($bytes / 1048576), 2);
	else
		return -42;
}

function get_disk()
{
	$all_size = $all_free = 0;
	if(substr(PHP_OS, 0, 3) == 'WIN')
	{
		// Windows
		$wmi = new COM("Winmgmts://");
		$obj = $wmi->execquery("SELECT *
								FROM Win32_LogicalDisk
								WHERE DriveType=3");
		foreach($obj as $disk)
		{
			$disks[$disk->DeviceID] = array('size'=>convert($disk->Size, 'gb'),
											'free'=>convert($disk->FreeSpace, 'gb'),
											'used'=>convert($disk->Size - $disk->FreeSpace, 'gb'),
											'percent'=>round(100 - ($disk->FreeSpace * 100) / $disk->Size));
			$all_size += $disk->Size;
			$all_free += $disk->FreeSpace;
		}
		$disks['total'] = array('size'=>convert($all_size, 'gb'),
								'free'=>convert($all_free, 'gb'),
								'used'=>convert($all_size - $all_free, 'gb'),
								'percent'=>round(100 - ($all_free * 100) / $all_size));
   	}
   	else
	{
		// Unix
		$buffer = shell_exec("df | grep -iE '^/dev/'");
		$buffer = preg_split("/\n/", $buffer);
		array_pop($buffer);
		$i = 0;
		foreach($buffer as $tmp)
		{
			$tab[] = preg_split("/[ ]+/", $tmp);
			$tab[$i][0] = preg_replace('/\/dev\//', '', $tab[$i][0]);
			$tab[$i][4] = preg_replace('/%/', '', $tab[$i][4]);
			$disks[$tab[$i][0]] = array('size'=>convert(intval($tab[$i][1]), 'mb'),
										'free'=>convert(intval($tab[$i][3]), 'mb'),
										'used'=>convert(intval($tab[$i][2]), 'mb'),
										'percent'=>intval($tab[$i][4]));
			$all_size += intval($tab[$i][1]);
			$all_free += intval($tab[$i][3]);
			$i++;
		}
		$disks['total'] = array('size'=>convert($all_size, 'mb'),
								'free'=>convert($all_free, 'mb'),
								'used'=>convert($all_size - $all_free, 'mb'),
								'percent'=>round(100 - ($all_free * 100) / $all_size));
	}
	return $disks;
}
$data = get_service_params($argc, $argv);
$result = get_disk();
$result['monig_id'] = $data['monig_id'];
$str = json_encode($result);
post_results($data['monig_post_url'], $data['monig_id'], $str);
?>
