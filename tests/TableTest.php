<?php

class TableQueryTest extends \PHPUnit\Framework\TestCase
{
    public $db = null;

    public function setUp(): void
    {
        $this->db = create_mock_db();
        $this->total = 162;
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
        $this->assertEquals($this->total, $n);
    }

    public function test_size()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r__size" => 5];
        $this->assertEquals(5, count($t->query()));
        $this->assertEquals($this->total, $t->count());
    }

    /**
     * When the programmer sets size=null, we should return all
     * data instead of paginating.
     */
    public function test_size_null()
    {
        $t = new IPBanTable($this->db);
        $t->size = null;
        $this->assertEquals($t->count(), count($t->query()));
    }

    public function test_limit()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r__size" => 9001];
        $this->assertEquals(20, count($t->query()));
        $this->assertEquals($this->total, $t->count());
    }

    public function test_page_start()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__page" => 1];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(10, count($rows));
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
    }

    public function test_page_offset()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__page" => 2];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(10, count($rows));
        $this->assertEquals("1.2.3.11", $rows[0]["ip"]);
    }

    public function test_page_size_offset()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__size" => 20, "r__page" => 3];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(20, count($rows));
        $this->assertEquals("1.2.3.41", $rows[0]["ip"]);
    }

    public function test_sort()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__sort" => "banner"];
        $rows = $t->query();
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }

    public function test_reverse_sort()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__sort" => "-banner"];
        $rows = $t->query();
        $this->assertEquals("Charlie", $rows[0]["banner"]);
    }

    public function test_invalid_sort()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__sort" => "asdfasdf"];
        $rows = $t->query();
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }
}
