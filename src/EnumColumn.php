<?php
namespace MicroCRUD;

use function MicroHTML\SELECT;
use function MicroHTML\OPTION;

class EnumColumn extends Column
{
    public $options;

    public function __construct($name, $title, $options)
    {
        parent::__construct(
            $name,
            $title
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
