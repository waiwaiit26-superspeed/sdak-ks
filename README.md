# SDAK-KS Member Management

ระบบเว็บไซต์และระบบบริหารจัดการสมาชิกสำหรับสมาคม โดยรองรับหลายเว็บไซต์ (multi-site) และใช้ฐานข้อมูลแยกตามโดเมน

## ความสามารถหลัก

- เว็บไซต์สาธารณะ ข่าวสาร หน้าเนื้อหา และกิจกรรม
- สมัครสมาชิกด้วยแบบฟอร์มหรือ Google Sign-In
- จัดการสมาชิก เพิ่ม แก้ไข อนุมัติ ระงับ และนำเข้าข้อมูลจำนวนมาก
- รองรับประเภทสมาชิกที่ตั้งค่าจากระบบ
- คำนำหน้าชื่อทั้งรายการมาตรฐานและแบบกำหนดเอง
- จัดการค่าธรรมเนียม การชำระเงิน และใบเสร็จ
- จัดการผู้ดูแลระบบ เมนู หน้าเว็บไซต์ และสิทธิ์การเข้าถึง
- Webhook สำหรับงานอัตโนมัติ และ deploy ผ่าน FTP ด้วย lftp

## เทคโนโลยี

- PHP 7.4 ขึ้นไป
- MySQL / MariaDB
- Medoo 2.x
- PHPMailer 7.x
- Bootstrap, jQuery, DataTables และ Flatpickr ในส่วน frontend

ติดตั้ง dependency ด้วย:

```bash
composer install
```

## โครงสร้างโครงการ

| โฟลเดอร์/ไฟล์ | หน้าที่ |
|---|---|
| `index.php` | หน้าเว็บไซต์หลัก |
| `admin/` | จุดเข้าใช้งานระบบผู้ดูแล |
| `member/` | จุดเข้าใช้งานสมาชิก |
| `auth/` | Login และสมัครสมาชิก |
| `views/` | หน้า view แยกตามส่วนของระบบ |
| `templates/` | layout, header, navbar และ footer ที่ใช้ร่วมกัน |
| `api/Controllers/` | business logic และ API endpoints |
| `api/Models/` | การเข้าถึงข้อมูลในฐานข้อมูล |
| `api/Core/` | router, auth, response และคลาสพื้นฐาน |
| `assets/` | JavaScript, CSS และรูปภาพ |
| `config/sites/` | config แยกตามโดเมน |
| `database/` | SQL schema และไฟล์ SQL ประกอบ |
| `migrations/` | migration ที่รันตามลำดับ |
| `uploads/` | ไฟล์อัปโหลด แยกตามโดเมน |
| `deploy-lftp.sh` | deploy เฉพาะไฟล์ที่เปลี่ยนจาก git baseline |
| `backup-db.php` | สำรองฐานข้อมูล |
| `migrate.php` | รัน migration บน server |
| `webhook.php` | รับ GitHub webhook สำหรับ auto-deploy |

## การทำงานของคำนำหน้าชื่อ

ฟิลด์ `users.prefix` เก็บคำนำหน้าเป็นข้อความ จึงรองรับทั้งค่าในรายการ เช่น `นาย`, `นาง`, `ดร.` และค่าที่ผู้ใช้กำหนดเอง

กติกากลางอยู่ที่ `UserModel`:

- `UserModel::resolvePrefixFromInput()` แปลง `prefix=other` และ `prefix_other` เป็นค่าจริงก่อนบันทึก
- `UserModel::buildFullName()` ประกอบ `prefix + first_name + last_name`
- Controller ที่สร้างหรือแก้ไขสมาชิกต้องเรียก helper เหล่านี้ ไม่ควรประกอบชื่อเอง

เส้นทางที่รองรับคำนำหน้ากำหนดเอง:

- จัดการสมาชิกในแอดมิน
- สมัครสมาชิกปกติ
- Google registration setup
- แก้ไขโปรไฟล์สมาชิก
- ทำเนียบสมาชิก
- bulk import สมาชิก

เมื่อบันทึกแล้วค่าจะอยู่ใน `prefix` และ `full_name` จึงถูกนำไปแสดงต่อในตารางสมาชิก โปรไฟล์ กิจกรรม ค่าธรรมเนียม ใบเสร็จ และส่วนอื่นที่ใช้ข้อมูลสมาชิก

## Multi-site

