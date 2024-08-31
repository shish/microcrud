<?php

declare(strict_types=1);

class TableHTMLTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_table(): void
    {
        $t = new IPBanTable($this->db);
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertInstanceOf("\MicroHTML\HTMLElement", $html);
    }

    public function test_no_delete(): void
    {
        $t = new IPBanTable($this->db);
        $t->delete_url = null;
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertInstanceOf("\MicroHTML\HTMLElement", $html);
    }

    public function test_table_attrs(): void
    {
        $t = new IPBanTable($this->db);
        $t->table_attrs = ["class" => "zebra table"];
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertStringContainsString("table class='zebra table'", (string)$html);
    }

    public function test_paginator(): void
    {
        $t = new IPBanTable($this->db);
        $this->assertStringContainsString("1", (string)$t->paginator());
    }
}
