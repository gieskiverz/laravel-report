<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class ExportController extends Controller
{
    private function transformData($data, $month)
    {
        $result = [];

        // Group the data by 'nama_kategori'
        $groupedData = collect($data)->groupBy('nama_kategori');

        foreach ($groupedData as $nama_kategori => $items) {
            // Create an array for each 'nama_kategori'
            $kategoriData = [
                'nama_kategori' => $nama_kategori,
                'report' => []
            ];

            // Loop through each month
            foreach ($month as $m) {
                $monthData = [
                    'month' => str_pad($m, 2, '0', STR_PAD_LEFT),
                    'total' => '0',
                ];

                // Find the matching item for the current month
                $matchedItem = $items->first(function ($item) use ($m) {
                    return $item->month === str_pad($m, 2, '0', STR_PAD_LEFT);
                });

                // If a match is found, update the credit and debit values
                if ($matchedItem) {
                    $monthData['total'] = $matchedItem->total;
                }

                // Add the month data to the 'report' array
                $kategoriData['report'][] = $monthData;
            }

            // Add the 'nama_kategori' data to the result array
            $result[] = $kategoriData;
        }

        return $result;
    }

    private function total($title,$data,$month){
        $totalIncome = [
            'nama' => $title,
            'total_all' => []
        ];
        
        // Iterate over each month
        foreach ($month as $m) {
            $total = 0;
        
            // Sum up the "total" values for each category in the given month
            foreach ($data as $category) {
                foreach ($category['report'] as $report) {
                    if ($report['month'] == $m) {
                        $total += intval($report['total']);
                        break;
                    }
                }
            }
        
            // Add the monthly total to the total income report
            $totalIncome['total_all'][] = [
                'month' => $m,
                'total' => strval($total)
            ];
        }
        
        return $totalIncome;
    }

    private function net_income($title,$totalIncome,$totalExpense){
        $nettIncome = [];
        foreach ($totalIncome as $incomeItem) {
            $month = $incomeItem['month'];
            $totalIncomeValue = (int)$incomeItem['total'];
            $totalExpenseValue = 0;

            // Find the matching Total Expense value for the same month
            foreach ($totalExpense as $expenseItem) {
                if ($expenseItem['month'] === $month) {
                    $totalExpenseValue = (int)$expenseItem['total'];
                    break;
                }
            }

            // Calculate Nett Income by subtracting Total Expense from Total Income
            $nettIncomeValue = $totalIncomeValue - $totalExpenseValue;

            // Create the Nett Income item for the month
            $nettIncomeItem = [
                'month' => $month,
                'total' => (string)$nettIncomeValue,
            ];

            // Add the Nett Income item to the result
            $nettIncome[] = $nettIncomeItem;
        }

        $nettIncomeResult = [
            'nama' => $title,
            'total_all' => $nettIncome,
        ];
        return $nettIncomeResult;
    }

    public function index(){
        echo "goblog";
    }

    public function edit($year){
        // $year = Carbon::now()->year;
        // $year = $request->input('year');
        $month = range(1, 12); // generate bulan

        $q_credit = DB::table('transaksis')
        ->join('coas', 'coas.id', '=', 'transaksis.coa_id')
        ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
        ->select(
            'kategoris.nama AS nama_kategori',
            DB::raw('DATE_FORMAT(transaksis.tanggal, "%m") AS month'),
            DB::raw('SUM(transaksis.credit) AS total')
        )
        ->where('transaksis.credit', '>', 0)
        ->where( DB::raw('YEAR(transaksis.tanggal)'), '=', $year )
        ->groupBy('kategoris.nama', 'month')
        ->orderBy('jenis')
        ->orderBy('month', 'ASC')
        ->get();

        $q_debit = DB::table('transaksis')
        ->join('coas', 'coas.id', '=', 'transaksis.coa_id')
        ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
        ->select(
            'kategoris.nama AS nama_kategori',
            DB::raw('DATE_FORMAT(transaksis.tanggal, "%m") AS month'),
            DB::raw('SUM(transaksis.debit) AS total')
        )
        ->where('transaksis.debit', '>', 0)
        ->where( DB::raw('YEAR(transaksis.tanggal)'), '=', $year )
        ->groupBy('kategoris.nama', 'month')
        ->orderBy('jenis')
        ->orderBy('month', 'ASC')
        ->get();

        $credit = $this->transformData($q_credit,$month);
        $debit = $this->transformData($q_debit,$month);

        $total_income = $this->total('Total Income',$credit,$month);
        $total_expense = $this->total('Total Expense',$debit,$month);
        $net_income = $this->net_income('Net Income',$total_income['total_all'],$total_expense['total_all']);
        // die(json_encode($net_income, JSON_PRETTY_PRINT));
        // return view('exports.report',compact('credit','debit','total_income','total_expense','net_income','month','year'));

        return Excel::download(new LaporanExport($credit, $debit, $total_income, $total_expense, $net_income, $month, $year), 'report.xlsx');
    }
    
    public function report($year){
        $month = range(1, 12); // generate bulan

        $q_credit = DB::table('transaksis')
        ->join('coas', 'coas.id', '=', 'transaksis.coa_id')
        ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
        ->select(
            'kategoris.nama AS nama_kategori',
            DB::raw('DATE_FORMAT(transaksis.tanggal, "%m") AS month'),
            DB::raw('SUM(transaksis.credit) AS total')
        )
        ->where('transaksis.credit', '>', 0)
        ->where( DB::raw('YEAR(transaksis.tanggal)'), '=', $year )
        ->groupBy('kategoris.nama', 'month')
        ->orderBy('jenis')
        ->orderBy('month', 'ASC')
        ->get();

        $q_debit = DB::table('transaksis')
        ->join('coas', 'coas.id', '=', 'transaksis.coa_id')
        ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
        ->select(
            'kategoris.nama AS nama_kategori',
            DB::raw('DATE_FORMAT(transaksis.tanggal, "%m") AS month'),
            DB::raw('SUM(transaksis.debit) AS total')
        )
        ->where('transaksis.debit', '>', 0)
        ->where( DB::raw('YEAR(transaksis.tanggal)'), '=', $year )
        ->groupBy('kategoris.nama', 'month')
        ->orderBy('jenis')
        ->orderBy('month', 'ASC')
        ->get();

        $credit = $this->transformData($q_credit,$month);
        $debit = $this->transformData($q_debit,$month);

        $total_income = $this->total('Total Income',$credit,$month);
        $total_expense = $this->total('Total Expense',$debit,$month);
        $net_income = $this->net_income('Net Income',$total_income['total_all'],$total_expense['total_all']);
        // die(json_encode($net_income, JSON_PRETTY_PRINT));
        // return view('exports.report',compact('credit','debit','total_income','total_expense','net_income','month','year'));

        return Excel::download(new LaporanExport($credit, $debit, $total_income, $total_expense, $net_income, $month, $year), 'report.xlsx');
    }
}