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
        }
    }

}

$my = new TheptarinHIMS("/var/www/mount/hims-doc/lis/ResultForHims/*.hl7");
