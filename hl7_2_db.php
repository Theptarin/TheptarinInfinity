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

    private $hl7;
    private $conn = null;

    /**
     * รับค่าพาธไฟล์ HL7
     * @param string $path_filename
     */
    public function __construct($path_filename) {

        //$path_filename = "./ext/lis/res/151008206007219.hl7"; //ชื่อภาษาไทย ต้องแปลงเป็น UTF8
        //$path_filename = "./ext/lis/res/151010206004213.hl7"; //Lab เยอะ
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
        $dsn = 'mysql:host=10.1.99.6;dbname=theptarin_utf8';
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
        $sql = "INSERT INTO lis_order (message_date, patient_id, patient_name, gender, birth_date, lab_number, reference_number, accept_time)
    VALUES (:message_date, :patient_id, :patient_name, :gender, :birth_date, :lab_number, :reference_number, :accept_time)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array(":message_date" => $message[0]->fields[5], ":patient_id" => $message[1]->fields[2], ":patient_name" => $message[1]->fields[4], ":gender" => $message[1]->fields[7], ":birth_date" => $message[1]->fields[6], ":lab_number" => $message[4]->fields[1], ":reference_number" => $message[3]->fields[1], ":accept_time" => $message[3]->fields[8]));
            $this->read_result($this->conn->lastInsertId());
        } catch (Exception $ex) {
            echo "Could not insert order : " . $ex->getMessage();
        }
    }

    /**
     * เลือกอ่าน segment ชื่อ OBX คือผลแต่ละรายการ และ NTE สำหรับหมายเหตุ
     * @param int $order_id
     */
    protected function read_result($order_id) {
        $message = $this->hl7->get_message();
        /**
         * คำสั่งคัดเฉพาะ secment ที่ต้องการ
         */
        foreach ($message as $value) {
            /**
             * @todo ถ้า$value->name มีขึ้นบรรทัดใหม่ต่อท้ายจะทำให้เช็คไม่เจอ ควรป้องกันปัญหานี้ต่อไป
             */
            if ($value->name == 'OBX') {
                $result_id = NULL;
                $result_id = $this->insert_result($order_id, $value);
            } elseif ($value->name == 'NTE' and $result_id > 0) {
                $this->insert_result_remark($result_id, $value);
            }
        }
    }

    /**
     * เพิ่มรายการใหม่ใน lis_result
     * @param int $order_id
     * @param array $value
     * @return int
     */
    protected function insert_result($order_id, $value) {
        /**
         * @todo รอปรับโครงสร้างตารางให้ถูกต้อง แล้วมาแก้ไข SQL
         */
        $sql = "INSERT INTO lis_result (lis_order_id, code_lis, test, code_lab, code_rst , result, result_comment, unit, normal_range, user_id, technical_time, medical_time)
    VALUES (:lis_order_id, :code_lis, :test, :code_lab, :code_rst, :result, '###', :unit, :normal_range, :user_id, :technical_time, :medical_time)";
        try {
            $test = explode("^",  $value->fields[2], 4);
            $validation_time = explode("^",  $value->fields[14], 2);
            //print_r($validation_time);
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array(":lis_order_id" => $order_id, ":code_lis" => $test[0], ":test" => $test[1], ":code_lab" => $test[2], ":code_rst" => $test[3], ":result" => $value->fields[4], ":unit" => $value->fields[5], ":normal_range" => $value->fields[6], ":technical_time" => $validation_time[0], ":medical_time" => $validation_time[1], ":user_id" => $value->fields[15]));
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            echo "Could not insert result : " . $ex->getMessage();
        }
    }

    /**
     * เพิ่มรายการใหม่ใน lis_result_remark
     * @param type $result_id
     * @param type $value
     * @return type
     */
    protected function insert_result_remark($result_id, $value) {
        $sql = "INSERT INTO lis_result_remark (lis_result_id, remark)
    VALUES (:lis_result_id, :remark)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array(":lis_result_id" => $result_id, ":remark" => $value->fields[2]));
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            echo "Could not insert remark result : " . $ex->getMessage();
        }
    }

}
