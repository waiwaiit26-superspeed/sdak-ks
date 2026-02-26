<?php
/**
 * PDPA Components — Cookie Consent Banner + Privacy Consent Modal
 * Include this in ALL template footers before </body>
 * Uses localStorage to track consent status
 * Variables: $basePath (default './')
 */
$basePath = $basePath ?? './';
?>

<!-- =====================================================
     PDPA Cookie Consent Banner
     แถบแจ้งเตือนคุกกี้ แสดงด้านล่างเมื่อผู้ใช้ยังไม่ยอมรับ
     ===================================================== -->
<div id="pdpa-cookie-banner" class="pdpa-cookie-banner" style="display:none;">
    <div class="container">
        <div class="pdpa-cookie-inner">
            <div class="pdpa-cookie-text">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-shield-lock-fill fs-4 me-2 text-warning"></i>
                    <strong>การใช้คุกกี้</strong>
                </div>
                <p class="mb-0 small">
                    เว็บไซต์นี้ใช้คุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานของท่าน 
                    คุกกี้ที่จำเป็นจะทำงานเสมอเพื่อให้เว็บไซต์ทำงานได้อย่างถูกต้อง 
                    ท่านสามารถเลือกยอมรับหรือปฏิเสธคุกกี้เพิ่มเติมได้ 
                    อ่านเพิ่มเติมใน <a href="<?php echo $basePath; ?>web/?page=privacy-policy" class="pdpa-link">นโยบายความเป็นส่วนตัว</a>
                </p>
            </div>
            <div class="pdpa-cookie-actions">
                <button type="button" id="pdpa-cookie-settings-btn" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-gear me-1"></i>ตั้งค่า
                </button>
                <button type="button" id="pdpa-cookie-reject-btn" class="btn btn-sm btn-outline-light">
                    ปฏิเสธทั้งหมด
                </button>
                <button type="button" id="pdpa-cookie-accept-btn" class="btn btn-sm btn-warning fw-bold">
                    <i class="bi bi-check-lg me-1"></i>ยอมรับทั้งหมด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cookie Settings Panel (sliding) -->
<div id="pdpa-cookie-settings" class="pdpa-cookie-settings" style="display:none;">
    <div class="container">
        <div class="pdpa-settings-inner">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-gear-fill me-2"></i>ตั้งค่าคุกกี้</h6>
                <button type="button" id="pdpa-settings-close-btn" class="btn btn-sm btn-outline-secondary">&times;</button>
            </div>
            <div class="pdpa-cookie-option">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>คุกกี้ที่จำเป็น</strong>
                        <p class="small text-muted mb-0">จำเป็นสำหรับการทำงานพื้นฐานของเว็บไซต์ เช่น การเข้าสู่ระบบ</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked disabled id="cookie-necessary">
                    </div>
                </div>
            </div>
            <div class="pdpa-cookie-option">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>คุกกี้เพื่อการวิเคราะห์</strong>
                        <p class="small text-muted mb-0">ช่วยให้เราเข้าใจการใช้งานเว็บไซต์เพื่อปรับปรุงประสิทธิภาพ</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="cookie-analytics">
                    </div>
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="button" id="pdpa-save-settings-btn" class="btn btn-sm btn-primary">
                    <i class="bi bi-check-lg me-1"></i>บันทึกการตั้งค่า
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     PDPA Privacy Consent Modal (First Visit)
     แสดงเมื่อผู้ใช้เข้าเว็บไซต์ครั้งแรก ต้องยอมรับก่อนใช้งาน
     ===================================================== -->
<div id="pdpa-consent-overlay" class="pdpa-consent-overlay" style="display:none;">
    <div class="pdpa-consent-modal">
        <div class="pdpa-consent-header">
            <div class="pdpa-consent-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h4 class="mb-1">แจ้งเตือนความเป็นส่วนตัว</h4>
            <p class="small mb-0 opacity-75">ตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA)</p>
        </div>
        <div class="pdpa-consent-body">
            <div class="pdpa-consent-scroll">
                <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>การเก็บรวบรวมข้อมูลส่วนบุคคล</h6>
                <p>เว็บไซต์<?php echo SITE_NAME; ?> (<?php echo SITE_NAME_SHORT; ?>) เก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคลของท่าน เพื่อวัตถุประสงค์ดังนี้:</p>
                <ul>
                    <li>การลงทะเบียนและบริหารจัดการสมาชิก</li>
                    <li>การจัดกิจกรรม อบรม สัมมนา</li>
                    <li>การจัดเก็บค่าธรรมเนียมและออกใบเสร็จ</li>
                    <li>การติดต่อสื่อสาร แจ้งข่าวสาร</li>
                    <li>การปรับปรุงและพัฒนาเว็บไซต์</li>
                </ul>

                <h6 class="fw-bold"><i class="bi bi-cookie me-2"></i>การใช้คุกกี้</h6>
                <p>เว็บไซต์นี้ใช้คุกกี้ที่จำเป็นสำหรับการทำงานพื้นฐาน และคุกกี้เพื่อการวิเคราะห์ (สามารถเลือกปิดได้ภายหลัง)</p>

                <h6 class="fw-bold"><i class="bi bi-person-check me-2"></i>สิทธิของท่าน</h6>
                <p>ท่านมีสิทธิในการเข้าถึง แก้ไข ลบ ระงับ โอนย้าย และคัดค้านการประมวลผลข้อมูลส่วนบุคคลของท่าน ตามที่กฎหมายกำหนด</p>

                <h6 class="fw-bold"><i class="bi bi-exclamation-octagon me-2"></i>ข้อมูลละเอียดอ่อน</h6>
                <p>สมาคมไม่เก็บรวบรวมข้อมูลละเอียดอ่อน (เช่น เชื้อชาติ ความคิดเห็นทางการเมือง ข้อมูลสุขภาพ) โดยไม่ได้รับความยินยอมโดยชัดแจ้งจากท่าน</p>

                <h6 class="fw-bold"><i class="bi bi-lock me-2"></i>ความปลอดภัย</h6>
                <p class="mb-0">สมาคมใช้มาตรการรักษาความปลอดภัยที่เหมาะสม รวมถึง SSL/TLS เพื่อปกป้องข้อมูลส่วนบุคคลของท่าน หากเกิดเหตุข้อมูลรั่วไหล สมาคมจะแจ้งให้ท่านทราบทันที</p>
            </div>
        </div>
        <div class="pdpa-consent-footer">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="pdpa-consent-checkbox">
                <label class="form-check-label small" for="pdpa-consent-checkbox">
                    ข้าพเจ้าได้อ่านและเข้าใจ <a href="<?php echo $basePath; ?>web/?page=privacy-policy" target="_blank" class="pdpa-link">นโยบายความเป็นส่วนตัว</a> 
                    และยินยอมให้สมาคมเก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคลของข้าพเจ้าตามวัตถุประสงค์ที่แจ้งไว้
                </label>
            </div>
            <button type="button" id="pdpa-consent-accept-btn" class="btn btn-primary w-100 fw-bold" disabled>
                <i class="bi bi-check-circle me-2"></i>ยอมรับและดำเนินการต่อ
            </button>
            <p class="text-muted text-center small mt-2 mb-0">
                ท่านสามารถถอนความยินยอมได้ตลอดเวลา โดยติดต่อ <a href="mailto:contact@sdak-ks.org" class="pdpa-link">contact@sdak-ks.org</a>
            </p>
        </div>
    </div>
</div>

<!-- PDPA JS -->
<script src="<?php echo $basePath; ?>assets/js/pdpa.js?v=<?php echo time(); ?>"></script>
