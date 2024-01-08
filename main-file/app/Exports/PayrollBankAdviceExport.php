<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\PaySlip;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PayrollBankAdviceExport implements FromArray, WithHeadings, WithStyles, WithCustomStartCell, WithColumnWidths, WithEvents
{
    protected $month;
    protected $branch;
    protected $department;

    public function __construct($data, $companyName, $month, $address)
    {
        $formattedData = [];
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($data as $key => $type) {
            $formattedData[] = [
                'STAFF ID'           => $type->employee_id,
                'NAME OF EMPLOYEE'   => strtoupper($type->name),
                'BANK & BRANCH'      => strtoupper($type->bank_name . ' - ' . $type->branch_location),
                'BANK A/C NO.'       => $type->account_number,
                'Net Salary'         => \Auth::user()->priceFormatExcel($type->net_payble)
            ];
        }

        $this->data         = $formattedData;
        $this->companyName  = $companyName;
        $this->month = $month;
        $this->address = $address;
    }
    public function startCell(): string
    {
        return 'A6';
    }
    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 30,
            'C' => 20,
            'D' => 15,
            'E' => 15,
        ];
    }
    public function styles(Worksheet $sheet)
    {
    }
    public function array(): array
    {
        return $this->data;
    }
    public function headings(): array
    {
        return  ["STAFF ID", "NAME OF EMPLOYEE", "BANK & BRANCH", "BANK A/C NO.", "Net Salary"];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->mergeCells('A2:E2'); // Merge A to E for COMPANY NAME
                $event->sheet->getDelegate()->mergeCells('A3:E3'); // Merge A to E for ADDRESS
                $event->sheet->getDelegate()->mergeCells('A4:E4'); // Merge A to E for PAYROLL PERIOD

                $event->sheet->getDelegate()->setCellValue('A2', 'COMPANY NAME : ' . $this->companyName)->getStyle('A2')->getFont();
                $event->sheet->getDelegate()->setCellValue('A3', 'ADDRESS : ' .  $this->address);
                $event->sheet->getDelegate()->setCellValue('A4', 'PAYROLL PERIOD : ' . $this->month);

                $startRow = 2;
                $endRow = 6;
                $event->sheet->getStyle('A' . $startRow . ':E' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Set horizontal alignment to HORIZONTAL_LEFT for rows after 9
                $startRow = 7;
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A' . $startRow . ':D' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $event->sheet->getStyle('E7:E' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $headingsRange = 'A6:E6'; // Assuming the headings are in row 6
                $event->sheet->getStyle($headingsRange)->getFont()->setBold(true);
                $borderStyle = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

                for ($col = 'A'; $col < 'F'; $col++) {
                    $event->sheet->getStyle($col . '6')->getBorders()->getRight()->setBorderStyle($borderStyle);
                    $event->sheet->getStyle($col . '6')->getBorders()->getTop()->setBorderStyle($borderStyle);
                    $event->sheet->getStyle($col . '6')->getBorders()->getLeft()->setBorderStyle($borderStyle);
                }

                $data = $this->data;
            },
        ];
    }

}
