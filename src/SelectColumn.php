<?php

declare(strict_types=1);

namespace MicroCRUD;

use MicroHTML\HTMLElement;

use function MicroHTML\{INPUT, emptyHTML, BR, BUTTON};

class SelectColumn extends Column
{
    public function __construct(string $name)
    {
        parent::__construct($name, "");
        $this->sortable = false;
    }

    public function create_input(array $inputs): HTMLElement|string
    {
        return emptyHTML();
    }

    public function read_input(array $inputs): HTMLElement|string
    {
        return BUTTON(
            ["type" => "submit", "form" => "bulk", "name" => "bulk_action", "value" => "delete"],
            emptyHTML("Delete", BR(), "Selected")
        );
    }

    public function update_input(array $row): HTMLElement|string|null
    {
        return null;
    }

    public function display(array $row): HTMLElement|string
    {
        if ($this->table->bulk_url) {
            return INPUT(["type" => "checkbox", "form" => "bulk", "name" => "{$this->name}[]", "value" => $row[$this->name]]);
        }
        return emptyHTML();
    }

    public function get_sql_filter(): ?string
    {
        return null;
    }
}
