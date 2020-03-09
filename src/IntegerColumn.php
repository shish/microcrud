<?php
namespace MicroCRUD;

class IntegerColumn extends Column
{
    public function get_sql_filter(): ?string
    {
        return "({$this->name} = :{$this->name})";
    }
}
