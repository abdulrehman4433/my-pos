<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('maintenance.index');
    }

    public function data()
    {
        try {
            $maintenances = Maintenance::orderBy('maintenance_id', 'desc')->get();

            return datatables()
                ->of($maintenances)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function () {
                    static $index = 1;
                    return $index++;
                })
                ->addColumn('phone_formatted', function ($maintenance) {
                    $phone = preg_replace('/[^0-9]/', '', $maintenance->maintenance_phone ?? '');
                    if (strlen($phone) >= 10) {
                        if (substr($phone, 0, 2) === '08') {
                            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
                        } elseif (substr($phone, 0, 2) === '03') {
                            return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
                        }
                    }
                    return $maintenance->maintenance_phone;
                })
                ->addColumn('address_short', function ($maintenance) {
                    if (!$maintenance->maintenance_address) return '-';
                    return strlen($maintenance->maintenance_address) > 60
                        ? substr($maintenance->maintenance_address, 0, 60) . '...'
                        : $maintenance->maintenance_address;
                })
                ->addColumn('price_formatted', function ($maintenance) {
                    return number_format($maintenance->maintenance_price, 2);
                })
                ->addColumn('status', function ($maintenance) {
                    return $maintenance->is_active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                })
                ->addColumn('created_at_formatted', function ($maintenance) {
                    return $maintenance->created_at
                        ? $maintenance->created_at->format('d M Y')
                        : '-';
                })
                ->addColumn('actions', function ($maintenance) {
                    return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button"
                                onclick="editForm(\'' . route('maintenance.update', $maintenance->maintenance_id) . '\')"
                                class="btn btn-warning"
                                title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>

                        <button type="button"
                                onclick="deleteData(
                                    \'' . route('maintenance.destroy', $maintenance->maintenance_id) . '\',
                                    \'' . addslashes($maintenance->maintenance_name) . '\'
                                )"
                                class="btn btn-danger"
                                title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Error in maintenance data method: ' . $e->getMessage());
            return datatables()->of(collect([]))->make(true);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request directly using DB column names
        $validator = Validator::make($request->all(), [
            'maintenance_name' => 'required|string|max:255|min:2',
            'maintenance_phone' => 'required|string|max:20|min:10',
            'maintenance_address' => 'nullable|string|max:500',
            'maintenance_price' => 'required|numeric|min:0',
            'maintenance_duration' => 'required|string|max:50',
            'maintenance_details' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
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
            $data = $request->only([
                'maintenance_name',
                'maintenance_phone',
                'maintenance_address',
                'maintenance_price',
                'maintenance_duration',
                'maintenance_details',
                'is_active'
            ]);
            $data['created_by'] = Auth::id();

            // Optional: prevent duplicate phone
            $existing = Maintenance::where('maintenance_phone', $data['maintenance_phone'])->first();
            if ($existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'Maintenance with this phone number already exists.',
                    'data' => [
                        'maintenance_id' => $existing->maintenance_id,
                        'maintenance_name' => $existing->maintenance_name,
                    ]
                ], 409);
            }

            // -------------------------------
            // AUTO-GENERATE maintenance_code
            // Example: MT-YYYYMMDD-001
            // -------------------------------
            $today = date('Ymd');
            $lastCode = Maintenance::whereDate('created_at', date('Y-m-d'))
                ->orderBy('maintenance_id', 'desc')
                ->value('maintenance_code');

            if ($lastCode) {
                $number = (int) substr($lastCode, -3) + 1;
            } else {
                $number = 1;
            }

            $data['maintenance_code'] = 'MT-' . $today . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);

            // Create record
            $maintenance = Maintenance::create($data);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Maintenance saved successfully.',
                'data' => $maintenance
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving maintenance: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => 'System error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        return response()->json($maintenance);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Maintenance $maintenance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'maintenance_name' => 'required|string|max:255|min:2',
            'maintenance_phone' => 'required|string|max:20|min:10',
            'maintenance_address' => 'nullable|string|max:500',
            'maintenance_price' => 'required|numeric|min:0',
            'maintenance_duration' => 'required|string|max:50',
            'maintenance_details' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
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
            $maintenance = Maintenance::where('maintenance_id', $id)->firstOrFail();

            // Optional: prevent duplicate phone (except self)
            $duplicatePhone = Maintenance::where('maintenance_phone', $request->maintenance_phone)
                ->where('maintenance_id', '!=', $id)
                ->exists();

            if ($duplicatePhone) {
                return response()->json([
                    'status' => false,
                    'message' => 'Maintenance with this phone number already exists.'
                ], 409);
            }

            // Update data (maintenance_code NOT touched)
            $maintenance->update([
                'maintenance_name' => trim($request->maintenance_name),
                'maintenance_phone' => trim($request->maintenance_phone),
                'maintenance_address' => $request->maintenance_address ? trim($request->maintenance_address) : null,
                'maintenance_price' => $request->maintenance_price,
                'maintenance_duration' => trim($request->maintenance_duration),
                'maintenance_details' => $request->maintenance_details ? trim($request->maintenance_details) : null,
                'is_active' => $request->is_active,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Maintenance updated successfully.',
                'data' => $maintenance
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error updating maintenance: ' . $e->getMessage(), [
                'maintenance_id' => $id,
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $maintenance = Maintenance::where('maintenance_id', $id)->firstOrFail();

            $maintenance->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Maintenance deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error deleting maintenance: ' . $e->getMessage(), [
                'maintenance_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to delete maintenance.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}
