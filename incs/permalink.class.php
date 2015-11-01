<?php

/**
 * Permalinks find/add/update/delete
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Permalink {
	// database connection
	private $db;

	/**
	 * load registry
	 * @param void
	 */
	public function __construct($registry) {
		$this->db = $registry->db;
	}

	/**
	 * check permalink exists or not
	 * @param  string $permalink_str
	 * @param  integer $exclude_id   exclude this id from existence ids
	 * @return boolean
	 */
	public function permalink_exists($permalink_str, $exclude_id = null) {
		if (is_array($permalink_str))
			$permalink_str = implode('/', $permalink_str);

		$values = [$permalink_str];

		if (empty($exclude_id) === false)
			$values[] = $exclude_id;

		$result = $this->db->prepare('SELECT query FROM ' . DB_PRFX . 'permalinks WHERE permalink = ?' . (empty($exclude_id) ? null : ' AND id != ?'));
		$result->execute($values);

		if ($permalink = $result->fetch(PDO::FETCH_ASSOC))
			return $permalink['query'];

		return false;
	}

	/**
	 * find permalink string by given page query parameter
	 * @param  string         $query
	 * @return string/boolean
	 */
	public function permalink_by_query($query) {
		$result = $this->db->prepare('SELECT id, permalink FROM ' . DB_PRFX . 'permalinks WHERE query = ?');
		$result->execute([$query]);

		if ($permalink = $result->fetch(PDO::FETCH_ASSOC))
			return $permalink;

		return false;
	}

	/**
	 * permalink generation pattern
	 * @param  string $str
	 * @return string     
	 */
	public function pattern($str) {
		$str = str_replace(['-', '!', '.', '/', '\\', '"', '\''], '', $str);
		$array = explode(' ', $str);
		$array = array_map('trim', $array);
		$array = array_filter($array);
		$str = implode('-', $array);

		return $str;
	}

	/**
	 * insert new permalink
	 * @param  string $query     page query parameter
	 * @param  string $permalink permalink name
	 * @return boolean
	 */
	public function add_permalink($query, $permalink) {
		return $this->db->prepare('INSERT INTO ' . DB_PRFX . 'permalinks (query, permalink) VALUES (?, ?)')->execute([$query, $permalink]);
	}

	/**
	 * update existence permalink by id or page query parameter
	 * @param  integer/string $id_or_query       current permalink id or page query parameter
	 * @param  string         $new_permalink_str
	 * @return boolean
	 */
	public function update_permalink($id_or_query, $new_permalink_str) {
		return $this->db->prepare('UPDATE ' . DB_PRFX . 'permalinks SET permalink = ? WHERE id = ? OR query = ?')->execute([$new_permalink_str, $id_or_query, $id_or_query]);
	}

	/**
	 * delete permalink
	 * @param  integer/string $id_or_query current permalink id or page query parameter
	 * @return boolean
	 */
	public function permalink_remove($id_or_query) {
		return $this->db->prepare('DELETE FROM ' . DB_PRFX . 'permalinks WHERE id = ? OR query = ?')->execute([$id_or_query, $id_or_query]);
	}

	/**
	 * generate new permalink name & check for not being duplicate
	 * @param  string  $title
	 * @param  integer $id    exclude this id
	 * @return string
	 */
	public function permalink_generate($title, $id = null) {
		$permalink = $this->pattern($title);
		$permalink_original = $permalink;

		$i = 1;
		while ($permalink_exists = $this->permalink_exists($permalink, $id))
			$permalink = $permalink_original . '-' . $i;

		return $permalink;
	}
}

?>