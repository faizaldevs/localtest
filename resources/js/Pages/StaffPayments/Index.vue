<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
};

const props = defineProps({
    payments: {
        type: Object,
        required: true
    }
});

const deletePayment = (id) => {
    if (confirm('Are you sure you want to delete this payment?')) {
        router.delete(route('staff-payments.destroy', id));
    }
};
</script>

<template>
    <Head title="Staff Payments" />    <AuthenticatedLayout>
        <div class="p-6 flex justify-between items-center border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Staff Payments</h2>
            <Link 
                :href="route('staff-payments.create')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200"
            >
                Create New Payment
            </Link>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="payments.data.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Staff
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment Date
                                        </th>                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Period
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Base Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Deductions
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Net Paid
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="payment in payments.data" :key="payment.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ payment.staff.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(payment.payment_date).toLocaleDateString() }}
                                        </td>                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(payment.period_from).toLocaleDateString() }} - 
                                            {{ new Date(payment.period_to).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(payment.total_gross_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(payment.total_deductions) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ formatCurrency(payment.net_amount_paid) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('staff-payments.show', payment.id)" 
                                                      class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </Link>
                                                <Link :href="route('staff-payments.edit', payment.id)" 
                                                      class="text-green-600 hover:text-green-900">
                                                    Edit
                                                </Link>
                                                <button @click="deletePayment(payment.id)" 
                                                        class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="mt-6">
                                <nav class="flex items-center justify-between">
                                    <div class="flex flex-1 justify-between sm:hidden">
                                        <Link v-if="payments.prev_page_url" 
                                              :href="payments.prev_page_url" 
                                              class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                            Previous
                                        </Link>
                                        <Link v-if="payments.next_page_url" 
                                              :href="payments.next_page_url" 
                                              class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                            Next
                                        </Link>
                                    </div>
                                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Showing {{ payments.from }} to {{ payments.to }} of {{ payments.total }} results
                                            </p>
                                        </div>
                                        <div>
                                            <span class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                <Link v-if="payments.prev_page_url" 
                                                      :href="payments.prev_page_url" 
                                                      class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                                                    Previous
                                                </Link>
                                                <Link v-if="payments.next_page_url" 
                                                      :href="payments.next_page_url" 
                                                      class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                                                    Next
                                                </Link>
                                            </span>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                        </div>

                        <div v-else class="text-center py-4">
                            <div class="text-gray-500">
                                No staff payments found.
                            </div>
                            <div class="mt-4">
                                <Link :href="route('staff-payments.create')">
                                    <PrimaryButton>
                                        Create First Payment
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
