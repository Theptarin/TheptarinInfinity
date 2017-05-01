<?php

require_once './orr_lib/hl7.php';

/**
 * Description of hl7_2_hims
 * 
 *  อ่านไฟล์ใน ResultForHims เพื่อเก็บรายละเอียดไปใช้ค้นข้อมูลที่ HIMS
 * ใช้ result_document_id จากข้อมูล HL7 เพื่อค้น v5_id
 *  สร้างไฟล์ชื่อตามแบบ HIMS
 * ใส่ v5_id แทน result_document_id เดิม
 * ย้ายไฟล์ไปที่ Result ให้ HIMS
 * @author สุชาติ บุญหชัยรัตน์ suchart bunhachirat <suchartbu@gmail.com>
 */
class hl7_2_hims {

    private $path_filename;
    private $hl7;
    private $conn = NULL;
    private $lab_checkin_match = NULL;
    public $error_message = NULL;

    /**
     * รับค่าพาธไฟล์ HL7
     * @param string $path_filename
     */
    public function __construct($path_filename) {
        $this->path_filename = $path_filename;
        try {
            $this->hl7 = new HL7($path_filename);
            $this->check_match();
            if ($this->lab_checkin_match['v5_id'] <> '') {
                $this->set_message_v5();
                $this->move_done_file($path_filename);
            }
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
     * ตรวจสอบ  v4_id จาก lab_checkin_match จาก ttr_hims
     */
    private function check_match() {

        $message = $this->hl7->get_message();
        $this->get_conn();
        $sql = "SELECT * FROM `lab_checkin_match` WHERE `result_document_id` = :reference_number";

        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $result = $stmt->execute(array(":reference_number" => $message[3]->fields[1]));
            if ($result) {
                $this->lab_checkin_match = $stmt->fetch();
                //print $message[4]->fields[1];
            } else {
                $error = $stmt->errorInfo();
                //echo 'Query failed with message: ' . $error[2];
                $this->error_message .= " v4_id : " . $error[2];
            }
        }
    }

    /**
     * สร้างไฟล์ HL7 ไปที่ /var/www/mount/hims-doc/lis/Result/
     */
    private function set_message_v5() {
        $message = $this->hl7->get_message();
        $file_contents = file_get_contents($this->path_filename);
        $search = "ORC|1|" . $message[3]->fields[1];
        $replace = "ORC|1|" . $this->lab_checkin_match['v5_id'];
        //print_r(str_replace($search, $replace, $file_contents));
        $filename = "/var/www/mount/hims-doc/lis/Result/" . $this->lab_checkin_match['v5_id'] . "_" . $message[4]->fields[1] . "_" . date("YmdHi") . "_TRH.hl7";
        //$filename = "/var/www/mount/hims-doc/lis/test/" . $this->lab_checkin_match['v5_id'] . "_" . $message[4]->fields[1] . "_" . date("YmdHi") . "_TRH.hl7";
        try {
            $handle = fopen($filename, "w") or die("Unable to open file!");
            fwrite($handle, str_replace($search, $replace, $file_contents));
            fclose($handle);
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
            $this->error_message .= " set_message_v5 : " . $ex->getMessage();
        }
    }
    
     /**
     * ย้ายไฟล์ที่ประมาลผลสำเร็จ
     * @param string $filename
     */
    private function move_done_file($filename) {
        try {
            rename($filename, "/var/www/mount/hims-doc/lis/done/" . basename($filename));
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
        }
    }

}
