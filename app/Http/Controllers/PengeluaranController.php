<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\Auth; // If using authentication
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    public function index()
    {
        return view('pengeluaran.index');
    }

    public function data()
    {
        $pengeluaran = Pengeluaran::with('branch') // Eager load branch relationship
            ->orderBy('id_pengeluaran', 'desc')
            ->get();

        return datatables()
            ->of($pengeluaran)
            ->addIndexColumn()
            ->addColumn('created_at', function ($pengeluaran) {
                return tanggal_indonesia($pengeluaran->created_at, false);
            })
            ->addColumn('nominal', function ($pengeluaran) {
                return format_uang($pengeluaran->nominal);
            })
            ->addColumn('branch_name', function ($pengeluaran) {
                return $pengeluaran->branch ? $pengeluaran->branch->name : '-'; // Adjust 'name' to your branch field
            })
            ->addColumn('aksi', function ($pengeluaran) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`'. route('pengeluaran.update', $pengeluaran->id_pengeluaran) .'`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`'. route('pengeluaran.destroy', $pengeluaran->id_pengeluaran) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|integer|min:0',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        try {
            $pengeluaran = Pengeluaran::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Expense data saved successfully',
                'data' => $pengeluaran
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save expense data: ' . $e->getMessage()
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
        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'Expense data not found'
            ], 404);
        }

        $validated = $request->validate([
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|integer|min:0',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        try {
            $pengeluaran->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Expense data updated successfully',
                'data' => $pengeluaran
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense data: ' . $e->getMessage()
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