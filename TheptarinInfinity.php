<?php

/**
 * การอ่านไฟล์ข้อมูลผลแลปผู้ป่วยจาก LIS
 * 1. อ่านไฟล์ HL7 ผลแลปอยู่ในโฟลเดอร์
 * 2. วิเคราะห์ไฟล์แยกส่วนข้อมูลเพื่อสามารถจัดเตรียมนำเข้าฐานข้อมูลได้
 * 3. ส่งข้อมูลเข้าฐานข้อมูล
 * @author สุชาติ บุญหชัยรัตน์ suchart bunhachirat <suchartbu@gmail.com>
 * @author ปนัสดา คชพันธ์ <panusdapun@gmail.com>
 * @link https://drive.google.com/file/d/0B9r7oU4ZCTVJcnhteF9YSUF3Q0k/view?usp=sharing รายละเอียด HL7
 */
require_once 'hl7_2_db.php';

class TheptarinInfinity {

    /**
     * รับค่าพาธโฟลเดอร์ HL7
     * @param string $path_foder
     */
    public function __construct($path_foder) {
        $list_files = glob($path_foder);
        foreach ($list_files as $filename) {
            printf("$filename size " . filesize($filename) . "  " . date('Ymd H:i:s') . "\n");
            $hl7_2_db = new hl7_2_db($filename);
            /**
             * ย้ายไฟล์ทั้งหมดไว้ที่ "/var/www/mount/hims-doc/lis/ResultForHims/" เพืื่อแปลงส่ง HIMS ต่อไป
             */
            if ($hl7_2_db->error_message == null) {
                $this->move_done_file($filename);
            } else {
                $this->move_error_file($filename);
                echo $hl7_2_db->error_message . "\n";
            }
        }
        /**
         * @todo เรียกโปรแกรมเพื่อแปลง HL7 ส่งเข้า HIMS
         */
        
    }

    /**
     * ย้ายไฟล์ที่ประมาลผลสำเร็จ
     * @param string $filename
     */
    private function move_done_file($filename) {
        try {
            rename($filename, "/var/www/mount/hims-doc/lis/ResultForHims/" . basename($filename));
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * ย้ายไฟล์ที่ประมาลผลไม่สำเร็จ
     * @param string $filename
     */
    private function move_error_file($filename) {
        try {
            rename($filename, "/var/www/mount/hims-doc/lis/ResultForHims/" . basename($filename));
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
        }
    }

}

$my = new TheptarinInfinity("/var/www/mount/hims-doc/lis/ResultForTheptarin/*.hl7");
//$my = new TheptarinInfinity("/var/www/mount/hims-doc/lis/HIMSReadNotDocument/25600411/60040700111_201704111322382331.hl7");
