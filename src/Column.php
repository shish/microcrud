<?php

declare(strict_types=1);

namespace MicroCRUD;

use function MicroHTML\INPUT;

class Column
{
    public string $name;
    public string $title;
    public Table $table;
    public bool $sortable = true;

    public function __construct(string $name, string $title)
    {
        $this->name = $name;
        $this->title = $title;
    }

    // What to add to the SQL query to search this field
    // eg "(user_name LIKE :user_name)"
    public function get_sql_filter(): ?string
    {
        return "({$this->name} = :{$this->name})";
    }

    /**
     * @param array<string, mixed> $row
     */
    public function display(array $row): \MicroHTML\HTMLElement|string
    {
        return $row[$this->name] ?? "";
    }

    /**
     * @param array<string, string> $inputs
     */
    public function read_input(array $inputs): \MicroHTML\HTMLElement|string
    {
        return INPUT([
            "type" => "text",
            "name" => "r_{$this->name}",
            "placeholder" => $this->title,
            "value" => @$inputs["r_{$this->name}"]
        ]);
    }

    // A filter function applied to inputs for this column, eg
    // "bob" -> "%bob%"
    /**
     * @param string|string[] $input
     */
    public function modify_input_for_read(string|array $input): mixed
    {
        return $input;
    }

    /**
     * @param array<string, string> $inputs
     */
    public function create_input(array $inputs): \MicroHTML\HTMLElement|string
    {
        return INPUT([
            "type" => "text",
            "name" => "c_{$this->name}",
            "placeholder" => $this->title,
            "value" => @$inputs["c_{$this->name}"]
        ]);
    }
}
