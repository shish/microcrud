<?php
use \MicroCRUD\EnumColumn;

class EnumColumnTest extends \PHPUnit\Framework\TestCase
{
    public $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    // HTML Generation
    public function test_no_selection()
    {
        $c = new EnumColumn("mode", "Mode", ["a"=>"1", "b"=>"2"]);
        $this->assertStringNotContainsString("selected", $c->read_input([]));
    }

    public function test_input_selection()
    {
        $c = new EnumColumn("mode", "Mode", ["a"=>"1", "b"=>"2"]);
        $this->assertStringContainsString("selected", $c->read_input(["r_mode" => "1"]));
    }

    public function test_create_selection()
    {
        $c = new EnumColumn("mode", "Mode", ["a"=>"1", "b"=>"2"]);
        $this->assertStringContainsString("selected", $c->create_input(["c_mode" => "1"]));
    }

    // SQL Generation
    public function test_string()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_mode"=>"block"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(mode = :mode)", $q);
        $this->assertEquals(['mode' => 'block'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals("block", $rows[0]["mode"]);
    }
}
