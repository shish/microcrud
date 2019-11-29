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

    public function display(array $row)
    {
        return substr($row[$this->name], 0, 10);
    }
}
