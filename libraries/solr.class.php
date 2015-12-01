<?php
/**
 * solr search engine client
 * 
 * by paco
 */
if (!defined('IN_DOIT')) {
	exit();
}

class solr extends Base {
	
	private $_solr_url_prefix;
	
	public function __construct() {
		$conf = Controller::getConfig('conf');
		$this->_solr_url_prefix = $conf['solr_url_prefix'];
	}
	
	public function select($args) {
		$args ['wt'] = 'json';
		$args ['_'] = time();
		$url = $this->_solr_url_prefix . 'select';
		$curl_ins = Controller::instance('curl');
		return $curl_ins->getRequest($url, $args);
	}
	
	public function add($data, $auto_commit = true){
		if($auto_commit)
			$url = $this->_solr_url_prefix . 'update?commit=true';
		else $url = $this->_solr_url_prefix . 'update';
		
		$header = array('Content-type:application/json');
		$doc_json = json_encode($data);
		$curl_ins = Controller::instance('curl');
		$ret = $curl_ins->postRequest($url, $doc_json, $header);
		if($ret !== false) {
			$ret_data = json_decode($ret, true);
			if(array_key_exists('responseHeader', $ret_data)){
				if(array_key_exists('status', $ret_data['responseHeader'])){
					if($ret_data['responseHeader']['status'] == 0) return true;
				}
			}
		}
		return false;
	}
	
	public function deleteById($id, $auto_commit = true){
		if(! $id ) return false;
		if($auto_commit)
			$url = $this->_solr_url_prefix . 'update?commit=true';
		else $url = $this->_solr_url_prefix . 'update';
		
		$header = array('Content-type:application/json');
		$curl_ins = Controller::instance('curl');
		$ret = $curl_ins->postRequest($url, '{"delete": { "id":"' . $id . '" }}', $header);
		if($ret !== false) {
			$ret_data = json_decode($ret, true);
			if(array_key_exists('responseHeader', $ret_data)){
				if(array_key_exists('status', $ret_data['responseHeader'])){
					if($ret_data['responseHeader']['status'] == 0) return true;
				}
			}
		}
		return false;
	}
	
	public function deleteByQuery($query_str, $auto_commit = true) {
		if(empty($query_str) ) return false;
		if($auto_commit)
			$url = $this->_solr_url_prefix . 'update?commit=true';
		else $url = $this->_solr_url_prefix . 'update';
		
		$header = array('Content-type:application/json');
		$curl_ins = Controller::instance('curl');
		Log::notice('url:' . $url);
		$ret = $curl_ins->postRequest($url, '{"delete": { "query":"text:' . $query_str . '" }}', $header);
		Log::notice('solr ret:' . $ret);
		if($ret !== false) {
			$ret_data = json_decode($ret, true);
			if(array_key_exists('responseHeader', $ret_data)){
				if(array_key_exists('status', $ret_data['responseHeader'])){
					if($ret_data['responseHeader']['status'] == 0) return true;
				}
			}
		}
		return false;
	}
}