<?php

class ForeignTextColumnTest extends \PHPUnit\Framework\TestCase
{
    public $db = null;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_foreign()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_banner" => "Alice"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(banner = :banner)", $q);
        $this->assertEquals(['banner' => 'Alice'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }
}
