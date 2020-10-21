<?php declare(strict_types=1);

class ActionColumnTest extends \PHPUnit\Framework\TestCase
{
    public $db = null;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_like()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_id" => "42"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(1=1)", $q);
        $this->assertEquals([], $a);
    }

    public function test_display()
    {
        $c = new \MicroCRUD\ActionColumn("id");
        $c->table = new IPBanTable($this->db);
        $this->assertStringContainsString(
            "type='hidden' name='d_id' value='42'",
            (string)$c->display(["id"=>42])
        );
    }
}
