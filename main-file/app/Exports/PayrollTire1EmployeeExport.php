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

class PayrollTire1EmployeeExport implements FromArray, WithHeadings, WithStyles, WithCustomStartCell, WithColumnWidths, WithEvents
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
            $amount=(13.5 / 100) * $type->basic_salary;
            $formattedData[] = [
                'NAME OF EMPLOYEE'   => strtoupper($type->name),
                'SSF. NO.'           => $type->social_security_number,
                'BASIC SALARY'       => \Auth::user()->priceFormatExcel($type->basic_salary),
                'AMOUNT'             => \Auth::user()->priceFormatExcel($amount)
            ];
        }

        $this->data         = $formattedData;
        $this->companyName  = $companyName;
        $this->month = $month;
        $this->address = $address;
    }
    public function startCell(): string
    {
        return 'A3';
    }
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 15,
            'D' => 15,
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
        return  ["NAME OF EMPLOYEE", "SSF. NO.", "BASIC SALARY", "AMOUNT"];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:C1'); // Merge A to D for COMPANY NAME
                $event->sheet->getDelegate()->mergeCells('A2:C2'); // Merge A to D for COMPANY NAME
    
                $event->sheet->getStyle('A1:A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A2:A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $event->sheet->getCell('A1')->setValue('SSNIT Tier 1-(13.5%) Contribution Returns');
                $event->sheet->getCell('A2')->setValue('For the Month of: ');
                $event->sheet->getStyle('A1:A2')->getFont()->setBold(true);
                $event->sheet->getCell('D2')->setValue($this->month);
                $event->sheet->getRowDimension('1')->setRowHeight(24);
                $event->sheet->getRowDimension('2')->setRowHeight(24);
                $event->sheet->getStyle('A2')->getFont()->setSize(14);
                $startRow = 2;
                $endRow = 3;
                $event->sheet->getStyle('A3:D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
                $startRow = 4;
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A4:C' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $event->sheet->getStyle('C4:C' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $event->sheet->getStyle('D4:D' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    
                $headingsRange = 'A3:D3'; // Assuming the headings are in row 4
                $event->sheet->getStyle($headingsRange)->getFont()->setBold(true);
                $borderStyle = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
    
                for ($col = 'A'; $col <= 'D'; $col++) {
                    $event->sheet->getStyle($col . '3')->getBorders()->getRight()->setBorderStyle($borderStyle);
                    $event->sheet->getStyle($col . '3')->getBorders()->getTop()->setBorderStyle($borderStyle);
                    $event->sheet->getStyle($col . '3')->getBorders()->getLeft()->setBorderStyle($borderStyle);
                    $event->sheet->getStyle($col . '3')->getBorders()->getBottom()->setBorderStyle($borderStyle);
                }
    
                $event->sheet->getRowDimension('3')->setRowHeight(30);
                $data = $this->data;
            },
        ];
    }
    

}
