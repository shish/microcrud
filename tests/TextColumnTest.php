<?php

declare(strict_types=1);

class TextColumnTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_whitespace(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_reason" => " "];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(1=1)", $q);
        $this->assertEquals([], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals("leech", $rows[0]["reason"]);
    }

    public function test_like(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_reason" => "off"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(LOWER(reason) LIKE LOWER(:reason))", $q);
        $this->assertEquals(['reason' => '%off%'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.55", $rows[0]["ip"]);
        $this->assertEquals("offtopic", $rows[0]["reason"]);
    }

    public function test_case_insensitive(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_reason" => "OFF"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(LOWER(reason) LIKE LOWER(:reason))", $q);
        $this->assertEquals(['reason' => '%OFF%'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.55", $rows[0]["ip"]);
        $this->assertEquals("offtopic", $rows[0]["reason"]);
    }
}
