<?php

/*
 * The MIT License
 *
 * Copyright 2559 suchart.orr@gmail.com.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once 'hl7_segment.php';

//namespace orr;

/**
 * Description of hl7
 * 1. โหลดไฟล์ HL7
 * 2. สร้าง DOM ของ HL7
 * 
 * @link http://stackoverflow.com/questions/9104377/hl7-parser-writer-for-php source code
 * @author suchart.orr@gmail.com
 */
class hl7 {

    /**
     * เก็บอาเรย์จากสตริง HL7
     */
    private $seg = array();

    /**
     * พาทและชื่อไฟล์ HL7
     */
    private $filename = "";

    /**
     * ออบเจ็คแต่ละ segment
     */
    private $message = array();

    /**
     * นับรายการแยกแต่ละ segment
     */
    public $segment_count = array();


    /**
     * ตรวจหา 'MSH' ส่วนแรกของสตริง HL7 Message
     * @access protected
     */
    public function __construct($filename) {
        $this->load($filename);
    }

    /**
     * โหลดไฟล์ HL7
     */
    public function load($filename) {
        if (file_exists($filename)) {
            try {
                $myfile = fopen($filename, "r");
                $this->set_content(fread($myfile, filesize($filename)));
                fclose($myfile);
            } catch (Exception $ex) {
                echo 'HL7 load exception: ', $ex->getMessage(), "\n";
            }
        } else {
            throw new Exception('file not exists!');
        }
    }

    /**
     * อ่านไฟล์ HL7
     */
    protected function set_content($string) {
        $this->seg = array_filter(explode("\r", $string));

        if (substr($this->seg[0], 0, 3) == 'MSH') {
            $i = 0;
            foreach ($this->seg as $value) {
                $seg = explode("|", $value, 2);
                $segment = new hl7_segment();
                $segment->name = $seg[0];
                $segment->index = $i;
                $segment->fields = explode("|", $seg[1]);
                $this->set_segemet_count($segment->name);
                $this->message[] = $segment;
                $i ++;
            }
        } else {
            throw new Exception('Invalid HL7 Message must start with MSH.');
        }
    }

    /**
     * คืนค่าอะเรย์ตามบรรทัดในไฟล์ HL7 ตามโครงสร้าง hl7_segment
     * @return type array
     */
    public function get_message() {
        return $this->message;
    }
    
    /**
     * นับรายการแต่ละ segment
     */
    private function set_segemet_count($key){
        if(array_key_exists($key, $this->segment_count)){
            $this->segment_count[$key] ++;
        }  else {
            $this->segment_count[$key] = 1;
        }
    }

}
