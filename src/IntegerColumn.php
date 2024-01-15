<?php

declare(strict_types=1);

namespace MicroCRUD;

class IntegerColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        return "({$this->name} = :{$this->name})";
    }

    public function display(array $row): \MicroHTML\HTMLElement|string
    {
        return (string)($row[$this->name]);
    }
}
