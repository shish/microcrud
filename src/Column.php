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

    // A filter function applied to inputs for this column, eg
    // "bob" -> "%bob%"
    public $input_mod;

    public function __construct($name, $title, $filter)
    {
        $this->name = $name;
        $this->title = $title;
        $this->filter = $filter;
    }

    public function display($data)
    {
        return $data;
    }

    public function read_input($inputs)
    {
        return INPUT([
            "type"=>"text",
            "name"=>"r_{$this->name}",
            "placeholder"=>$this->title,
            "value"=>@$inputs["r_{$this->name}"]
        ]);
    }

    public function create_input($inputs)
    {
        return INPUT([
            "type"=>"text",
            "name"=>"c_{$this->name}",
            "placeholder"=>$this->title,
            "value"=>@$inputs["c_{$this->name}"]
        ]);
    }
}
