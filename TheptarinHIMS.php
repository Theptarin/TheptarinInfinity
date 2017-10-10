<?php

require_once 'hl7_2_hims.php';

/**
 * Description of TheptarinHIMS
 * 
 *  อ่านไฟล์ใน ResultForHims เพื่อเก็บรายละเอียดไปใช้ค้นข้อมูลที่ HIMS
 * ใช้ result_document_id จากข้อมูล HL7 เพื่อค้น v5_id
 *  สร้างไฟล์ชื่อตามแบบ HIMS
 * ใส่ v5_id แทน result_document_id เดิม
 * ย้ายไฟล์ไปที่ Result ให้ HIMS
 * @author สุชาติ บุญหชัยรัตน์ suchart bunhachirat <suchartbu@gmail.com>
 */
class TheptarinHIMS {

    /**
     * รับค่าพาธโฟลเดอร์ HL7
     * @param string $path_foder
     */
    public function __construct($path_foder) {
        $list_files = glob($path_foder);
        foreach ($list_files as $filename) {
            printf("$filename size " . filesize($filename) . "  " . date('Ymd H:i:s') . "\n");
            $hl7_2_hims = new hl7_2_hims($filename);
            /**
             * ย้ายไฟล์ที่ประมวลผลตามสถานะ
             */
            switch ($hl7_2_hims->message) {
                case "DONE" :
                    $this->move_done_file($filename);
                    break;
                case "ERROR":
                    $this->move_error_file($path_foder);
                    break;
                default :
            }
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

    /**
     * ย้ายไฟล์ที่ประมาลผลไม่สำเร็จ
     * @param string $filename
     */
    private function move_error_file($filename) {
        try {
            rename($filename, "/var/www/mount/hims-doc/lis/error/" . basename($filename));
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
        }
    }

}

$my = new TheptarinHIMS("/var/www/mount/hims-doc/lis/ResultForHims/*.hl7");
