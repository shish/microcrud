MicroCRUD
=========
I kept re-implementing (badly) a bunch of code to do this stuff. Instead of
implementing it badly a 10th time, I decided to put it into a self-contained
library with the union of features.

```
use \MicroCRUD\{Table, Column};

class MyTable extends Table {
	$columns = [
		Column("username", "Username"),
		Column("email", "Email Address"),
	];
}

$t = MyTable();
print($t->table());
```

```
<table>
	<thead>
		[... titles and search fields ...]
	</thead>
	<tbody>
		[... contents of the table, in pages ...]
	</tbody>
	<tfoot>
		[... code to add a new entry ...]
	</tfoot>
</table>
```
