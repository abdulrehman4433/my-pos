<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('kategori.index');
    }

    public function data()
    {
        // Get current user's branch
        $userBranchId = Auth::user()->branch_id;
        
        // Filter categories by user's branch
        $kategori = Kategori::where('branch_id', $userBranchId)
            ->orderBy('id_kategori', 'desc')
            ->get();

        return datatables()
            ->of($kategori)
            ->addIndexColumn()
            ->addColumn('aksi', function ($kategori) {
                $buttons = '
                <div class="btn-group">
                    <button onclick="editForm(`'. route('kategori.update', $kategori->id_kategori) .'`)" 
                            class="btn btn-xs btn-primary btn-flat" 
                            title="Edit">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button onclick="deleteData(`'. route('kategori.destroy', $kategori->id_kategori) .'`)" 
                            class="btn btn-xs btn-danger btn-flat" 
                            title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                ';
                
                return $buttons;
            })
            ->addColumn('branch_name', function ($kategori) {
                return $kategori->branch ? $kategori->branch->name : 'N/A';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Not needed for SPA/AJAX implementation
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,NULL,id_kategori,branch_id,' . Auth::user()->branch_id,
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi',
            'nama_kategori.unique' => 'Kategori sudah ada di cabang ini',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        try {
            $user = Auth::user();
            
            if (!$user->branch_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'User tidak memiliki cabang yang ditugaskan'
                ], 403);
            }

            $kategori = Kategori::create([
                'nama_kategori' => $request->nama_kategori,
                'branch_id' => $user->branch_id,
            ]);

            // Log activity (optional - if you have activity logging)
            // activity()
            //     ->causedBy($user)
            //     ->performedOn($kategori)
            //     ->log('created kategori: ' . $kategori->nama_kategori);

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil disimpan',
                'data' => $kategori
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error saving kategori: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $kategori = Kategori::with('branch')
                ->where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $kategori
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Not needed for SPA/AJAX implementation
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = validator($request->all(), [
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id . ',id_kategori,branch_id,' . Auth::user()->branch_id,
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi',
            'nama_kategori.unique' => 'Kategori sudah ada di cabang ini',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        try {
            $kategori = Kategori::where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            $oldName = $kategori->nama_kategori;
            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->save();

            // Log activity (optional)
            // activity()
            //     ->causedBy(Auth::user())
            //     ->performedOn($kategori)
            //     ->log('updated kategori from "' . $oldName . '" to "' . $kategori->nama_kategori . '"');

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $kategori
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error updating kategori: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'kategori_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $kategori = Kategori::where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            // Check if kategori has related products (optional)
            // if ($kategori->produk()->exists()) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Tidak dapat menghapus kategori karena memiliki produk terkait'
            //     ], 422);
            // }

            $kategoriName = $kategori->nama_kategori;
            $kategori->delete();

            // Log activity (optional)
            // activity()
            //     ->causedBy(Auth::user())
            //     ->log('deleted kategori: ' . $kategoriName);

            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting kategori: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'kategori_id' => $id
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get categories for dropdown/select (optional)
     */
    public function getCategoriesForSelect()
    {
        $categories = Kategori::where('branch_id', Auth::user()->branch_id)
            ->orderBy('nama_kategori', 'asc')
            ->get(['id_kategori as id', 'nama_kategori as text'])
            ->toArray();

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }

    /**
     * Search categories (optional)
     */
    public function search(Request $request)
    {
        $query = Kategori::where('branch_id', Auth::user()->branch_id);

        if ($request->has('q') && !empty($request->q)) {
            $query->where('nama_kategori', 'like', '%' . $request->q . '%');
        }

        $categories = $query->orderBy('nama_kategori', 'asc')
            ->limit(10)
            ->get(['id_kategori as id', 'nama_kategori as text']);

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }
}