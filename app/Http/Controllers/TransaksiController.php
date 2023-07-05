<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Kategori;
use App\Models\Transaksi;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;

use Yajra\DataTables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(StoreTransaksiRequest $request)
    {
        $year = Carbon::now()->year;
        $coa = Coa::all();
        // $kategori = Kategori::all();

        $q_kategori = DB::table('coas')
        ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
        ->select('kategoris.nama AS nama_kategori', 'coas.id as id', 'coas.nama AS nama_coa', 'coas.kode AS kode')
        ->get();
        
        $kategori = collect($q_kategori)->groupBy('nama_kategori');

        // dd($groupedData);
        if ($request->ajax()) {
            $data =  Transaksi::all();
            $count = 0;
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('iteration', function () use (&$count) {
                $count++;
                return $count;
            })
            ->addColumn('kategori', function($row){
                $kategori = DB::table('coas')
                    ->join('kategoris', 'kategoris.id', '=', 'coas.kategori_id')
                    ->where('coas.id', $row->coa_id)
                    ->select('kategoris.nama')
                    ->get();          
                return $kategori[0]->nama;
            })
            ->addColumn('coa_kode', function($row){
                return Coa::findOrFail($row->coa_id)->kode;
            })
            ->addColumn('coa_nama', function($row){
                return Coa::findOrFail($row->coa_id)->nama;
            })
            ->editColumn('debit', function($row){
                return currencyFormat($row->debit,'Rp.');
            })
            ->editColumn('credit', function($row){
                return currencyFormat($row->credit,'Rp.');
            })
            ->editColumn('tanggal', function($row){
                return Carbon::parse($row->tanggal)->format('d-M-Y');
            })
            ->editColumn('created_at', function($row){
                return Carbon::parse($row->created_at)->format('d-M-Y H:i:s');
            })
            ->editColumn('updated_at', function($row){
                return Carbon::parse($row->updated_at)->format('d-M-Y H:i:s');
            })
            ->addColumn('action', function($row){
                $button = '<button type="button" name="edit" id="'.$row->id.'" class="edit btn btn-warning btn-sm"> <i class="fa fa-sm fa-fw fa-pen"></i></button>';
                $button .= '   <button type="button" name="hapus" id="'.$row->id.'" class="hapus btn btn-danger btn-sm"> <i class="fa fa-sm fa-fw fa-trash"></i></button>';
                return $button;
            })
            ->rawColumns(['iteration','action'])
            ->make(true);
        }
        return view('transaksi.index', compact('coa','year','kategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('transaksi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransaksiRequest $request)
    {
        $validatedData = $request->validate([
            'tanggal' => 'required|date_format:m/d/Y',
            'coa_id' => 'required',
            'debit' => 'numeric',
            'credit' => 'numeric',
        ]);
        
        $tanggal = Carbon::createFromFormat('m/d/Y', $validatedData['tanggal'])->format('Y-m-d');
        $check = DB::table('coas')
            ->join('kategoris', 'coas.kategori_id', '=', 'kategoris.id')
            ->select('kategoris.jenis', 'coas.id')
            ->where('coas.id', $validatedData['coa_id'])
            ->get();
        $debit = 0;
        $credit = 0;
        if ($check[0]->jenis=='income') {
            $credit = $validatedData['credit'];
            $debit = 0;
        } else {
            $credit = 0;
            $debit = $validatedData['debit'];
        }

        $transaksi = Transaksi::create([
            'tanggal' => $tanggal,
            'coa_id' => $validatedData['coa_id'],
            'debit' => $debit,
            'credit' => $credit,
            'desc' => $request->input('desc'),
        ]);
        return response()->json([
            'success' => 'Data Added successfully.',
            'message' => 'Transaksi created successfully',
            'transaksi' => $transaksi,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaksi $transaksi)
    {
        return view('transaksi.show', compact('transaksi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_transaksi)
    {
        
        if(request()->ajax())
        {
            $data = Transaksi::findOrFail($id_transaksi);
            $data->tanggal = Carbon::parse($data->tanggal)->format('m/d/Y');
            return response()->json(['result' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    
    public function update(UpdateTransaksiRequest $request)
    {
        $message = [
            'message' => 'Transaksi failed to updated',
            'transaksi' => null,
        ];
        $respon = 404;
        $validatedData = $request->validate([
            'tanggal' => 'required|date_format:m/d/Y',
            'coa_id' => 'required',
            'debit' => 'numeric',
            'credit' => 'numeric',
        ]);

        $tanggal = Carbon::createFromFormat('m/d/Y', $validatedData['tanggal'])->format('Y-m-d');
        $check = DB::table('coas')
            ->join('kategoris', 'coas.kategori_id', '=', 'kategoris.id')
            ->select('kategoris.jenis', 'coas.id')
            ->where('coas.id', $validatedData['coa_id'])
            ->get();

        $debit = 0;
        $credit = 0;
        if ($check[0]->jenis=='income') {
            $credit = $validatedData['credit'];
            $debit = 0;
        } else {
            $credit = 0;
            $debit = $validatedData['debit'];
        }
        $data_update = [
            'tanggal' => $tanggal,
            'coa_id' => $validatedData['coa_id'],
            'debit' => $debit,
            'credit' => $credit,
            'desc' => $request->input('desc'),
        ];
        $transaksi = Transaksi::findOrFail($request->hidden_id);
        if ($transaksi->update($data_update)) {
            $message = [
                'success' => 'Transaksi updated successfully.',
                'message' => 'Transaksi updated successfully',
                'transaksi' => $transaksi,
            ];
            $respon = 200;
        }
        
        return response()->json($message, $respon);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_transaksi)
    {
        $message = [
            'message' => 'Transaksi failed to delete',
            'transaksi' => null,
        ];
        $respon = 404;
        $data = Transaksi::findOrFail($id_transaksi);
        if ($data->delete()) {
            $message = [
                'success' => 'Transaksi deleted successfully.',
                'message' => 'Transaksi deleted successfully'
            ];
            $respon = 200;
        }
        return response()->json($message, $respon);
    }
}
