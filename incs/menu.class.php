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
	private $registry;

	/**
	 * set registry property
	 * @param [type] $registry [description]
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * get menu items as an array
	 * selected item specified with different index name
	 * @return array/boolean boolean as error
	 */
	public function items() {
		$current_q = count($this->registry->q['param']) > 0 ? implode('/', $this->registry->q['param']) : null;

		$result = $this->registry->db->query('SELECT * FROM ' . DB_PRFX . 'menu WHERE enabled = 1 ORDER BY sort');
		while ($item = $result->fetch(PDO::FETCH_ASSOC)) {
			if (empty($item['address']) === false && strlen($item['address']) > 2 && substr($item['address'], 0, 1) === '[' && substr($item['address'], -1) === ']')
				$item['address'] = url(substr($item['address'], 1, -1));

			if ((empty($item['address']) && empty($current_q)) || strpos($item['address'], $current_q) !== false)
				$items['selected'] = $item;
			else
				$items[] = $item;
		}
		
		return isset($items) ? $items : false;
	}
}

?>