<?php

require_once './orr_lib/hl7.php';

/**
 * Description of hl7_2_hims
 * 
 *  อ่านไฟล์ใน ResultForHims เพื่อเก็บรายละเอียดไปใช้ค้นข้อมูลที่ HIMS
 * ใช้ result_document_id จากข้อมูล HL7 เพื่อค้น file_no
 *  สร้างไฟล์ชื่อตามแบบ HIMS
 * ใส่ file_no แทน result_document_id เดิม
 * ย้ายไฟล์ไปที่ Result ให้ HIMS
 * @author สุชาติ บุญหชัยรัตน์ suchart bunhachirat <suchartbu@gmail.com>
 */
class hl7_2_hims {

    private $path_filename;
    private $hl7;
    private $conn = NULL;
    public $error_message = NULL;
    public $record_count = 0;

    /**
     * รับค่าพาธไฟล์ HL7
     * @param string $path_filename
     */
    public function __construct($path_filename) {
        $this->path_filename = $path_filename;
        try {
            $this->hl7 = new HL7($path_filename);
            $this->check_match();
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
                $record = $stmt->fetch();
                $this->set_message_v5($record);
                $this->record_count ++;

                echo " record_count = " . $this->record_count . " | " . __FILE__ . " | " . __LINE__ . "\n";
            } else {
                $error = $stmt->errorInfo();
                $this->error_message .= " v4_id : " . $error[2];
            }
        }
    }

    /**
     * สร้างไฟล์ HL7 ไปที่ /var/www/mount/hims-doc/lis/Result/
     * @param array $record
     */
    private function set_message_v5($record) {
        $message = $this->hl7->get_message();
        $file_contents = file_get_contents($this->path_filename);
        $search = "ORC|1|" . $message[3]->fields[1];
        $replace = "ORC|1|" . $record['file_no'];
        //print_r(str_replace($search, $replace, $file_contents));
        $filename = "/var/www/mount/hims-doc/lis/Result/" . $record['file_no'] . "_" . $message[0]->fields[8] . "_TRH.hl7";
        echo " filename = " . $filename . " | " . __FILE__ . " | " . __LINE__ . "\n";
        try {
            $handle = fopen($filename, "w") or die("Unable to open file!");
            fwrite($handle, str_replace($search, $replace, $file_contents));
            fclose($handle);
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
            $this->error_message .= " set_message_v5 : " . $ex->getMessage();
        }
    }

}
