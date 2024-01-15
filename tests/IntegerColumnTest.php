<?php

declare(strict_types=1);

use MicroCRUD\DateColumn;

class IntegerColumnTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_exact_match(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_banner_id" => "1"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(banner_id = :banner_id)", $q);
        $this->assertEquals(['banner_id' => '1'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals(81, $t->count());
    }
}
