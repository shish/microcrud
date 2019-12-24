<?php
namespace MicroCRUD;

class TextColumn extends Column
{
    public function __construct($name, $title)
    {
        parent::__construct(
            $name,
            $title,
            "(LOWER($name) LIKE LOWER(:$name))"
        );
    }

    public function modify_input_for_read($input)
    {
        return "%$input%";
    }
}