ระบบเลือก config จาก host ที่เปิดเข้ามา โดยโหลดไฟล์:

```text
config/sites/{domain}.php
```

เว็บไซต์ที่ตั้งค่าไว้ในปัจจุบัน:

- `sdak.obec.in` ใช้ฐานข้อมูล `obecin_sdakks`
- `saak.obec.in` ใช้ฐานข้อมูล `obecin_saak`

การเพิ่มเว็บไซต์ใหม่:

1. สร้าง `config/sites/{domain}.php`
2. สร้างฐานข้อมูลและกำหนดค่าการเชื่อมต่อ
3. ชี้โดเมนไปยัง web root เดียวกัน
4. เพิ่ม URL migration ของไซต์นั้นใน `.deploy.env`
5. ทดสอบหน้าเว็บและการเชื่อมต่อฐานข้อมูลก่อน deploy จริง

ไฟล์ config ที่มีข้อมูลลับต้องไม่ commit ขึ้น repository

## ฐานข้อมูลและ migration

สร้าง schema ใหม่จากไฟล์ใน `database/` หรือ migration เริ่มต้น จากนั้นรัน migration ด้วย:

```bash
php migrate.php
```

Migration อยู่ใน `migrations/` และต้องตั้งชื่อเป็นเลขลำดับ เช่น `034_example.sql` เพื่อให้ระบบรันตามลำดับ

เมื่อเพิ่มหรือแก้ migration ให้ตรวจสอบ:

- migration รันซ้ำได้อย่างปลอดภัย หรือมีการบันทึกสถานะแล้ว
- ทุกฐานข้อมูลของทุกไซต์ต้องได้รับ migration เดียวกัน
- มี backup ก่อนเปลี่ยน schema production
- ทดสอบกับฐานข้อมูลสำรองก่อน

## การตั้งค่า local

1. ติดตั้ง PHP, MySQL/MariaDB และ Composer
2. ติดตั้ง dependency:

```bash
composer install
```

3. สร้างฐานข้อมูล local และนำเข้า schema
4. สร้างหรือแก้ config ใน `config/sites/` สำหรับ host local
5. ตรวจสอบค่า `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` และค่า OAuth/SMTP ตามต้องการ
6. เปิดโปรเจกต์ผ่าน web server ที่รองรับ PHP

ห้ามใส่ password, token, FTP credential หรือ secret จริงไว้ใน source control

## แนวทางการ deploy มาตรฐาน

โปรเจกต์นี้ใช้ `lftp` ผ่าน `deploy-lftp.sh` เป็นวิธีหลัก ส่วน `webhook.php` เป็นวิธีเสริมสำหรับ server ที่ตั้งค่า GitHub webhook ไว้แล้ว

### ติดตั้งครั้งแรก

สร้างไฟล์ตั้งค่าเฉพาะเครื่องจาก template:

```bash
cp .deploy.env.example .deploy.env
```

กำหนดค่าอย่างน้อย:

- `FTP_HOST`, `FTP_PORT`, `FTP_USER`, `FTP_PASS`
- `REMOTE_DIR`
- `SITE_URL`
- `DEPLOY_SECRET`
- `REMOTE_STATE_URL` (ถ้าใช้ตรวจสถานะ remote)
- `BACKUP_URL`
- `MIGRATE_URL`
- `MIGRATE_URL_2` สำหรับ `saak.obec.in`
- `DEPLOY_SECRET_2` ถ้าไซต์ที่สองใช้ secret ต่างกัน

กำหนด baseline ให้ตรงกับ commit ที่อยู่บน production:

```bash
git rev-parse HEAD > .deploy.git_hash
```

> อย่าสร้าง baseline ใหม่ทับของเดิมโดยไม่ตรวจว่า production อยู่ที่ commit ใด เพราะ script ใช้ baseline เพื่อคำนวณไฟล์ที่จะส่งขึ้น server

### Deploy ประจำวัน

1. ตรวจสอบและทดสอบการแก้ไขใน local
2. ตรวจสอบไฟล์ที่เปลี่ยน:

```bash
git status
git diff --check
```

3. commit การแก้ไข:

```bash
git add <files>
git commit -m "อธิบายการเปลี่ยนแปลง"
```

4. deploy:

```bash
./deploy-lftp.sh
```

5. ตรวจสอบเว็บไซต์ทุกโดเมนและฟังก์ชันที่เกี่ยวข้องหลัง deploy

