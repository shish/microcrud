<?php

declare(strict_types=1);

namespace MicroCRUD;

use function MicroHTML\SELECT;
use function MicroHTML\OPTION;

class EnumColumn extends Column
{
    /** @var array<string, string> */
    public array $options;

    /**
     * @param array<string, string> $options
     */
    public function __construct(string $name, string $title, array $options)
    {
        parent::__construct(
            $name,
            $title
        );
        $this->options = $options;
    }

    public function read_input(array $inputs): \MicroHTML\HTMLElement|string
    {
        $s = SELECT(["name" => "r_{$this->name}"]);
        $s->appendChild(OPTION(["value" => ""], '-'));
        foreach ($this->options as $k => $v) {
            $attrs = ["value" => $v];
            if ($v == @$inputs["r_{$this->name}"]) {
                $attrs["selected"] = true;
            }
            $s->appendChild(OPTION($attrs, $k));
        }
        return $s;
    }

    public function create_input(array $inputs): \MicroHTML\HTMLElement|string
    {
        $s = SELECT(["name" => "c_{$this->name}"]);
        foreach ($this->options as $k => $v) {
            $attrs = ["value" => $v];
            if ($v == @$inputs["c_{$this->name}"]) {
                $attrs["selected"] = true;
            }
            $s->appendChild(OPTION($attrs, $k));
        }
        return $s;
    }
}
