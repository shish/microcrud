<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once "model.php";

use \MicroCRUD\EnumColumn;

class EnumColumnTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->db = create_mock_db();
        $_GET = [];
    }

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
}
