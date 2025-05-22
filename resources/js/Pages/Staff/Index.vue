<template>
    <Head title="Staff" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff Management</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex-1">
                        <Link :href="route('staff.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add New Staff
                        </Link>
                    </div>
                </div>

                <div v-if="flash.message" class="mb-4 font-medium text-sm text-green-600">
                    {{ flash.message }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
                    <tr v-for="member in staff.data" :key="member.id" class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left">{{ member.name }}</td>
                        <td class="py-3 px-6 text-left">{{ member.phone }}</td>
                        <td class="py-3 px-6 text-left">{{ member.location?.name }}</td>
                        <td class="py-3 px-6 text-right">{{ member.salary ? formatCurrency(member.salary) : 'N/A' }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <Link :href="route('staff.show', member.id)" class="text-blue-500 hover:text-blue-700 mx-2">
                                    View
                                </Link>
                                <Link :href="route('staff.edit', member.id)" class="text-yellow-500 hover:text-yellow-700 mx-2">
                                    Edit
                                </Link>
                                <button @click="deleteStaff(member)" class="text-red-500 hover:text-red-700 mx-2">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
                </div>
                <div class="mt-6">
                    <Pagination :links="staff.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    staff: Object,
    flash: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({});

const deleteStaff = (staff) => {
    if (confirm('Are you sure you want to delete this staff member?')) {
        form.delete(route('staff.destroy', staff.id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};
</script>
