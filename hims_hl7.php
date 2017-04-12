<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once './orr_lib/hl7.php';

/**
 * ค้นผลแลปจากข้อมูลผู้ป่วยที่ CheckIN ใน HimsV5 และแปลงไฟล์ให้ HimsV5
 * + อ่านไฟล์ HL7 เพื่อนำ result_document_id ของไฟล์ไปค้นข้อมูลที่ labplus_checkin
 * อ่านข้อมูล hn request_date  reuest_lab_type ที่ HimsV5
 * ค้นข้อมูลที่  HimsV4 เพื่อหาเลขที่ผลการตรวจ
 * ค้นไฟล์ HL7 ผลการตรวจ
 * แปลงเลขผลการตรวจให้เป็นของ HimsV5
 * @author สุชาติ บุญหชัยรัตน์ suchart bunhachirat <suchartbu@gmail.com>
 */
class hims_hl7 {

    private $hl7;
    private $conn_v4;
    private $data_v4;
    private $conn_v5;
    

    /**
     * รับค่าพาธไฟล์ HL7
     * @param string $path_filename
     */
    public function __construct($path_filename) {
        try {
            $this->hl7 = new hl7($path_filename);
            $this->get_v4_data();
            $this->get_v5_data();
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * เตรียมฐานข้อมูล ttr_mse เพื่อใช้ข้อมูลของ HIMS V4
     */
    private function db_conn_v4() {
        $dsn = 'mysql:host=10.1.99.6;dbname=ttr_mse';
        $username = 'orr-projects';
        $password = 'orr-projects';
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        try {
            $this->conn_v4 = new PDO($dsn, $username, $password, $options);
        } catch (Exception $ex) {
            echo "Could not connect to database : " . $ex->getMessage(), "\n";
            exit();
        }
    }

    /**
     * เตรียมฐานข้อมูล ttr_hims เพื่อใช้ข้อมูลของ HIMS V5
     */
    private function db_conn_v5() {
        $dsn = 'mysql:host=10.1.99.6;dbname=ttr_hims';
        $username = 'orr-projects';
        $password = 'orr-projects';
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        try {
            $this->conn_v5 = new PDO($dsn, $username, $password, $options);
        } catch (Exception $ex) {
            echo "Could not connect to database : " . $ex->getMessage(), "\n";
            exit();
        }
    }

    /**
     * hn request_date  reuest_lab_type ของ hims v4 ใช้ข้อมูลรายการแรกที่พบ
     */
    private function get_v4_data() {
        $this->db_conn_v4();
        $sql = "SELECT DISTINCT `hn` , `checkin_date`,`lab_type` FROM `labplus_checkin` WHERE `result_document_id` = :result_document_id";
        $message = $this->hl7->get_message();
        $result_document_id = $message[3]->fields[1];
        echo $result_document_id;
        $stmt = $this->conn_v4->prepare($sql);

        if ($stmt) {
            $result = $stmt->execute(array(":result_document_id" => $result_document_id));

            if ($result) {
                $record = $stmt->fetch();
                $this->data_v4 =  $record;
                print_r($record);
            } else {
                $error = $stmt->errorInfo();
                echo 'Query failed with message: ' . $error[2];
            }
        }
    }
    
    /**
     * hn  ของ hims v5 ใช้ข้อมูลรายการแรกที่พบ
     */
    private function get_v5_data(){
        $this->db_conn_v5();
        $sql = "SELECT DISTINCT `hn`,`request_lab_type`,`document_id` FROM `lab_request` WHERE `hn` = :hn AND `checkin_date` = :checkin_date";
        
        $stmt = $this->conn_v5->prepare($sql);

        if ($stmt) {
            $result = $stmt->execute(array(":hn" => $this->data_v4["hn"] , ":checkin_date" => $this->data_v4["checkin_date"]));

            if ($result) {
                $record = $stmt->fetch();
                $this->data_v5 =  $record;
                print_r($record);
            } else {
                $error = $stmt->errorInfo();
                echo 'Query failed with message: ' . $error[2];
            }
        }
        
    }

}

//$path_filename = "./ext/hl7/v4/60032900058_201703291003586817.hl7";
$path_filename = "./ext/hl7/v4/60032900058_201703290936314748.hl7";
$my = new hims_hl7($path_filename);
