<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

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
        $branchId = Auth::user()->branch_id;

        $categories = Kategori::where('branch_id', $branchId)
            ->orderByDesc('category_id')
            ->get();

        return datatables()
            ->of($categories)
            ->addIndexColumn()

            ->addColumn('action', function ($category) {
                return '
                    <div class="btn-group">
                        <button onclick="editForm(`'.route('kategori.update', $category->category_id).'`)"
                            class="btn btn-xs btn-primary btn-flat" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </button>

                        <button onclick="deleteData(`'.route('kategori.destroy', $category->category_id).'`)"
                            class="btn btn-xs btn-danger btn-flat" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                ';
            })

            ->addColumn('branch_name', function ($category) {
                return $category->branch ? $category->branch->name : 'N/A';
            })

            ->rawColumns(['action'])
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
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'category_name')
                    ->where('branch_id', Auth::user()->branch_id),
            ],
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
                'category_name' => $request->nama_kategori,
                'branch_id' => $user->branch_id,
            ]);

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
        $user = Auth::user();

        $validator = validator($request->all(), [
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'category_name')
                    ->ignore($id, 'category_id')
                    ->where('branch_id', $user->branch_id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        try {
            $category = Kategori::where('branch_id', $user->branch_id)
                ->findOrFail($id);

            $category->update([
                'category_name' => $request->nama_kategori,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Category updated successfully',
                'data'    => $category
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error updating category', [
                'user_id'     => $user->id,
                'category_id' => $id,
                'request'     => $request->all(),
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'System error occurred',
                'error'   => config('app.debug') ? $e->getMessage() : null
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

            $kategoriName = $kategori->category_name;
            $kategori->delete();

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
            ->orderBy('category_name', 'asc')
            ->get(['category_id as id', 'category_name as text'])
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
            $query->where('category_name', 'like', '%' . $request->q . '%');
        }

        $categories = $query->orderBy('category_name', 'asc')
            ->limit(10)
            ->get(['category_id as id', 'category_name as text']);

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }
}