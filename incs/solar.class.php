<?php

class Solar {
	private $unix_epoch = [1348, 10, 11];
	private $s_months = [1 => 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
	private $leap_mods = [1, 5, 9, 13, 17, 22, 26, 30];
	
	public function gregorian_to_solar($g_year, $g_month = 1, $d_day = 1) {
		$timestamp = mktime(0, 0, 0, $g_month, $d_day, $g_year);
		
		list($year, $month, $day) = $this->unix_epoch;
		
		$days = floor($timestamp / 86400);
		$years = floor($days / 365);
		$modulus = $days % 365;
		
		$year += $years;
		
		$leap_years = [];
		
		for ($i = 1348; $i <= $year + 4; $i++) {
			if (in_array($i % 33, $this->leap_mods)) {
				array_push($leap_years, $i);
				if ($i < $year)
					$modulus -= 1;
			}
		}
		
		$this->s_months[12] = in_array($year, $leap_years) ? 30 : 29;
		
		for($i = 0; $i <= $modulus; $i++) {
			$day += 1;
			
			if ($day == ($this->s_months[$month] + 1)) {
				$month += 1;
				$day = 1;
			}
			
			if ($month == 13) {
				$year += 1;
				$month = 1;
				$this->s_months[12] = in_array($year, $leap_years) ? 30 : 29;
			}
		}
		
		if ($month < 10)
			$month = '0' . $month;

		if ($day < 10)
			$day = '0' . $day;
		
		return [$year, $month, $day];
	}
	
	function solar_to_gregorian($s_year, $s_month = 1, $s_day = 1) {
		$years = $s_year - $this->unix_epoch[0] - 1;
		$timestamp = $years * 31536000 ;
		
		for ($i = 1970; $i <= 1970 + $years; $i++)
			if ($i % 4 == 0 && ($i % 100 != 0 || $i % 400 == 0))
				$timestamp += 86400;
		
		$modulus = 78;
		
		for ($i = 1; $i < 13; $i++) {
			if ($s_month > $i)
				$modulus += $this->s_months[$i];
			else
			{
				$modulus += $s_day;
				break;
			}
		}
		
		if (in_array($s_year % 33, $this->leap_mods))
			$modulus -= 1;
		
		$timestamp += $modulus * 86400;
		
		return explode(' ', date('Y m d', $timestamp));
	}
}

?>