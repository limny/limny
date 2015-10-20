<?php

class Menu {
	public function items() {
		global $db;

		$q = query();

		$current_q = count($q['param']) > 0 ? implode('/', $q['param']) : null;

		$result = $db->query('SELECT * FROM ' . DB_PRFX . 'menu WHERE enabled = 1 ORDER BY sort');
		while ($item = $result->fetch(PDO::FETCH_ASSOC)) {
			if (empty($item['address']) === false && strlen($item['address']) > 2 && substr($item['address'], 0, 1) === '[' && substr($item['address'], -1) === ']')
				$item['address'] = url(substr($item['address'], 1, -1));

			if ((empty($item['address']) && empty($current_q)) || strpos($item['address'], $current_q) !== false)
				$items['selected'] = $item;
			else
				$items[] = $item;
		}
		
		return $items;
	}
}

?>