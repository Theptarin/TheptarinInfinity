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

    public function __construct($path_foder) {
        $list_files = glob($path_foder);
        foreach ($list_files as $filename) {
            printf("$filename size " . filesize($filename) . "  " . date('Ymd H:i:s') . "\n");
            $hl7_2_db = new hl7_2_db($filename);
        }
    }
}

$my = new TheptarinInfinity("./ext/lis/res/*.hl7");
