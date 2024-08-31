<?php

declare(strict_types=1);

namespace MicroCRUD;

use FFSPHP\PDO;

use MicroHTML\HTMLElement;

use function MicroHTML\emptyHTML;
use function MicroHTML\TABLE as html_TABLE;
use function MicroHTML\THEAD;
use function MicroHTML\TBODY;
use function MicroHTML\TFOOT;
use function MicroHTML\TR;
use function MicroHTML\TH;
use function MicroHTML\TD;
use function MicroHTML\INPUT;
use function MicroHTML\FORM;
use function MicroHTML\DIV;
use function MicroHTML\A;
use function MicroHTML\B;
use function MicroHTML\BR;

/**
 * @template T
 * @param T[] $val
 * @return bool
 */
function all_empty(array $val): bool
{
    foreach ($val as $v) {
        if ($v) {
            return false;
        }
    }
    return true;
}

/**
 * @template T
 * @param T $val
 * @return T|null
 */
function emptyish_to_null(mixed $val)
{
    // convert an array of empty strings into an empty value
    if (is_array($val)) {
        if (all_empty(array_map("trim", $val))) {
            $val = null;
        }
    }
    // convert whitespace-only strings to empty value
    elseif (is_string($val)) {
        if (trim($val) == "") {
            $val = null;
        }
    }
    return $val;
}

class Table
{
    public PDO $db;

    public string $table;
    public string $base_query;
    public ?int $size = 100;
    public int $limit = 1000;
    /** @var Column[] */
    public array $columns = [];
    /** @var string[] */
    public array $order_by = [];
    /**
     * @var array<string, array<string|null>>
     *
     * flag => [filter_if_false, filter_if_true]
     * eg
     * "show_deleted" => ["(deleted=0)", "(deleted=1)"]
     */
    public array $flags = [];
    public string $primary_key = "id";
    /** @var array<string, mixed> */
    public array $table_attrs = [];

    public ?string $create_url = null;
    public ?string $update_url = null;
    public ?string $delete_url = null;
    public ?string $token = null;

    /** @var array<string, mixed> - eg $_POST */
    public array $inputs = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param Column[] $columns
     */
    public function set_columns(array $columns): void
    {
        $this->columns = $columns;
        foreach ($this->columns as $col) {
            $col->table = $this;
        }
    }

    // args
    public function size(): ?int
    {
        // admin can set $table->size = null if they want all results
        if (!$this->size) {
            return null;
        }

        $size = (int)($this->inputs["r__size"] ?? $this->size);
        if ($size <= 0) {
            $size = $this->size;
        }
        if ($size > $this->limit) {
            $size = $this->limit;
        }
        return $size;
    }

