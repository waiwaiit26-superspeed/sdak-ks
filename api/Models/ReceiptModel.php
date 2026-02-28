<?php
namespace App\Models;

use App\Core\Model;

/**
 * ReceiptModel — manages `receipts` table
 * ระบบออกใบเสร็จรับเงิน
 */
class ReceiptModel extends Model
{
    protected string $table = 'receipts';

    /**
     * Generate next receipt number and create receipt.
     * Book number = prefix setting + 2-digit Buddhist year from issued_date.
     * Receipt number resets to 1 each Buddhist year.
     */
    public function createReceipt(array $data): int
    {
        $settings = new SettingsModel();
        $prefix = trim($settings->get('receipt_book_number', SITE_NAME_SHORT));

        // Determine issued_date and derive Buddhist year
        $issuedDate = $data['issued_date'] ?? date('Y-m-d');
        $data['issued_date'] = $issuedDate;
        $bookNum = self::buildBookNumber($prefix, $issuedDate);
        $data['book_number'] = $bookNum;

        // Use custom receipt_number if provided, otherwise auto-generate
        if (empty($data['receipt_number'])) {
            $startNumber = (int)$settings->get('receipt_start_number', '1');
            $data['receipt_number'] = $this->getNextNumber($bookNum, $startNumber);
        }

        // Convert amount to Thai text
        if (empty($data['amount_text'])) {
            $data['amount_text'] = self::amountToThaiText((float)$data['amount']);
        }

        return (int)$this->create($data);
    }

    /**
     * Get next receipt number for a given book_number
     * Respects receipt_start_number setting: if no receipts exist yet,
     * use start number instead of 1
     */
    public function getNextNumber(string $bookNumber, int $startNumber = 1): int
    {
        $max = $this->db->max($this->table, 'receipt_number', [
            'book_number' => $bookNumber,
        ]);
        $maxInt = (int)$max;
        // If no receipts yet, use start number; otherwise max + 1
        // If max already exceeds start, just continue from max + 1
        if ($maxInt === 0) {
            return max($startNumber, 1);
        }
        return $maxInt + 1;
    }

    /**
     * Find duplicate receipt: same book_number + receipt_number, excluding a given id
     */
    public function findDuplicate(string $bookNumber, $receiptNumber, int $excludeId = 0): ?array
    {
        $where = [
            'book_number'    => $bookNumber,
            'receipt_number' => $receiptNumber,
        ];
        if ($excludeId > 0) {
            $where['id[!]'] = $excludeId;
        }
        $row = $this->db->get($this->table, ['id', 'payer_name', 'book_number', 'receipt_number'], $where);
        return $row ?: null;
    }

    /**
     * Build book_number string: prefix + " " + 2-digit Buddhist year
     * e.g. "SITE_NAME_SHORT" + "2026-02-14" → "SITE_NAME_SHORT 69" (พ.ศ.2569)
     */
    public static function buildBookNumber(string $prefix, string $date): string
    {
        $ceYear = (int)date('Y', strtotime($date));
        $buddhistYear2 = substr((string)($ceYear + 543), -2);
        return $prefix . ' ' . $buddhistYear2;
    }

    /**
     * Get receipt by id with user info
     */
    public function getDetail(int $id): ?array
    {
        return $this->getJoin(
            ['[>]users' => ['user_id' => 'id']],
            [
                'receipts.id', 'receipts.receipt_number', 'receipts.book_number',
                'receipts.user_id', 'receipts.receipt_type', 'receipts.reference_id',
                'receipts.title', 'receipts.payer_name', 'receipts.payer_address',
                'receipts.description', 'receipts.amount', 'receipts.amount_text',
                'receipts.received_by', 'receipts.issued_date',
                'receipts.created_at',
                'users.full_name', 'users.email', 'users.member_type',
            ],
            ['receipts.id' => $id]
        );
    }

    /**
     * Get all receipts for a user
     */
    public function getUserReceipts(int $userId): array
    {
        return $this->db->select($this->table, '*', [
            'user_id' => $userId,
            'ORDER' => ['created_at' => 'DESC']
        ]) ?: [];
    }

