<?php

namespace App\Controllers;

class AdminReportController extends BaseController
{
    private function checkAdmin()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses hanya untuk admin.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        return view('admin/reports/index', $this->reportData());
    }

    public function exportExcel()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $data = $this->reportData();
        $filename = 'laporan-penjualan-' . $data['periodSlug'] . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody(view('admin/reports/excel', $data));
    }

    public function exportPdf()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $data = $this->reportData();
        $filename = 'laporan-penjualan-' . $data['periodSlug'] . '.pdf';
        $pdf = $this->buildPdf($data);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    private function reportData(): array
    {
        $type = $this->request->getGet('type') === 'month' ? 'month' : 'day';
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $month = $this->request->getGet('month') ?: date('Y-m');

        if ($type === 'month') {
            $startDate = date('Y-m-01', strtotime($month . '-01'));
            $endDate = date('Y-m-t', strtotime($month . '-01'));
            $periodLabel = date('F Y', strtotime($startDate));
            $periodSlug = date('Y-m', strtotime($startDate));
        } else {
            $startDate = date('Y-m-d', strtotime($date));
            $endDate = $startDate;
            $periodLabel = date('d F Y', strtotime($startDate));
            $periodSlug = $startDate;
        }

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $db = \Config\Database::connect();

        $transactions = $db->table('transactions')
            ->select("
                transactions.id,
                transactions.invoice_number,
                transactions.customer_name,
                transactions.payment_method,
                transactions.payment_status,
                transactions.order_status,
                transactions.total_amount,
                transactions.created_at,
                COALESCE(SUM(transaction_details.quantity), 0) as total_items,
                GROUP_CONCAT(CONCAT(transaction_details.product_name, ' ', transaction_details.variant_size, ' x', transaction_details.quantity) SEPARATOR ', ') as product_summary
            ", false)
            ->join('transaction_details', 'transaction_details.transaction_id = transactions.id', 'left')
            ->where('transactions.payment_status', 'paid')
            ->where('transactions.created_at >=', $startDateTime)
            ->where('transactions.created_at <=', $endDateTime)
            ->groupBy('transactions.id')
            ->orderBy('transactions.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $productRows = $db->table('transaction_details')
            ->select('transaction_details.product_name, transaction_details.variant_size, SUM(transaction_details.quantity) as total_qty, SUM(transaction_details.subtotal) as total_sales', false)
            ->join('transactions', 'transactions.id = transaction_details.transaction_id')
            ->where('transactions.payment_status', 'paid')
            ->where('transactions.created_at >=', $startDateTime)
            ->where('transactions.created_at <=', $endDateTime)
            ->groupBy('transaction_details.product_name, transaction_details.variant_size')
            ->orderBy('total_sales', 'DESC')
            ->get()
            ->getResultArray();

        $totalRevenue = 0;
        $totalItems = 0;

        foreach ($transactions as $transaction) {
            $totalRevenue += (float) $transaction['total_amount'];
            $totalItems += (int) $transaction['total_items'];
        }

        $totalTransactions = count($transactions);

        return [
            'type'              => $type,
            'date'              => $startDate,
            'month'             => date('Y-m', strtotime($startDate)),
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'periodLabel'       => $periodLabel,
            'periodSlug'        => $periodSlug,
            'transactions'      => $transactions,
            'productRows'       => $productRows,
            'totalRevenue'      => $totalRevenue,
            'totalItems'        => $totalItems,
            'totalTransactions' => $totalTransactions,
            'averageRevenue'    => $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0,
        ];
    }

    private function buildPdf(array $data): string
    {
        // ── Page setup (A4 Landscape: 842 x 595 pt) ───────────────────────────
        $pageW   = 595;
        $pageH   = 842;
        $margin  = 32;
        $usableW = $pageW - $margin * 2;

        // ── Kolom tabel transaksi ─────────────────────────────────────────────
        // No | Tanggal | Invoice | Customer | Produk | Item | Total
        $colsTx = [
            'x'     => [$margin, $margin+22, $margin+74, $margin+154, $margin+254, $margin+424, $margin+456],
            'w'     => [22,       52,         80,         100,          170,          32,           75],
            'label' => ['No','Tanggal','Invoice','Customer','Produk','Item','Total'],
            'align' => ['C','C','L','L','L','C','R'],
        ];

        // ── Kolom tabel produk ────────────────────────────────────────────────
        $colsPr = [
            'x'     => [$margin, $margin+230, $margin+330, $margin+390],
            'w'     => [230,       100,         60,           140],
            'label' => ['Produk','Varian','Qty','Total Penjualan'],
            'align' => ['L','L','C','R'],
        ];

        // ── Helper: filled rect ───────────────────────────────────────────────
        $rectFill = static function (float $x, float $y, float $w, float $h, array $rgb): string {
            return sprintf(
                "%.2f %.2f %.2f rg\n%.2f %.2f %.2f %.2f re f\n",
                $rgb[0], $rgb[1], $rgb[2],
                $x, $y, $w, $h
            );
        };

        // ── Helper: horizontal line ───────────────────────────────────────────
        $hLine = static function (float $x1, float $y, float $x2, array $rgb = [0.75, 0.75, 0.75], float $lw = 0.3): string {
            return sprintf(
                "%.2f w %.2f %.2f %.2f RG %.2f %.2f m %.2f %.2f l S\n",
                $lw, $rgb[0], $rgb[1], $rgb[2],
                $x1, $y, $x2, $y
            );
        };

        // ── Helper: vertical line ─────────────────────────────────────────────
        $vLine = static function (float $x, float $y1, float $y2, array $rgb = [0.75, 0.75, 0.75], float $lw = 0.3): string {
            return sprintf(
                "%.2f w %.2f %.2f %.2f RG %.2f %.2f m %.2f %.2f l S\n",
                $lw, $rgb[0], $rgb[1], $rgb[2],
                $x, $y1, $x, $y2
            );
        };

        // ── Helper: place text in cell ────────────────────────────────────────
        $cellText = function (string $text, float $cellX, float $cellW, float $baseline, string $align, int $fontSize = 8, bool $bold = false): string {
            $font = $bold ? '/F2' : '/F1';
            $safe = $this->pdfText($text);
            $pad  = 3;
            // approximate char width at given font size
            $textW = strlen($text) * $fontSize * 0.52;

            switch ($align) {
                case 'R':
                    $tx = $cellX + $cellW - $pad - $textW;
                    if ($tx < $cellX + $pad) $tx = $cellX + $pad;
                    break;
                case 'C':
                    $tx = $cellX + ($cellW - $textW) / 2;
                    if ($tx < $cellX + $pad) $tx = $cellX + $pad;
                    break;
                default:
                    $tx = $cellX + $pad;
            }

            return sprintf(
                "%s %d Tf\n1 0 0 1 %.2f %.2f Tm (%s) Tj\n",
                $font, $fontSize, $tx, $baseline, $safe
            );
        };

        $shortText = static function (?string $text, int $maxLength): string {
            $text = trim((string) $text);

            if (strlen($text) <= $maxLength) {
                return $text;
            }

            return substr($text, 0, $maxLength - 3) . '...';
        };

        // ── Helper: draw a full table row ─────────────────────────────────────
        // rowY = logical top-down Y; PDF uses bottom-left origin so we flip via (pageH - y)
        $drawRow = function (
            array $cols,
            array $values,
            float $rowY,
            float $rowH,
            bool  $isHeader,
            bool  $isAlt,
            array $aligns,
            int   $fontSize = 8
        ) use ($pageH, $margin, $usableW, $rectFill, $hLine, $vLine, $cellText): string {
            $out    = '';
            $pdfTop = $pageH - $rowY - $rowH;   // bottom edge of row in PDF coords

            // background
            if ($isHeader) {
                $out .= $rectFill($margin, $pdfTop, $usableW, $rowH, [0.18, 0.38, 0.62]);
            } elseif ($isAlt) {
                $out .= $rectFill($margin, $pdfTop, $usableW, $rowH, [0.93, 0.95, 0.98]);
            } else {
                $out .= $rectFill($margin, $pdfTop, $usableW, $rowH, [1.0, 1.0, 1.0]);
            }

            // bottom border
            $out .= $hLine($margin, $pdfTop, $margin + $usableW, [0.75, 0.75, 0.75]);

            // text
            $baseline = $pdfTop + ($rowH - $fontSize) / 2 + 1;
            $out .= "BT\n";
            foreach ($values as $i => $val) {
                $out .= $isHeader ? "1 1 1 rg\n" : "0.1 0.1 0.1 rg\n";
                $out .= $cellText((string) $val, $cols['x'][$i], $cols['w'][$i], $baseline, $aligns[$i], $fontSize, $isHeader);
            }
            $out .= "ET\n";

            // vertical separators between columns
            for ($i = 1; $i < count($cols['x']); $i++) {
                $out .= $vLine($cols['x'][$i], $pdfTop, $pdfTop + $rowH, [0.75, 0.75, 0.75]);
            }

            return $out;
        };

        // ── State: pages & cursor ─────────────────────────────────────────────
        $pages   = [];
        $curPage = '';
        $curY    = $margin;   // logical top-down Y, starts at top margin

        $newPage = function () use (&$curPage, &$curY, &$pages, $margin): void {
            if ($curPage !== '') {
                $pages[] = $curPage;
            }
            $curPage = '';
            $curY    = $margin;
        };

        $ensureSpace = function (float $needed) use (&$curY, $pageH, $margin, $newPage): void {
            if ($curY + $needed > $pageH - $margin) {
                $newPage();
            }
        };

        // ── Page 1: Title & summary ───────────────────────────────────────────
        $newPage();

        // Title bar
        $titleH  = 22;
        $curPage .= $rectFill($margin, $pageH - $curY - $titleH, $usableW, $titleH, [0.12, 0.28, 0.52]);
        $curPage .= "BT\n/F2 13 Tf\n1 1 1 rg\n";
        $curPage .= sprintf("1 0 0 1 %.2f %.2f Tm (LAPORAN PENJUALAN MALINI PARFUM) Tj\n",
            $margin + 6, $pageH - $curY - $titleH + 6);
        $curPage .= "ET\n";
        $curY    += $titleH + 3;

        // Subtitle: periode
        $subH    = 14;
        $curPage .= $rectFill($margin, $pageH - $curY - $subH, $usableW, $subH, [0.85, 0.90, 0.96]);
        $curPage .= "BT\n/F1 8 Tf\n0.1 0.1 0.1 rg\n";
        $curPage .= sprintf("1 0 0 1 %.2f %.2f Tm (Periode: %s) Tj\n",
            $margin + 4, $pageH - $curY - $subH + 4, $this->pdfText($data['periodLabel']));
        $curPage .= "ET\n";
        $curY    += $subH + 4;

        // Summary cards (4 boxes side by side)
        $cardW      = ($usableW - 9) / 4;
        $cardH      = 32;
        $cards      = [
            ['label' => 'Total Transaksi',    'value' => (string) $data['totalTransactions']],
            ['label' => 'Produk Terjual',      'value' => (string) $data['totalItems']],
            ['label' => 'Total Penjualan',     'value' => 'Rp ' . number_format($data['totalRevenue'], 0, ',', '.')],
            ['label' => 'Rata-rata/Transaksi', 'value' => 'Rp ' . number_format($data['averageRevenue'], 0, ',', '.')],
        ];
        $cardColors = [
            [0.18, 0.48, 0.72],
            [0.12, 0.58, 0.42],
            [0.62, 0.32, 0.12],
            [0.45, 0.18, 0.58],
        ];

        foreach ($cards as $ci => $card) {
            $cx      = $margin + $ci * ($cardW + 3);
            $pdfCardY = $pageH - $curY - $cardH;
            $curPage .= $rectFill($cx, $pdfCardY, $cardW, $cardH, $cardColors[$ci]);
            $curPage .= "BT\n";
            // label (small, near top of card)
            $curPage .= sprintf("/F1 7 Tf\n1 1 1 rg\n1 0 0 1 %.2f %.2f Tm (%s) Tj\n",
                $cx + 4, $pdfCardY + $cardH - 12, $this->pdfText($card['label']));
            // value (bold, near bottom of card)
            $curPage .= sprintf("/F2 9 Tf\n1 1 1 rg\n1 0 0 1 %.2f %.2f Tm (%s) Tj\n",
                $cx + 4, $pdfCardY + 4, $this->pdfText($card['value']));
            $curPage .= "ET\n";
        }
        $curY += $cardH + 10;

        // ── Section: Detail Transaksi ──────────────────────────────────────────
        $secH    = 14;
        $rowH    = 14;
        $hdrRowH = 16;

        $curPage .= $rectFill($margin, $pageH - $curY - $secH, $usableW, $secH, [0.22, 0.44, 0.68]);
        $curPage .= "BT\n/F2 9 Tf\n1 1 1 rg\n";
        $curPage .= sprintf("1 0 0 1 %.2f %.2f Tm (DETAIL TRANSAKSI) Tj\n",
            $margin + 4, $pageH - $curY - $secH + 3);
        $curPage .= "ET\n";
        $curY    += $secH;

        // Table header
        $curPage .= $drawRow($colsTx, $colsTx['label'], $curY, $hdrRowH, true, false, $colsTx['align'], 8);
        $curY    += $hdrRowH;

        // Outer top border of table
        $curPage .= sprintf("0.5 w 0.40 0.40 0.40 RG %.2f %.2f m %.2f %.2f l S\n",
            $margin, $pageH - $curY + $hdrRowH, $margin + $usableW, $pageH - $curY + $hdrRowH);

        if (empty($data['transactions'])) {
            $curPage .= $drawRow($colsTx, ['', 'Tidak ada transaksi paid pada periode ini.', '', '', '', '', ''],
                $curY, $rowH, false, false, array_fill(0, 7, 'L'), 8);
            $curY    += $rowH;
        } else {
            foreach ($data['transactions'] as $idx => $tx) {
                $ensureSpace($rowH + 2);
                $row = [
                    $idx + 1,
                    date('d/m/Y', strtotime($tx['created_at'])),
                    $shortText($tx['invoice_number'], 14),
                    $shortText($tx['customer_name'], 18),
                    $shortText($tx['product_summary'] ?? '-', 34),
                    $tx['total_items'],
                    'Rp ' . number_format($tx['total_amount'], 0, ',', '.'),
                ];
                $curPage .= $drawRow($colsTx, $row, $curY, $rowH, false, $idx % 2 === 1, $colsTx['align'], 7);
                $curY    += $rowH;
            }
        }

        $curY += 10;

        // ── Section: Rekap Produk ──────────────────────────────────────────────
        $ensureSpace($secH + $hdrRowH + $rowH * 3);

        $curPage .= $rectFill($margin, $pageH - $curY - $secH, $usableW, $secH, [0.22, 0.44, 0.68]);
        $curPage .= "BT\n/F2 9 Tf\n1 1 1 rg\n";
        $curPage .= sprintf("1 0 0 1 %.2f %.2f Tm (REKAP PRODUK TERJUAL) Tj\n",
            $margin + 4, $pageH - $curY - $secH + 3);
        $curPage .= "ET\n";
        $curY    += $secH;

        $curPage .= $drawRow($colsPr, $colsPr['label'], $curY, $hdrRowH, true, false, $colsPr['align'], 8);
        $curY    += $hdrRowH;

        if (empty($data['productRows'])) {
            $curPage .= $drawRow($colsPr, ['Belum ada produk terjual pada periode ini.', '', '', ''],
                $curY, $rowH, false, false, array_fill(0, 4, 'L'), 8);
            $curY    += $rowH;
        } else {
            foreach ($data['productRows'] as $idx => $row) {
                $ensureSpace($rowH + 2);
                $cells = [
                    $shortText($row['product_name'], 34),
                    $shortText($row['variant_size'], 18),
                    $row['total_qty'],
                    'Rp ' . number_format($row['total_sales'], 0, ',', '.'),
                ];
                $curPage .= $drawRow($colsPr, $cells, $curY, $rowH, false, $idx % 2 === 1, $colsPr['align'], 7);
                $curY    += $rowH;
            }
        }

        // flush last page
        $pages[] = $curPage;

        // ── Assemble PDF objects ───────────────────────────────────────────────
        $objects           = [];
        $pageObjectNumbers = [];

        // obj 1: Catalog
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        // obj 2: Pages (filled in after we know all page object numbers)
        $objects[] = '';

        foreach ($pages as $pageContent) {
            $pageObjNum     = count($objects) + 1;
            $streamObjNum   = count($objects) + 2;
            $fontRegObjNum  = count($objects) + 3;
            $fontBoldObjNum = count($objects) + 4;

            $pageObjectNumbers[] = $pageObjNum;

            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                . '/Resources << /Font << /F1 ' . $fontRegObjNum . ' 0 R /F2 ' . $fontBoldObjNum . ' 0 R >> >> '
                . '/Contents ' . $streamObjNum . ' 0 R >>';
            $objects[] = "<< /Length " . strlen($pageContent) . " >>\nstream\n" . $pageContent . "endstream";
            $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
            $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        }

        // Fill in obj 2
        $objects[1] = '<< /Type /Pages /Kids ['
            . implode(' ', array_map(static fn ($n) => $n . ' 0 R', $pageObjectNumbers))
            . '] /Count ' . count($pageObjectNumbers) . ' >>';

        // ── Write PDF bytes ────────────────────────────────────────────────────
        $pdf     = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf       .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf        .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf        .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function pdfText(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
