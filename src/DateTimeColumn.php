<?php
namespace MicroCRUD;

class DateTimeColumn extends Column
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
        return $row[$this->name];
    }
}
