<?php
namespace MicroCRUD;

use function MicroHTML\FORM;
use function MicroHTML\INPUT;
use function MicroHTML\emptyHTML;

class ActionColumn extends Column
{
    public function __construct($name)
    {
        parent::__construct($name, "Action");
    }

    public function read_input(array $inputs)
    {
        return emptyHTML(
            INPUT(["type"=>"hidden", "name"=>"r__size", "value"=>@$inputs["r__size"]]),
            INPUT(["type"=>"hidden", "name"=>"r__page", "value"=>1]),
            INPUT(["type"=>"submit", "value"=>"Search"])
        );
    }

    public function display(array $row)
    {
        if ($this->table->delete_url) {
            return FORM(
                ["method" => "POST", "action" => $this->table->delete_url],
                INPUT(["type" => "hidden", "name" => "auth_token", "value" => $this->table->token]),
                INPUT(["type" => "hidden", "name" => "d_{$this->name}", "value" => $row[$this->name]]),
                INPUT(["type" => "submit", "value" => "Delete"])
            );
        } else {
            return emptyHTML();
        }
    }

    public function create_input(array $inputs)
    {
        return emptyHTML(
            INPUT(["type"=>"hidden", "name"=>"auth_token", "value"=>$this->table->token]),
            INPUT(["type"=>"submit", "value"=>"Add"])
        );
    }
}
