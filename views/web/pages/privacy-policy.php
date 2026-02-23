<?php
/**
 * Privacy Policy Page — PDPA Compliance
 * นโยบายความเป็นส่วนตัว ตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562
 */
$pageTitle = 'นโยบายความเป็นส่วนตัว | ส.ร.ม.ก. SDAK';
$currentPage = 'privacy-policy';
include ROOT_PATH . 'templates/public/header.php';
?>

<section class="py-5" style="min-height:80vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Header -->
            <div class="text-center mb-5">
                <span class="badge bg-primary-custom px-3 py-2 mb-3"><i class="bi bi-shield-lock me-1"></i> PDPA</span>
                <h1 class="fw-bold">นโยบายความเป็นส่วนตัว</h1>
                <p class="text-muted">Privacy Policy — พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562</p>
                <p class="text-muted small">ปรับปรุงล่าสุด: <?php echo date('d/m/') . (date('Y') + 543); ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5 privacy-content">

                    <!-- 1. บทนำ -->
                    <h4 class="privacy-heading"><i class="bi bi-1-circle-fill me-2"></i>บทนำ</h4>
                    <p>สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์ (ส.ร.ม.ก.) หรือ SDAK (ต่อไปนี้เรียกว่า "สมาคม") ตระหนักถึงความสำคัญของการคุ้มครองข้อมูลส่วนบุคคลของท่าน และมุ่งมั่นที่จะปฏิบัติตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA) อย่างเคร่งครัด</p>
                    <p>นโยบายฉบับนี้จัดทำขึ้นเพื่อแจ้งให้ท่านทราบถึงวิธีการที่สมาคมเก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคลของท่าน รวมถึงสิทธิต่าง ๆ ของท่านในฐานะเจ้าของข้อมูลส่วนบุคคล</p>

                    <!-- 2. ข้อมูลที่เก็บรวบรวม -->
                    <h4 class="privacy-heading"><i class="bi bi-2-circle-fill me-2"></i>ข้อมูลส่วนบุคคลที่เก็บรวบรวม</h4>
                    <p>สมาคมอาจเก็บรวบรวมข้อมูลส่วนบุคคลของท่าน ดังนี้:</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="privacy-info-box">
                                <h6><i class="bi bi-person-badge me-2 text-primary"></i>ข้อมูลระบุตัวตน</h6>
                                <ul class="mb-0">
                                    <li>ชื่อ-นามสกุล, คำนำหน้า</li>
                                    <li>หมายเลขบัตรประชาชน (เฉพาะสมาชิก)</li>
                                    <li>วัน/เดือน/ปีเกิด</li>
                                    <li>รูปภาพโปรไฟล์</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="privacy-info-box">
                                <h6><i class="bi bi-telephone me-2 text-primary"></i>ข้อมูลการติดต่อ</h6>
                                <ul class="mb-0">
                                    <li>อีเมล</li>
                                    <li>หมายเลขโทรศัพท์</li>
                                    <li>ที่อยู่</li>
                                    <li>สถานที่ทำงาน/โรงเรียน</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="privacy-info-box">
                                <h6><i class="bi bi-briefcase me-2 text-primary"></i>ข้อมูลการเป็นสมาชิก</h6>
                                <ul class="mb-0">
                                    <li>ประเภทสมาชิก, รหัสสมาชิก</li>
                                    <li>ตำแหน่ง, สถานะ</li>
                                    <li>ประวัติการชำระค่าธรรมเนียม</li>
                                    <li>ประวัติการเข้าร่วมกิจกรรม</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="privacy-info-box">
                                <h6><i class="bi bi-globe me-2 text-primary"></i>ข้อมูลทางเทคนิค</h6>
                                <ul class="mb-0">
                                    <li>IP Address, ประเภทเบราว์เซอร์</li>
                                    <li>คุกกี้ (Cookies)</li>
                                    <li>ข้อมูลการใช้งานเว็บไซต์</li>
                                    <li>ข้อมูลการเข้าสู่ระบบ (Log)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 3. วัตถุประสงค์ -->
                    <h4 class="privacy-heading"><i class="bi bi-3-circle-fill me-2"></i>วัตถุประสงค์ในการเก็บรวบรวมข้อมูล</h4>
                    <p>สมาคมเก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคลของท่าน เพื่อวัตถุประสงค์ดังนี้:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered privacy-table">
                            <thead>
                                <tr>
                                    <th style="width:5%;">#</th>
                                    <th style="width:35%;">วัตถุประสงค์</th>
                                    <th style="width:30%;">ฐานกฎหมาย</th>
                                    <th style="width:30%;">รายละเอียด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>การสมัครและบริหารจัดการสมาชิก</td>
                                    <td>สัญญา (Contract)</td>
                                    <td>ลงทะเบียน, ยืนยันตัวตน, จัดการข้อมูลสมาชิก</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>การจัดกิจกรรมและอบรม</td>
                                    <td>สัญญา / ความยินยอม</td>
                                    <td>ลงทะเบียนกิจกรรม, ออกใบเสร็จ, ติดตามการเข้าร่วม</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>การเงินและค่าธรรมเนียม</td>
                                    <td>สัญญา / กฎหมาย</td>
                                    <td>เรียกเก็บค่าธรรมเนียม, ออกใบเสร็จรับเงิน</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>การติดต่อสื่อสาร</td>
                                    <td>ประโยชน์โดยชอบธรรม</td>
                                    <td>แจ้งข่าวสาร, ส่งจดหมายข่าว, แจ้งเตือนกิจกรรม</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>การปรับปรุงบริการ</td>
                                    <td>ประโยชน์โดยชอบธรรม</td>
                                    <td>วิเคราะห์การใช้งาน, พัฒนาเว็บไซต์</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>การปฏิบัติตามกฎหมาย</td>
                                    <td>หน้าที่ตามกฎหมาย</td>
                                    <td>ปฏิบัติตามคำสั่งหน่วยงานรัฐ, จัดทำรายงาน</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 4. การเปิดเผยข้อมูล -->
                    <h4 class="privacy-heading"><i class="bi bi-4-circle-fill me-2"></i>การเปิดเผยข้อมูลส่วนบุคคล</h4>
                    <p>สมาคมอาจเปิดเผยข้อมูลส่วนบุคคลของท่านให้แก่บุคคลหรือหน่วยงานดังนี้:</p>
                    <ul>
                        <li><strong>คณะกรรมการสมาคม</strong> — เพื่อการบริหารจัดการสมาคม</li>
                        <li><strong>ผู้ให้บริการภายนอก</strong> — เช่น ผู้ให้บริการ Hosting, ระบบอีเมล (เท่าที่จำเป็น)</li>
                        <li><strong>หน่วยงานราชการ</strong> — เมื่อมีคำสั่งตามกฎหมาย</li>
                    </ul>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        สมาคม <strong>จะไม่ขาย หรือเปิดเผยข้อมูลส่วนบุคคลของท่านเพื่อวัตถุประสงค์ทางการตลาด</strong> ให้แก่บุคคลภายนอกโดยไม่ได้รับความยินยอมจากท่าน
                    </div>

                    <!-- 5. คุกกี้ -->
                    <h4 class="privacy-heading"><i class="bi bi-5-circle-fill me-2"></i>นโยบายคุกกี้ (Cookie Policy)</h4>
                    <p>เว็บไซต์นี้ใช้คุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานของท่าน โดยแบ่งออกเป็น:</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="privacy-info-box border-start border-success border-3">
                                <h6><i class="bi bi-check-circle-fill text-success me-2"></i>คุกกี้ที่จำเป็น (Necessary)</h6>
                                <p class="mb-0 small text-muted">คุกกี้สำหรับการทำงานพื้นฐานของเว็บไซต์ เช่น การเข้าสู่ระบบ, การจดจำการตั้งค่า — ไม่สามารถปิดได้</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="privacy-info-box border-start border-info border-3">
                                <h6><i class="bi bi-bar-chart-fill text-info me-2"></i>คุกกี้เพื่อการวิเคราะห์ (Analytics)</h6>
                                <p class="mb-0 small text-muted">คุกกี้ที่ช่วยให้เราเข้าใจวิธีที่ผู้เข้าชมใช้งานเว็บไซต์ เพื่อปรับปรุงประสิทธิภาพ — สามารถเลือกปิดได้</p>
                            </div>
                        </div>
                    </div>
                    <p>ท่านสามารถจัดการการตั้งค่าคุกกี้ได้ผ่านแถบ Cookie Banner ที่แสดงเมื่อเข้าใช้งานเว็บไซต์ หรือผ่านการตั้งค่าเบราว์เซอร์ของท่าน</p>

                    <!-- 6. ความปลอดภัย -->
                    <h4 class="privacy-heading"><i class="bi bi-6-circle-fill me-2"></i>การรักษาความปลอดภัยของข้อมูล</h4>
                    <p>สมาคมใช้มาตรการรักษาความปลอดภัยที่เหมาะสม ได้แก่:</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-lock-fill fs-1 text-primary"></i>
                                <h6 class="mt-2">SSL/TLS Encryption</h6>
                                <p class="small text-muted mb-0">เข้ารหัสข้อมูลระหว่างการส่งข้อมูลทั้งหมด</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-shield-check fs-1 text-primary"></i>
                                <h6 class="mt-2">Access Control</h6>
                                <p class="small text-muted mb-0">ระบบควบคุมการเข้าถึงข้อมูลตามสิทธิ์</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-database-lock fs-1 text-primary"></i>
                                <h6 class="mt-2">Data Protection</h6>
                                <p class="small text-muted mb-0">เข้ารหัสข้อมูลสำคัญในฐานข้อมูล</p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        หากเกิดเหตุการณ์ข้อมูลรั่วไหล สมาคมจะแจ้งให้ท่านทราบภายใน 72 ชั่วโมง และรายงานต่อสำนักงานคณะกรรมการคุ้มครองข้อมูลส่วนบุคคลตามที่กฎหมายกำหนด
                    </div>

                    <!-- 7. สิทธิของเจ้าของข้อมูล -->
                    <h4 class="privacy-heading"><i class="bi bi-7-circle-fill me-2"></i>สิทธิของเจ้าของข้อมูล</h4>
                    <p>ท่านมีสิทธิตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล ดังนี้:</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="privacy-right-card">
                                <i class="bi bi-eye fs-4 text-primary"></i>
                                <h6>สิทธิในการเข้าถึง</h6>
                                <p class="small text-muted mb-0">ขอเข้าถึงและขอรับสำเนาข้อมูลส่วนบุคคลของท่าน</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="privacy-right-card">
                                <i class="bi bi-pencil-square fs-4 text-primary"></i>
                                <h6>สิทธิในการแก้ไข</h6>
                                <p class="small text-muted mb-0">ขอแก้ไขข้อมูลส่วนบุคคลให้ถูกต้องและเป็นปัจจุบัน</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="privacy-right-card">
                                <i class="bi bi-trash fs-4 text-danger"></i>
                                <h6>สิทธิในการลบ</h6>
                                <p class="small text-muted mb-0">ขอให้ลบหรือทำลายข้อมูลส่วนบุคคลของท่าน</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="privacy-right-card">
                                <i class="bi bi-pause-circle fs-4 text-warning"></i>
                                <h6>สิทธิในการระงับ</h6>
                                <p class="small text-muted mb-0">ขอให้ระงับการใช้ข้อมูลส่วนบุคคลชั่วคราว</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="privacy-right-card">
                                <i class="bi bi-arrow-left-right fs-4 text-info"></i>
                                <h6>สิทธิในการโอนย้าย</h6>
                                <p class="small text-muted mb-0">ขอรับข้อมูลส่วนบุคคลในรูปแบบที่อ่านได้ด้วยเครื่อง</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="privacy-right-card">
                                <i class="bi bi-x-circle fs-4 text-secondary"></i>
                                <h6>สิทธิในการคัดค้าน</h6>
                                <p class="small text-muted mb-0">คัดค้านการเก็บรวบรวม ใช้ หรือเปิดเผยข้อมูล</p>
                            </div>
                        </div>
                    </div>
                    <p>หากท่านต้องการใช้สิทธิใด ๆ ข้างต้น กรุณาติดต่อเจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคลของสมาคม (รายละเอียดด้านล่าง)</p>

                    <!-- 8. ข้อมูลละเอียดอ่อน -->
                    <h4 class="privacy-heading"><i class="bi bi-8-circle-fill me-2"></i>ข้อมูลละเอียดอ่อน (Sensitive Data)</h4>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <strong>สมาคมไม่เก็บรวบรวมข้อมูลละเอียดอ่อน</strong> ตามมาตรา 26 แห่ง พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล ได้แก่:
                    </div>
                    <ul>
                        <li>เชื้อชาติ, เผ่าพันธุ์, ความคิดเห็นทางการเมือง</li>
                        <li>ความเชื่อทางศาสนา, พฤติกรรมทางเพศ</li>
                        <li>ประวัติอาชญากรรม, ข้อมูลสหภาพแรงงาน</li>
                        <li>ข้อมูลสุขภาพ, ข้อมูลพันธุกรรม, ข้อมูลชีวมิติ</li>
                    </ul>
                    <p>หากมีความจำเป็นต้องเก็บข้อมูลละเอียดอ่อน สมาคมจะขอ <strong>ความยินยอมโดยชัดแจ้ง (Explicit Consent)</strong> จากท่านเป็นรายกรณีไป</p>

                    <!-- 9. การถอนความยินยอม -->
                    <h4 class="privacy-heading"><i class="bi bi-9-circle-fill me-2"></i>การถอนความยินยอม</h4>
                    <p>ท่านมีสิทธิถอนความยินยอมที่เคยให้ไว้กับสมาคมได้ตลอดเวลา โดย:</p>
                    <ul>
                        <li>ติดต่อเจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคลของสมาคม</li>
                        <li>ส่งอีเมลแจ้งขอถอนความยินยอมมาที่อีเมลด้านล่าง</li>
                        <li>จัดการการตั้งค่าคุกกี้ผ่าน Cookie Banner บนเว็บไซต์</li>
                    </ul>
                    <p class="text-muted small">การถอนความยินยอมจะไม่กระทบต่อความชอบด้วยกฎหมายของการประมวลผลข้อมูลที่ท่านได้ให้ความยินยอมไว้ก่อนหน้า</p>

                    <!-- 10. ระยะเวลาการเก็บรักษา -->
                    <h4 class="privacy-heading"><i class="bi bi-clock-fill me-2"></i>ระยะเวลาการเก็บรักษาข้อมูล</h4>
                    <p>สมาคมจะเก็บรักษาข้อมูลส่วนบุคคลของท่านตามระยะเวลาที่จำเป็น:</p>
                    <ul>
                        <li><strong>ข้อมูลสมาชิก:</strong> ตลอดระยะเวลาที่ท่านยังคงเป็นสมาชิก และอีก 5 ปีหลังจากสิ้นสุดสมาชิกภาพ</li>
                        <li><strong>ข้อมูลธุรกรรม/การเงิน:</strong> 7 ปี ตามกฎหมายภาษีอากร</li>
                        <li><strong>ข้อมูลการใช้งานเว็บไซต์:</strong> 1 ปี</li>
                        <li><strong>คุกกี้:</strong> ตามระยะเวลาที่กำหนดในนโยบายคุกกี้</li>
                    </ul>

                    <!-- 11. ติดต่อ -->
                    <h4 class="privacy-heading"><i class="bi bi-envelope-fill me-2"></i>ช่องทางการติดต่อ</h4>
                    <div class="privacy-info-box bg-light">
                        <h6 class="fw-bold">เจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล (DPO)</h6>
                        <p class="mb-1"><i class="bi bi-building me-2"></i>สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์ (ส.ร.ม.ก.)</p>
                        <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>จังหวัดกาฬสินธุ์</p>
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i>อีเมล: <a href="mailto:contact@sdak.org">contact@sdak.org</a></p>
                    </div>

                    <hr class="my-4">
                    <p class="text-muted small text-center mb-0">
                        นโยบายความเป็นส่วนตัวฉบับนี้มีผลบังคับใช้ตั้งแต่วันที่ประกาศ สมาคมอาจปรับปรุงนโยบายเป็นครั้งคราว <br>
                        การเปลี่ยนแปลงจะประกาศบนเว็บไซต์นี้ ท่านควรตรวจสอบนโยบายนี้อย่างสม่ำเสมอ
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
</section>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>
<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
