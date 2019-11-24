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

    public function search_input()
    {
        return INPUT([
            "name"=>"s_{$this->name}",
            "placeholder"=>$this->title,
            "value"=>@$_GET["s_{$this->name}"]
        ]);
    }

    public function add_input()
    {
        return INPUT([
            "name"=>"a_{$this->name}",
            "placeholder"=>$this->title,
            "value"=>@$_GET["a_{$this->name}"]
        ]);
    }
}
