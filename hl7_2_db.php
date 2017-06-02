<?php

/*
 * The MIT License
 *
 * Copyright 2559 it.
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
require_once './orr_lib/hl7.php';

/**
 * Description of hl7_2_db
 * 
 * 1. อ่านไฟล์โดยใช้คลาส hl7
 * 2. เชื่อมฐานข้อมูล
 * 3. เพิ่มรายการใหม่ใน theptarin_utf8 lis_order lis_result lis_result_remark
 *
 * @author suchart bunhachirat
 */
class hl7_2_db {

    private $path_filename;
    private $hl7;
    private $conn = null;
    public $error_message = null;

    /**
     * รับค่าพาธไฟล์ HL7
     * @param string $path_filename
     */
    public function __construct($path_filename) {

        //$path_filename = "./ext/lis/res/151008206007219.hl7"; //ชื่อภาษาไทย ต้องแปลงเป็น UTF8
        //$path_filename = "./ext/lis/res/151010206004213.hl7"; //Lab เยอะ
        $this->path_filename = $path_filename;
        try {
            $this->hl7 = new HL7($path_filename);
            $this->insert_order();
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * เชื่อมฐานข้อมูลที่ต้องการใช้งาน
     */
    private function get_conn() {
        $dsn = 'mysql:host=10.1.99.6;dbname=ttr_hims';
        $username = 'orr-projects';
        $password = 'orr-projects';
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (Exception $ex) {
            echo "Could not connect to database : " . $ex->getMessage(), "\n";
            exit();
        }
    }

    /**
     * เพิ่มรายการใหม่ใน lis_order
     */
    protected function insert_order() {
        $this->get_conn();
        $message = $this->hl7->get_message();
        //print_r($message);
        $sql = "INSERT INTO lis_order (message_date, patient_id, patient_name, gender, birth_date, lis_number, reference_number, accept_time,request_div) VALUES (:message_date, :patient_id, :patient_name, :gender, :birth_date, :lis_number, :reference_number, :accept_time,:request_div) ON DUPLICATE KEY UPDATE message_date = :message_date , accept_time = :accept_time ";
        //$sql = "INSERT INTO lis_order (message_date, patient_id, patient_name, gender, birth_date, lis_number, reference_number, accept_time,request_div) VALUES (:message_date, :patient_id, :patient_name, :gender, :birth_date, :lis_number, :reference_number, :accept_time,:request_div)";
        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $result = $stmt->execute(array(":message_date" => $message[0]->fields[5], ":patient_id" => $message[1]->fields[2], ":patient_name" => $message[1]->fields[4], ":gender" => $message[1]->fields[7], ":birth_date" => $message[1]->fields[6], ":lis_number" => $message[4]->fields[1], ":reference_number" => $message[3]->fields[1], ":accept_time" => $message[3]->fields[8], ":request_div" => substr($message[2]->fields[18],3)));

            if ($result) {
                $this->read_result($message[4]->fields[1]);
                //print $message[4]->fields[1];
            } else {
                $error = $stmt->errorInfo();
                //echo 'Query failed with message: ' . $error[2];
                $this->error_message .= " insert_order : " . $error[2];
            }
        }
    }

    /**
     * เลือกอ่าน segment ชื่อ OBX คือผลแต่ละรายการ และ NTE สำหรับหมายเหตุ
     * @param int $lis_number
     */
    protected function read_result($lis_number) {
        $message = $this->hl7->get_message();
        /**
         * คำสั่งคัดเฉพาะ secment ที่ต้องการ
         */
        foreach ($message as $value) {
            /**
             * @todo ถ้า$value->name มีขึ้นบรรทัดใหม่ต่อท้ายจะทำให้เช็คไม่เจอ ควรป้องกันปัญหานี้ต่อไป
             */
            switch ($value->name) {
                case "OBX":
                    if (!is_null($remark)) {
                        $remark = $this->insert_result_remark($lis_number, $lis_code, $remark);
                    }
                    $lis_code = $this->insert_result($lis_number, $value);
                    break;
                case "NTE":
                    $remark .= $value->fields[2] . "\n";
                    break;
                default :
                    $lis_code = 0;
                    $remark = NULL;
            }
        }
    }

    /**
     * เพิ่มรายการใหม่ใน lis_result
     * @param int $lis_number
     * @param array $message
     * @return int
     */
    protected function insert_result($lis_number, $message) {

        $test = explode("^", $message->fields[2], 4);
        $validation_time = explode("^", $message->fields[14], 2);

        $sql = "INSERT INTO lis_result (lis_number, lis_code, test, lab_code, result_code , result,  unit, normal_range, user_id, technical_time, medical_time) VALUES (:lis_number, :lis_code, :test, :lab_code, :result_code, :result, :unit, :normal_range, :user_id, :technical_time, :medical_time)";
        $stmt = $this->conn->prepare($sql);
        /**
         * @todo  lab_type จากไฟล์ HL7 ไม่มี แต่แก้ไขให้มีในตารางตามเดิม
         * */
        if ($stmt) {
            $result = $stmt->execute(array(":lis_number" => $lis_number, ":lis_code" => $test[0], ":test" => $test[1], ":lab_code" => $test[2], ":result_code" => $test[3], ":result" => $message->fields[4], ":unit" => $message->fields[5], ":normal_range" => $message->fields[6], ":technical_time" => $validation_time[0], ":medical_time" => $validation_time[1], ":user_id" => $message->fields[15]));

            if ($result) {
                return $test[0];
            } else {
                $error = $stmt->errorInfo();
                //echo 'Query failed with message: ' . $error[2];
                $this->error_message .= " insert_result : " . $error[2];
            }
        }
    }

    /**
     * เพิ่มรายการใหม่ใน lis_result_remark
     * @param int $lis_number
     * @param int $lis_code
     * @param string $remark
     * @return int
     */
    protected function insert_result_remark($lis_number, $lis_code, $remark) {

        $sql = "UPDATE `lis_result` SET `remark`= :remark WHERE `lis_number` = :lis_number AND `lis_code` = :lis_code";
        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $result = $stmt->execute(array(":lis_number" => $lis_number, ":lis_code" => $lis_code, ":remark" => $remark));
            if ($result) {
                //print $remark;
                return null;
            } else {
                $error = $stmt->errorInfo();
                //echo 'Query failed with message: ' . $error[2];
                $this->error_message .= "  insert_result_remark : " . $error[2];
            }
        }
    }

}
