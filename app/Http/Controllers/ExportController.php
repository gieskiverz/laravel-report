<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Kategori;


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

    private function total($title, $data, $month)
    {
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

    private function net_income($title, $totalIncome, $totalExpense)
    {
        $nettIncome = [];
        foreach ($totalIncome as $incomeItem) {
            $month = $incomeItem['month'];
            $totalIncomeValue = (int) $incomeItem['total'];
            $totalExpenseValue = 0;

            // Find the matching Total Expense value for the same month
            foreach ($totalExpense as $expenseItem) {
                if ($expenseItem['month'] === $month) {
                    $totalExpenseValue = (int) $expenseItem['total'];
                    break;
                }
            }

            // Calculate Nett Income by subtracting Total Expense from Total Income
            $nettIncomeValue = $totalIncomeValue - $totalExpenseValue;

            // Create the Nett Income item for the month
            $nettIncomeItem = [
                'month' => $month,
                'total' => (string) $nettIncomeValue,
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

    public function edit($year)
    {
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
            ->where(DB::raw('YEAR(transaksis.tanggal)'), '=', $year)
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
            ->where(DB::raw('YEAR(transaksis.tanggal)'), '=', $year)
            ->groupBy('kategoris.nama', 'month')
            ->orderBy('jenis')
            ->orderBy('month', 'ASC')
            ->get();

        $credit = $this->transformData($q_credit, $month);
        $debit = $this->transformData($q_debit, $month);

        $total_income = $this->total('Total Income', $credit, $month);
        $total_expense = $this->total('Total Expense', $debit, $month);
        $net_income = $this->net_income('Net Income', $total_income['total_all'], $total_expense['total_all']);
        // die(json_encode($net_income, JSON_PRETTY_PRINT));
        // return view('exports.report',compact('credit','debit','total_income','total_expense','net_income','month','year'));

        return Excel::download(new LaporanExport($credit, $debit, $total_income, $total_expense, $net_income, $month, $year), 'report.xlsx');
    }

    public function report(Request $request)
    {
        $dateRange = $request->range_filter;
        // Split the date range into two dates
        $dates = explode(" - ", $dateRange);

        // Extract month and year from each date
        // $startDate = date('m/Y', strtotime($dates[0]));
        // $endDate = date('m/Y', strtotime($dates[0]));
        $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->format('Y-m-d');

        $startMonth = Carbon::createFromFormat('m/d/Y', $dates[0])->format('m');
        $endMonth = Carbon::createFromFormat('m/d/Y', $dates[1])->format('m');

        $period = CarbonPeriod::create($startDate, '1 month', $endDate);

        $month = [];
        foreach ($period as $date) {
            $bulan = ltrim($date->format('m'), '0');
            $month[] = $bulan;
        }

        $year = [];
        foreach ($period as $date) {
            $ym = $date->format('Y-m');
            $year[] = $ym;
        }

        $kategori_income = Kategori::where('jenis', 'income')->get();
        $kategori_expense = Kategori::where('jenis', 'expense')->get();

        $q_credit = DB::table('transaksis')
            ->join('coas', 'coas.id', '=', 'transaksis.coa_id')
            ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
            ->select(
                'kategoris.nama AS nama_kategori',
                DB::raw('DATE_FORMAT(transaksis.tanggal, "%m") AS month'),
                DB::raw('SUM(transaksis.credit) AS total')
            )
            ->where('transaksis.credit', '>', 0)
            ->whereBetween('transaksis.tanggal', [$startDate, $endDate])
            ->groupBy('kategoris.nama', 'month')
            ->orderBy('jenis')
            ->orderBy('month', 'ASC')
            ->get();
        // $q_credit = DB::table('transaksis')
        // ->join('coas', 'coas.id', '=', 'transaksis.coa_id')
        // ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
        // ->select(
        //     'kategoris.nama AS nama_kategori',
        //     DB::raw('DATE_FORMAT(transaksis.tanggal, "%m") AS month'),
        //     DB::raw('SUM(transaksis.credit) AS total')
        // )
        // ->where('transaksis.credit', '>', 0)
        // ->where( DB::raw('YEAR(transaksis.tanggal)'), '=', $request )
        // ->groupBy('kategoris.nama', 'month')
        // ->orderBy('jenis')
        // ->orderBy('month', 'ASC')
        // ->get();

        $q_debit = DB::table('transaksis')
            ->join('coas', 'coas.id', '=', 'transaksis.coa_id')
            ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
            ->select(
                'kategoris.nama AS nama_kategori',
                DB::raw('DATE_FORMAT(transaksis.tanggal, "%m") AS month'),
                DB::raw('SUM(transaksis.debit) AS total')
            )
            ->where('transaksis.debit', '>', 0)
            ->whereBetween('transaksis.tanggal', [$startDate, $endDate])
            ->groupBy('kategoris.nama', 'month')
            ->orderBy('jenis')
            ->orderBy('month', 'ASC')
            ->get();

        $credit = $this->transformData($q_credit, $month);
        $debit = $this->transformData($q_debit, $month);

        $total_income = $this->total('Total Income', $credit, $month);
        $total_expense = $this->total('Total Expense', $debit, $month);
        $net_income = $this->net_income('Net Income', $total_income['total_all'], $total_expense['total_all']);

        $result_income = $this->getDataByFilter($kategori_income,$credit,$startMonth,$endMonth);
        $result_expense = $this->getDataByFilter($kategori_expense,$debit,$startMonth,$endMonth);
        // die(json_encode($result_income, JSON_PRETTY_PRINT));
        if ($request->aksi=='view') {
            return view('exports.report',compact('result_income','result_expense','total_income','total_expense','net_income','month','year','dateRange'));
        } else {
            // dd(count($month));
            return Excel::download(new LaporanExport($result_income, $result_expense, $total_income, $total_expense, $net_income, $month, $year), 'report.xlsx');
        }

    }

    private function getDataByFilter($kategori,$data,$startMonth,$endMonth){
        $result = [];

        foreach ($kategori as $category) {
            $found = false;
        
            // Search for matching category in reports
            if (!empty($data)) {
                foreach ($data as $report) {
                    if ($category->nama === $report["nama_kategori"]) {
                        $result[] = $report;
                        $found = true;
                        break;
                    }
                }
            }
        
            // If no matching category found or $reports is empty, add a default report with "total" as 0
            if (!$found || empty($data)) {
                $report = [
                    "nama_kategori" => $category->nama,
                    "report" => []
                ];
        
                // Determine the months dynamically
                // $startMonth = 6; // Start month value
                // $endMonth = 7; // End month value
        
                for ($month = $startMonth; $month <= $endMonth; $month++) {
                    $report["report"][] = [
                        "month" => str_pad($month, 2, "0", STR_PAD_LEFT),
                        "total" => "0"
                    ];
                }
        
                $result[] = $report;
            }
        }
        return $result;
    }
}