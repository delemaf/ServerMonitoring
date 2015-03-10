<?php
/*
** ram01.php for Monig in /services
**
** Server uptime.
*/

require dirname(__FILE__).DIRECTORY_SEPARATOR.'utils/utils.php';

function aff_time($second, $minute, $hour, $day, $month, $year)
{
	echo "Zero : $year $month $day\n";
	echo "$hour:$minute:$second\n";
}

function get_uptime()
{
	$boottime = $localtime = 0;
	date_default_timezone_set('UTC');
	if(substr(PHP_OS, 0, 3) == 'WIN')
	{
		// Windows

		$wmi = new COM("Winmgmts://");
		$buffer = $wmi->execquery("SELECT LastBootUpTime, LocalDateTime
								   FROM Win32_OperatingSystem");
        foreach($buffer as $obj)
		{
			// echo $obj->LastBootUpTime.PHP_EOL;
            $byear = intval(substr($obj->LastBootUpTime, 0, 4));
            $bmonth = intval(substr($obj->LastBootUpTime, 4, 2));
            $bday = intval(substr($obj->LastBootUpTime, 6, 2));
            $bhour = intval(substr($obj->LastBootUpTime, 8, 2));
            $bminute = intval(substr($obj->LastBootUpTime, 10, 2));
            $bsecond = intval(substr($obj->LastBootUpTime, 12, 2));
            $lyear = intval(substr($obj->LocalDateTime, 0, 4));
            $lmonth = intval(substr($obj->LocalDateTime, 4, 2));
            $lday = intval(substr($obj->LocalDateTime, 6, 2));
            $lhour = intval(substr($obj->LocalDateTime, 8, 2));
            $lminute = intval(substr($obj->LocalDateTime, 10, 2));
            $lsecond = intval(substr($obj->LocalDateTime, 12, 2));
            $boottime = mktime($bhour, $bminute, $bsecond, $bmonth, $bday, $byear);
            $localtime = mktime($lhour, $lminute, $lsecond, $lmonth, $lday, $lyear);
		}   	
	}
   	else
	{
		// Unix
		$boottime = intval(shell_exec("cat /proc/uptime | grep '^[0-9]*'"));
		$localtime = intval(shell_exec("date +%s"));
		$boottime = $localtime - $boottime;
	}
	$btime = new DateTime();
	$btime->setTimestamp($boottime);
	$ltime = new DateTime();
	$ltime->setTimestamp($localtime);
	$time_diff = $ltime->diff($btime);
	$uptime = array('year'=>$time_diff->format('%Y'),
					'month'=>$time_diff->format('%M'),
					'day'=>$time_diff->format('%D'),
					'hour'=>$time_diff->format('%H'),
					'minute'=>$time_diff->format('%I'),
					'second'=>$time_diff->format('%S'));
	return $uptime;
}
$data = get_service_params($argc, $argv);
$result = get_uptime();
$result['monig_id'] = $data['monig_id'];
$str = json_encode($result);
post_results($data['monig_post_url'], $data['monig_id'], $str);
?>