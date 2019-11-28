<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once "model.php";

class CRUDTableTest extends \PHPUnit\Framework\TestCase
{
    public $db = null;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    // Database queries
    public function test_query()
    {
        $t = new IPBanTable($this->db);
        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals(10, count($rows));
    }

    public function test_count()
    {
        $t = new IPBanTable($this->db);
        $n = $t->count();
        $this->assertEquals(54, $n);
    }

    public function test_size()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r__size"=>5];
        $this->assertEquals(5, count($t->query()));
        $this->assertEquals(54, $t->count());
    }

    public function test_limit()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r__size"=>9001];
        $this->assertEquals(20, count($t->query()));
        $this->assertEquals(54, $t->count());
    }

    public function test_page_start()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r__page"=>1];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(10, count($rows));
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
    }

    public function test_page_offset()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r__page"=>2];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(10, count($rows));
        $this->assertEquals("1.2.3.11", $rows[0]["ip"]);
    }

    public function test_page_size_offset()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r__size"=>20, "r__page"=>3];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(20, count($rows));
        $this->assertEquals("1.2.3.41", $rows[0]["ip"]);
    }

    //class TableTest extends CRUDTableTest {
    public function test_table()
    {
        $t = new IPBanTable($this->db);
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertInstanceOf("\MicroHTML\HTMLElement", $html);
    }

    public function test_table_attrs()
    {
        $t = new IPBanTable($this->db);
        $t->table_attrs = ["class" => "zebra table"];
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertStringContainsString("table class='zebra table'", (string)$html);
    }

    //class FilterTest extends CRUDTableTest {
    public function test_default()
    {
        $t = new IPBanTable($this->db);
        list($q, $a) = $t->get_filter();

        $this->assertEquals("1=1 AND ((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", $q);
        $this->assertEquals([], $a);
    }

    public function test_flag()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("1=1", $q);
        $this->assertEquals([], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
    }

    public function test_eq()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_mode"=>"block"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("1=1 AND (mode = :mode)", $q);
        $this->assertEquals(['mode' => 'block'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals("block", $rows[0]["mode"]);
    }

    public function test_like()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_reason"=>"off"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("1=1 AND (reason LIKE :reason)", $q);
        $this->assertEquals(['reason' => '%off%'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.19", $rows[0]["ip"]);
        $this->assertEquals("offtopic", $rows[0]["reason"]);
    }

    public function test_foreign()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_banner"=>"Alice"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("1=1 AND (banner = :banner)", $q);
        $this->assertEquals(['banner' => 'Alice'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }

    // other html
    public function test_paginator()
    {
        $t = new IPBanTable($this->db);
        $this->assertStringContainsString("1", $t->paginator());
    }
}
