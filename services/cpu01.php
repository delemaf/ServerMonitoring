<?php
/*
** cpu01.php for Monig in /services
**
** CPU load and temperature.
*/

require dirname(__FILE__).DIRECTORY_SEPARATOR.'utils/utils.php';

function get_cpu_load()
{
	if(substr(PHP_OS, 0, 3) == 'WIN')
	{
		// Windows
		$wmi = new COM("Winmgmts://");
		$server = $wmi->execquery("SELECT LoadPercentage
								   FROM Win32_Processor");		
		$cpu_num = 0;
		$load_total = 0;
		foreach($server as $cpu)
		{
			$cpu_num++;
			$load_total += $cpu->loadpercentage;
		}
		$load = round($load_total / $cpu_num, 2);
		return $load;
	}
	else
	{
		// Unix
		static $i = 0;
		static $prev_total = 0;
		static $prev_idle = 0;
		// Get the total CPU stats.
		$cpu_file = shell_exec("cat /proc/stat | grep '^cpu '");
		$buf = preg_split('/[\s,]+/', $cpu_file);
		$idle = intval($buf[4]);
		unset($buf[0]);
		// Calculate the total CPU time.
		$total = 0;
		foreach ($buf as $value)
			$total += $value;
		$diff_idle = $idle - $prev_idle;
		$diff_total = $total - $prev_total;
		$diff_usage = (1000 * ($diff_total - $diff_idle) / $diff_total + 5) / 10;
		$percent = round($diff_usage, 1);	
		$prev_total = $total;
		$prev_idle = $idle;
		if ($i == 0)
		{
			$i = 1;
			sleep(1);	
			// Recursive for the next check (1 second)
			$percent = get_cpu_load($prev_total, $prev_idle);
		}
		return intval($percent - 0.25);
	}
}

function get_cpu_temperature()
{
   if(substr(PHP_OS, 0, 3) == 'WIN')
	{ 
		// Windows
		$wmi = new COM("winmgmts://./root\WMI");
		$buffer = $wmi->execquery("SELECT *
								   FROM MSAcpi_ThermalZoneTemperature");
		foreach($buffer as $obj)
		{
			// output cpu(1) temp
			$temp = ($obj->CurrentTemperature / 10) - 273.15;	
			return $temp;
		}
	}
   else
	{
		// Unix
		$buf = shell_exec('cat /sys/class/hwmon/hwmon1/device/temp1_input');
		$temp = intval($buf) / 1000;
		return round($temp, 1);
	}
}

$data = get_service_params($argc, $argv);
$result['cpu_load'] = get_cpu_load();
$result['temperature'] = get_cpu_temperature();
$result['monig_id'] = $data['monig_id'];
$str = json_encode($result);
post_results($data['monig_post_url'], $data['monig_id'], $str);
?>