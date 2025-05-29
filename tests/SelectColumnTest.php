<?php

declare(strict_types=1);

require_once __DIR__ . "/model.php";

use MicroCRUD\SelectColumn;

class SelectColumnTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_display_bulk(): void
    {
        $c = new SelectColumn("id");
        $c->table = new IPBanTable($this->db);
        $this->assertStringContainsString(
            "type='checkbox' form='bulk' name='id[]' value='42'",
            (string)$c->display(["id" => 42])
        );
    }

    public function test_display_no_bulk(): void
    {
        $c = new SelectColumn("id");
        $c->table = new IPBanTable($this->db);
        $c->table->bulk_url = null;
        $this->assertStringNotContainsString(
            "type='checkbox' form='bulk' name='id[]' value='42'",
            (string)$c->display(["id" => 42])
        );
    }
}
