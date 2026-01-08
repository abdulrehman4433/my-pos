<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\Auth; // If using authentication
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PengeluaranController extends Controller
{
    public function index()
    {
        return view('pengeluaran.index');
    }

    public function data(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Start query with eager loading
            $query = Pengeluaran::with('branch')
                ->select('expense_id', 'description', 'amount', 'created_at', 'branch_id');
            
            // Filter by branch if user belongs to a specific branch
            if ($user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
            
            // Add search functionality
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchTerm = $request->search['value'];
                $query->where(function($q) use ($searchTerm) {
                    $q->where('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('amount', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('branch', function($branchQuery) use ($searchTerm) {
                        $branchQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
                });
            }
            
            // Handle ordering
            if ($request->has('order') && count($request->order)) {
                $columns = $request->columns;
                foreach ($request->order as $order) {
                    $columnIndex = $order['column'];
                    $columnName = $columns[$columnIndex]['data'] ?? $columns[$columnIndex]['name'] ?? null;
                    
                    if ($columnName) {
                        switch ($columnName) {
                            case 'description':
                            case 'amount':
                            case 'created_at':
                                $query->orderBy($columnName, $order['dir']);
                                break;
                            case 'branch_name':
                                $query->orderBy('branch_id', $order['dir']);
                                break;
                        }
                    }
                }
            } else {
                $query->orderBy('expense_id', 'desc');
            }
            
            // Get data
            $pengeluaran = $query->get();

            return datatables()
                ->of($pengeluaran)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($pengeluaran) {
                    static $index = 0;
                    return ++$index;
                })
                ->addColumn('created_at_formatted', function ($pengeluaran) {
                    return tanggal_indonesia($pengeluaran->created_at, false);
                })
                ->addColumn('amount_formatted', function ($pengeluaran) {
                    return format_uang($pengeluaran->amount); // Changed from 'nominal' to 'amount'
                })
                ->addColumn('branch_name', function ($pengeluaran) {
                    return $pengeluaran->branch ? $pengeluaran->branch->name : '<span class="text-muted">-</span>';
                })
                ->addColumn('aksi', function ($pengeluaran) use ($user) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // Check permission for editing (if user can only edit their branch's data)
                    $canEdit = true;
                    if ($user->branch_id && $pengeluaran->branch_id != $user->branch_id) {
                        $canEdit = false;
                    }
                    
                    // Edit button
                    $editButton = '
                        <button type="button" 
                                onclick="editForm(`'. route('pengeluaran.update', $pengeluaran->expense_id) .'`)" 
                                class="btn btn-warning btn-edit" 
                                data-id="' . $pengeluaran->expense_id . '"
                                ' . ($canEdit ? '' : 'disabled title="Tidak dapat mengedit data cabang lain"') . '
                                data-toggle="tooltip" 
                                title="Edit Pengeluaran">
                            <i class="fa fa-pencil"></i>
                        </button>';
                    
                    // Delete button
                    $deleteButton = '
                        <button type="button" 
                                onclick="deleteData(`'. route('pengeluaran.destroy', $pengeluaran->expense_id) .'`, `' . addslashes($pengeluaran->description) . '`)" 
                                class="btn btn-danger btn-delete" 
                                data-id="' . $pengeluaran->expense_id . '"
                                data-description="' . htmlspecialchars($pengeluaran->description) . '"
                                ' . ($canEdit ? '' : 'disabled title="Tidak dapat menghapus data cabang lain"') . '
                                data-toggle="tooltip" 
                                title="Hapus Pengeluaran">
                            <i class="fa fa-trash"></i>
                        </button>';
                    
                    
                    $buttons .= $editButton . $deleteButton;
                    $buttons .= '</div>';
                    
                    return $buttons;
                })
                ->rawColumns(['aksi', 'branch_name', 'amount_formatted'])
                ->make(true);
                
        } catch (\Exception $e) {
            \Log::error('Error fetching pengeluaran data: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty response with error
            return response()->json([
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data pengeluaran'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validate with Indonesian field names
        $validator = Validator::make($request->all(), [
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|integer|min:1',
        ], [
            'deskripsi.required' => 'Deskripsi pengeluaran wajib diisi',
            'deskripsi.string' => 'Deskripsi harus berupa teks',
            'deskripsi.max' => 'Deskripsi maksimal 255 karakter',
            'nominal.required' => 'Nominal pengeluaran wajib diisi',
            'nominal.integer' => 'Nominal harus berupa angka',
            'nominal.min' => 'Nominal minimal Rp 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Get authenticated user and their branch_id
            $user = Auth::user();
            
            if (!$user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terhubung dengan cabang manapun'
                ], 400);
            }

            // Map Indonesian field names to English database columns
            $expenseData = [
                'description' => trim($request->deskripsi),      // Map: deskripsi -> description
                'amount' => (int) $request->nominal,             // Map: nominal -> amount
                'branch_id' => $user->branch_id,                // Get from authenticated user
            ];

            // Optional: Add created_by user tracking if column exists
            if (Schema::hasColumn('expenses', 'created_by')) { // Make sure this matches your actual table name
                $expenseData['created_by'] = $user->id;
            }

            // Debug: Log the data being inserted
            \Log::info('Creating expense with data:', $expenseData);

            // Create expense
            $pengeluaran = Pengeluaran::create($expenseData);

            // Verify the expense was created
            if (!$pengeluaran) {
                throw new \Exception('Failed to create expense record');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pengeluaran berhasil disimpan',
                'data' => [
                    'expense_id' => $pengeluaran->expense_id,
                    'deskripsi' => $pengeluaran->description,
                    'nominal' => $pengeluaran->amount,
                    'amount_formatted' => 'Rp ' . number_format($pengeluaran->amount, 0, ',', '.'),
                    'tanggal' => $pengeluaran->created_at->format('d/m/Y H:i:s'),
                    'branch_id' => $pengeluaran->branch_id,
                    'branch_name' => optional($pengeluaran->branch)->name ?? '-',
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error saving expense: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'branch_id' => Auth::user()->branch_id ?? 'null',
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data pengeluaran',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show($id)
    {
        $pengeluaran = Pengeluaran::with('branch')->find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'Expense data not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pengeluaran
        ]);
    }

    public function update(Request $request, $id)
    {
        // Find the expense
        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengeluaran tidak ditemukan'
            ], 404);
        }

        $user = Auth::user();
        
        // Optional: Check if user has permission to update this expense
        // (if you have branch-based restrictions)
        if ($user->branch_id && $pengeluaran->branch_id != $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah data pengeluaran cabang lain'
            ], 403);
        }

        // Validate with Indonesian field names
        $validator = Validator::make($request->all(), [
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|integer|min:1',
        ], [
            'deskripsi.required' => 'Deskripsi pengeluaran wajib diisi',
            'deskripsi.string' => 'Deskripsi harus berupa teks',
            'deskripsi.max' => 'Deskripsi maksimal 255 karakter',
            'nominal.required' => 'Nominal pengeluaran wajib diisi',
            'nominal.integer' => 'Nominal harus berupa angka',
            'nominal.min' => 'Nominal minimal Rp 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Store old values for logging
            $oldValues = [
                'description' => $pengeluaran->description,
                'amount' => $pengeluaran->amount,
            ];

            // Map Indonesian field names to English database columns
            $updateData = [
                'description' => trim($request->deskripsi),  // Map: deskripsi -> description
                'amount' => (int) $request->nominal,         // Map: nominal -> amount
                // Note: branch_id should NOT be updated - it should remain the same
            ];

            // Simple logging without activity package
            \Log::info('Expense updated', [
                'user_id' => $user->id,
                'expense_id' => $pengeluaran->expense_id,
                'old_values' => $oldValues,
                'new_values' => $updateData,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Update the expense
            $pengeluaran->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pengeluaran berhasil diperbarui',
                'data' => [
                    'expense_id' => $pengeluaran->expense_id,
                    'deskripsi' => $pengeluaran->description,    // Return Indonesian field name
                    'nominal' => $pengeluaran->amount,           // Return Indonesian field name
                    'amount_formatted' => 'Rp ' . number_format($pengeluaran->amount, 0, ',', '.'),
                    'tanggal_diperbarui' => $pengeluaran->updated_at->format('d/m/Y H:i:s'),
                    'branch_id' => $pengeluaran->branch_id,
                    'branch_name' => optional($pengeluaran->branch)->name ?? '-',
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating expense: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'expense_id' => $id,
                'branch_id' => $user->branch_id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pengeluaran',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'Expense data not found'
            ], 404);
        }

        try {
            $pengeluaran->delete();
            return response()->json([
                'success' => true,
                'message' => 'Expense data deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get branches for dropdown/select (optional helper method)
     */
    public function getBranches()
    {
        // Assuming you have a Branch model
        $branches = DB::table('branches')
            ->select('id', 'name') // Adjust fields as needed
            ->orderBy('name')
            ->get();
            
        return response()->json($branches);
    }
}