<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PDF;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get categories only from user's branch
        $kategori = Kategori::where('branch_id', Auth::user()->branch_id)
            ->orderBy('nama_kategori', 'asc')
            ->pluck('nama_kategori', 'id_kategori');

        return view('produk.index', compact('kategori'));
    }

    public function data()
    {
        $produk = Produk::with(['kategori'])
            ->where('branch_id', Auth::user()->branch_id) // Direct filter by branch_id
            ->select('produk.*')
            ->orderBy('produk.created_at', 'desc')
            ->get();

        return datatables()
            ->of($produk)
            ->addIndexColumn()
            ->addColumn('select_all', function ($produk) {
                return '
                    <input type="checkbox" 
                           name="id_produk[]" 
                           value="'. $produk->id_produk .'"
                           class="select-checkbox">
                ';
            })
            ->addColumn('kode_produk', function ($produk) {
                return '<span class="badge bg-primary">'. $produk->kode_produk .'</span>';
            })
            ->addColumn('nama_kategori', function ($produk) {
                return $produk->kategori->nama_kategori ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('harga_beli', function ($produk) {
                return format_uang($produk->harga_beli);
            })
            ->addColumn('harga_jual', function ($produk) {
                return format_uang($produk->harga_jual);
            })
            ->addColumn('harga_jual_formatted', function ($produk) {
                return 'Rp ' . number_format($produk->harga_jual, 0, ',', '.');
            })
            ->addColumn('stok', function ($produk) {
                // Remove stok_minimum check since column doesn't exist
                $badgeClass = $produk->stok <= 10 ? 'badge-danger' : 'badge-success'; // Using fixed threshold
                return '<span class="badge ' . $badgeClass . '">' . format_uang($produk->stok) . '</span>';
            })
            ->addColumn('keuntungan', function ($produk) {
                $keuntungan = $produk->harga_jual - $produk->harga_beli;
                $persentase = $produk->harga_beli > 0 ? ($keuntungan / $produk->harga_beli) * 100 : 0;
                return '<div>
                    <div>Rp ' . format_uang($keuntungan) . '</div>
                    <small class="text-muted">' . number_format($persentase, 1) . '%</small>
                </div>';
            })
            ->addColumn('aksi', function ($produk) {
                $buttons = '
                <div class="btn-group btn-group-sm">
                    <button type="button" 
                            onclick="editForm(`'. route('produk.update', $produk->id_produk) .'`)" 
                            class="btn btn-primary btn-flat" 
                            title="Edit"><i class="fa-regular fa-pen-to-square"></i>
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" 
                            onclick="showDetail(`'. route('produk.show', $produk->id_produk) .'`)" 
                            class="btn btn-info btn-flat" 
                            title="Detail">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button type="button" 
                            onclick="deleteData(`'. route('produk.destroy', $produk->id_produk) .'`)" 
                            class="btn btn-danger btn-flat" 
                            title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                ';
                
                return $buttons;
            })
            ->rawColumns([
                'aksi', 
                'kode_produk', 
                'select_all', 
                'stok', 
                'keuntungan',
                'nama_kategori'
            ])
            ->make(true);
    }

    /**
     * Generate product code
     */
    private function generateKodeProduk()
    {
        $lastProduk = Produk::where('branch_id', Auth::user()->branch_id)
            ->latest('id_produk')
            ->first();

        $prefix = 'PRD';
        $branchCode = str_pad(Auth::user()->branch_id, 2, '0', STR_PAD_LEFT);
        
        if ($lastProduk) {
            $lastNumber = (int) substr($lastProduk->kode_produk, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $branchCode . date('Ymd') . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Remove validation for columns that don't exist: stok_minimum, satuan, deskripsi, status
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'merk' => 'nullable|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:' . max($request->harga_beli, 0),
            'diskon' => 'nullable|numeric|min:0|max:100',
            'stok' => 'required|integer|min:0',
        ], [
            'nama_produk.required' => 'Product name is required',
            'nama_produk.string' => 'Product name must be a string',
            'nama_produk.max' => 'Product name may not be greater than 255 characters',
            'id_kategori.required' => 'Category is required',
            'id_kategori.exists' => 'Selected category is invalid',
            'merk.string' => 'Brand must be a string',
            'merk.max' => 'Brand may not be greater than 100 characters',
            'harga_beli.required' => 'Purchase price is required',
            'harga_beli.numeric' => 'Purchase price must be a number',
            'harga_beli.min' => 'Purchase price must be at least 0',
            'harga_jual.required' => 'Selling price is required',
            'harga_jual.numeric' => 'Selling price must be a number',
            'harga_jual.min' => 'Selling price must be greater than purchase price',
            'diskon.numeric' => 'Discount must be a number',
            'diskon.min' => 'Discount must be at least 0',
            'diskon.max' => 'Discount may not be greater than 100',
            'stok.required' => 'Stock is required',
            'stok.integer' => 'Stock must be an integer',
            'stok.min' => 'Stock must be at least 0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        // Check if category belongs to user's branch
        $kategori = Kategori::where('id_kategori', $request->id_kategori)
            ->where('branch_id', Auth::user()->branch_id)
            ->first();

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found or does not belong to your branch'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $produk = Produk::create([
                'kode_produk' => $this->generateKodeProduk(),
                'nama_produk' => $request->nama_produk,
                'id_kategori' => $request->id_kategori,
                'merk' => $request->merk,
                'harga_beli' => $request->harga_beli,
                'harga_jual' => $request->harga_jual,
                'diskon' => $request->diskon ?? 0,
                'stok' => $request->stok,
                // Remove columns that don't exist: stok_minimum, satuan, deskripsi, status, created_by, updated_by
                'branch_id' => Auth::user()->branch_id,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product saved successfully',
                'data' => $produk
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error saving product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred',
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
            $produk = Produk::with(['kategori'])
                ->where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $produk
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }
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
        // Remove validation for columns that don't exist
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'merk' => 'nullable|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:' . max($request->harga_beli, 0),
            'diskon' => 'nullable|numeric|min:0|max:100',
            'stok' => 'required|integer|min:0',
        ], [
            'nama_produk.required' => 'Product name is required',
            'id_kategori.required' => 'Category is required',
            'harga_beli.required' => 'Purchase price is required',
            'harga_jual.required' => 'Selling price is required',
            'harga_jual.min' => 'Selling price must be greater than purchase price',
            'stok.required' => 'Stock is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        // Check if category belongs to user's branch
        $kategori = Kategori::where('id_kategori', $request->id_kategori)
            ->where('branch_id', Auth::user()->branch_id)
            ->first();

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Category does not belong to your branch'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $produk = Produk::where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            $produk->update([
                'nama_produk' => $request->nama_produk,
                'id_kategori' => $request->id_kategori,
                'merk' => $request->merk,
                'harga_beli' => $request->harga_beli,
                'harga_jual' => $request->harga_jual,
                'diskon' => $request->diskon ?? 0,
                'stok' => $request->stok,
                // Remove columns that don't exist
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => $produk
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'product_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred',
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
        DB::beginTransaction();
        try {
            $produk = Produk::where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            $produk->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error deleting product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'product_id' => $id
            ]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Delete multiple selected products
     */
    public function deleteSelected(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|array',
            'id_produk.*' => 'exists:produk,id_produk'
        ], [
            'id_produk.required' => 'No products selected',
            'id_produk.array' => 'Invalid product data format',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid data'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $productIds = $request->id_produk;
            
            // Get products that belong to user's branch
            $productsToDelete = Produk::whereIn('id_produk', $productIds)
                ->where('branch_id', Auth::user()->branch_id)
                ->get();

            $deletedCount = 0;
            foreach ($productsToDelete as $product) {
                $product->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $deletedCount . ' product(s) deleted successfully',
                'deleted_count' => $deletedCount,
                'total_selected' => count($productIds)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error deleting selected products: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'product_ids' => $request->id_produk
            ]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred'
            ], 500);
        }
    }

    /**
     * Export barcode PDF for selected products
     */
    public function cetakBarcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|array',
            'id_produk.*' => 'exists:produk,id_produk'
        ], [
            'id_produk.required' => 'No products selected',
            'id_produk.array' => 'Invalid product data format',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $products = Produk::whereIn('id_produk', $request->id_produk)
                ->where('branch_id', Auth::user()->branch_id)
                ->with('kategori')
                ->get();

            if ($products->isEmpty()) {
                return back()->with('error', 'No products found');
            }

            $no = 1;
            $pdf = PDF::loadView('produk.barcode', compact('products', 'no'));
            $pdf->setPaper('a4', 'portrait');
            
            $filename = 'product-barcodes-' . date('Y-m-d-H-i-s') . '.pdf';
            
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Log::error('Error generating barcode PDF: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'product_ids' => $request->id_produk
            ]);

            return back()->with('error', 'Failed to generate barcode: ' . $e->getMessage());
        }
    }

    /**
     * Get products for dropdown/select (for sales, etc.)
     */
    public function getProdukForSelect(Request $request)
    {
        $query = Produk::with('kategori')
            ->where('branch_id', Auth::user()->branch_id)
            // Remove status filter since column doesn't exist
            // ->where('status', true)
            ;

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('kode_produk', 'like', '%' . $search . '%')
                  ->orWhereHas('kategori', function ($q2) use ($search) {
                      $q2->where('nama_kategori', 'like', '%' . $search . '%');
                  });
            });
        }

        $products = $query->orderBy('nama_produk', 'asc')
            ->limit(50)
            ->get(['id_produk as id', 'nama_produk as text', 'kode_produk', 'harga_jual', 'stok']);

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    /**
     * Check stock alert (products with low stock)
     */
    public function stockAlert()
    {
        // Since stok_minimum column doesn't exist, use fixed threshold (e.g., 10)
        $threshold = 10;
        
        $products = Produk::with('kategori')
            ->where('branch_id', Auth::user()->branch_id)
            ->where('stok', '<=', $threshold) // Using fixed threshold
            // Remove status filter since column doesn't exist
            // ->where('status', true)
            ->orderBy('stok', 'asc')
            ->get(['id_produk', 'kode_produk', 'nama_produk', 'stok']);

        return response()->json([
            'status' => true,
            'data' => $products,
            'count' => $products->count(),
            'threshold' => $threshold
        ]);
    }

    /**
     * Update stock for a product
     */
    public function updateStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stok' => 'required|integer|min:0',
            'type' => 'required|in:add,subtract,set', // add, subtract, or set exact value
            'keterangan' => 'nullable|string|max:255'
        ], [
            'stok.required' => 'Stock quantity is required',
            'stok.integer' => 'Stock must be an integer',
            'stok.min' => 'Stock must be at least 0',
            'type.required' => 'Adjustment type is required',
            'type.in' => 'Invalid adjustment type',
            'keterangan.string' => 'Note must be a string',
            'keterangan.max' => 'Note may not be greater than 255 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $product = Produk::where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            $oldStock = $product->stok;
            
            switch ($request->type) {
                case 'add':
                    $product->stok += $request->stok;
                    break;
                case 'subtract':
                    $product->stok = max(0, $product->stok - $request->stok);
                    break;
                case 'set':
                    $product->stok = $request->stok;
                    break;
            }

            $product->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Stock updated successfully',
                'data' => [
                    'old_stock' => $oldStock,
                    'new_stock' => $product->stok
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating stock: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'product_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred'
            ], 500);
        }
    }
}