<?php
namespace MicroCRUD;

use function MicroHTML\INPUT;

class Column
{
    public $name;
    public $title;

    // What to add to the SQL query to search this field
    // eg (user_name LIKE :user_name)
    public $filter;

    public function __construct(string $name, string $title, string $filter)
    {
        $this->name = $name;
        $this->title = $title;
        $this->filter = $filter;
    }

    public function display(array $row)
    {
        return $row[$this->name];
    }

    public function read_input(array $inputs)
    {
        return INPUT([
            "type"=>"text",
            "name"=>"r_{$this->name}",
            "placeholder"=>$this->title,
            "value"=>@$inputs["r_{$this->name}"]
        ]);
    }

    // A filter function applied to inputs for this column, eg
    // "bob" -> "%bob%"
    public function modify_input_for_read($input)
    {
        return $input;
    }

    public function create_input(array $inputs)
    {
        return INPUT([
            "type"=>"text",
            "name"=>"c_{$this->name}",
            "placeholder"=>$this->title,
            "value"=>@$inputs["c_{$this->name}"]
        ]);
    }
}
