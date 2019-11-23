<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MicroCRUD\{Column,Table};

function anywhere($s) {return "%$s%";}

class IPBanTable extends Table {
	function __construct() {
		$this->table = "bans";
		$this->base_query = "
			SELECT *, users.name AS banner
			FROM bans JOIN users ON banner_id=users.id
		";

		$this->limit = 100;
		$this->columns = [
			new Column("ip", "IP", "(ip = :ip)"),
			new Column("reason", "Reason", "(reason LIKE :reason)", "anywhere"),
			new Column("banner_id", "Banner", "(banner_id = (SELECT id FROM user WHERE name = :banner_id))", null, "banner"),
			new Column("added", "Added", "(added LIKE :added)", "anywhere"),
			new Column("expires", "Expires", "(expires LIKE :expires)", "anywhere"),
			new Column("mode", "Mode", "(mode = :mode)"),
		];
		$this->order_by = ["expires", "id"];
		$this->flags = [
			"all" => ["((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", null],
		];
	}
}


class CRUDTableTest extends \PHPUnit\Framework\TestCase {
	var $db = null;

	function setUp(): void {
		$this->db = new PDO('sqlite::memory:', null, null, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);

		$this->db->exec("CREATE TABLE users (
            id integer PRIMARY KEY AUTOINCREMENT,
            name text NOT NULL
        );");
		$this->db->exec("INSERT INTO users(name) VALUES ('User1');");
		$this->db->exec("INSERT INTO users(name) VALUES ('User2');");

		$this->db->exec("CREATE TABLE bans (
            id integer PRIMARY KEY AUTOINCREMENT,
            ip inet NOT NULL,
            mode text DEFAULT 'block' NOT NULL,
            reason text NOT NULL,
            banner_id integer NOT NULL,
            added timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
            expires timestamp without time zone
        );");
		$this->db->exec("INSERT INTO bans(ip, reason, banner_id) VALUES ('1.2.3.4', 'test reason', 1);");
		$this->db->exec("INSERT INTO bans(ip, reason, banner_id) VALUES ('1.2.3.5', 'test reason', 1);");
		$this->db->exec("INSERT INTO bans(ip, reason, banner_id) VALUES ('1.2.3.6', 'leech', 2);");
		$this->db->exec("INSERT INTO bans(ip, reason, banner_id) VALUES ('1.2.3.7', 'leech', 2);");

		$_GET = [];
	}

	//class TableTest extends CRUDTableTest {
	public function test_table() {
		$t = new IPBanTable();
		$rows = $t->paged_query($this->db, 1);
		$html = $t->table($rows);
		$this->assertInstanceOf("\MicroHTML\HTMLElement", $html);
	}

	//class FilterTest extends CRUDTableTest {
	public function test_default() {
		$t = new IPBanTable();
		list($q, $a) = $t->get_filter();

		$this->assertEquals($q, "1=1 AND ((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))");
		$this->assertEquals($a, []);
	}

	public function test_flag() {
		$_GET["s_all"] = "on";

		$t = new IPBanTable();
		list($q, $a) = $t->get_filter();

		$this->assertEquals($q, "1=1");
		$this->assertEquals($a, []);
	}

	public function test_eq() {
		$_GET["s_all"] = "on";
		$_GET["s_mode"] = "block";

		$t = new IPBanTable();
		list($q, $a) = $t->get_filter();

		$this->assertEquals($q, "1=1 AND (mode = :mode)");
		$this->assertEquals($a, ['mode' => 'block']);
	}

	public function test_like() {
		$_GET["s_all"] = "on";
		$_GET["s_reason"] = "reason";

		$t = new IPBanTable();
		list($q, $a) = $t->get_filter();

		$this->assertEquals($q, "1=1 AND (reason LIKE :reason)");
		$this->assertEquals($a, ['reason' => '%reason%']);
	}

	public function test_foreign() {
		$_GET["s_all"] = "on";
		$_GET["s_banner_id"] = "User1";

		$t = new IPBanTable();
		list($q, $a) = $t->get_filter();

		$this->assertEquals($q, "1=1 AND (banner_id = (SELECT id FROM user WHERE name = :banner_id))");
		$this->assertEquals($a, ['banner_id' => 'User1']);
	}
}
