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
        // Validate with Indonesian field names (matching the store method)
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'nullable|string',
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
            // Find the member first
            $member = Member::find($id);
            
            if (!$member) {
                return response()->json([
                    'status' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            // Optional: Check if user has permission to update this member
            // (if you have branch-based permissions like in store method)
            if ($member->branch_id != Auth::user()->branch_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to update this member'
                ], 403);
            }

            // Map Indonesian payload fields to English database columns
            $member->name = $request->nama;           // Map: nama -> name
            $member->phone = $request->telepon;       // Map: telepon -> phone
            $member->address = $request->alamat;      // Map: alamat -> address
            
            // Don't update member_code during update (it should remain the same)
            // Don't update branch_id during update (it should remain the same)
            
            $member->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Member updated successfully',
                'data' => $member
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating member: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'member_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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

    // public function cetakMember(Request $request)
    // {
    //     $datamember = collect(array());
    //     foreach ($request->id_member as $id) {
    //         $member = Member::find($id);
    //         $datamember[] = $member;
    //     }

    //     $datamember = $datamember->chunk(2);
    //     $setting    = Setting::first();

    //     $no  = 1;
    //     $pdf = PDF::loadView('member.cetak', compact('datamember', 'no', 'setting'));
    //     $pdf->setPaper(array(0, 0, 566.93, 850.39), 'potrait');
    //     return $pdf->stream('member.pdf');
    // }
    public function cetakMember(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'id_member' => 'required|array|min:1',
        'id_member.*' => 'required|integer|exists:member,member_id',
    ], [
        'id_member.required' => 'Please select at least one member',
        'id_member.array' => 'Invalid member data format',
        'id_member.*.exists' => 'One or more selected members do not exist',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
            'message' => 'Validation failed'
        ], 422);
    }

    try {
        // Fetch all members in a single query for better performance
        $memberIds = $request->id_member;
        
        // Optional: Filter by current branch if needed
        $members = Member::whereIn('member_id', $memberIds)
            ->when(Auth::check() && Auth::user()->branch_id, function ($query) {
                return $query->where('branch_id', Auth::user()->branch_id);
            })
            ->orderByRaw("FIELD(member_id, " . implode(',', $memberIds) . ")")
            ->get();

        // Check if any members were found
        if ($members->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No members found or you do not have permission to access them'
            ], 404);
        }

        // Group members into pairs for printing (2 per page)
        $groupedMembers = $members->chunk(2);
        
        // Get settings with fallback
        $setting = Setting::first();
        
        // If no settings exist, create a default one to prevent errors
        if (!$setting) {
            $setting = new Setting();
            $setting->company_name = config('app.name', 'My POS');
            $setting->company_address = 'Not configured';
            $setting->company_phone = '-';
        }

        // Generate the PDF
        $pdf = PDF::loadView('member.cetak', [
            'groupedMembers' => $groupedMembers,
            'setting' => $setting,
            'totalMembers' => $members->count(),
            'printDate' => now()->format('d/m/Y H:i:s'),
        ]);

        // Configure PDF settings
        $pdf->setPaper([0, 0, 566.93, 850.39], 'portrait'); // Fixed typo: 'potrait' -> 'portrait'
        
        // Set PDF metadata
        $pdf->setOptions([
            'title' => 'Member Cards - ' . $setting->company_name,
            'subject' => 'Member Cards Export',
            'author' => $setting->company_name,
            'creator' => config('app.name', 'Laravel'),
            'keywords' => 'member, cards, export',
        ]);

        // Generate a unique filename
        $filename = 'member-cards-' . date('Ymd-His') . '.pdf';

        // Return the PDF as stream
        return $pdf->stream($filename);

    } catch (\Exception $e) {
        \Log::error('Error generating member PDF: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'member_ids' => $request->id_member ?? [],
            'trace' => $e->getTraceAsString()
        ]);

        // Return error response if it's an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate member cards',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

        // For non-AJAX requests, return a simple error page
        return response()->view('errors.pdf-generation', [
            'message' => 'Failed to generate member cards. Please try again.'
        ], 500);
    }
}
}
