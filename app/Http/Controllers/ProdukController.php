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
            ->orderBy('category_name', 'asc')
            ->pluck('category_name', 'category_id');

        return view('produk.index', compact('kategori'));
    }

    /**
     * Get data for datatables
     */
    public function data()
    {
        $produk = Produk::with(['kategori', 'stock'])
            ->where('products.branch_id', Auth::user()->branch_id)
            ->select([
                'products.*',
                'categories.category_name'
            ])
            ->leftJoin('categories', 'products.category_id', '=', 'categories.category_id')
            ->orderBy('products.created_at', 'desc')
            ->get();

        return datatables()
            ->of($produk)
            ->addIndexColumn()
            ->addColumn('select_all', function ($produk) {
                return '
                    <input type="checkbox" 
                        name="product_id[]" 
                        value="'. $produk->product_id .'"
                        class="select-checkbox">
                ';
            })
            ->addColumn('product_code', function ($produk) {
                return '<span class="badge bg-primary">'. e($produk->product_code) .'</span>';
            })
            ->addColumn('product_name', function ($produk) {
                return '<span class="fw-semibold">'. e($produk->product_name) .'</span>';
            })
            ->addColumn('brand', function ($produk) {
                return $produk->brand ? e($produk->brand) : '<span class="text-muted">-</span>';
            })
            ->addColumn('category_name', function ($produk) {
                return $produk->category_name ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('per_item_price', function ($produk) {
                return 'RS ' . number_format($produk->per_item_price ?? 0, 0, ',', '.');
            })
            ->addColumn('purchase_price', function ($produk) {
                return 'RS ' . number_format($produk->purchase_price ?? 0, 0, ',', '.');
            })
            ->addColumn('selling_price', function ($produk) {
                $sellingPrice = $produk->selling_price ?? 0;
                return 'RS ' . number_format($sellingPrice, 0, ',', '.');
            })
            ->addColumn('stock', function ($produk) {
                $stockData = $produk->stock;
                $currentStock = $stockData ? $stockData->stock : 0;
                $minimumStock = $stockData ? $stockData->minimum_stock : 0;
                
                if ($currentStock <= 0) {
                    $badgeClass = 'badge-danger';
                    $status = 'Out of Stock';
                } elseif ($currentStock <= $minimumStock) {
                    $badgeClass = 'badge-warning';
                    $status = 'Low Stock';
                } else {
                    $badgeClass = 'badge-success';
                    $status = 'In Stock';
                }
                
                return '
                    <div class="d-flex flex-column">
                        <span class="badge ' . $badgeClass . ' mb-1">' . $currentStock . '</span>
                        <small class="text-muted">' . $status . '</small>
                    </div>
                ';
            })
            ->addColumn('profit', function ($produk) {
                $purchasePrice = $produk->purchase_price ?? 0;
                $sellingPrice = $produk->selling_price ?? 0;
                $profit = $sellingPrice - $purchasePrice;
                
                $percentage = 0;
                if ($purchasePrice > 0) {
                    $percentage = ($profit / $purchasePrice) * 100;
                }
                
                $profitClass = $profit >= 0 ? 'text-success' : 'text-danger';
                $profitIcon = $profit >= 0 ? '▲' : '▼';
                
                return '
                    <div>
                        <div class="' . $profitClass . ' fw-semibold">
                            ' . $profitIcon . ' RS ' . number_format(abs($profit), 0, ',', '.') . '
                        </div>
                        <small class="' . $profitClass . '">
                            ' . number_format($percentage, 1) . '%
                        </small>
                    </div>
                ';
            })
            ->addColumn('action', function ($produk) {
                return '
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" 
                            onclick="editForm(`'. route('produk.show', $produk->product_id) .'`)" 
                            class="btn btn-xs btn-primary btn-flat" 
                            style="margin-right: 5px;"
                            title="Edit">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" 
                            onclick="updateStockForm(`'. route('produk.stock_details', $produk->product_id) .'`)" 
                            class="btn btn-xs btn-info btn-flat" 
                            style="margin-right: 5px;"
                            title="Update Stock">
                        <i class="fa fa-cube"></i>
                    </button>
                    <button type="button" 
                            onclick="deleteData(`'. route('produk.destroy', $produk->product_id) .'`)" 
                            class="btn btn-xs btn-danger btn-flat" title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                ';
            })
            ->addColumn('last_updated', function ($produk) {
                return $produk->updated_at 
                    ? $produk->updated_at->format('d/m/Y H:i')
                    : '<span class="text-muted">Never</span>';
            })
            ->rawColumns([
                'action', 
                'product_code', 
                'product_name',
                'brand',
                'select_all', 
                'stock', 
                'profit',
                'category_name',
                'selling_price',
                'last_updated'
            ])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate with Indonesian field names (from payload)
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:categories,category_id',
            'merk' => 'nullable|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'stok' => 'required|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
        ], [
            'nama_produk.required' => 'Product name is required',
            'nama_produk.string' => 'Product name must be text',
            'nama_produk.max' => 'Product name maximum 255 characters',
            'id_kategori.required' => 'Category is required',
            'id_kategori.exists' => 'Selected category is invalid',
            'merk.string' => 'Brand must be text',
            'merk.max' => 'Brand maximum 100 characters',
            'harga_beli.required' => 'Purchase price is required',
            'harga_beli.numeric' => 'Purchase price must be a number',
            'harga_beli.min' => 'Purchase price minimum 0',
            'harga_jual.required' => 'Selling price is required',
            'harga_jual.numeric' => 'Selling price must be a number',
            'harga_jual.min' => 'Selling price minimum 0',
            'diskon.numeric' => 'Discount must be a number',
            'diskon.min' => 'Discount minimum 0',
            'diskon.max' => 'Discount maximum 100',
            'stok.required' => 'Stock is required',
            'stok.integer' => 'Stock must be an integer',
            'stok.min' => 'Stock minimum 0',
        ]);

        // Custom validation for selling price after discount
        $validator->after(function ($validator) use ($request) {
            if ($request->has('harga_jual') && $request->has('harga_beli') && $request->has('diskon')) {
                $hargaBeli = (float) $request->harga_beli;
                $hargaJual = (float) $request->harga_jual;
                $diskon = (float) $request->diskon;
                
                // Calculate price after discount
                $hargaSetelahDiskon = $hargaJual - ($hargaJual * $diskon / 100);
                
                // Check if final price after discount is greater than purchase price
                if ($hargaSetelahDiskon <= $hargaBeli) {
                    $validator->errors()->add(
                        'harga_jual', 
                        'Selling price after discount (' . number_format($hargaSetelahDiskon) . ') must be greater than purchase price (' . number_format($hargaBeli) . ')'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        // Check if category belongs to user's branch
        $kategori = Kategori::where('category_id', $request->id_kategori)
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
            // Generate product code
            $productCode = $this->generateProductCode();
            
            // Map Indonesian payload fields to English database columns
            $productData = [
                'product_code' => $productCode,
                'product_name' => $request->nama_produk,
                'category_id' => $request->id_kategori,
                'brand' => $request->merk,
                'purchase_price' => $request->harga_beli,
                'selling_price' => $request->harga_jual,
                'branch_id' => Auth::user()->branch_id,
            ];

            $produk = Produk::create($productData);

            // Check if stock entry exists
            if (!$produk->stock) {
                $produk->stock()->create([
                    'stock' => 1,
                    'minimum_stock' => 0,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'data' => $produk
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error saving product: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function generateProductCode()
    {
        $date = date('Ymd');
        $lastProduct = Produk::whereDate('created_at', today())
            ->where('branch_id', Auth::user()->branch_id)
            ->latest()
            ->first();
        
        if ($lastProduct && $lastProduct->product_code) {
            $lastCode = $lastProduct->product_code;
            if (preg_match('/-(\d+)$/', $lastCode, $matches)) {
                $number = (int)$matches[1] + 1;
                return 'PRD-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        }
        
        return 'PRD-' . $date . '-0001';
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
        // Validate with Indonesian field names (from payload)
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'required|exists:categories,category_id', // Updated table name
            'merk' => 'nullable|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'stok' => 'required|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
        ], [
            'nama_produk.required' => 'Product name is required',
            'nama_produk.string' => 'Product name must be text',
            'nama_produk.max' => 'Product name maximum 255 characters',
            'id_kategori.required' => 'Category is required',
            'id_kategori.exists' => 'Selected category is invalid',
            'merk.string' => 'Brand must be text',
            'merk.max' => 'Brand maximum 100 characters',
            'harga_beli.required' => 'Purchase price is required',
            'harga_beli.numeric' => 'Purchase price must be a number',
            'harga_beli.min' => 'Purchase price minimum 0',
            'harga_jual.required' => 'Selling price is required',
            'harga_jual.numeric' => 'Selling price must be a number',
            'harga_jual.min' => 'Selling price minimum 0',
            'diskon.numeric' => 'Discount must be a number',
            'diskon.min' => 'Discount minimum 0',
            'diskon.max' => 'Discount maximum 100',
            'stok.required' => 'Stock is required',
            'stok.integer' => 'Stock must be an integer',
            'stok.min' => 'Stock minimum 0',
        ]);

        // Custom validation for selling price after discount
        $validator->after(function ($validator) use ($request) {
            if ($request->has('harga_jual') && $request->has('harga_beli') && $request->has('diskon')) {
                $hargaBeli = (float) $request->harga_beli;
                $hargaJual = (float) $request->harga_jual;
                $diskon = (float) $request->diskon;
                
                // Calculate price after discount
                $hargaSetelahDiskon = $hargaJual - ($hargaJual * $diskon / 100);
                
                // Check if final price after discount is greater than purchase price
                if ($hargaSetelahDiskon <= $hargaBeli) {
                    $validator->errors()->add(
                        'harga_jual', 
                        'Selling price after discount (' . number_format($hargaSetelahDiskon) . ') must be greater than purchase price (' . number_format($hargaBeli) . ')'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        // Check if category belongs to user's branch
        $kategori = Kategori::where('category_id', $request->id_kategori)
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
            // Find product that belongs to user's branch
            $produk = Produk::where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            // Map Indonesian payload fields to English database columns
            $updateData = [
                'product_name' => $request->nama_produk,     // Map: nama_produk -> product_name
                'category_id' => $request->id_kategori,      // Map: id_kategori -> category_id
                'brand' => $request->merk,                   // Map: merk -> brand
                'purchase_price' => $request->harga_beli,    // Map: harga_beli -> purchase_price
                'selling_price' => $request->harga_jual,     // Map: harga_jual -> selling_price
                'discount' => $request->diskon ?? 0,         // Map: diskon -> discount
                'stock' => $request->stok, 
                'minimum_stock' => $request->minimum_stock,   // Map: minimum_stock -> minimum_stock
            ];

            $produk->update($updateData);

            DB::commit();

            // Reload the product with category relationship
            $produk->load('kategori');

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => $produk
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Product not found or does not belong to your branch'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'product_id' => $id,
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
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ], [
            'stock.required' => 'Stock quantity is required',
            'stock.integer' => 'Stock must be an integer',
            'stock.min' => 'Stock must be at least 0',
            'minimum_stock.required' => 'Minimum stock is required',
            'minimum_stock.integer' => 'Minimum stock must be an integer',
            'minimum_stock.min' => 'Minimum stock must be at least 0',
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

            // Update or create stock entry
            if ($product->stock) {
                $product->stock()->update([
                    'stock' => $request->stock,
                    'minimum_stock' => $request->minimum_stock,
                    'updated_by' => Auth::id(),
                ]);
            } else {
                $product->stock()->create([
                    'stock' => $request->stock,
                    'minimum_stock' => $request->minimum_stock,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Stock updated successfully'
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
                'message' => 'System error occurred',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get product stock details
     */
    public function getStockDetails($id)
    {
        try {
            $product = Produk::with('stock')
                ->where('branch_id', Auth::user()->branch_id)
                ->findOrFail($id);

            $stockData = $product->stock;

            return response()->json([
                'status' => true,
                'data' => [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'stock' => $stockData ? $stockData->stock : 0,
                    'minimum_stock' => $stockData ? $stockData->minimum_stock : 0,
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }
}