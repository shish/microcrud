<?php

namespace MicroCRUD;

require_once "vendor/autoload.php";

use MicroHTML\HTMLElement;
use function MicroHTML\{TABLE,THEAD,TBODY,TFOOT,TR,TH,TD,INPUT};


class Table {
	var $table = null;
	var $base_query = null;
	var $limit = 100;
	var $max_limit = 1000;
	var $columns = [];
	var $order_by = "";
	var $flags = [];
	var $allow_create = true;

	public function get_filter() {
		$filters = ["1=1"];
		$args = [];
		foreach($this->columns as $col) {
			if(isset($_GET["s_{$col->name}"])) {
				$filters[] = $col->filter;
				$val = $_GET["s_{$col->name}"];
				if($col->mod) {
					$args[$col->name] = ($col->mod)($val);
				}
				else {
					$args[$col->name] = $val;
				}
			}
		}
		foreach($this->flags as $flag => $filter) {
			if(isset($_GET["s_{$flag}"])) {
				if($filter[1]) $filters[] = $filter[1];
			} else {
				if($filter[0]) $filters[] = $filter[0];
			}
		}
        return [implode(" AND ", $filters), $args];
	}

	public function paged_query(\PDO $db, int $page) {
		list($filter, $args) = $this->get_filter();

		$order = "";
		if($this->order_by) {
			$order = "ORDER BY " . join(", ", $this->order_by);
		}

		$query = "
			{$this->base_query}
			WHERE {$filter}
			$order
			LIMIT :limit
			OFFSET :offset
        ";
		$limit = isset($_GET["s_limit"]) ? (int)$_GET["s_limit"] : $this->limit;
		if($limit > $this->max_limit) $limit = $this->max_limit;
		$args["offset"] = $limit * ($page-1);
		$args["limit"] = $limit;

		$stmt = $db->prepare($query);
		$stmt->execute($args);
		$rows = $stmt->fetchAll();

		return $rows;
	}

	public function table(array $rows): HTMLElement {
		return TABLE(
			"\n",
			$this->thead(), "\n",
			$this->tbody($rows), "\n",
			$this->tfoot(), "\n"
		);
	}

	public function thead(): HTMLElement {
		$thead = THEAD();

		$tr = TR();
		foreach ($this->columns as $col) {
			$tr->appendChild(TH($col->title));
		}
		$tr->appendChild(TH("Action"));
		$thead->appendChild($tr);

		$tr = TR();
		foreach ($this->columns as $col) {
			$tr->appendChild(TH(INPUT(["name"=>"s_{$col->name}", "placeholder"=>$col->title])));
		}
		$tr->appendChild(TH(INPUT(["type"=>"submit", "value"=>"Search"])));
		$thead->appendChild($tr);

		return $thead;
	}

	public function tbody(array $rows): HTMLElement {
		$tbody = TBODY();
		foreach ($rows as $row) {
			$tr = TR();
			$tbody->appendChild($tr);
			foreach ($this->columns as $col) {
				if($col->display_field) {
					$tr->appendChild(TD($row[$col->display_field]));
				}
				else {
					$tr->appendChild(TD($row[$col->name]));
				}
			}
		}
		return $tbody;
	}

	public function tfoot(): HTMLElement {
		$tfoot = TFOOT();
		if($this->allow_create) {
			$tr = TR();
			$tfoot->appendChild($tr);
			foreach($this->columns as $col) {
				$tr->appendChild(TH(INPUT(["name"=>"a_{$col->name}", "placeholder"=>$col->title])));
			}
			$tr->appendChild(TH(INPUT(["type"=>"submit", "value"=>"Add"])));
		}
		return $tfoot;
	}
}

