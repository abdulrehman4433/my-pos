<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('project.index');
    }

    public function data()
    {
        try {
            $projects = Project::orderBy('project_id', 'desc')->get();

            return datatables()
                ->of($projects)
                ->addIndexColumn()

                ->addColumn('DT_RowIndex', function () {
                    static $index = 1;
                    return $index++;
                })

                ->addColumn('project_phone_formatted', function ($project) {
                    if (!$project->project_phone) {
                        return '-';
                    }

                    $phone = preg_replace('/[^0-9]/', '', $project->project_phone);

                    if (strlen($phone) >= 10) {
                        return substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
                    }

                    return $project->project_phone;
                })

                ->addColumn('project_address_short', function ($project) {
                    if (!$project->project_address) {
                        return '-';
                    }

                    return strlen($project->project_address) > 60
                        ? substr($project->project_address, 0, 60) . '...'
                        : $project->project_address;
                })

                ->addColumn('project_price_formatted', function ($project) {
                    return number_format($project->project_price ?? 0, 2);
                })

                ->addColumn('project_status_badge', function ($project) {
                    $status = $project->project_status ?? 'inactive';

                    $badgeClass = match ($status) {
                        'active' => 'label-success',
                        'completed' => 'label-primary',
                        'cancelled' => 'label-danger',
                        default => 'label-default',
                    };

                    return '<span class="label ' . $badgeClass . '">' . ucfirst($status) . '</span>';
                })

                ->addColumn('created_at_formatted', function ($project) {
                    return $project->created_at
                        ? $project->created_at->format('d M Y')
                        : '-';
                })

                ->addColumn('aksi', function ($project) {
                    return '
                        <div class="btn-group btn-group-sm" role="group">
                            <button 
                                type="button"
                                onclick="editForm(\'' . route('project.update', $project->project_id) . '\')"
                                class="btn btn-warning"
                                title="Edit">
                                <i class="fa fa-edit"></i>
                            </button>

                            <button 
                                type="button"
                                onclick="deleteData(\'' . route('project.destroy', $project->project_id) . '\')"
                                class="btn btn-danger"
                                title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    ';
                })

                ->rawColumns(['aksi', 'project_status_badge'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Error loading project data: ' . $e->getMessage());

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
    $request->validate([
        'project_name'     => 'required|string|max:255',
        'project_phone'    => 'required|string|max:20',
        'project_address'  => 'nullable|string',
        'project_price'    => 'nullable|numeric|min:0',
        'project_duration' => 'nullable|string|max:100',
        'project_details'  => 'nullable|string',
        'project_status'  => 'nullable|in:active,on_hold,completed,cancelled',
    ]);

    Project::create([
        'project_code'     => 'PRJ-' . now()->format('His'),
        'project_name'     => $request->project_name,
        'project_phone'    => $request->project_phone,
        'project_address'  => $request->project_address,
        'project_price'    => $request->project_price,
        'project_duration' => $request->project_duration,
        'project_details'  => $request->project_details,
        'project_status'   => $request->project_status ?? 'inactive',
        'created_by'       => Auth::id(),
        'updated_by'       => Auth::id(),
    ]);

    return response()->json([
        'message' => 'Project created successfully'
    ]);
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'project_name'     => 'required|string|max:255',
            'project_phone'    => 'required|string|max:20',
            'project_address'  => 'nullable|string',
            'project_price'    => 'nullable|numeric|min:0',
            'project_duration' => 'nullable|string|max:100',
            'project_details'  => 'nullable|string',
            'project_status'  => 'nullable|in:active,on_hold,completed,cancelled',
        ]);

        $project = Project::findOrFail($id);
        $project->update([
            'project_name'     => $request->project_name,
            'project_phone'    => $request->project_phone,
            'project_address'  => $request->project_address,
            'project_price'    => $request->project_price,
            'project_duration' => $request->project_duration,
            'project_details'  => $request->project_details,
            'project_status'   => $request->project_status ?? $project->project_status,
            'updated_by'       => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Project updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}
