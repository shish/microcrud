<?php
namespace MicroCRUD;

use MicroHTML\HTMLElement;
use function MicroHTML\SELECT;
use function MicroHTML\OPTION;

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

    public function read_input($inputs)
    {
        $s = SELECT(["name"=>"r_{$this->name}"]);
        $s->appendChild(OPTION(["value"=>""], '-'));
        foreach ($this->options as $k => $v) {
            $attrs = ["value"=>$v];
            if ($v == @$inputs["r_{$this->name}"]) {
                $attrs["selected"] = true;
            }
            $s->appendChild(OPTION($attrs, $k));
        }
        return $s;
    }

    public function create_input($inputs)
    {
        $s = SELECT(["name"=>"c_{$this->name}"]);
        foreach ($this->options as $k => $v) {
            $attrs = ["value"=>$v];
            if ($v == @$inputs["c_{$this->name}"]) {
                $attrs["selected"] = true;
            }
            $s->appendChild(OPTION($attrs, $k));
        }
        return $s;
    }
}
