<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;

use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class KategoriController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(StoreKategoriRequest $request)
    {
        if ($request->ajax()) {
            $data =  Kategori::all();
            $count = 0;
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('iteration', function () use (&$count) {
                $count++;
                return $count;
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
        return view('kategori.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKategoriRequest $request)
    {
        // dd(auth()->check());
        $request->validate([
            'nama' => 'required|max:255',
            'jenis' => 'required|max:255',
        ]);

        $kategori = Kategori::create([
            'nama' => $request->input('nama'),
            'jenis' => $request->input('jenis'),
        ]);
        return response()->json([
            'success' => 'Data Added successfully.',
            'message' => $request->input('nama') . ' added to kategori.',
            'kategori' => $kategori,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Kategori $kategori)
    {
        return view('kategori.show', compact('kategori'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id_kategori)
    {
        
        if(request()->ajax())
        {
            $data = Kategori::findOrFail($id_kategori);
            return response()->json(['result' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    
    public function update(UpdateKategoriRequest $request)
    {
        $message = [
            'message' => 'Kategori failed to updated',
            'kategori' => null,
        ];
        $respon = 404;
        $validatedData = $request->validate([
            'nama' => 'required|max:255',
            'jenis' => 'required|max:255',
        ]);

        $kategori = Kategori::findOrFail($request->hidden_id);
        if ($kategori->update($validatedData)) {
            $message = [
                'success' => 'Kategori updated successfully.',
                'message' => 'Kategori updated successfully',
                'kategori' => $kategori,
            ];
            $respon = 200;
        }
        
        return response()->json($message, $respon);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_kategori)
    {
        $message = [
            'message' => 'Kategori failed to delete',
            'kategori' => null,
        ];
        $respon = 404;
        $data = Kategori::findOrFail($id_kategori);
        if ($data->delete()) {
            $message = [
                'success' => 'Kategori deleted successfully.',
                'message' => 'Kategori deleted successfully'
            ];
            $respon = 200;
        }
        return response()->json($message, $respon);
    }
}
