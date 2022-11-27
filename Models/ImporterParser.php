<?php

namespace App\Modules\Importer\Models;
use Illuminate\Support\Facades\DB;


class ImporterParser {

    private $items;
    private $entries_processed;
    private $entries_created;
    private $entries_skipped;
    private $errors;


public function __construct() {

    $this->clear();

}


private function clear() {

    $this->items = array();
    $this->entries_processed = 0;
    $this->entries_created = 0;
    $this->entries_skipped = 0;
    $this->errors = 0;

}


public function getItems() {
    return $this->items;
}


public function getEntriesProcessed() {
    return $this->entries_processed;
}


public function getEntriesCreated() {
    return $this->entries_created;
}


public function getEntriesSkipped() {
    return $this->entries_skipped;
}


public function getErrors() {
    return $this->errors;
}


public function getEntriesFounded() {
    return count($this->items);
}


public function loadFromHtml($data) {

    $this->clear();

    $this->searchTable('ctl00_ctl00_ContentPlaceHolderMain_MainContent_TicketLists_AllTickets_ctl00',
        $data, array(
            'work_order_number' => 0,
            'priority' => 3,  
            'received_date' => 4,
            'category' => 8,
            'fin_loc' => 10  
            ));
    $this->searchTable('ctl00_ctl00_ContentPlaceHolderMain_MainContent_TicketLists_PaperworkTickets_ctl00',
        $data, array(
            'work_order_number' => 0,
            'priority' => 3,  
            'received_date' => 4,
            'category' => 8,
            'fin_loc' => 10  
            ));
    $this->searchTable('ctl00_ctl00_ContentPlaceHolderMain_MainContent_TicketLists_OpenTickets_ctl00', 
        $data, array(
            'work_order_number' => 0,
            'received_date' => 1,
            'category' => 5,
            'fin_loc' => 7  
        ));
    
    return true;

}


private function searchTable($id, &$data, $fields_map) {

    $table_start_pos = strpos($data, 'id="'.$id.'"');
    if ($table_start_pos === false) {return false;}
    
    $body_start_pos = strpos($data, '<tbody>', $table_start_pos);
    if ($body_start_pos === false) {return false;}

    $body_end_pos = strpos($data, '</tbody>', $body_start_pos);
    if ($body_end_pos === false) {return false;}
    
    $tbody = substr($data, $body_start_pos + 7, $body_end_pos - $body_start_pos - 7);

    return $this->parseTBody($tbody, $fields_map);		

}


private function parseTBody($tdata, $fields_map) {

    $str_pointer = 0;
    $founded = true;
    while ($founded) {
        $founded = false;

        $tr_start1 = strpos($tdata, '<tr ', $str_pointer);
        if ($tr_start1 === false) {continue;}
        
        $tr_start2 = strpos($tdata, '>', $tr_start1);
        if ($tr_start2 === false) {continue;}

        $tr_end = strpos($tdata, '</tr>', $tr_start2);
        if ($tr_end === false) {continue;}
        
        $row = substr($tdata, $tr_start2 + 1, $tr_end - $tr_start2 - 1);
        $str_pointer = $tr_end;
        $founded = true;
                    
        $fields_arr = $this->parseRow($row);
        
        $item = $this->bindFields($fields_arr, $fields_map);
        if ($item) {
            $this->items[] = $item;
        }
            
    }
    
    return true;
}


private function parseRow($row) {

    $fields_arr = array();
    $str_pointer = 0;
    $founded = true;

    while ($founded) {
        $founded = false;

        $td_start1 = strpos($row, '<td ', $str_pointer);
        if ($td_start1 === false) {continue;}
        
        $td_start2 = strpos($row, '>', $td_start1);
        if ($td_start2 === false) {continue;}

        $td_end = strpos($row, '</td>', $td_start2);
        if ($td_end === false) {continue;}
        
        $field = substr($row, $td_start2 + 1, $td_end - $td_start2 - 1);
        $str_pointer = $td_end;
        $founded = true;
        
        $fields_arr[] = trim($field); 
    }

    return $fields_arr;

}

private function getFieldByMap($fieldname, $fields_arr, $fields_map) {

    if ((isset($fields_map[$fieldname])) && (count($fields_arr) >= $fields_map[$fieldname])) {
        return $fields_arr[$fields_map[$fieldname]];
    } else {
        return null;
    }

}

private function bindFields($fields_arr, $fields_map) {

    if (count($fields_arr)) {
     
        $res = array();
        $res['import_status'] = 'FOUNDED';

        list($res['work_order_number'], $res['external_id']) = $this->exiractTicket($fields_arr[0]);
        if (!$res['work_order_number']) {return null;}
        
        $res['priority']        = $this->getFieldByMap('priority', $fields_arr, $fields_map);
        $res['received_date']   = $this->exiractDate($this->getFieldByMap('received_date', $fields_arr, $fields_map));
        $res['category']        = $this->getFieldByMap('category', $fields_arr, $fields_map);
        $res['fin_loc']         = $this->getFieldByMap('fin_loc', $fields_arr, $fields_map);

        return $res;
        
    } else {
        return null;
    }

}


private function exiractDate($value) {
    
    if (preg_match("/<span class=\"lbl CFX\">(\d+\/\d+\/\d+)<\/span>/", $value, $matches)) {

        $dateObj = \DateTime::createFromFormat('n/j/Y', $matches[1]);
        if ($dateObj !== false) {
            return $dateObj->format('Y-m-d');
        } else {
            return $matches[1];
        }
    } else {
        return null;
    }

}


private function exiractTicket($value) {

    if (preg_match("/entityid=([^\"]+)\">(\d+)</", $value, $matches)) {
        return array($matches[2], $matches[1]);		
    } else {
        return array(null, null);
    }
    
    
}


private function quoteField($field) {

    return '"' . str_replace('"', '\"', $field) . '"';
    
}


public function storeAll() {

    foreach ($this->items as &$item) {

            $this->storeItem($item);

    }

}


private function storeItem(&$item) {

    $this->entries_processed++;
    if ($this->isEntryExists($item['work_order_number'])) {
        $item['import_status'] = 'SKIPPED';
        $this->entries_skipped++;
    } else {
        if ($this->writeItem($item)) {
            $item['import_status'] = 'CREATED';
            $this->entries_created++;
        } else {
            $this->errors++;
        }
    }

}


private function isEntryExists($work_order_number) {

    try {
        return DB::table('work_order')->where('work_order_number', $work_order_number)->exists();
    } catch (\Exception $e) {
        //echo $e->getMessage();
        return false;
    }

}


private function writeItem($item) {

    try {
        return DB::table('work_order')->insert([
            'work_order_number' => $item['work_order_number'],
            'external_id'       => $item['external_id'],
            'priority'          => $item['priority'],
            'received_date'     => $item['received_date'],
            'category'          => $item['category'],
            'fin_loc'           => $item['fin_loc'],
        ]);
    } catch (\Exception $e) {
        //echo $e->getMessage();
        return false;
    }
    
}


public function saveToCsv() {

    $res = '';
    foreach ($this->items as $item) {
    
        $res .= implode(',', array(
            $this->quoteField($item['import_status']),
            $this->quoteField($item['work_order_number']),
            $this->quoteField($item['external_id']),
            $this->quoteField($item['priority']),
            $this->quoteField($item['received_date']),
            $this->quoteField($item['category']),
            $this->quoteField($item['fin_loc']),
        )) . "\n"; 
    
    }
    
    return $res;	
    
}

} //class