ถ้า script ยังไม่มีสิทธิ์ execute ให้ใช้:

```bash
chmod +x deploy-lftp.sh
```

### พฤติกรรมของ `deploy-lftp.sh`

- ตรวจ `.deploy.env` และ `.deploy.git_hash`
- เปรียบเทียบ local HEAD กับ baseline
- อัปโหลดเฉพาะไฟล์ที่เปลี่ยนหรือเพิ่มระหว่าง baseline กับ HEAD
- ลบไฟล์บน server ที่ถูกลบจาก git
- ไม่อัปโหลด `.git`, `uploads/`, `webhook-secret.php` และไฟล์ deploy ที่ไม่ควรเผยแพร่
- ถ้ามีไฟล์ใน `migrations/` เปลี่ยน จะเรียก backup ก่อน แล้วรัน migration ของไซต์ที่ตั้งค่าไว้
- เมื่อสำเร็จจะบันทึก HEAD ใหม่ลง `.deploy.git_hash`

### กรณีมี migration

ก่อน deploy ต้องตรวจว่าใน `.deploy.env` มีอย่างน้อย:

```text
BACKUP_URL=...
MIGRATE_URL=...
MIGRATE_URL_2=...
```

ระบบจะทำงานตามลำดับ:

```text
upload files -> backup site 1 -> migrate site 1 -> migrate site 2 ...
```

หาก backup หรือ migration ล้มเหลว ให้หยุดตรวจสอบผลลัพธ์ก่อนดำเนินการต่อ อย่ารัน migration ซ้ำโดยไม่ตรวจตารางสถานะและผลจาก server

### เพิ่มไซต์ใน deploy

สคริปต์รองรับ `MIGRATE_URL_2` ถึง `MIGRATE_URL_10`:

```text
MIGRATE_URL_3=...
DEPLOY_SECRET_3=...
```

ต้องสร้าง `config/sites/{domain}.php` ให้เรียบร้อยก่อนเพิ่ม URL migration ของไซต์นั้น

## Webhook deploy

ใช้เฉพาะกรณีที่ server ตั้งค่า GitHub webhook และ secret ไว้แล้ว:

1. GitHub ส่ง push event ไปยัง `webhook.php`
2. ระบบตรวจ HMAC signature
3. server ดึงโค้ดด้วย git หรือ zip fallback
4. รัน `composer install`
5. backup database
6. รัน migration

ไฟล์ `webhook-secret.php` ต้องอยู่บน server และต้องไม่ commit ขึ้น GitHub

## การตรวจสอบก่อน release

```bash
php -l api/Models/UserModel.php
php -l api/Controllers/AuthController.php
php -l api/Controllers/MemberController.php
git diff --check
```

ควรทดสอบอย่างน้อย:

- เพิ่มสมาชิกด้วยคำนำหน้ามาตรฐาน
- เพิ่มสมาชิกด้วยคำนำหน้ากำหนดเอง
- แก้ไขสมาชิกเดิมและตรวจว่าค่ากำหนดเองยังอยู่
- แก้ไขผ่านหน้าโปรไฟล์และทำเนียบสมาชิก
- ตรวจชื่อในกิจกรรม ค่าธรรมเนียม และใบเสร็จ
- สมัครสมาชิกปกติและ Google registration
- ตรวจทั้ง `sdak.obec.in` และ `saak.obec.in`

## ความปลอดภัยและการดูแลระบบ

- ห้าม commit `.deploy.env`, `webhook-secret.php`, password และ token
- จำกัดสิทธิ์ endpoint backup, migration และ webhook ด้วย secret
- สำรองฐานข้อมูลก่อน migration production
- ไม่ deploy ไฟล์ทดสอบหรือข้อมูลส่วนตัวโดยไม่จำเป็น
- ตรวจ log หลัง deploy และตรวจสิทธิ์ไฟล์อัปโหลด
- ใช้ HTTPS สำหรับ production และตรวจค่า `TOKEN_SECRET` ให้เป็นค่าที่ปลอดภัย

## เอกสารอ้างอิง

- [DEPLOY_LFTP_RUNBOOK.md](DEPLOY_LFTP_RUNBOOK.md)
- [WEBHOOK_DEPLOY_GUIDE.md](WEBHOOK_DEPLOY_GUIDE.md)
- [MULTISITE_AUDIT_2026-07-14.md](MULTISITE_AUDIT_2026-07-14.md)
