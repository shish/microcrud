<?php
namespace MicroCRUD;

class StringColumn extends Column
{
    public function __construct($name, $title)
    {
        parent::__construct(
            $name,
            $title,
            "($name = :$name)"
        );
    }
}
