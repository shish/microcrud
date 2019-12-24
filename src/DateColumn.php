<?php
namespace MicroCRUD;

use function MicroHTML\INPUT;
use function MicroHTML\emptyHTML;
use function MicroHTML\BR;

class DateColumn extends Column
{
    public function __construct($name, $title)
    {
        parent::__construct(
            $name,
            $title,
            "($name >= :{$name}_0 AND $name < :{$name}_1)"
        );
    }

    public function read_input(array $inputs)
    {
        return emptyHTML(
            INPUT([
                "type"=>"date",
                "name"=>"r_{$this->name}[]",
                "value"=>@$inputs["r_{$this->name}"][0]
            ]),
            BR(),
            INPUT([
                "type"=>"date",
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
        return substr($row[$this->name], 0, 10);
    }

    public function create_input(array $inputs)
    {
        return INPUT([
            "type"=>"date",
            "name"=>"c_{$this->name}",
            "value"=>@$inputs["c_{$this->name}"]
        ]);
    }
}
