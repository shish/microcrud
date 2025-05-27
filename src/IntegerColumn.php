<?php

declare(strict_types=1);

namespace MicroCRUD;

use MicroHTML\HTMLElement;

class IntegerColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        return "({$this->name} = :{$this->name})";
    }

    public function display(array $row): HTMLElement|string
    {
        return (string)($row[$this->name]);
    }
}
