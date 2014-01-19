<?php

namespace Drupal\bukuai;

use Drupal\Core\Database\Connection;


class BukuaiManager {

	/** 
	* The database connection used to check the IP against.
	*
	* @var \Drupal\Core\Database\Connection
	*/
	protected $connection;

	/** 
	* Construct the BanSubscriber.
	*
	* @param \Drupal\Core\Database\Connection $connection
	*   The database connection which will be used to check the IP against.
	*/
	public function __construct(Connection $connection) {
		$this->connection = $connection;
	}

	public function fmtstr_array($str = '')
	{
		if (strlen($str) == 0) {
			return array();
		}
		$str = str_replace('[', 'array(', $str);
		$str = str_replace(']', ')', $str);
		$str = str_replace(':', '=>', $str);
		$str = 'return ' . $str . ';';
		return eval($str);
	}

	public function fill_kvs($prefix = '', $no = 1, $chds = 0, &$kvs)
	{
		$kvs[$prefix] = array();
		$kvs[$prefix]['no'] = $no;
		$kvs[$prefix]['chds'] = $chds;
	}

	public function flat_tree($prefix = '', $no = 1, $ary = array(), &$kvs)
	{
		$this->fill_kvs($prefix, $no, count($ary), $kvs);

		$i = 1;
		foreach($ary as $key => $value) {
			if (is_array($value)) {
				$this->fill_kvs($prefix . '.' . $i, $key, count($value), $kvs);
				$this->flat_tree($prefix . '.' . $i, $key, $value, $kvs);
			} else {
				$this->fill_kvs($prefix . '.' . $i, $value, 0, $kvs);
			}
			$i++;
		}
	}

	private function entry_load($table, $entry = array()) {
		// Read all fields from the table.
		$query = $this->connection->select($table, 't');
		$query->fields('t');

		// Add each field and value as a condition to this query.
		foreach ($entry as $field => $value) {
			$query->condition($field, $value);
		}
		// Return the result in object format.
		return $query->execute()->fetchAll();
	}

	private function entry_count($table) {
		// Read all fields from the table.
		$query = $this->connection->select($table, 't');
		$query->fields('t');

		return count($query->execute());
	}

	public function node_load($entry = array())
	{
		return $this->entry_load('xhchar_node', $entry);
	}

	public function tree_load($entry = array())
	{
		return $this->entry_load('xhchar_tree', $entry);
	}

	public function node_count()
	{
		return $this->entry_count('xhchar_node');
	}

	public function tree_count()
	{
		return $this->entry_count('xhchar_tree');
	}
	
	/**
	 * $count must be bigger than 0.
	 */
	public function next_id($id, $count)
	{
		$id = ($id + 1) % $count;

		if ($id == 0)
			$id = $count;

		return $id;
	}


}

