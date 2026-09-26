<?php
/**
 * Earnings Service - FundiPro
 * Income and expense tracking for professionals in KES
 */
class EarningsService {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function getSummary($professionalId, $year = null) {
        $year = $year ?: date('Y');

        $sql = "SELECT
                    DATE_FORMAT(p.created_at, '%Y-%m') AS month,
                    COUNT(p.id)                         AS job_count,
                    SUM(p.amount)                       AS gross_income
                FROM payments p
                JOIN bookings b ON p.job_id = b.job_id
                WHERE b.professional_id = ?
                AND p.status = 'completed'
                AND YEAR(p.created_at) = ?
                GROUP BY month
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId, $year]);
        $monthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = array_sum(array_column($monthly, 'gross_income'));

        return [
            'year'        => $year,
            'total'       => 'KES ' . number_format($total, 2),
            'monthly'     => array_map(fn($m) => [
                'month'      => $m['month'],
                'job_count'  => $m['job_count'],
                'income'     => 'KES ' . number_format($m['gross_income'], 2),
            ], $monthly),
        ];
    }

    public function logExpense($professionalId, $data) {
        $sql = "INSERT INTO expenses
                (professional_id, amount, category, description, expense_date)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $professionalId,
            $data['amount'],
            $data['category'], // 'materials', 'transport', 'tools', 'other'
            $data['description'] ?? null,
            $data['expense_date'] ?? date('Y-m-d'),
        ]);
    }

    public function getProfitLoss($professionalId, $month) {
        $income = $this->db->prepare(
            "SELECT COALESCE(SUM(p.amount),0) FROM payments p
             JOIN bookings b ON p.job_id = b.job_id
             WHERE b.professional_id = ? AND p.status = 'completed'
             AND DATE_FORMAT(p.created_at,'%Y-%m') = ?"
        );
        $income->execute([$professionalId, $month]);
        $gross = (float)$income->fetchColumn();

        $expenses = $this->db->prepare(
            "SELECT COALESCE(SUM(amount),0) FROM expenses
             WHERE professional_id = ? AND DATE_FORMAT(expense_date,'%Y-%m') = ?"
        );
        $expenses->execute([$professionalId, $month]);
        $totalExpenses = (float)$expenses->fetchColumn();

        return [
            'month'    => $month,
            'income'   => 'KES ' . number_format($gross, 2),
            'expenses' => 'KES ' . number_format($totalExpenses, 2),
            'profit'   => 'KES ' . number_format($gross - $totalExpenses, 2),
        ];
    }
}
