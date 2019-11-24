<?php
namespace MicroCRUD;

class TextColumn extends Column
{
    public function __construct($name, $title)
    {
        parent::__construct(
            $name,
            $title,
            "($name LIKE :$name)"
        );
        $this->input_mod = function ($x) {
            return "%$x%";
        };
    }
}
