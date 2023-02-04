<?php

declare(strict_types=1);

namespace MicroCRUD;

class StringColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        return "(LOWER({$this->name}) = LOWER(:{$this->name}))";
    }
}
