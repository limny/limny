<?php

/**
 * prepare menu items
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Menu {
	/**
	 * get menu items as an array
	 * @return array selected item specified with different index name
	 */
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