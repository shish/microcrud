<?php

declare(strict_types=1);

require_once __DIR__ . "/model.php";

use MicroHTML\HTMLElement;

class MockUrl
{
    public function __toString(): string
    {
        return "https://example.com";
    }
}

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
        $this->assertInstanceOf(HTMLElement::class, $html);
    }

    public function test_extra_inputs(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["extra_input" => "42"];
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertStringContainsString("name='extra_input' value='42'", (string)$html);
    }

    public function test_no_delete(): void
    {
        $t = new IPBanTable($this->db);
        $t->delete_url = null;
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertInstanceOf(HTMLElement::class, $html);
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

    public function test_stringable_url(): void
    {
        $t = new IPBanTable($this->db);
        $t->create_url = new MockUrl();
        $rows = $t->query();
        $html = $t->table($rows);
        $this->assertStringContainsString("https://example.com", (string)$html);
    }
}
