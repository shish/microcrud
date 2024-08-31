<?php

declare(strict_types=1);

class CustomColumnTest extends \PHPUnit\Framework\TestCase
{
    public \FFSPHP\PDO $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    // `WHERE ip_address = 'Alice'` is a syntax error in postgres - so
    // we need to check if the search string is a valid IP, and replace
    // with null if not
    public function test_custom_user(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_user_or_ip" => "Alice"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("((ip=:user_or_ip_1) OR (banner=:user_or_ip_0))", $q);
        $this->assertEquals(['user_or_ip_0' => 'Alice', 'user_or_ip_1' => null], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.1", $rows[0]["ip"]);
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }

    public function test_custom_ip(): void
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all" => "on", "r_user_or_ip" => "1.2.3.4"];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("((ip=:user_or_ip_1) OR (banner=:user_or_ip_0))", $q);
        $this->assertEquals(['user_or_ip_0' => '1.2.3.4', 'user_or_ip_1' => '1.2.3.4'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.4", $rows[0]["ip"]);
        $this->assertEquals("Alice", $rows[0]["banner"]);
    }
}
