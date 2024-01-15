<?php

declare(strict_types=1);

use MicroCRUD\DateColumn;

class DateColumnTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_date_range_search(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_added" => ["1985/01/01", "1995/02/01"]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(added >= :added_0 AND added < :added_1)", $q);
        $this->assertEquals(['added_0' => '1985/01/01', 'added_1' => '1995/02/02'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.4", $rows[0]["ip"]);
        $this->assertEquals(81, $t->count());
    }

    public function test_date_one_day_search(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_added" => ["1990-01-01", "1990-01-01"]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(added >= :added_0 AND added < :added_1)", $q);
        $this->assertEquals(['added_0' => '1990-01-01', 'added_1' => '1990/01/02'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.4", $rows[0]["ip"]);
        $this->assertEquals(81, $t->count());
    }

    public function test_date_range_open_end(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_added" => ["1985/01/01", ""]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(added >= :added_0 AND added < :added_1)", $q);
        $this->assertEquals(['added_0' => '1985/01/01', 'added_1' => '9999/12/31'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.4", $rows[0]["ip"]);
        $this->assertEquals(162, $t->count());
    }

    public function test_date_range_open_start(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_added" => ["", "1995/02/01"]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(added >= :added_0 AND added < :added_1)", $q);
        $this->assertEquals(['added_0' => '0001/01/01', 'added_1' => '1995/02/02'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals(162, $t->count());
    }

    public function test_date_range_empty(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_added" => ["", ""]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(1=1)", $q);
        $this->assertEquals([], $a);
    }

    public function test_display(): void
    {
        $c = new DateColumn("test", "Test");
        $this->assertEquals(
            "2020/05/10",
            $c->display(["test" => "2020/05/10 12:34:56"])
        );
        $this->assertEquals(
            "",
            $c->display(["test" => null])
        );
    }
}
