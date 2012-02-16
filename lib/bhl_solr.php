<?php
class BHLSolr {
	#SolrClient $this->client	
	private $client;	
	
	function __construct() {
		$options = array
		(
		    'hostname' => SOLR_SERVER_HOSTNAME,
		    'login'    => SOLR_SERVER_USERNAME,
		    'password' => SOLR_SERVER_PASSWORD,
		    'port'     => SOLR_SERVER_PORT,
		);
		
		$this->client = new SolrClient($options);
	}
	
	function __destruct() {
		$this->client = null;
	}
	
	function add_to_index($fields) {
		$doc = new SolrInputDocument();

		foreach ($fields as $key => $value)
		{
			$doc->addField($key, $value);			
		}
		
		$updateResponse = $this->client->addDocument($doc);		
	}
	
	function delete_by_id($id) {
		$this->client->deleteById($id);
	}
	
	function exists_by_id($id) {
		$query = new SolrQuery();
		$query->setQuery($id);
		$query->addField('id');
		$query_response = $this->client->query($query);
		$response = $query_response->getResponse();
		if ($response['numFound'] > 0)
			return true;
		else
			return false;
	}
	
	function commit_and_optimize() {
		$this->client->commit();
		$this->client->optimize();		
	}
}
?>