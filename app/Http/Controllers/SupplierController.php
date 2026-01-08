<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index()
    {
        return view('supplier.index');
    }

    public function data()
    {
        try {
            $suppliers = Supplier::orderBy('supplier_id', 'desc')->get();

            return datatables()
                ->of($suppliers)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($supplier) {
                    static $index = 1;
                    return $index++;
                })
                ->addColumn('phone_formatted', function ($supplier) {
                    // Format phone number for better display
                    $phone = preg_replace('/[^0-9]/', '', $supplier->phone);
                    
                    if (strlen($phone) >= 10) {
                        if (substr($phone, 0, 2) == '08') {
                            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
                        } elseif (substr($phone, 0, 2) == '03') {
                            return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
                        }
                    }
                    return $supplier->phone;
                })
                ->addColumn('address_short', function ($supplier) {
                    if (!$supplier->address) return '-';
                    return strlen($supplier->address) > 60 
                        ? substr($supplier->address, 0, 60) . '...' 
                        : $supplier->address;
                })
                ->addColumn('created_at_formatted', function ($supplier) {
                    return $supplier->created_at->format('d M Y');
                })
                ->addColumn('aksi', function ($supplier) {
                    return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" 
                                onclick="editForm(\'' . route('supplier.update', $supplier->supplier_id) . '\')" 
                                class="btn btn-warning" 
                                data-toggle="tooltip" 
                                title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        
                        <button type="button" 
                                onclick="deleteData(\'' . route('supplier.destroy', $supplier->supplier_id) . '\', \'' . addslashes($supplier->name) . '\')" 
                                class="btn btn-danger" 
                                data-toggle="tooltip" 
                                title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
                
        } catch (\Exception $e) {
            \Log::error('Error in supplier data method: ' . $e->getMessage());
            
            return datatables()
                ->of(collect([]))
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'nama' => 'required|string|max:255|min:2',
            'telepon' => 'required|string|max:20|min:10',
            'alamat' => 'nullable|string|max:500',
        ], [
            'nama.required' => 'Nama supplier wajib diisi',
            'nama.string' => 'Nama supplier harus berupa teks',
            'nama.max' => 'Nama supplier maksimal 255 karakter',
            'nama.min' => 'Nama supplier minimal 2 karakter',
            'telepon.required' => 'Nomor telepon wajib diisi',
            'telepon.string' => 'Nomor telepon harus berupa teks',
            'telepon.max' => 'Nomor telepon maksimal 20 karakter',
            'telepon.min' => 'Nomor telepon minimal 10 karakter',
            'alamat.string' => 'Alamat harus berupa teks',
            'alamat.max' => 'Alamat maksimal 500 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Clean and prepare the data
            $name = trim($request->nama);
            $phone = trim($request->telepon);
            $address = $request->alamat ? trim($request->alamat) : null;

            // Check if supplier with same phone already exists (optional)
            $existingSupplier = Supplier::where('phone', $phone)->first();
            
            if ($existingSupplier) {
                return response()->json([
                    'status' => false,
                    'message' => 'Supplier dengan nomor telepon ini sudah terdaftar',
                    'data' => [
                        'existing_id' => $existingSupplier->supplier_id,
                        'existing_name' => $existingSupplier->name
                    ]
                ], 409); // 409 Conflict
            }

            // Map Indonesian payload fields to English database columns
            $supplierData = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];

            // Create supplier
            $supplier = Supplier::create($supplierData);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Supplier berhasil disimpan',
                'data' => [
                    'supplier_id' => $supplier->supplier_id,
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'created_at' => $supplier->created_at->format('d/m/Y H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error saving supplier: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => [
                    'nama' => $request->nama,
                    'telepon' => $request->telepon,
                    'alamat' => $request->alamat,
                ]
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
        $supplier = Supplier::find($id);

        return response()->json($supplier);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    // visit "codeastro" for more projects!
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
        // Validate with Indonesian field names
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|min:2',
            'telepon' => 'required|string|max:20|min:10',
            'alamat' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Find the supplier
            $supplier = Supplier::find($id);
            
            if (!$supplier) {
                return response()->json([
                    'status' => false,
                    'message' => 'Supplier tidak ditemukan'
                ], 404);
            }

            // Clean and prepare the data
            $name = trim($request->nama);
            $phone = trim($request->telepon);
            $address = $request->alamat ? trim($request->alamat) : null;

            // Check if phone is being changed and if it conflicts with another supplier
            if ($supplier->phone !== $phone) {
                $existingSupplier = Supplier::where('phone', $phone)
                    ->where('supplier_id', '!=', $id)
                    ->first();
                
                if ($existingSupplier) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Nomor telepon ini sudah digunakan oleh supplier lain',
                        'data' => [
                            'existing_id' => $existingSupplier->supplier_id,
                            'existing_name' => $existingSupplier->name
                        ]
                    ], 409);
                }
            }

            // Update supplier
            $supplier->name = $name;
            $supplier->phone = $phone;
            $supplier->address = $address;
            $supplier->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Supplier berhasil diperbarui',
                'data' => $supplier
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating supplier: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'supplier_id' => $id,
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
        $supplier = Supplier::find($id)->delete();

        return response(null, 204);
    }
}
// visit "codeastro" for more projects!