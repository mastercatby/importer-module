<?php

class OrderParser {

	private $customer;
	private $trade;
	private $scheduled_date;
	private $nte;
	private $po_number;
	private $wo_number;
	private $store_id;
	private $location_city;
	private $location_street;
	private $location_state;
	private $location_post;
	private $location_phone;


	public function __construct() {

		$this->customer		= null;
		$this->trade		= null;
		$this->scheduled_date	= null;
		$this->nte		= null;
		$this->po_number	= null;
		$this->wo_number	= null;
		$this->store_id		= null;
		$this->location_city	= null;
		$this->location_street	= null;
		$this->location_state	= null;
		$this->location_post	= null;
		$this->location_phone	= null;
	
	}

	
	public function loadFromHtml($data) {

		$matches = array();
		$pattern = '/'
			. 'id="customer">([^<]*)'
			. '[\s\S]*id="trade">([^<]*)'
			. '[\s\S]*id="scheduled_date">([^\/]*)'
			. '[\s\S]*id="nte">([^<]*)'
			. '[\s\S]*id="po_number">([^<]*)'
			. '[\s\S]*id="wo_number">([^<]*)'
			. '[\s\S]*id="location_name">([^<]*)'
//			. '[\s\S]*id="store_id">([^<]*)'
			. '[\s\S]*id="location_address">([^\/]*)'
			. '[\s\S]*id="location_phone">([^<]*)'
			. '/';

		if (preg_match($pattern, $data, $matches)) {

			$this->customer		= trim($matches[1]);	
			$this->trade		= trim($matches[2]);	
			$this->scheduled_date	= $this->parseDate( substr($matches[3], 0, strlen($matches[3]) - 1) );	
			$this->nte		= $this->parseNte(trim($matches[4]));	
			$this->po_number	= trim($matches[5]);	
			$this->wo_number	= trim($matches[6]);	
			$this->store_id		= trim($matches[7]);	
			$this->parseAddr( substr($matches[8], 0, strlen($matches[8]) - 1) );	
			$this->location_phone	= $this->parsePhone(trim($matches[9]));
			
			return true;
				
		} else {
			return false;
		}
	}
	

	private function clearTags($value) {
	
		return preg_replace('/\s+/', ' ', strip_tags($value));
		
	}
	
	
	private function parseDate($value) {
	
		$value = trim($this->clearTags($value));
		$dateObj = DateTime::createFromFormat('F j, Y h:i A', $value);
			if ($dateObj !== false) {
				return $dateObj->format('Y-m-d H:i');
		} else {
			return $value;
		}
		
	}

	
	private function parseAddr($value) {

		$pos = strpos($value, '<br>');
		if ($pos !== false) {

			$this->location_street = trim($this->clearTags(substr($value, 0, $pos - 1)));
			$value = trim($this->clearTags(substr($value, $pos + 4)));

			if (preg_match("/^(.+) (\S\S) (\d+)$/", $value, $matches)) {

				$this->location_city = $matches[1];
				$this->location_state = $matches[2];
				$this->location_post = $matches[3];

			}
						
		} else {
			$this->location_street = trim($this->clearTags($value));
		}
		
	}


	private function parsePhone($value) {
	
		return preg_replace('/[^\d]/', '', $value);
		
	}


	private function parseNte($value) {

		$res = preg_replace('/^[^\d]/', '', $value);
		return str_replace(array(',', ' '), array('', ''), $res);
		
	}


	private function quoteField($field) {
	
		return '"' . str_replace('"', '\"', $field) . '"';
		
	}


	public function saveToCsvString() {
	
		return implode(',', array(
			$this->quoteField($this->wo_number),
			$this->quoteField($this->po_number),
			$this->quoteField($this->scheduled_date),
			$this->quoteField($this->customer),
			$this->quoteField($this->trade),
			$this->quoteField($this->nte),
			$this->quoteField($this->store_id),
			$this->quoteField($this->location_street),
			$this->quoteField($this->location_city),
			$this->quoteField($this->location_state),
			$this->quoteField($this->location_post),
			$this->quoteField($this->location_phone)
			)) . "\n"; 
		
	}
	
} 


$inputfile = 'wo_for_parse.html';
$outputfile = 'wo_res.csv';

$data = file_get_contents($inputfile);
if (FALSE !== $data) {

	$parser = new OrderParser();
	if ($parser) {

		if ($parser->loadFromHtml($data) ) {

			$data = $parser->saveToCsvString();
			//echo $data;
			if (FALSE !== file_put_contents($outputfile, $data, FILE_APPEND)) {
				echo 'ok';
			} else {
				echo 'write file error';
			}
			
		} else {
			echo 'import error';
		} 

	} else {
		echo 'internal error';
	} 

} else {
	echo 'read file error';
}

?>