@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Staff Details</h1>
            <div>
                <a href="{{ route('staff.edit', $staff) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                    Edit
                </a>
                <a href="{{ route('staff.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Name</h3>
                <p class="text-gray-900">{{ $staff->name }}</p>
            </div>

            <div class="mb-4">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Phone</h3>
                <p class="text-gray-900">{{ $staff->phone }}</p>
            </div>

            <div class="mb-4 col-span-2">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Address</h3>
                <p class="text-gray-900">{{ $staff->address }}</p>
            </div>

            <div class="mb-4">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Location</h3>
                <p class="text-gray-900">{{ $staff->location->name }}</p>
            </div>

            <div class="mb-4">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Salary</h3>
                <p class="text-gray-900">{{ $staff->salary ? number_format($staff->salary, 2) : 'N/A' }}</p>
            </div>

            <div class="mb-4 col-span-2">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Added On</h3>
                <p class="text-gray-900">{{ $staff->created_at->format('F j, Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
