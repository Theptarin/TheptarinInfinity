<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * ค้นผลแลปจากข้อมูลผู้ป่วยที่ CheckIN ใน HimsV5 และแปลงไฟล์ให้ HimsV5
 * อ่านไฟล์ HL7 เพื่อนำ result_document_id ของไฟล์ไปค้นข้อมูลที่ labplus_checkin
 * อ่านข้อมูล hn request_date  reuest_lab_type ที่ HimsV5
 * ค้นข้อมูลที่  HimsV4 เพื่อหาเลขที่ผลการตรวจ
 * ค้นไฟล์ HL7 ผลการตรวจ
 * แปลงเลขผลการตรวจให้เป็นของ HimsV5
 * @author สุชาติ บุญหชัยรัตน์ suchart bunhachirat <suchartbu@gmail.com>
 */
class hims_hl7 {

    public function __construct($path_foder) {
        
    }

     /**
     * เตรียมฐานข้อมูล ttr_hims เพื่อใช้ข้อมูลของ HIMS V5
     */
    private function get_conn_v5() {
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
     * hn request_date  reuest_lab_type ของ hims v5
     */
    private function get_v5_data() {
        $this->get_conn_v5();
        $sql ="";
        
    }

}
