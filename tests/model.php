<?php
use MicroCRUD\{StringColumn,DateColumn,TextColumn,EnumColumn,Table};
use FFSPHP\PDO;

class IPBanTable extends Table {
	function __construct(\PDO $db, $token=null) {
		parent::__construct($db, $token);

		$this->table = "bans";
		$this->base_query = "
			SELECT bans.*, users.name AS banner
			FROM bans JOIN users ON banner_id=users.id
		";

		$this->size = 10;
		$this->limit = 20;
		$this->columns = [
			new StringColumn("ip", "IP"),
			new EnumColumn("mode", "Mode", ["Block"=>"block", "Firewall"=>"firewall"]),
			new TextColumn("reason", "Reason"),
			new StringColumn("banner", "Banner"),
			new DateColumn("added", "Added"),
			new DateColumn("expires", "Expires"),
		];
		$this->order_by = ["expires", "bans.id"];
		$this->flags = [
			"all" => ["((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", null],
		];
		$this->create_url = "/ip_ban/create";
		$this->delete_url = "/ip_ban/remove";
	}
}

function create_mock_db() {
	$e = getenv('DSN');
	$dsn = $e ? $e : 'sqlite::memory:';
	$db = new PDO($dsn, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	]);

	# FFS.
	$dbe = explode(":", $dsn)[0];
	$aipk = [
		"pgsql" => "INTEGER PRIMARY KEY NOT NULL GENERATED ALWAYS AS IDENTITY",
		"sqlite" => "INTEGER PRIMARY KEY AUTOINCREMENT",
		"mysql" => "INTEGER PRIMARY KEY AUTO_INCREMENT"
	][$dbe];

	$db->exec("DROP TABLE IF EXISTS users");
	$db->exec("CREATE TABLE users (
		id $aipk,
		name varchar(250) NOT NULL
	);");
	$db->exec("INSERT INTO users(name) VALUES ('User1');");
	$db->exec("INSERT INTO users(name) VALUES ('User2');");

	$db->exec("DROP TABLE IF EXISTS bans");
	$db->exec("CREATE TABLE bans (
		id $aipk,
		ip varchar(250) NOT NULL,
		mode varchar(250) NOT NULL DEFAULT 'block',
		reason text NOT NULL,
		banner_id integer NOT NULL,
		added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		expires timestamp NULL DEFAULT NULL
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
