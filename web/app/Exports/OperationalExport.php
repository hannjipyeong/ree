<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class OperationalExport
{
    const COLOR_HEADER1  = '1E3A8A';
    const COLOR_HEADER2  = '2563EB';
    const COLOR_HEADER3  = '3B82F6';
    const COLOR_SECTION  = 'EFF6FF';
    const COLOR_SECTION_TEXT = '1E3A8A';
    const COLOR_ALT_ROW  = 'F8FAFC';
    const COLOR_WHITE    = 'FFFFFF';
    const COLOR_BORDER   = '94A3B8';
    const COLOR_DONE_BG  = 'DCFCE7';
    const COLOR_DONE_FG  = '15803D';
    const COLOR_PENDING_BG = 'FEF2F2';
    const COLOR_PENDING_FG = 'B91C1C';

    private Spreadsheet $spreadsheet;
    private $sheet;
    private int $row = 1;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('Laporan Operasional');
        // Set default font
        $this->spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);
    }

    public function build(Collection $orders, ?string $payloadFilter = null): void
    {
        $containerOrders = $orders->filter(fn($o) => $o->containers->isNotEmpty());
        $cargoOrders     = $orders->filter(fn($o) => $o->containers->isEmpty());

        $showContainer = !$payloadFilter || strtolower($payloadFilter) === 'container';
        $showCargo     = !$payloadFilter || strtolower($payloadFilter) === 'cargo';

        if ($showContainer && $containerOrders->isNotEmpty()) {
            $this->writeContainerSection($containerOrders);
        }

        if ($showCargo && $cargoOrders->isNotEmpty()) {
            if ($this->row > 1) $this->row += 1;
            $this->writeCargoSection($cargoOrders);
        }

        // Auto-size columns A-Q
        foreach (range('A', 'Q') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function writeContainerSection(Collection $orders): void
    {
        // Section title
        $this->sheet->mergeCells("A{$this->row}:Q{$this->row}");
        $this->sheet->setCellValue("A{$this->row}", '1. REKAPITULASI LAYANAN KONTAINER (HAULAGE, LOLO, PENUMPUKAN, TKBM)');
        $this->applyStyle("A{$this->row}:Q{$this->row}", [
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF' . self::COLOR_SECTION_TEXT]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::COLOR_SECTION]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            'borders'   => ['left' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF' . self::COLOR_HEADER1]]],
        ]);
        $this->sheet->getRowDimension($this->row)->setRowHeight(20);
        $this->row++;

        $r1 = $this->row; $r2 = $r1 + 1; $r3 = $r1 + 2;

        // Merge static columns A-E spanning 3 header rows, and N-Q
        foreach (['A','B','C','D','E'] as $col) $this->sheet->mergeCells("{$col}{$r1}:{$col}{$r3}");
        $this->sheet->mergeCells("N{$r1}:N{$r3}");
        $this->sheet->mergeCells("O{$r1}:O{$r3}");
        $this->sheet->mergeCells("P{$r1}:P{$r3}");
        $this->sheet->mergeCells("Q{$r1}:Q{$r3}");

        $this->sheet->setCellValue("A{$r1}", 'No');
        $this->sheet->setCellValue("B{$r1}", 'No Order');
        $this->sheet->setCellValue("C{$r1}", 'Nama PT');
        $this->sheet->setCellValue("D{$r1}", 'No Container');
        $this->sheet->setCellValue("E{$r1}", 'Ukuran / Tipe');
        $this->sheet->setCellValue("N{$r1}", 'Catatan');
        $this->sheet->setCellValue("O{$r1}", 'Status Asuransi');
        $this->sheet->setCellValue("P{$r1}", 'Status Invoice');
        $this->sheet->setCellValue("Q{$r1}", 'Status PNBP');

        // Row 1: "Layanan & Progress" spanning F-M
        $this->sheet->mergeCells("F{$r1}:M{$r1}");
        $this->sheet->setCellValue("F{$r1}", 'Layanan & Progress Waktu Lapangan');

        // Row 2: service groups
        foreach(['F','G','H','I','J','K','L','M'] as $i=>$col) {
            $groups = ['F'=>'Railing','H'=>'LOLO','J'=>'Storage','L'=>'TKBM'];
            if(isset($groups[$col])) {
                $endCol = chr(ord($col)+1);
                $this->sheet->mergeCells("{$col}{$r2}:{$endCol}{$r2}");
                $this->sheet->setCellValue("{$col}{$r2}", $groups[$col]);
            }
        }

        // Row 3: IN/OUT labels
        foreach(['F'=>'IN','G'=>'OUT','H'=>'IN','I'=>'OUT','J'=>'IN','K'=>'OUT','L'=>'IN','M'=>'OUT'] as $col=>$lbl) {
            $this->sheet->setCellValue("{$col}{$r3}", $lbl);
        }

        $this->applyStyle("A{$r1}:Q{$r1}", $this->headerStyle(self::COLOR_HEADER1));
        $this->applyStyle("A{$r2}:Q{$r2}", $this->headerStyle(self::COLOR_HEADER2));
        $this->applyStyle("A{$r3}:Q{$r3}", $this->headerStyle(self::COLOR_HEADER3));
        foreach([$r1,$r2,$r3] as $hr) $this->sheet->getRowDimension($hr)->setRowHeight(16);

        $this->row = $r3 + 1;
        $rowNo = 1;

        foreach ($orders as $ord) {
            $startRow = $this->row;
            $containerCount = $ord->containers->count();

            foreach ($ord->containers as $c) {
                $pH = $c->progresses->first(fn($p)=>$p->subTask&&strcasecmp($p->subTask->service_type,'Railing')===0);
                $pL = $c->progresses->first(fn($p)=>$p->subTask&&strcasecmp($p->subTask->service_type,'LOLO')===0);
                $pP = $c->progresses->first(fn($p)=>$p->subTask&&strcasecmp($p->subTask->service_type,'Storage')===0);
                $pT = $c->progresses->first(fn($p)=>$p->subTask&&strcasecmp($p->subTask->service_type,'TKBM')===0);

                $tkbmOpt = $c->tkbm_option ?: ($ord->tkbm_option ?? '');
                $tkbmBadge = str_contains(strtolower($tkbmOpt), 'forklift') ? ' (MP+Forklift)' : (strtolower($tkbmOpt) == 'man power' ? ' (MP)' : '');

                $notes     = $c->progresses->pluck('in_note')->merge($c->progresses->pluck('out_note'))->merge($c->progresses->pluck('done_note'))->filter()->unique()->implode('; ');
                
                $isAsuransi = (bool)$ord->has_asuransi;
                $asuransiText = ($isAsuransi ? '✓ Aktif' : '✗ Tidak') . ($isAsuransi && $ord->asuransi_value ? "\n(Rp " . number_format($ord->asuransi_value, 0, ',', '.') . ')' : '');

                $isInv     = $c->progresses->contains('is_invoiced', true);
                $invNum    = $c->progresses->where('is_invoiced',true)->pluck('invoice_number')->filter()->unique()->implode(', ');
                $invText   = ($isInv ? '✓ Terbit' : '✗ Belum') . ($invNum ? "\n{$invNum}" : '');

                $isKoperasi = strcasecmp($ord->source, 'Koperasi') === 0;
                $isPnbp    = (bool)$c->is_pnbp;
                $pnbpText  = $isKoperasi ? '-' : (($isPnbp ? '✓ Selesai' : '✗ Belum') . ($c->pnbp_number ? "\n{$c->pnbp_number}" : ''));

                $this->sheet->setCellValue("D{$this->row}", $c->container_number ?: 'Tanpa No');
                $this->sheet->setCellValue("E{$this->row}", $c->container_size . ' (' . $c->container_type . ')');
                $this->sheet->setCellValue("F{$this->row}", $this->ft($pH?->in_time));
                $this->sheet->setCellValue("G{$this->row}", $this->ft($pH?->out_time));
                $this->sheet->setCellValue("H{$this->row}", $this->ft($pL?->in_time));
                $this->sheet->setCellValue("I{$this->row}", $this->ft($pL?->out_time));
                $this->sheet->setCellValue("J{$this->row}", $this->ft($pP?->in_time));
                $this->sheet->setCellValue("K{$this->row}", $this->ft($pP?->out_time));
                $this->sheet->setCellValue("L{$this->row}", $this->ft($pT?->in_time) . ($pT?->in_time && $tkbmBadge ? "\n{$tkbmBadge}" : ''));
                $this->sheet->setCellValue("M{$this->row}", $this->ft($pT?->out_time) . ($pT?->out_time && $tkbmBadge ? "\n{$tkbmBadge}" : ''));
                $this->sheet->setCellValue("N{$this->row}", $notes ?: '-');
                $this->sheet->setCellValue("O{$this->row}", $asuransiText);
                $this->sheet->setCellValue("P{$this->row}", $invText);
                $this->sheet->setCellValue("Q{$this->row}", $pnbpText);

                $rowBg = ($rowNo % 2 === 0) ? self::COLOR_ALT_ROW : self::COLOR_WHITE;
                $this->applyStyle("A{$this->row}:Q{$this->row}", ['fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.$rowBg]],'borders'=>$this->thinBorder(),'alignment'=>['vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true]]);
                $this->applyStyle("A{$this->row}", ['alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]]);
                $this->applyStyle("O{$this->row}", ['font'=>['bold'=>true,'color'=>['argb'=>'FF'.($isAsuransi?self::COLOR_DONE_FG:self::COLOR_PENDING_FG)]],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.($isAsuransi?self::COLOR_DONE_BG:self::COLOR_PENDING_BG)]]]);
                $this->applyStyle("P{$this->row}", ['font'=>['bold'=>true,'color'=>['argb'=>'FF'.($isInv?self::COLOR_DONE_FG:self::COLOR_PENDING_FG)]],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.($isInv?self::COLOR_DONE_BG:self::COLOR_PENDING_BG)]]]);
                if (!$isKoperasi) {
                    $this->applyStyle("Q{$this->row}", ['font'=>['bold'=>true,'color'=>['argb'=>'FF'.($isPnbp?self::COLOR_DONE_FG:self::COLOR_PENDING_FG)]],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.($isPnbp?self::COLOR_DONE_BG:self::COLOR_PENDING_BG)]]]);
                }
                $this->sheet->getRowDimension($this->row)->setRowHeight(-1);
                $this->row++;
            }

            // Merge A/B/C for multi-container
            if ($containerCount > 1) {
                $endRow = $this->row - 1;
                foreach(['A','B','C'] as $col) $this->sheet->mergeCells("{$col}{$startRow}:{$col}{$endRow}");
            }
            $this->sheet->setCellValue("A{$startRow}", $rowNo);
            $this->sheet->setCellValue("B{$startRow}", $ord->order_number);
            $this->sheet->setCellValue("C{$startRow}", $ord->nama_pt);
            $this->applyStyle("A{$startRow}", ['font'=>['bold'=>true],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER]]);
            $this->applyStyle("B{$startRow}", ['font'=>['bold'=>true],'alignment'=>['vertical'=>Alignment::VERTICAL_CENTER]]);
            $this->applyStyle("C{$startRow}", ['alignment'=>['vertical'=>Alignment::VERTICAL_CENTER]]);
            $rowNo++;
        }
    }

    private function writeCargoSection(Collection $orders): void
    {
        // Section title
        $this->sheet->mergeCells("A{$this->row}:Q{$this->row}");
        $this->sheet->setCellValue("A{$this->row}", '2. REKAPITULASI LAYANAN CARGO (MUATAN BEBAS / GENERAL CARGO)');
        $this->applyStyle("A{$this->row}:Q{$this->row}", [
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF' . self::COLOR_SECTION_TEXT]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::COLOR_SECTION]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            'borders'   => ['left' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF' . self::COLOR_HEADER1]]],
        ]);
        $this->sheet->getRowDimension($this->row)->setRowHeight(20);
        $this->row++;

        $r1 = $this->row; $r2 = $r1 + 1; $r3 = $r1 + 2;

        $staticCols = ['A','B','C','D','E','F','G','H','I','J','K','N','O','P','Q'];
        foreach($staticCols as $col) $this->sheet->mergeCells("{$col}{$r1}:{$col}{$r3}");

        $labels = ['A'=>'No','B'=>'No Order','C'=>'Nama PT','D'=>'Jenis Barang','E'=>'Jml Barang','F'=>'Tonase','G'=>'No BL','H'=>'Vessel','I'=>'Voyage','J'=>'No Surat Jalan','K'=>'No BP','N'=>'Catatan','O'=>'Status Asuransi','P'=>'Status Invoice','Q'=>'Status PNBP'];
        foreach($labels as $col=>$lbl) $this->sheet->setCellValue("{$col}{$r1}", $lbl);

        $this->sheet->mergeCells("L{$r1}:M{$r1}");
        $this->sheet->setCellValue("L{$r1}", 'Progress Waktu Lapangan');
        $this->sheet->setCellValue("L{$r2}", 'Waktu IN');
        $this->sheet->setCellValue("M{$r2}", 'Waktu OUT / Selesai');
        $this->sheet->setCellValue("L{$r3}", 'Tgl & Jam IN');
        $this->sheet->setCellValue("M{$r3}", 'Tgl & Jam OUT');

        $this->applyStyle("A{$r1}:Q{$r1}", $this->headerStyle(self::COLOR_HEADER1));
        $this->applyStyle("A{$r2}:Q{$r2}", $this->headerStyle(self::COLOR_HEADER2));
        $this->applyStyle("A{$r3}:Q{$r3}", $this->headerStyle(self::COLOR_HEADER3));
        foreach([$r1,$r2,$r3] as $hr) $this->sheet->getRowDimension($hr)->setRowHeight(16);

        $this->row = $r3 + 1;
        $rowNo = 1;

        foreach ($orders as $ord) {
            $inTimes   = $ord->subTasks->pluck('in_time')->filter();
            $outTimes  = $ord->subTasks->pluck('out_time')->filter();
            $doneTimes = $ord->subTasks->pluck('done_time')->filter();
            $earliestIn = $inTimes->isNotEmpty() ? \Carbon\Carbon::parse($inTimes->min())->format('d/m/Y H:i') : '-';
            $latestOut  = $outTimes->isNotEmpty() ? \Carbon\Carbon::parse($outTimes->max())->format('d/m/Y H:i') : ($doneTimes->isNotEmpty() ? \Carbon\Carbon::parse($doneTimes->max())->format('d/m/Y H:i') : '-');
            $notesCargo = $ord->subTasks->pluck('in_note')->merge($ord->subTasks->pluck('out_note'))->merge($ord->subTasks->pluck('done_note'))->filter()->unique()->implode('; ');
            
            $isAsuransi = (bool)$ord->has_asuransi;
            $asuransiText = ($isAsuransi ? '✓ Aktif' : '✗ Tidak') . ($isAsuransi && $ord->asuransi_value ? "\n(Rp " . number_format($ord->asuransi_value, 0, ',', '.') . ')' : '');

            $isInv     = (bool)$ord->is_invoiced;
            $invText   = ($isInv ? '✓ Terbit' : '✗ Belum') . ($ord->invoice_number ? "\n{$ord->invoice_number}" : '');
            
            $isKoperasi = strcasecmp($ord->source, 'Koperasi') === 0;
            $isPnbp    = (bool)$ord->is_pnbp;
            $pnbpText  = $isKoperasi ? '-' : (($isPnbp ? '✓ Selesai' : '✗ Belum') . ($ord->pnbp_number ? "\n{$ord->pnbp_number}" : ''));

            $this->sheet->setCellValue("A{$this->row}", $rowNo);
            $this->sheet->setCellValue("B{$this->row}", $ord->order_number);
            $this->sheet->setCellValue("C{$this->row}", $ord->nama_pt);
            $this->sheet->setCellValue("D{$this->row}", $ord->jenis_barang ?: 'General Cargo');
            $this->sheet->setCellValue("E{$this->row}", $ord->jumlah_barang ?: '-');
            $this->sheet->setCellValue("F{$this->row}", $ord->jumlah_tonase ? $ord->jumlah_tonase . ' T' : '-');
            $this->sheet->setCellValue("G{$this->row}", $ord->nomor_bl ?: '-');
            $this->sheet->setCellValue("H{$this->row}", $ord->vessel ?: '-');
            $this->sheet->setCellValue("I{$this->row}", $ord->voyage ?: '-');
            $this->sheet->setCellValue("J{$this->row}", $ord->no_surat_jalan ?: '-');
            $this->sheet->setCellValue("K{$this->row}", $ord->no_bp ?: '-');
            $this->sheet->setCellValue("L{$this->row}", $earliestIn);
            $this->sheet->setCellValue("M{$this->row}", $latestOut);
            $this->sheet->setCellValue("N{$this->row}", $notesCargo ?: ($ord->pnbp_note ?: '-'));
            $this->sheet->setCellValue("O{$this->row}", $asuransiText);
            $this->sheet->setCellValue("P{$this->row}", $invText);
            $this->sheet->setCellValue("Q{$this->row}", $pnbpText);

            $rowBg = ($rowNo % 2 === 0) ? self::COLOR_ALT_ROW : self::COLOR_WHITE;
            $this->applyStyle("A{$this->row}:Q{$this->row}", ['fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.$rowBg]],'borders'=>$this->thinBorder(),'alignment'=>['vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true]]);
            $this->applyStyle("A{$this->row}", ['font'=>['bold'=>true],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]]);
            $this->applyStyle("B{$this->row}", ['font'=>['bold'=>true]]);
            $this->applyStyle("O{$this->row}", ['font'=>['bold'=>true,'color'=>['argb'=>'FF'.($isAsuransi?self::COLOR_DONE_FG:self::COLOR_PENDING_FG)]],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.($isAsuransi?self::COLOR_DONE_BG:self::COLOR_PENDING_BG)]]]);
            $this->applyStyle("P{$this->row}", ['font'=>['bold'=>true,'color'=>['argb'=>'FF'.($isInv?self::COLOR_DONE_FG:self::COLOR_PENDING_FG)]],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.($isInv?self::COLOR_DONE_BG:self::COLOR_PENDING_BG)]]]);
            if (!$isKoperasi) {
                $this->applyStyle("Q{$this->row}", ['font'=>['bold'=>true,'color'=>['argb'=>'FF'.($isPnbp?self::COLOR_DONE_FG:self::COLOR_PENDING_FG)]],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.($isPnbp?self::COLOR_DONE_BG:self::COLOR_PENDING_BG)]]]);
            }
            $this->sheet->getRowDimension($this->row)->setRowHeight(-1);
            $this->row++;
            $rowNo++;
        }
    }

    public function stream(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $writer = new Xlsx($this->spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function ft(?string $time): string
    {
        return $time ? \Carbon\Carbon::parse($time)->format('d/m/Y H:i') : '-';
    }

    private function applyStyle(string $range, array $style): void
    {
        $this->sheet->getStyle($range)->applyFromArray($style);
    }

    private function headerStyle(string $color): array
    {
        return [
            'font'      => ['bold'=>true,'color'=>['argb'=>'FF'.self::COLOR_WHITE],'size'=>9],
            'fill'      => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['argb'=>'FF'.$color]],
            'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            'borders'   => $this->thinBorder(),
        ];
    }

    private function thinBorder(): array
    {
        return ['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['argb'=>'FF'.self::COLOR_BORDER]]];
    }
}
