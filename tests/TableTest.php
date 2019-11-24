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
		$this->assertEquals(24, $n);
	}

	public function test_size() {
		$_GET["s__size"] = 5;
		$t = new IPBanTable($this->db);
		$this->assertEquals(5, count($t->query()));
		$this->assertEquals(24, $t->count());
	}

	public function test_limit() {
		$_GET["s__size"] = 9001;
		$t = new IPBanTable($this->db);
		$this->assertEquals(20, count($t->query()));
		$this->assertEquals(24, $t->count());
	}

	//class TableTest extends CRUDTableTest {
	public function test_table() {
		$t = new IPBanTable($this->db);
		$rows = $t->query();
		$html = $t->table($rows);
		$this->assertInstanceOf("\MicroHTML\HTMLElement", $html);
	}

	//class FilterTest extends CRUDTableTest {
	public function test_default() {
		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND ((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", $q);
		$this->assertEquals([], $a);
	}

	public function test_flag() {
		$_GET["s_all"] = "on";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1", $q);
		$this->assertEquals([], $a);
	}

	public function test_eq() {
		$_GET["s_all"] = "on";
		$_GET["s_mode"] = "block";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND (mode = :mode)", $q);
		$this->assertEquals(['mode' => 'block'], $a);
	}

	public function test_like() {
		$_GET["s_all"] = "on";
		$_GET["s_reason"] = "reason";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND (reason LIKE :reason)", $q);
		$this->assertEquals(['reason' => '%reason%'], $a);
	}

	public function test_foreign() {
		$_GET["s_all"] = "on";
		$_GET["s_banner"] = "User1";

		$t = new IPBanTable($this->db);
		list($q, $a) = $t->get_filter();

		$this->assertEquals("1=1 AND (banner = :banner)", $q);
		$this->assertEquals(['banner' => 'User1'], $a);
	}

	// other html
	public function test_paginator() {
		$t = new IPBanTable($this->db);
		$this->assertStringContainsString("1", $t->paginator());
	}
}