    /**
     * Find receipt by reference
     */
    public function findByReference(string $receiptType, int $referenceId): ?array
    {
        return $this->findBy([
            'receipt_type' => $receiptType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Load reference/source data for a receipt.
     * Returns enriched info about what this receipt was generated from.
     *
     * @return array|null  Reference data or null if not applicable
     */
    public function getReferenceData(string $receiptType, ?int $referenceId): ?array
    {
        if (!$referenceId || $referenceId <= 0) return null;

        if ($receiptType === 'membership_fee') {
            // Load from membership_fees + user
            $row = $this->db->get('membership_fees', [
                '[>]users' => ['user_id' => 'id'],
            ], [
                'membership_fees.id', 'membership_fees.user_id',
                'membership_fees.year', 'membership_fees.amount',
                'membership_fees.fee_type', 'membership_fees.status',
                'membership_fees.payment_slip', 'membership_fees.paid_at',
                'membership_fees.created_at',
                'users.full_name', 'users.email', 'users.phone',
                'users.member_type', 'users.school_organization',
                'users.work_address', 'users.home_address',
                'users.profile_image',
            ], [
                'membership_fees.id' => $referenceId,
            ]);
            if (!$row) return null;

            return [
                'source_type'  => 'membership_fee',
                'source_label' => 'ค่าธรรมเนียมสมาชิก',
                'source_id'    => (int)$row['id'],
                'user_id'      => (int)$row['user_id'],
                'full_name'    => $row['full_name'],
                'email'        => $row['email'],
                'phone'        => $row['phone'] ?? '',
                'member_type'  => $row['member_type'] ?? '',
                'school_organization' => $row['school_organization'] ?? '',
                'work_address' => $row['work_address'] ?? '',
                'home_address' => $row['home_address'] ?? '',
                'profile_image' => $row['profile_image'] ?? '',
                'fee_year'     => (int)$row['year'],
                'fee_type'     => $row['fee_type'] ?? 'annual',
                'fee_amount'   => (float)$row['amount'],
                'fee_status'   => $row['status'],
                'payment_slip' => $row['payment_slip'] ?? '',
                'paid_at'      => $row['paid_at'] ?? '',
                'created_at'   => $row['created_at'] ?? '',
            ];
        }

        if ($receiptType === 'activity_fee') {
            // Load from activity_registrations + user + activity
            $row = $this->db->get('activity_registrations', [
                '[>]users'      => ['user_id' => 'id'],
                '[>]activities' => ['activity_id' => 'id'],
            ], [
                'activity_registrations.id',
                'activity_registrations.activity_id',
                'activity_registrations.user_id',
                'activity_registrations.status',
                'activity_registrations.payment_status',
                'activity_registrations.payment_proof',
                'activity_registrations.note',
                'activity_registrations.registered_at',
                'users.full_name', 'users.email', 'users.phone',
                'users.member_type', 'users.school_organization',
                'users.work_address', 'users.home_address',
                'users.profile_image',
                'activities.title(activity_title)',
                'activities.fee_amount(activity_fee_amount)',
                'activities.fee_description(activity_fee_description)',
                'activities.start_date(activity_start_date)',
                'activities.end_date(activity_end_date)',
                'activities.location(activity_location)',
            ], [
                'activity_registrations.id' => $referenceId,
            ]);
            if (!$row) return null;

            return [
                'source_type'   => 'activity_fee',
                'source_label'  => 'ค่าลงทะเบียนกิจกรรม',
                'source_id'     => (int)$row['id'],
                'user_id'       => (int)$row['user_id'],
                'full_name'     => $row['full_name'],
                'email'         => $row['email'],
                'phone'         => $row['phone'] ?? '',
                'member_type'   => $row['member_type'] ?? '',
                'school_organization' => $row['school_organization'] ?? '',
                'work_address'  => $row['work_address'] ?? '',
                'home_address'  => $row['home_address'] ?? '',
                'profile_image' => $row['profile_image'] ?? '',
                'activity_id'   => (int)$row['activity_id'],
                'activity_title' => $row['activity_title'] ?? '',
                'activity_fee_amount' => (float)($row['activity_fee_amount'] ?? 0),
                'activity_fee_description' => $row['activity_fee_description'] ?? '',
                'activity_start_date' => $row['activity_start_date'] ?? '',
                'activity_end_date'   => $row['activity_end_date'] ?? '',
                'activity_location'   => $row['activity_location'] ?? '',
                'registration_status' => $row['status'],
                'payment_status'      => $row['payment_status'],
                'payment_proof'       => $row['payment_proof'] ?? '',
                'note'                => $row['note'] ?? '',
                'registered_at'       => $row['registered_at'] ?? '',
            ];
        }

        return null;
    }

    /**
     * Search for membership fee records that can be referenced by a receipt
     */
    public function searchMembershipFeeReferences(string $q = '', int $limit = 50): array
    {
        $where = ['membership_fees.status' => 'paid'];
        if ($q) {
            $where['OR'] = [
                'users.full_name[~]' => '%' . $q . '%',
                'users.email[~]' => '%' . $q . '%',
            ];
        }
        $where['ORDER'] = ['membership_fees.created_at' => 'DESC'];
        $where['LIMIT'] = $limit;

        $rows = $this->db->select('membership_fees', [
            '[>]users' => ['user_id' => 'id'],
        ], [
            'membership_fees.id',
            'membership_fees.user_id',
            'membership_fees.year',
            'membership_fees.amount',
            'membership_fees.fee_type',
            'membership_fees.status',
            'users.full_name',
            'users.email',
        ], $where) ?: [];

        foreach ($rows as &$r) {
            $existing = $this->findByReference('membership_fee', (int)$r['id']);
            $feeLabel = $r['fee_type'] === 'onetime' ? 'ครั้งเดียว' : 'ปี ' . $r['year'];
            $r['label'] = $r['full_name'] . ' — ค่าธรรมเนียม' . $feeLabel . ' (' . number_format($r['amount'], 2) . ' บาท)';
            $r['has_receipt'] = (bool)$existing;
            $r['reference_id'] = (int)$r['id'];
        }
        return $rows;
    }

    /**
     * Search for activity registration records that can be referenced by a receipt
     */
    public function searchActivityFeeReferences(string $q = '', int $limit = 50): array
    {
        $where = [
            'activity_registrations.payment_status' => 'paid',
            'activities.has_fee' => 1,
        ];
        if ($q) {
            $where['OR'] = [
                'users.full_name[~]' => '%' . $q . '%',
                'activities.title[~]' => '%' . $q . '%',
            ];
        }
        $where['ORDER'] = ['activity_registrations.registered_at' => 'DESC'];
        $where['LIMIT'] = $limit;

        $rows = $this->db->select('activity_registrations', [
            '[>]users'      => ['user_id' => 'id'],
            '[>]activities' => ['activity_id' => 'id'],
        ], [
            'activity_registrations.id',
            'activity_registrations.user_id',
            'activity_registrations.activity_id',
            'activity_registrations.payment_status',
            'users.full_name',
            'users.email',
            'activities.title(activity_title)',
            'activities.fee_amount',
        ], $where) ?: [];

        foreach ($rows as &$r) {
            $existing = $this->findByReference('activity_fee', (int)$r['id']);
            $r['label'] = $r['full_name'] . ' — ' . ($r['activity_title'] ?? 'กิจกรรม') . ' (' . number_format($r['fee_amount'] ?? 0, 2) . ' บาท)';
            $r['has_receipt'] = (bool)$existing;
            $r['reference_id'] = (int)$r['id'];
        }
        return $rows;
    }

    /**
     * Paginated list for admin
     */
    public function getFilteredList(array $filters, int $page, int $perPage): array
    {
        $join = ['[>]users' => ['user_id' => 'id']];
        $where = [];

        if (!empty($filters['receipt_type'])) {
            $where['receipts.receipt_type'] = $filters['receipt_type'];
        }
        if (!empty($filters['user_id'])) {
            $where['receipts.user_id'] = (int)$filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where['OR'] = [
                'receipts.title[~]' => $s,
                'receipts.payer_name[~]' => $s,
                'receipts.description[~]' => $s,
                'users.full_name[~]' => $s,
            ];
        }
        if (!empty($filters['date_from'])) {
            $where['receipts.issued_date[>=]'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where['receipts.issued_date[<=]'] = $filters['date_to'];
        }

        $where['ORDER'] = ['receipts.created_at' => 'DESC'];

        $countWhere = $where;
        unset($countWhere['ORDER']);
        $total = $this->countJoin($join, '*', $countWhere);

        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];
        $data = $this->selectJoin($join, [
            'receipts.id', 'receipts.receipt_number', 'receipts.book_number',
            'receipts.receipt_type', 'receipts.reference_id',
            'receipts.title', 'receipts.description', 'receipts.payer_name',
            'receipts.payer_address',
            'receipts.amount', 'receipts.issued_date', 'receipts.created_at',
            'users.full_name', 'users.member_type',
        ], $where);

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * Convert number to Thai baht text
     * e.g. 1000 => "หนึ่งพันบาทถ้วน"
     */
    public static function amountToThaiText(float $amount): string
    {
        $baht = (int)$amount;
        $satang = round(($amount - $baht) * 100);

        $text = self::numberToThai($baht) . 'บาท';
        if ($satang > 0) {
            $text .= self::numberToThai((int)$satang) . 'สตางค์';
        } else {
            $text .= 'ถ้วน';
        }
        return $text;
    }

    /**
     * Convert integer to Thai text
     */
    private static function numberToThai(int $number): string
    {
        if ($number === 0) return 'ศูนย์';

        $digits = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $positions = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

        $result = '';
        $numStr = (string)$number;
        $len = strlen($numStr);

        for ($i = 0; $i < $len; $i++) {
            $digit = (int)$numStr[$i];
            $pos = $len - $i - 1;

            if ($digit === 0) continue;

            // Handle millions recursion
            if ($pos >= 6) {
                $millions = (int)substr($numStr, 0, $len - 6);
                $result = self::numberToThai($millions) . 'ล้าน';
                $remainder = $number % 1000000;
                if ($remainder > 0) {
                    $result .= self::numberToThai($remainder);
                }
                return $result;
            }

            // Special cases
            if ($pos === 1 && $digit === 1) {
                $result .= 'สิบ';
            } elseif ($pos === 1 && $digit === 2) {
                $result .= 'ยี่สิบ';
            } elseif ($pos === 0 && $digit === 1 && $len > 1) {
                $result .= 'เอ็ด';
            } else {
                $result .= $digits[$digit] . $positions[$pos];
            }
        }

        return $result;
    }
}
