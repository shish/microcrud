<?php

declare(strict_types=1);

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

    public function read_input(array $inputs): \MicroHTML\HTMLElement|string
    {
        return emptyHTML(
            INPUT([
                "type" => "datetime-local",
                "name" => "r_{$this->name}[]",
                "value" => @$inputs["r_{$this->name}"][0]
            ]),
            BR(),
            INPUT([
                "type" => "datetime-local",
                "name" => "r_{$this->name}[]",
                "value" => @$inputs["r_{$this->name}"][1]
            ])
        );
    }

    public function modify_input_for_read(string|array $input): mixed
    {
        assert(is_array($input));
        list($s, $e) = $input;
        if (empty($s)) {
            $s = "0001/01/01";
        }
        if (empty($e)) {
            $e = "9999/12/31";
        }
        return [$s, $e];
    }

    public function display(array $row): \MicroHTML\HTMLElement|string
    {
        if (is_null($row[$this->name])) {
            return "";
        }
        return substr($row[$this->name], 0, 19);
    }

    public function create_input(array $inputs): \MicroHTML\HTMLElement|string
    {
        return INPUT([
            "type" => "datetime-local",
            "name" => "c_{$this->name}",
            "value" => @$inputs["c_{$this->name}"]
        ]);
    }
}
