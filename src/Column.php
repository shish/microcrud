<?php
namespace MicroCRUD;

class Column {
	public function __construct($name, $title, $filter, $mod=null, $display_field=null) {
		$this->name = $name;
		$this->title = $title;
		$this->filter = $filter;
		$this->mod = $mod;
		$this->display_field = $display_field;
	}
}