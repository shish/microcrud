<?php
namespace MicroCRUD;

class InetColumn extends Column
{
    public function get_sql_filter(): string
    {
        return "({$this->name} = :{$this->name})";
    }
}
