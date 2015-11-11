<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HL7
 * @link http://stackoverflow.com/questions/9104377/hl7-parser-writer-for-php source code
 * @author orr
 */
class HL7 {

    protected $segs = "";
    protected $field_segment = "";
    protected $message = array();
    public $valid = FALSE;

    public function __construct($string) {
        
        if($this->set_content($string)){
            $this->parsemsg();
            $this->valid = TRUE;
        }  else {
            print_r($this->message);
        }
    }

    protected function set_content($string) {
        //$this->segs = explode("\n", $string);
        $this->segs = explode("\r", $string);
        //get delimiting characters
        if (substr($this->segs[0], 0, 3) != 'MSH') {
            $this->message['ERROR'][0] = 'Invalid HL7 Message.';
            $this->message['ERROR'][1] = 'Must start with MSH';
            return FALSE;
        }  else {
            return TRUE;
        }
    }

    protected function parsemsg() {
        $delbarpos = strpos($this->segs[0], '|', 4);  //looks for the closing bar of the delimiting characters
        $delchar = substr($this->segs[0], 4, ($delbarpos - 4));
        $field_segment = substr($delchar, 0, 1);
        foreach ($this->segs as $fseg) {
            $segments = explode('|', $fseg);
            $segname = $segments[0];
            $i = 0;
            foreach ($segments as $seg) {
                if (strpos($seg, $field_segment) == false) {
                    $this->message[$segname][$i] = $seg;
                } else {
                    $j = 0;
                    $sf = explode($field_segment, $seg);
                    foreach ($sf as $f) {
                        $this->message[$segname][$i][$j] = $f;
                        $j++;
                    }
                }
                $i++;
            }
        }
        //define('PT_NAME',$this->message['PID'][5][0],true);
        return NULL;
    }
    
    public function get_message(){
        return $this->message;
    }
    
    public function check_message(){
        
    }
}
