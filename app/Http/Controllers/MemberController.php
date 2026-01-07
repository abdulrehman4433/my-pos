<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PDF;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('member.index');
    }

    public function data()
    {
        $member = Member::where('branch_id', Auth::user()->branch_id) // Filter by branch
            ->orderBy('member_code')
            ->get();

        return datatables()
            ->of($member)
            ->addIndexColumn()
            ->addColumn('select_all', function ($member) {
                return '
                    <input type="checkbox" name="member_id[]" value="'. $member->member_id .'" class="select-checkbox">
                ';
            })
            ->addColumn('member_code', function ($member) {
                return '<span class="badge bg-success">'. e($member->member_code) .'</span>';
            })
            ->addColumn('name', function ($member) {
                return e($member->name);
            })
            ->addColumn('address', function ($member) {
                return $member->address ? e($member->address) : '<span class="text-muted">-</span>';
            })
            ->addColumn('phone', function ($member) {
                return e($member->phone);
            })
            ->addColumn('created_at', function ($member) {
                return $member->created_at ? $member->created_at->format('d/m/Y') : '-';
            })
            ->addColumn('action', function ($member) {
                return '
                <div class="btn-group btn-group-sm">
                    <button type="button" onclick="editForm(`'. route('member.update', $member->member_id) .'`)" 
                            class="btn btn-primary btn-flat" title="Edit">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" onclick="deleteData(`'. route('member.destroy', $member->member_id) .'`)" 
                            class="btn btn-danger btn-flat" title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                ';
            })
            ->rawColumns(['action', 'select_all', 'member_code', 'address'])
            ->make(true);
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
        'nama' => 'required|string|max:255',      // Changed from 'name'
        'telepon' => 'required|string|max:20',    // Changed from 'phone'
        'alamat' => 'nullable|string',            // Changed from 'address'
    ], [
        'nama.required' => 'Member name is required',
        'telepon.required' => 'Phone number is required',
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
        // Get the latest member for code generation
        $lastMember = Member::where('branch_id', Auth::user()->branch_id)
            ->orderBy('member_id', 'desc')
            ->first();
        
        // Generate member code
        $memberCode = 'M' . date('Ymd') . '-';
        
        if ($lastMember && $lastMember->member_code) {
            // Extract number from existing code
            if (preg_match('/-(\d+)$/', $lastMember->member_code, $matches)) {
                $number = (int)$matches[1] + 1;
                $memberCode .= str_pad($number, 4, '0', STR_PAD_LEFT);
            } else {
                $memberCode .= '0001';
            }
        } else {
            $memberCode .= '0001';
        }

        // Map Indonesian payload fields to English database columns
        $member = new Member();
        $member->member_code = $memberCode;
        $member->name = $request->nama;           // Map: nama -> name
        $member->phone = $request->telepon;       // Map: telepon -> phone
        $member->address = $request->alamat;      // Map: alamat -> address
        $member->branch_id = Auth::user()->branch_id;
        $member->save();

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Member saved successfully',
            'data' => $member
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('Error saving member: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
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
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $member = Member::find($id);

        return response()->json($member);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $member = Member::find($id)->update($request->all());

        return response()->json('Data saved successfully', 200);
    }
    // visit "codeastro" for more projects!
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $member = Member::find($id);
        $member->delete();

        return response(null, 204);
    }

    public function cetakMember(Request $request)
    {
        $datamember = collect(array());
        foreach ($request->id_member as $id) {
            $member = Member::find($id);
            $datamember[] = $member;
        }

        $datamember = $datamember->chunk(2);
        $setting    = Setting::first();

        $no  = 1;
        $pdf = PDF::loadView('member.cetak', compact('datamember', 'no', 'setting'));
        $pdf->setPaper(array(0, 0, 566.93, 850.39), 'potrait');
        return $pdf->stream('member.pdf');
    }
}
