<?php

namespace Drupal\sina;

use Drupal\Core\Database\Connection;


class WeiboManager {

	static protected $DB_TABLE = "sina";

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

	public function findByUid($uid) {
		// Read all fields from the table.
		$query = $this->connection->select(self::$DB_TABLE, 't');
		$query->fields('t');
		$query->condition("uid", $uid);

		$wba = $query->execute()->fetchAll();
		return reset($wba);
	}

	public function findByWid($wid) {
		// Read all fields from the table.
		$query = $this->connection->select(self::$DB_TABLE, 't');
		$query->fields('t');
		$query->condition("weibo_uid", $wid);

		$wba = $query->execute()->fetchAll();
		return reset($wba);
	}

	public function findAll() {
		// Read all fields from the table.
		$query = $this->connection->select(self::$DB_TABLE, 't');
		$query->fields('t');
		return $query->execute()->fetchAll();
	}

	public function add($account) {
		$query = $this->connection->insert(self::$DB_TABLE);
		$query->fields($account);
		return $query->execute();
	}

	public function update($account) {
		$query = $this->connection->update(self::$DB_TABLE);
		$query->condition("uid", $account["uid"]);
		$query->fields(
			array(
				'weibo_uid' => $account['weibo_uid'],
				'access_token' => $account['access_token'],
				'sync' => $account['sync'],
			)
		);

		return $query->execute();
	}

	public function delete($uid) {
		return $this->connection
			->delete(self::$DB_TABLE)
			->condition("uid", $uid)
			->execute();
	}

	public function updateToken($uid, $token) {
		$query = $this->connection->update(self::$DB_TABLE);
		$query->condition("uid", $account["uid"]);
		$query->fields(
			array(
				'access_token' => $token,
			)
		);

		return $query->execute();
	}

	public function bind($uid, $wid) {
		
		$account = array(
			'uid' => $uid,
			'weibo_uid' => $wid,
			'access_token' => '',
			'sync' => 1,
		);

		$wba = $this->findByUid($uid);
		if (empty($wba)) {
			$this->add($account);
		} else {
			$this->update($account);
		}
	}

}

