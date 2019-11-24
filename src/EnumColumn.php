<?php
namespace MicroCRUD;

use MicroHTML\HTMLElement;

function SELECT(...$args)
{
    return new HTMLElement("select", $args);
}
function OPTION(...$args)
{
    return new HTMLElement("option", $args);
}


class EnumColumn extends StringColumn
{
    public $options;

    public function __construct($name, $title, $options)
    {
        parent::__construct(
            $name,
            $title,
            "($name = :$name)"
        );
        $this->options = $options;
    }

    public function search_input()
    {
        $s = SELECT(["name"=>"s_{$this->name}"]);
        $s->appendChild(OPTION(["value"=>""], '-'));
        foreach ($this->options as $k => $v) {
            $attrs = ["value"=>$v];
            if ($v == @$_GET["s_{$this->name}"]) {
                $attrs["selected"] = true;
            }
            $s->appendChild(OPTION($attrs, $k));
        }
        return $s;
    }

    public function add_input()
    {
        $s = SELECT(["name"=>"a_{$this->name}"]);
        foreach ($this->options as $k => $v) {
            $s->appendChild(OPTION(["value"=>$v], $k));
        }
        return $s;
    }
}
