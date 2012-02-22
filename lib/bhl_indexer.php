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
		$this->index_languages();
		$this->index_identifier();
		$this->index_subjects();
		$this->index_creators();
		$this->index_publisher();
		
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

	private function index_languages() {
		if ($this->xml->language->languageTerm) {
			foreach ($this->xml->language->languageTerm as $lt) {
				$this->keys['language_t'] = $lt;
			}
		}
	}

	private function index_identifier() {
		if ($this->xml->identifier) {
			foreach ($this->xml->identifier as $identifier) {
				if ($identifier->attributes()->type) {
					if ($identifier->attributes()->type == 'lccn') {
						$this->keys['identifier_lccn_t'] = $identifier;
					}
				}
			}
		}
	}

	private function index_subjects() {
		$subject_t = '';
		foreach ($this->xml->subject as $subject) {
			foreach ($subject->topic as $topic) {
				if ($subject_t != '') 
					$subject_t .= SPLIT_PATTERN;
				$subject_t .= $topic;
			}
		}
		$this->keys['subject_txt'] = $subject_t;
	}
	
	private function index_creators() {
		$creator_t = '';
		
		foreach ($this->xml->name as $name) {			
			if ($name->role->roleTerm == 'creator') {								
				$current_creator = '';
				foreach ($name->namePart as $name_part) {
					if ($current_creator != '')
						$current_creator .= ', ';
					$current_creator .= $name_part;
				}
				if ($creator_t != '') 
					$creator_t .= SPLIT_PATTERN;
				$creator_t .= $current_creator;
			}
		}
		$this->keys['creator_txt'] = $creator_t;	
	}

	private function index_publisher() {
		if ($this->xml->originInfo) {
			foreach($this->xml->originInfo->place as $place) {
				if ($place->attributes()->type == 'text') {
					$this->keys['origin_info_place_t'] = $place;
				}
			}
			$this->keys['origin_info_publisher_t'] = $this->xml->originInfo->publisher;
			$this->keys['origin_info_date_issueed_t'] = $this->xml->originInfo->dateIssued;
			$this->keys['origin_info_issuance_t'] = $this->xml->originInfo->issuance;
		} 
	}

}
?>