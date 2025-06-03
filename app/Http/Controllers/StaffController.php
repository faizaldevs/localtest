<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        return Inertia::render('Staff/Index', [
            'staff' => Staff::with('location')
                ->latest()
                ->paginate(10)
                ->through(fn ($staff) => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'phone' => $staff->phone,
                    'address' => $staff->address,
                    'salary' => $staff->salary,
                    'location' => $staff->location ? [
                        'id' => $staff->location->id,
                        'name' => $staff->location->name,
                    ] : null,
                ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/Create', [
            'locations' => Location::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'salary' => 'nullable|numeric|min:0'
        ]);

        Staff::create($validated);

        return redirect()->route('staff.index')
            ->with('message', 'Staff member created successfully.');
    }

    public function show(Staff $staff)
    {
        return Inertia::render('Staff/Show', [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'phone' => $staff->phone,
                'address' => $staff->address,
                'salary' => $staff->salary,
                'created_at' => $staff->created_at->format('F j, Y'),
                'location' => $staff->location ? [
                    'id' => $staff->location->id,
                    'name' => $staff->location->name,
                ] : null,
            ]
        ]);
    }

    public function edit(Staff $staff)
    {
        return Inertia::render('Staff/Edit', [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'phone' => $staff->phone,
                'address' => $staff->address,
                'salary' => $staff->salary,
                'location_id' => $staff->location_id,
            ],
            'locations' => Location::select('id', 'name')->get()
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'salary' => 'nullable|numeric|min:0'
        ]);

        $staff->update($validated);

        return redirect()->route('staff.index')
            ->with('message', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('message', 'Staff member deleted successfully.');
    }

    public function suppliers($staffId)
    {
        try {
            $staff = Staff::findOrFail($staffId);
            $suppliers = $staff->suppliers()
                ->select('id', 'name', 'staff_id')
                ->get();
            
            return response()->json($suppliers);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load suppliers'], 500);
        }
    }
}