    // database
    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    public function get_filter(): array
    {
        $filters = [];
        $args = [];
        foreach ($this->columns as $col) {
            $val = emptyish_to_null(@$this->inputs["r_{$col->name}"]);

            if ($val != null) {
                $filter = $col->get_sql_filter();
                if ($filter != null) {
                    $filters[] = $filter;
                    $val = $col->modify_input_for_read($val);
                    if (!is_array($val)) {
                        $args[$col->name] = $val;
                    } else { // array
                        foreach ($val as $k => $v) {
                            $args["{$col->name}_$k"] = $v;
                        }
                    }
                }
            }
        }
        foreach ($this->flags as $flag => $filter) {
            if (!empty($this->inputs["r_{$flag}"])) {
                if ($filter[1]) {
                    $filters[] = $filter[1];
                }
            } else {
                if ($filter[0]) {
                    $filters[] = $filter[0];
                }
            }
        }
        if (count($filters) == 0) {
            $filters[] = "(1=1)";
        }
        return [implode(" AND ", $filters), $args];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function query(): array
    {
        // WHERE
        list($filter, $args) = $this->get_filter();

        // ORDER BY
        $order_by = "";
        if (!empty($this->inputs["r__sort"])) {
            $asc = true;
            $suggested_order = $this->inputs["r__sort"];
            if ($suggested_order[0] == "-") {
                $asc = false;
                $suggested_order = substr($suggested_order, 1);
            }
            foreach ($this->columns as $col) {
                if ($col->name == $suggested_order) {
                    $order_by = "ORDER BY " . $col->name . ($asc ? " ASC" : " DESC");
                    break;
                }
            }
        }
        if ($order_by == "" && count($this->order_by) > 0) {
            $order_by = "ORDER BY " . join(", ", $this->order_by);
        }

        // LIMIT / OFFSET
        $page = (int)($this->inputs["r__page"] ?? 1);
        $size = $this->size();
        $pager = "";
        if ($size !== null && $size > 0) {
            $pager = "LIMIT :limit OFFSET :offset";
            $args["offset"] = $size * ($page - 1);
            $args["limit"] = $size;
        }

        $query = "
			{$this->base_query}
			WHERE {$filter}
			$order_by
			$pager
        ";

        return $this->db->execute($query, $args)->fetchAll();
    }

    public function count(): int
    {
        list($filter, $args) = $this->get_filter();

        $query = "
			SELECT COUNT(*) FROM (
				{$this->base_query}
				WHERE {$filter}
			) AS tbl2
        ";

        return intval($this->db->execute($query, $args)->fetch()[0]);
    }

    public function count_pages(): int
    {
        $p = (int)ceil($this->count() / $this->size());
        if ($p == 0) {
            $p = 1;
        }
        return $p;
    }

    // html generation
    /**
     * @param array<array<string, mixed>> $rows
     */
    public function table(array $rows): HTMLElement
    {
        return html_TABLE(
            $this->table_attrs,
            "\n",
            $this->thead(),
            "\n",
            $this->tbody($rows),
            "\n",
            $this->tfoot(),
            "\n"
        );
    }

    public function thead(): HTMLElement
    {
        $thead = THEAD(["id" => "read"]);

        $tr = TR();
        foreach ($this->columns as $col) {
            if ($col->sortable) {
                $sort_name = (@$this->inputs["r__sort"] == $col->name) ? "-{$col->name}" : $col->name;
                $sort = "?" . $this->modify_url(["r__sort" => $sort_name]);
                $tr->appendChild(TH(A(["href" => $sort], $col->title)));
            } else {
                $tr->appendChild(TH($col->title));
            }
        }
        $thead->appendChild($tr);

        $tr = TR();
        $used_inputs = ["r__page", "r__size"];
        foreach ($this->columns as $col) {
            $tr->appendChild(TD($col->read_input($this->inputs)));
            $used_inputs[] = "r_{$col->name}";
        }
        foreach ($this->flags as $flag => $_vals) {
            $tr->appendChild(
                INPUT(["type" => "hidden", "name" => "r_{$flag}", "value" => @$this->inputs["r_{$flag}"]])
            );
            $used_inputs[] = "r_{$flag}";
        }
        foreach ($this->inputs as $k => $v) {
            if (!in_array($k, $used_inputs)) {
                $tr->appendChild(
                    INPUT(["type" => "hidden", "name" => $k, "value" => $v])
                );
            }
        }
        $thead->appendChild(FORM($tr));

        return $thead;
    }

    /**
     * @param array<array<string, mixed>> $rows
     */
    public function tbody(array $rows): HTMLElement
    {
        $tbody = TBODY(["id" => "update"]);
        foreach ($rows as $row) {
            $tr = TR();
            $tbody->appendChild($tr);
            foreach ($this->columns as $col) {
                $tr->appendChild(TD($col->display($row)));
            }
        }
        return $tbody;
    }

    public function tfoot(): HTMLElement
    {
        $tfoot = TFOOT(["id" => "create"]);
        if ($this->create_url) {
            $tr = TR();
            $tfoot->appendChild(FORM(["method" => "POST", 'action' => $this->create_url], $tr));
            foreach ($this->columns as $col) {
                $tr->appendChild(TD($col->create_input($this->inputs)));
            }
        }
        return $tfoot;
    }

    /**
     * @param array<string, mixed> $changes
     */
    public function modify_url(array $changes): string
    {
        $args_copy = $this->inputs;
        foreach ($changes as $k => $v) {
            $args_copy[$k] = $v;
        }
        return http_build_query($args_copy);
    }

    public function page_url(int $page): string
    {
        return "?" . $this->modify_url(["r__page" => $page]);
    }

    public function paginator(): HTMLElement
    {
        $min = 1;
        $cur = (int)($this->inputs["r__page"] ?? 1);
        $max = $this->count_pages();

        $first_html  = $cur == $min ? "First" : A(["href" => $this->page_url($min)], "First");
        $prev_html   = $cur == $min ? "Prev" : A(["href" => $this->page_url($cur - 1)], "Prev");

        $random_html = "-";

        $next_html   = $cur == $max ? "Next" : A(["href" => $this->page_url($cur + 1)], "Next");
        $last_html   = $cur == $max ? "Last" : A(["href" => $this->page_url($max)], "Last");

        $start = $cur - 5 > $min ? $cur - 5 : $min;
        $end = $start + 10 < $max ? $start + 10 : $max;

        $pages = emptyHTML();
        foreach (range($start, $end) as $i) {
            $link = A(["href" => $this->page_url($i)], "$i");
            if ($i == $cur) {
                $link = B($link);
            }
            $pages->appendChild($link);
            if ($i < $end) {
                $pages->appendChild(" | ");
            }
        }

        return DIV(
            $first_html,
            ' | ',
            $prev_html,
            ' | ',
            $random_html,
            ' | ',
            $next_html,
            ' | ',
            $last_html,
            BR(),
            "<< ",
            $pages,
            " >>"
        );
    }
}
