<?php

declare(strict_types=1);

namespace MicroCRUD;

use MicroHTML\HTMLElement;

use function MicroHTML\BUTTON;
use function MicroHTML\FORM;
use function MicroHTML\INPUT;
use function MicroHTML\emptyHTML;

class ActionColumn extends Column
{
    public function __construct(string $name)
    {
        parent::__construct($name, "Action");
        $this->sortable = false;
    }

    public function read_input(array $inputs): HTMLElement|string
    {
        return emptyHTML(
            INPUT(["type" => "hidden", "name" => "r__size", "value" => @$inputs["r__size"]]),
            INPUT(["type" => "hidden", "name" => "r__page", "value" => 1]),
            BUTTON(["type" => "submit"], "Search")
        );
    }

    public function display(array $row): HTMLElement|string
    {
        if ($this->table->delete_url) {
            return FORM(
                ["method" => "POST", "action" => $this->table->delete_url],
                INPUT(["type" => "hidden", "name" => "auth_token", "value" => $this->table->token]),
                INPUT(["type" => "hidden", "name" => "d_{$this->name}", "value" => $row[$this->name]]),
                BUTTON(["type" => "submit"], "Delete")
            );
        } else {
            return emptyHTML();
        }
    }

    public function create_input(array $inputs): HTMLElement|string
    {
        return emptyHTML(
            INPUT(["type" => "hidden", "name" => "auth_token", "value" => $this->table->token]),
            BUTTON(["type" => "submit"], "Add")
        );
    }

    public function get_sql_filter(): ?string
    {
        return null;
    }
}
