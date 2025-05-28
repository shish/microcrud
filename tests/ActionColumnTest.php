<?php

declare(strict_types=1);

require_once __DIR__ . "/model.php";

use MicroCRUD\ActionColumn;

class ActionColumnTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_like(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_id" => "42"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(1=1)", $q);
        $this->assertEquals([], $a);
    }

    public function test_display_full(): void
    {
        $c = new ActionColumn("id");
        $c->table = new IPBanTable($this->db);
        $this->assertStringContainsString(
            "type='hidden' name='d_id' value='42'",
            (string)$c->display([
                "id" => 42,
                "expires" => null,
                "added" => null,
                "mode" => null,
            ])
        );
    }

    public function test_display_no_edit(): void
    {
        $c = new ActionColumn("id");
        $c->table = new IPBanTable($this->db);
        $c->table->update_url = null;
        $this->assertStringContainsString(
            "type='hidden' name='d_id' value='42'",
            (string)$c->display(["id" => 42])
        );
    }

    public function test_display_no_delete(): void
    {
        $c = new ActionColumn("id");
        $c->table = new IPBanTable($this->db);
        $c->table->update_url = null;
        $c->table->delete_url = null;
        $this->assertStringNotContainsString(
            "type='hidden' name='d_id' value='42'",
            (string)$c->display(["id" => 42])
        );
    }
}
