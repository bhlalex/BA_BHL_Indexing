<?php
class CSVImport {
	
	static function convert_to_array($content) {
		$ret = explode("\n", $content);
		for ($i=0; $i<count($ret); $i++) {
			$ret[$i] = explode(",", $ret[$i]);
		}
		return $ret;
	}
}
?>