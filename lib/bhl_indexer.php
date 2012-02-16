<?php
class BHLIndexer {
	private $id;
	private $xml;	
	private $keys;
	private $book_viewer;

	function __construct($job_id, $file_path, $viewer_path) {
		$this->id = $job_id;
		$this->xml = simplexml_load_file($file_path);
		$this->book_viewer = $viewer_path;
	}

	function __destruct() {
		$this->xml = null;
	}

	function index($commit) {
		$this->keys['id'] = $this->id;
		$this->index_titles();
		$this->index_viewer_uri();
		
		#send it to solr
		$this->add_to_solr($commit);
	}


	private function add_to_solr($commit) {
		$solr = new BHLSolr();

		# check if the same record exists, then delete it
		if ($solr->exists_by_id($this->id)) {
			$solr->delete_by_id($this->id);
			$solr->commit_and_optimize();
		}
		echo("Indexing id: ".$this->id."\n");
		$solr->add_to_index($this->keys);

		if ($commit == true)
			$solr->commit_and_optimize();
	}

	private function index_titles() {
		foreach ($this->xml->titleInfo as $titleInfo) {
			$key_name = 'titleInfo';
			if ($titleInfo->attributes()->type)
				$key_name .= '_'.$titleInfo->attributes()->type;
			
			$this->keys[$key_name.'_nonSort_t'] = $titleInfo->nonSort;
			$this->keys[$key_name.'_title_t'] = $titleInfo->title;
			$this->keys[$key_name.'_subTitle_t'] = $titleInfo->subTitle;		
		}
	}

	private function index_viewer_uri() {
		$this->keys['book_viewer_t'] = $this->book_viewer;
	}
	

}
?>