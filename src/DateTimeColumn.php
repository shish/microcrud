<?php
namespace MicroCRUD;

use function MicroHTML\INPUT;
use function MicroHTML\emptyHTML;
use function MicroHTML\BR;

class DateTimeColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        return "({$this->name} >= :{$this->name}_0 AND {$this->name} < :{$this->name}_1)";
    }

    public function read_input(array $inputs)
    {
        return emptyHTML(
            INPUT([
                "type"=>"datetime-local",
                "name"=>"r_{$this->name}[]",
                "value"=>@$inputs["r_{$this->name}"][0]
            ]),
            BR(),
            INPUT([
                "type"=>"datetime-local",
                "name"=>"r_{$this->name}[]",
                "value"=>@$inputs["r_{$this->name}"][1]
            ])
        );
    }

    public function modify_input_for_read($input)
    {
        list($s, $e) = $input;
        if (empty($s)) {
            $s = "0001/01/01";
        }
        if (empty($e)) {
            $e = "9999/12/31";
        }
        return [$s, $e];
    }
    public function display(array $row)
    {
        return substr($row[$this->name], 0, 19);
    }

    public function create_input(array $inputs)
    {
        return INPUT([
            "type"=>"datetime-local",
            "name"=>"c_{$this->name}",
            "value"=>@$inputs["c_{$this->name}"]
        ]);
    }
}
