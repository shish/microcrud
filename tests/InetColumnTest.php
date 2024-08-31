<?php

declare(strict_types=1);

use MicroCRUD\DateColumn;

class InetColumnTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    // Unit tests
    public function test_default_sql(): void
    {
        $mock_db = $this->createMock(\FFSPHP\PDO::class);
        $mock_db->method('getAttribute')->willReturn('sqlite');
        $t = new IPBanTable($mock_db);
        $t->inputs = ["r_all" => "on", "r_ip" => "1.2.3.4"];
        list($q, $a) = $t->get_filter();
        $this->assertEquals("(ip = :ip)", $q);
    }

    public function test_postgres_sql(): void
    {
        $mock_db = $this->createMock(\FFSPHP\PDO::class);
        $mock_db->method('getAttribute')->willReturn('pgsql');
        $t = new IPBanTable($mock_db);
        $t->inputs = ["r_all" => "on", "r_ip" => "1.2.3.4"];
        list($q, $a) = $t->get_filter();
        $this->assertEquals("(ip && cast(:ip as inet))", $q);
    }

    // Integration tests
    public function test_inet_exact_search(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_ip" => "1.2.3.4"];
        list($q, $a) = $t->get_filter();
        $rows = $t->query();

        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) == "pgsql") {
            $this->assertEquals("(ip && cast(:ip as inet))", $q);
        } else {
            $this->assertEquals("(ip = :ip)", $q);
        }
        $this->assertEquals(['ip' => '1.2.3.4'], $a);
        $this->assertEquals("1.2.3.4", $rows[0]["ip"]);
        $this->assertEquals(1, $t->count());
    }

    public function test_inet_range_search(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_ip" => "1.2.3.0/30"];
        list($q, $a) = $t->get_filter();
        $rows = $t->query();

        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) == "pgsql") {
            $this->assertEquals("(ip && cast(:ip as inet))", $q);
            $this->assertEquals(['ip' => '1.2.3.0/30'], $a);
            // .0/32 = 0,1,2,3 - but test data starts incrementing from 1
            $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
            $this->assertEquals(3, $t->count());
        } else {
            $this->assertEquals("(ip = :ip)", $q);
            $this->assertEquals(['ip' => '1.2.3.0/30'], $a);
            $this->assertEquals(0, $t->count());
        }
    }
}
