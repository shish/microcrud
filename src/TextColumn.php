<?php

declare(strict_types=1);

namespace MicroCRUD;

class TextColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        return "(LOWER({$this->name}) LIKE LOWER(:{$this->name}))";
    }

    public function modify_input_for_read(string|array $input): mixed
    {
        assert(is_string($input));
        return "%$input%";
    }
}
