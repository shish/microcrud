<?php
namespace MicroCRUD;

class DateColumn extends Column
{
    public function __construct($name, $title)
    {
        parent::__construct(
            $name,
            $title,
            "($name = :$name)"
        );
    }

    public function display($data)
    {
        return substr($data, 0, 10);
    }
}
