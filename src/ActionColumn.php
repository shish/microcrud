<?php

declare(strict_types=1);

namespace MicroCRUD;

use MicroHTML\HTMLElement;

use function MicroHTML\BUTTON;
use function MicroHTML\DIV;
use function MicroHTML\FORM;
use function MicroHTML\INPUT;
use function MicroHTML\emptyHTML;
use function MicroHTML\DIALOG;
use function MicroHTML\TABLE;
use function MicroHTML\TBODY;
use function MicroHTML\TD;
use function MicroHTML\TH;
use function MicroHTML\TR;

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

    public function update_input(array $row): HTMLElement|string|null
    {
        return null;
    }

    public function display(array $row): HTMLElement|string
    {
        if ($this->table->update_url) {
            return emptyHTML(
                DIALOG(
                    ["id" => "edit-modal-" . $row[$this->name], "popover" => true],
                    DIV(
                        ["class" => "dialog-header"],
                        DIV("Edit {$this->table->table} ({$this->name}={$row[$this->name]})"),
                        BUTTON([
                            "type" => "button",
                            "popovertarget" => "edit-modal-" . $row[$this->name],
                            "popovertargetaction" => "hide",
                        ], "X"),
                    ),
                    FORM(
                        ["method" => "POST", "action" => $this->table->update_url, "id" => "edit-form-" . $row[$this->name]],
                        INPUT(["type" => "hidden", "name" => "auth_token", "value" => $this->table->token]),
                        INPUT(["type" => "hidden", "name" => "e_{$this->name}", "value" => $row[$this->name]]),
                        TABLE(
                            TBODY(
                                ...array_map(
                                    function ($column) use ($row) {
                                        $inp = $column->update_input($row);
                                        if ($inp === null) {
                                            return null;
                                        } else {
                                            return TR(
                                                TH($column->title),
                                                TD($inp)
                                            );
                                        }
                                    },
                                    $this->table->columns
                                )
                            ),
                        ),
                    ),
                    $this->table->delete_url ? FORM(
                        ["method" => "POST", "action" => $this->table->delete_url, "id" => "delete-form-" . $row[$this->name]],
                        INPUT(["type" => "hidden", "name" => "auth_token", "value" => $this->table->token]),
                        INPUT(["type" => "hidden", "name" => "d_{$this->name}", "value" => $row[$this->name]]),
                    ) : null,
                    DIV(
                        ["class" => "dialog-buttons"],
                        BUTTON(
                            ["type" => "submit", "form" => "edit-form-" . $row[$this->name]],
                            "Save"
                        ),
                        $this->table->delete_url ? BUTTON(
                            ["type" => "submit", "form" => "delete-form-" . $row[$this->name]],
                            "Delete"
                        ) : null,
                    )
                ),
                BUTTON([
                    "type" => "button",
                    "popovertarget" => "edit-modal-" . $row[$this->name],
                    "popovertargetaction" => "show",
                ], "Edit")
            );
        } elseif ($this->table->delete_url) {
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
