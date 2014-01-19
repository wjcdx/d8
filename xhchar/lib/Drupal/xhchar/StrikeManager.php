<?php

namespace Drupal\xhchar;

use Drupal\Core\Database\Connection;


class StrikeManager {

	static protected $DB_TABLE = "char_strike";

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

	public function findById($id) {
		// Read all fields from the table.
		$query = $this->connection->select(self::$DB_TABLE, 't');
		$query->fields('t');
		$query->condition("id", $id);

		return $query->execute();
	}

	public function findByNo($no) {
		// Read all fields from the table.
		$query = $this->connection->select(self::$DB_TABLE, 't');
		$query->fields('t');
		$query->condition("no", $no);

		return $query->execute();
	}

	public function findAll() {
		// Read all fields from the table.
		$query = $this->connection->select(self::$DB_TABLE, 't');
		$query->fields('t');

		return $query->execute();
	}

	public function add($strike) {
		// Read all fields from the table.
		$query = $this->connection->insert(self::$DB_TABLE);
		$query->fields(
			array(
				"no" => $strike["no"],
				"name" => $strike["name"],
				"fid" => $strike["fid"],
				"description" => $strike["desc"]
			)
		);

		return $query->execute();
	}

	public function update($strike) {
		// Read all fields from the table.
		$query = $this->connection->update(self::$DB_TABLE);
		$query->condition("id", $strike["id"]);
		$query->fields(
			array(
				"no" => $strike["no"],
				"name" => $strike["name"],
				"fid" => $strike["fid"],
				"description" => $strike["desc"]
			)
		);

		return $query->execute();
	}

	public function delete($id) {
		// Read all fields from the table.
		$query = $this->connection->delete(self::$DB_TABLE);
		$query->condition("id", $strike["id"]);

		return $query->execute();
	}
}

