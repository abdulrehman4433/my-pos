<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customer.index');
    }

    public function data()
    {
        try {
            $customers = Customer::orderBy('id', 'desc')->get();

            return datatables()
                ->of($customers)
                ->addIndexColumn()

                ->addColumn('customer_code', function ($customer) {
                    return $customer->customer_code ?? '-';
                })

                ->addColumn('current_balance', function ($customer) {
                    return number_format($customer->current_balance, 2);
                })

                ->addColumn('is_active', function ($customer) {
                    return $customer->is_active
                        ? '<span class="label label-success">Active</span>'
                        : '<span class="label label-danger">Inactive</span>';
                })

                ->addColumn('address', function ($customer) {
                    if (!$customer->address) {
                        return '-';
                    }

                    return strlen($customer->address) > 50
                        ? substr($customer->address, 0, 50) . '...'
                        : $customer->address;
                })

                ->addColumn('aksi', function ($customer) {
                    return '
                    <div class="btn-group btn-group-sm">
                        <button type="button"
                            onclick="editForm(\'' . route('customer.update', $customer->id) . '\')"
                            class="btn btn-warning"
                            title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>

                        <button type="button"
                            onclick="deleteData(\'' . route('customer.destroy', $customer->id) . '\')"
                            class="btn btn-danger"
                            title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>';
                })

                ->rawColumns(['is_active', 'aksi'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Error in customer data(): ' . $e->getMessage());

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
        $validator = Validator::make($request->all(), [
            'customer_code'    => 'required|string|max:50|unique:customers,customer_code',
            'name'             => 'required|string|min:2|max:255',
            'phone'            => 'required|string|min:10|max:20',
            'address'          => 'nullable|string|max:500',
            'current_balance'  => 'nullable|numeric|min:0',
            'discount'         => 'nullable|numeric|min:0|max:100',
            'is_active'        => 'required|boolean',
            'notes'            => 'nullable|string|max:500',
        ], [
            'customer_code.required' => 'Customer code is required',
            'customer_code.unique'   => 'Customer code already exists',
            'name.required'          => 'Customer name is required',
            'phone.required'         => 'Phone number is required',
            'discount.max'           => 'Discount cannot exceed 100%',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::create([
                'customer_code'   => trim($request->customer_code),
                'name'            => trim($request->name),
                'phone'           => trim($request->phone),
                'address'         => $request->address ? trim($request->address) : null,
                'current_balance' => $request->current_balance ?? 0,
                'discount'        => $request->discount ?? 0,
                'is_active'       => $request->is_active,
                'notes'           => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Customer has been saved successfully.',
                'data'    => [
                    'id'    => $customer->id,
                    'customer_code'  => $customer->customer_code,
                    'name'           => $customer->name,
                    'phone'          => $customer->phone,
                    'current_balance'=> number_format($customer->current_balance, 2),
                    'discount'       => $customer->discount,
                    'is_active'      => $customer->is_active,
                    'created_at'     => $customer->created_at->format('d/m/Y H:i:s'),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error saving customer', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'A system error has occurred.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
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
        $customer = Customer::find($id);

        return response()->json($customer);
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
        $validator = Validator::make($request->all(), [
            'customer_code'    => 'required|string|max:50|unique:customers,customer_code,' . $id . ',id',
            'name'             => 'required|string|min:2|max:255',
            'phone'            => 'required|string|min:10|max:20',
            'address'          => 'nullable|string|max:500',
            'current_balance'  => 'nullable|numeric|min:0',
            'discount'         => 'nullable|numeric|min:0|max:100',
            'is_active'        => 'required|boolean',
            'notes'            => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::find($id);

            if (! $customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Customer not found',
                ], 404);
            }

            // Check phone uniqueness (if changed)
            if ($customer->phone !== $request->phone) {
                $exists = Customer::where('phone', $request->phone)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'This phone number is already used by another customer',
                    ], 409);
                }
            }

            $customer->update([
                'customer_code'   => trim($request->customer_code),
                'name'            => trim($request->name),
                'phone'           => trim($request->phone),
                'address'         => $request->address ? trim($request->address) : null,
                'current_balance' => $request->current_balance ?? 0,
                'discount'        => $request->discount ?? 0,
                'is_active'       => $request->is_active,
                'notes'           => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Customer updated successfully',
                'data'    => [
                    'id'   => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'name'          => $customer->name,
                    'phone'         => $customer->phone,
                    'address'       => $customer->address,
                    'is_active'     => $customer->is_active,
                    'updated_at'    => $customer->updated_at->format('d/m/Y H:i:s'),
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error updating customer', [
                'user_id'     => Auth::id(),
                'id' => $id,
                'error'       => $e->getMessage(),
                'request'     => $request->all(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'A system error has occurred',
                'error'   => config('app.debug') ? $e->getMessage() : null,
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
        $customer = Customer::find($id)->delete();

        return response(null, 204);
    }
}