<?php
namespace MicroCRUD;

use function MicroHTML\INPUT;
use function MicroHTML\emptyHTML;
use function MicroHTML\BR;

class DateTimeColumn extends Column
{
    public function __construct($name, $title)
    {
        parent::__construct(
            $name,
            $title,
            "($name = :$name)"
        );
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

    public function display(array $row)
    {
        return $row[$this->name];
    }

    public function create_input(array $inputs)
    {
        return INPUT([
            "type"=>"datetime-local",
            "name"=>"r_{$this->name}",
            "value"=>@$inputs["c_{$this->name}"]
        ]);
    }
}
