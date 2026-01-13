<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class RentalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('rental.index');
    }

    public function data()
    {
        try {
            $rentals = Rental::orderBy('rental_id', 'desc')->get();

            return datatables()
                ->of($rentals)
                ->addIndexColumn()

                ->addColumn('DT_RowIndex', function () {
                    static $index = 1;
                    return $index++;
                })

                ->addColumn('phone_formatted', function ($rental) {
                    if (!$rental->rental_person_phone) {
                        return '-';
                    }

                    $phone = preg_replace('/[^0-9]/', '', $rental->rental_person_phone);

                    if (strlen($phone) >= 10) {
                        if (substr($phone, 0, 2) === '08') {
                            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
                        } elseif (substr($phone, 0, 2) === '03') {
                            return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
                        }
                    }

                    return $rental->rental_person_phone;
                })

                ->addColumn('address_short', function ($rental) {
                    if (!$rental->rental_person_address) {
                        return '-';
                    }

                    return strlen($rental->rental_person_address) > 60
                        ? substr($rental->rental_person_address, 0, 60) . '...'
                        : $rental->rental_person_address;
                })

                ->addColumn('price_formatted', function ($rental) {
                    return number_format($rental->rental_price, 2);
                })

                ->addColumn('status', function ($rental) {
                    switch ($rental->rental_status) {
                        case 'returned':
                            return '<span class="badge badge-success">Returned</span>';
                        case 'active':
                            return '<span class="badge badge-warning">Active</span>';
                        case 'cancelled':
                            return '<span class="badge badge-danger">Cancelled</span>';
                        default:
                            return '<span class="badge badge-secondary">Pending</span>';
                    }
                })

                ->addColumn('created_at_formatted', function ($rental) {
                    return $rental->created_at
                        ? $rental->created_at->format('d M Y')
                        : '-';
                })

                ->addColumn('actions', function ($rental) {
                    return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button"
                                onclick="editForm(\'' . route('rental.update', $rental->rental_id) . '\')"
                                class="btn btn-warning"
                                title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>

                        <button type="button"
                                onclick="deleteData(
                                    \'' . route('rental.destroy', $rental->rental_id) . '\',
                                    \'' . addslashes($rental->rental_product) . '\'
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
            \Log::error('Error in rental data method: ' . $e->getMessage());

            return datatables()
                ->of(collect([]))
                ->make(true);
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
        // Validate request
        $validator = Validator::make($request->all(), [
            'rental_product' => 'required|string|max:255',
            'rental_person' => 'required|string|max:255',
            'rental_person_phone' => 'required|string|max:20',
            'rental_person_address' => 'nullable|string|max:500',
            'rental_price' => 'required|numeric|min:0',
            'rental_duration' => 'required|string|max:50',
            'rental_start_date' => 'required|date',
            'rental_end_date' => 'required|date|after_or_equal:rental_start_date',
            'rental_status' => 'required|string|in:pending,ongoing,completed,overdue',
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
            // Check for duplicate phone (optional)
            $existing = Rental::where('rental_person_phone', $request->rental_person_phone)->first();
            if ($existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rental with this phone number already exists.',
                    'data' => [
                        'rental_id' => $existing->rental_id,
                        'rental_person' => $existing->rental_person
                    ]
                ], 409);
            }

            // Auto-generate rental_code: RT-YYYYMMDD-XXX
            $today = date('Ymd');
            $lastCode = Rental::whereDate('created_at', date('Y-m-d'))
                ->orderBy('rental_id', 'desc')
                ->value('rental_code');

            $number = $lastCode ? ((int) substr($lastCode, -3) + 1) : 1;
            $rentalCode = 'RT-' . $today . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);

            // Prepare data for insert
            $data = $request->only([
                'rental_product',
                'rental_person',
                'rental_person_phone',
                'rental_person_address',
                'rental_price',
                'rental_duration',
                'rental_start_date',
                'rental_end_date',
                'rental_status',
            ]);
            $data['rental_code'] = $rentalCode;
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            // Create rental
            $rental = Rental::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Rental saved successfully.',
                'data' => $rental
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error saving rental: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);

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
        $rental = Rental::findOrFail($id);
        return response()->json($rental);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rental $rental)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rental = Rental::findOrFail($id);
        $rental->update($request->all());
        return response()->json($rental);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rental = Rental::findOrFail($id);
        $rental->delete();
        return response()->json(null, 204);
    }
}
