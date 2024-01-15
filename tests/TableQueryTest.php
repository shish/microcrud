<?php

declare(strict_types=1);

class TableQueryTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;
    public int $total = 0;

    public function setUp(): void
    {
        $this->db = create_mock_db();
        $this->total = 162;
    }

    // Database queries
    public function test_query(): void
    {
        $t = new IPBanTable($this->db);
        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals(10, count($rows));
    }

    public function test_count(): void
    {
        $t = new IPBanTable($this->db);
        $n = $t->count();
        $this->assertEquals($this->total, $n);
    }

    public function test_size(): void
    {
        $t = new IPBanTable($this->db);

        // sensible number
        $t->inputs = ["r__size" => 5];
        $this->assertEquals(5, count($t->query()));
        $this->assertEquals($this->total, $t->count());

        // empty: default
        $t->inputs = ["r__size" => ""];
        $this->assertEquals(10, $t->size());

        // invalid: default
        $t->inputs = ["r__size" => "foo"];
        $this->assertEquals(10, $t->size());

        // too high: set to limit
        $t->inputs = ["r__size" => "30"];
        $this->assertEquals(20, $t->size());
    }

    public function test_count_pages(): void
    {
        // 0 results should generate one page,
        // even though it's really 0 pages
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_ip" => "9.9.9.9"];
        $this->assertEquals(1, $t->count_pages());
    }

    /**
     * When the programmer sets size=null, we should return all
     * data instead of paginating.
     */
    public function test_size_null(): void
    {
        $t = new IPBanTable($this->db);
        $t->size = null;
        $this->assertEquals($t->count(), count($t->query()));
    }

    public function test_limit(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r__size" => 9001];
        $this->assertEquals(20, count($t->query()));
        $this->assertEquals($this->total, $t->count());
    }

    public function test_page_start(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__page" => 1];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(10, count($rows));
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
    }

    public function test_page_offset(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__page" => 2];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(10, count($rows));
        $this->assertEquals("1.2.3.11", $rows[0]["ip"]);
    }

    public function test_page_size_offset(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__size" => 20, "r__page" => 3];
        $t->order_by = ['id'];
        $rows = $t->query();
        $this->assertEquals(20, count($rows));
        $this->assertEquals("1.2.3.41", $rows[0]["ip"]);
    }

    public function test_sort(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__sort" => "banner"];
        $rows = $t->query();
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }

    public function test_reverse_sort(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__sort" => "-banner"];
        $rows = $t->query();
        $this->assertEquals("Charlie", $rows[0]["banner"]);
    }

    public function test_invalid_sort(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r__sort" => "asdfasdf"];
        $rows = $t->query();
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }
}
