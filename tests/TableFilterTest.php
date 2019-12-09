<?php

class TableFilterTest extends \PHPUnit\Framework\TestCase
{
    public $db = null;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_default()
    {
        $t = new IPBanTable($this->db);
        list($q, $a) = $t->get_filter();

        $this->assertEquals("((expires > CURRENT_TIMESTAMP) OR (expires IS NULL))", $q);
        $this->assertEquals([], $a);
    }

    public function test_flag()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(1=1)", $q);
        $this->assertEquals([], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
    }
}