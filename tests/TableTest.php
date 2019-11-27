<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once "model.php";

class CRUDTableTest extends \PHPUnit\Framework\TestCase {
	var $db = null;

	function setUp(): void {
		$this->db = create_mock_db();
		$_GET = [];
	}

	// Database queries
	public function test_query() {
		$t = new IPBanTable($this->db);
		$rows = $t->query();
		$this->assertEquals("1.2.3.1", $rows[0]["ip"]);
		$this->assertEquals(10, count($rows));
	}

	public function test_count() {
		$t = new IPBanTable($this->db);
		$n = $t->count();
		$this->assertEquals(54, $n);
	}

	public function test_size() {
		$_GET["r__size"] = 5;
		$t = new IPBanTable($this->db);
		$this->assertEquals(5, count($t->query()));
		$this->assertEquals(54, $t->count());
	}

	public function test_limit() {
		$_GET["r__size"] = 9001;
		$t = new IPBanTable($this->db);
		$this->assertEquals(20, count($t->query()));
		$this->assertEquals(54, $t->count());
	}

	public function test_page_start() {
		$_GET["r_all"] = "on";
		$_GET["r__page"] = 1;
		$t = new IPBanTable($this->db);
		$t->order_by = ['id'];
		$rows = $t->query();
		$this->assertEquals(10, count($rows));
		$this->assertEquals("1.2.3.1", $rows[0]["ip"]);
	}

	public function test_page_offset() {
		$_GET["r_all"] = "on";
		$_GET["r__page"] = 2;
		$t = new IPBanTable($this->db);
		$t->order_by = ['id'];
		$rows = $t->query();
		$this->assertEquals(10, count($rows));
		$this->assertEquals("1.2.3.11", $rows[0]["ip"]);
	}

	public function test_page_size_offset() {
		$_GET["r_all"] = "on";
		$_GET["r__size"] = 20;
		$_GET["r__page"] = 3;
		$t = new IPBanTable($this->db);
		$t->order_by = ['id'];
		$rows = $t->query();
		$this->assertEquals(20, count($rows));
		$this->assertEquals("1.2.3.41", $rows[0]["ip"]);
	}

	//class TableTest extends CRUDTableTest {
	public function test_table() {
		$t = new IPBanTable($this->db);
		$rows = $t->query();
		$html = $t->table($rows);
		$this->assertInstanceOf("\MicroHTML\HTMLElement", $html);
	}

	public function test_css() {
		$t = new IPBanTable($this->db);
		$t->css = ["class" => "zebra table"];
		$rows = $t->query();
		$html = $t->table($rows);
		$this->assertStringContainsString("table class='zebra table'", (string)$html);
	}

	//class FilterTest extends CRUDTableTest {
	public function test_default() {
		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND ((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", $q);
		$this->assertEquals([], $a);
	}

	public function test_flag() {
		$_GET["r_all"] = "on";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1", $q);
		$this->assertEquals([], $a);

		$rows = $t->query();
		$this->assertEquals("1.2.3.1", $rows[0]["ip"]);
	}

	public function test_eq() {
		$_GET["r_all"] = "on";
		$_GET["r_mode"] = "block";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND (mode = :mode)", $q);
		$this->assertEquals(['mode' => 'block'], $a);

		$rows = $t->query();
		$this->assertEquals("1.2.3.1", $rows[0]["ip"]);
		$this->assertEquals("block", $rows[0]["mode"]);
	}

	public function test_like() {
		$_GET["r_all"] = "on";
		$_GET["r_reason"] = "off";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND (reason LIKE :reason)", $q);
		$this->assertEquals(['reason' => '%off%'], $a);

		$rows = $t->query();
		$this->assertEquals("1.2.3.19", $rows[0]["ip"]);
		$this->assertEquals("offtopic", $rows[0]["reason"]);
	}

	public function test_foreign() {
		$_GET["r_all"] = "on";
		$_GET["r_banner"] = "Alice";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND (banner = :banner)", $q);
		$this->assertEquals(['banner' => 'Alice'], $a);

		$rows = $t->query();
		$this->assertEquals("1.2.3.1", $rows[0]["ip"]);
		$this->assertEquals("Alice", $rows[0]["banner"]);
	}

	// other html
	public function test_paginator() {
		$t = new IPBanTable($this->db);
		$this->assertStringContainsString("1", $t->paginator());
	}
}
