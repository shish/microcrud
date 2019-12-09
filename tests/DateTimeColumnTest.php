<?php
use \MicroCRUD\DateTimeColumn;

class DateTimeColumnTest extends \PHPUnit\Framework\TestCase
{
    public $db;

    public function setUp(): void
    {
        $this->db = create_mock_db();
    }

    public function test_date_range_search()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_expires"=>["2005/01/01", "2015/02/01"]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(expires >= :expires_0 AND expires < :expires_1)", $q);
        $this->assertEquals(['expires_0' => '2005/01/01', 'expires_1' => '2015/02/01'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.2", $rows[0]["ip"]);
        $this->assertEquals(81, $t->count());
    }

    public function test_date_range_open_end()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_expires"=>["1985/01/01", ""]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(expires >= :expires_0 AND expires < :expires_1)", $q);
        $this->assertEquals(['expires_0' => '1985/01/01', 'expires_1' => '9999/12/31'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.2", $rows[0]["ip"]);
        $this->assertEquals(162, $t->count());
    }

    public function test_date_range_open_start()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_expires"=>["", "2015/02/01"]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(expires >= :expires_0 AND expires < :expires_1)", $q);
        $this->assertEquals(['expires_0' => '0001/01/01', 'expires_1' => '2015/02/01'], $a);

        $rows = $t->query();
        $this->assertEquals("1.2.3.2", $rows[0]["ip"]);
        $this->assertEquals(81, $t->count());
    }

    public function test_date_range_empty()
    {
        $t = new IPBanTable($this->db);
        $t->inputs = ["r_all"=>"on", "r_expires"=>["", ""]];
        list($q, $a) = $t->get_filter();

        $this->assertEquals("(1=1)", $q);
        $this->assertEquals([], $a);
    }
}