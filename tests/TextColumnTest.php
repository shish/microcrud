<?php

class TextColumnTest extends \PHPUnit\Framework\TestCase
{
    public $db = null;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_like()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_reason" => "off"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(reason LIKE :reason)", $q);
        $this->assertEquals(['reason' => '%off%'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.55", $rows[0]["ip"]);
        $this->assertEquals("offtopic", $rows[0]["reason"]);
    }
}
