<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanExport implements FromView, WithEvents
{
    protected $result_income;
    protected $result_expense;
    protected $total_income;
    protected $total_expense;
    protected $net_income;
    protected $month;
    protected $year;
    
    protected $m_color_income;
    protected $a_color_outcome;
    protected $m_color_outcome;
    protected $color_total_income;
    protected $color_total_outcome;
    public function __construct($result_income, $result_expense, $total_income, $total_expense, $net_income, $month, $year)
    {
        $this->result_income = $result_income;
        $this->result_expense = $result_expense;
        $this->total_income = $total_income;
        $this->total_expense = $total_expense;
        $this->net_income = $net_income;
        $this->month = $month;
        $this->year = $year;

        $this->m_color_income = count($this->result_expense)+3;
        $this->a_color_outcome = $this->m_color_income+3;
        $this->a_color_outcome = $this->m_color_income+2;
        $this->m_color_outcome = $this->m_color_income+count($this->result_income);

        $this->color_total_income = $this->m_color_income+1;
        $this->color_total_outcome = $this->m_color_outcome+1;
    }

    public function view(): View
    {
        return view('exports.download', [
            'result_income' => $this->result_income,
            'result_expense' => $this->result_expense,
            'total_income' => $this->total_income,
            'total_expense' => $this->total_expense,
            'net_income' => $this->net_income,
            'month' => $this->month,
            'year' => $this->year,
        ]);
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {  
                $header = $event->sheet->getDelegate()->getStyle('A1:'.strtoupper(chr(ord('a') + count($this->month))).'2')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');

                $income = $event->sheet->getDelegate()->getStyle('A3:'.strtoupper(chr(ord('a') + count($this->month))).$this->m_color_income)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('C6E0B4');
                        
                $outcome = $event->sheet->getDelegate()->getStyle('A'.$this->a_color_outcome.':'.strtoupper(chr(ord('a') + count($this->month))).$this->m_color_outcome)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('F8CBAD');
                
                $total_income = $event->sheet->getDelegate()->getStyle('A'.$this->color_total_income.':'.strtoupper(chr(ord('a') + count($this->month))).$this->color_total_income)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('A9D08E');
                
                $total_outcome = $event->sheet->getDelegate()->getStyle('A'.$this->color_total_outcome.':'.strtoupper(chr(ord('a') + count($this->month))).$this->color_total_outcome)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('F4B084');
  
            },
        ];
    }
}
