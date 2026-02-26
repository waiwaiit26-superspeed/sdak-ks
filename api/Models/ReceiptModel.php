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
            $data['receipt_number'] = $this->getNextNumber($bookNum);
        }

        // Convert amount to Thai text
        if (empty($data['amount_text'])) {
            $data['amount_text'] = self::amountToThaiText((float)$data['amount']);
        }

        return (int)$this->create($data);
    }

    /**
     * Get next receipt number for a given book_number
     */
    public function getNextNumber(string $bookNumber): int
    {
        $max = $this->db->max($this->table, 'receipt_number', [
            'book_number' => $bookNumber,
        ]);
        return ((int)$max) + 1;
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
            'receipts.receipt_type', 'receipts.title', 'receipts.payer_name',
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
