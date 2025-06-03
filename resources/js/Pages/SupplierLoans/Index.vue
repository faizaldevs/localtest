<template>
    <Head title="Supplier Loans" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supplier Loans</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex-1">
                        <Link 
                            :href="route('supplier-loans.create')" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Add Loan
                        </Link>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="loan in loans.data" :key="loan.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(loan.date) }}</td>
                                    <td class="px-6 py-4">{{ loan.supplier.name }}</td>
                                    <td class="px-6 py-4">{{ formatCurrency(loan.amount) }}</td>
                                    <td class="px-6 py-4">{{ loan.notes }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link 
                                            :href="route('supplier-loans.edit', loan.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteLoan(loan)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="loans.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No loans found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <Pagination :links="loans.links" class="mt-6" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    loans: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
};

const deleteLoan = (loan) => {
    if (confirm('Are you sure you want to delete this loan?')) {
        router.delete(route('supplier-loans.destroy', loan.id));
    }
};
</script>
