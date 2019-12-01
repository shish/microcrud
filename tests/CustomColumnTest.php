<?php

class CustomColumnTest extends \PHPUnit\Framework\TestCase
{
    public $db = null;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_custom()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_user_or_ip" => "Alice"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("((ip=:user_or_ip) OR (banner=:user_or_ip))", $q);
        $this->assertEquals(['user_or_ip' => 'Alice'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }
}
