<?php

include "config/bootstrap.php";
include "lib/bhl_solr.php";

$solr = new BHLSolr();

$solr->delete_by_id(334455);

$solr->commit_and_optimize();
?>
