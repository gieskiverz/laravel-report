<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Kategori;
use App\Http\Requests\StoreCoaRequest;
use App\Http\Requests\UpdateCoaRequest;

use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class CoaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(StoreCoaRequest $request)
    {
        $kategori = Kategori::all();
        if ($request->ajax()) {
            $data =  Coa::all();
            $count = 0;
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('iteration', function () use (&$count) {
                $count++;
                return $count;
            })
            ->editColumn('kategori_id', function($row){
                return Kategori::findOrFail($row->kategori_id)->nama;
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
        return view('coa.index', compact('kategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('coa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoaRequest $request)
    {
        // dd(auth()->check());
        $request->validate([
            'kode' => 'required|max:255',
            'nama' => 'required|max:255',
            'kategori_id' => 'required|max:255',
        ]);

        $coa = Coa::create([
            'kode' => $request->input('kode'),
            'nama' => $request->input('nama'),
            'kategori_id' => $request->input('kategori_id'),
        ]);
        return response()->json([
            'success' => 'Data Added successfully.',
            'message' => 'Coa created successfully',
            'coa' => $coa,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coa $coa)
    {
        return view('coa.show', compact('coa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_coa)
    {
        
        if(request()->ajax())
        {
            $data = Coa::findOrFail($id_coa);
            return response()->json(['result' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    
    public function update(UpdateCoaRequest $request)
    {
        $message = [
            'message' => 'Coa failed to updated',
            'coa' => null,
        ];
        $respon = 404;
        $validatedData = $request->validate([
            'kode' => 'required|max:255',
            'nama' => 'required|max:255',
            'kategori_id' => 'required|max:255',
        ]);

        $coa = Coa::findOrFail($request->hidden_id);
        if ($coa->update($validatedData)) {
            $message = [
                'success' => 'Coa updated successfully.',
                'message' => 'Coa updated successfully',
                'coa' => $coa,
            ];
            $respon = 200;
        }
        
        return response()->json($message, $respon);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_coa)
    {
        $message = [
            'message' => 'Coa failed to delete',
            'coa' => null,
        ];
        $respon = 404;
        $data = Coa::findOrFail($id_coa);
        if ($data->delete()) {
            $message = [
                'success' => 'Coa deleted successfully.',
                'message' => 'Coa deleted successfully'
            ];
            $respon = 200;
        }
        return response()->json($message, $respon);
    }
}
