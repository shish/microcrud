<?php

declare(strict_types=1);

namespace MicroCRUD;

use MicroHTML\HTMLElement;

use function MicroHTML\INPUT;
use function MicroHTML\emptyHTML;
use function MicroHTML\BR;

class DateColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        return "({$this->name} >= :{$this->name}_0 AND {$this->name} < :{$this->name}_1)";
    }

    public function read_input(array $inputs): HTMLElement|string
    {
        return emptyHTML(
            INPUT([
                "type" => "date",
                "name" => "r_{$this->name}[]",
                "value" => @$inputs["r_{$this->name}"][0]
            ]),
            BR(),
            INPUT([
                "type" => "date",
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
        if ($e) {
            $date = new \DateTime($e);
            $date->modify('+1 day');
            $e = $date->format('Y/m/d');
        }
        if (empty($e)) {
            $e = "9999/12/31";
        }
        return [$s, $e];
    }

    public function display(array $row): HTMLElement|string
    {
        if (is_null($row[$this->name])) {
            return "";
        }
        return substr($row[$this->name], 0, 10);
    }

    public function create_input(array $inputs): HTMLElement|string
    {
        return INPUT([
            "type" => "date",
            "name" => "c_{$this->name}",
            "value" => @$inputs["c_{$this->name}"]
        ]);
    }

    public function update_input(array $row): HTMLElement|string|null
    {
        return INPUT([
            "type" => "date",
            "name" => "u_{$this->name}",
            "value" => $row[$this->name]
        ]);
    }
}
