<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HL7Massage
 * คลาสเพื่อวิเคราะห์โครงสร้างไฟล์ HL7
 * 
 * 
 * @author suchart bunhachirat
 * @link http://stackoverflow.com/questions/9104377/hl7-parser-writer-for-php ต้นฉบับโค้ดโปรแกรม
 */
class HL7 {

    /**
     * segs เก็บอาเรย์จากสตริง HL7
     */
    private $segs = "";
        
    protected $field_segment = "";
    protected $message = array();
    public $valid = FALSE;
    
    
    /**
     * ตรวจสอบข้ัอมูลความถูกต้องเบื้องต้นของสตริง HL7 ก่อนทำงานต่อ 
     * @access protected
     */
    public function __construct($string) {
        $this->segs = explode("\r", $string);
        if (substr($this->segs[0], 0, 3) == 'MSH') {
            $this->parsemsg();
        } else {
            throw new Exception('Invalid HL7 Message must start with MSH.');
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

    public function get_message() {
        return $this->message;
    }

    public function check_message() {
        
    }

}
