<?php

include "config/bootstrap.php";
include "lib/bhl_solr.php";
include "lib/bhl_indexer.php";
include "lib/csv_import.php";

# csv file path
$file_path = 'csv/BHL_10.csv';


# getting file content
$theData = file_get_contents($file_path, true);


$records = CSVImport::convert_to_array($theData);

$count = 0;

foreach ($records as $index => $record) {	
	if (count($record) == 3) {
		$indexer = new BHLIndexer($record[0], $record[1], $record[2]);
		
		$count = 0;

		$indexer->index(true);
	}
}

?>
