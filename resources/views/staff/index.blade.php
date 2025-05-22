@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Staff List</h1>
        <a href="{{ route('staff.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add New Staff
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded my-6">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Name</th>
                    <th class="py-3 px-6 text-left">Phone</th>
                    <th class="py-3 px-6 text-left">Location</th>
                    <th class="py-3 px-6 text-right">Salary</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                @foreach($staff as $member)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left">{{ $member->name }}</td>
                        <td class="py-3 px-6 text-left">{{ $member->phone }}</td>
                        <td class="py-3 px-6 text-left">{{ $member->location->name }}</td>
                        <td class="py-3 px-6 text-right">{{ $member->salary ? number_format($member->salary, 2) : 'N/A' }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <a href="{{ route('staff.show', $member) }}" class="text-blue-500 hover:text-blue-700 mx-2">
                                    View
                                </a>
                                <a href="{{ route('staff.edit', $member) }}" class="text-yellow-500 hover:text-yellow-700 mx-2">
                                    Edit
                                </a>
                                <form action="{{ route('staff.destroy', $member) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 mx-2" onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $staff->links() }}
    </div>
</div>
@endsection
