<?php
use MicroCRUD\{StringColumn,DateColumn,TextColumn,EnumColumn,Table};

class IPBanTable extends Table {
	function __construct(\PDO $db, $token=null) {
		parent::__construct($db, $token);

		$this->table = "bans";
		$this->base_query = "
			SELECT *, users.name AS banner
			FROM bans JOIN users ON banner_id=users.id
		";

		$this->size = 10;
		$this->columns = [
			new StringColumn("ip", "IP"),
			new EnumColumn("mode", "Mode", ["Block"=>"block", "Firewall"=>"firewall"]),
			new TextColumn("reason", "Reason"),
			new StringColumn("banner", "Banner"),
			new DateColumn("added", "Added"),
			new DateColumn("expires", "Expires"),
		];
		$this->order_by = ["expires", "id"];
		$this->flags = [
			"all" => ["((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", null],
		];
		$this->create_url = "/ip_ban/create";
		$this->delete_url = "/ip_ban/remove";
	}
}

function create_mock_db() {
	$db = new PDO('sqlite::memory:', null, null, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	]);

	$db->exec("CREATE TABLE users (
		id integer PRIMARY KEY AUTOINCREMENT,
		name text NOT NULL
	);");
	$db->exec("INSERT INTO users(name) VALUES ('User1');");
	$db->exec("INSERT INTO users(name) VALUES ('User2');");

	$db->exec("CREATE TABLE bans (
		id integer PRIMARY KEY AUTOINCREMENT,
		ip inet NOT NULL,
		mode text DEFAULT 'block' NOT NULL,
		reason text NOT NULL,
		banner_id integer NOT NULL,
		added timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
		expires timestamp without time zone
	);");

	$n = 1;
	foreach(['block', 'firewall'] as $mode) {
		foreach(['leech', 'spam', 'offtopic'] as $reason) {
			foreach([1, 2] as $banner_id) {
				foreach(['NULL', "'2000-01-01'", "'2030-01-01'"] as $expires) {
					$q = "
						INSERT INTO bans(ip, mode, reason, banner_id, expires)
						VALUES ('1.2.3.$n', '$mode', '$reason', $banner_id, $expires);
					";
					$db->exec($q);
					$n++;
				}
			}
		}
	}

	return $db;
}