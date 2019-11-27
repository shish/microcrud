<?php
namespace MicroCRUD;

class InetColumn extends Column
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
