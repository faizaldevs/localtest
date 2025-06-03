<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(amount || 0);
};

const props = defineProps({
    payment: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Staff Payment Details" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Staff Payment Details
                </h2>
                <div class="flex space-x-3">
                    <Link :href="route('staff-payments.edit', payment.id)">
                        <PrimaryButton>
                            Edit Payment
                        </PrimaryButton>
                    </Link>
                    <Link :href="route('staff-payments.index')">
                        <PrimaryButton class="bg-gray-600 hover:bg-gray-700">
                            Back to List
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">                        <!-- Payment Header -->
                        <div class="border-b border-gray-200 pb-6 mb-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        Payment to {{ payment.staff.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Payment Date: {{ new Date(payment.payment_date).toLocaleDateString() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                        Staff Payment
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Period -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-lg font-medium text-gray-900 mb-3">Payment Period</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">From:</span>
                                        <span class="font-medium">{{ new Date(payment.period_from).toLocaleDateString() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">To:</span>
                                        <span class="font-medium">{{ new Date(payment.period_to).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-lg font-medium text-gray-900 mb-3">Staff Information</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Name:</span>
                                        <span class="font-medium">{{ payment.staff.name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Base Salary:</span>
                                        <span class="font-medium">{{ formatCurrency(payment.staff.salary) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Breakdown -->
                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Breakdown</h4>
                            
                            <!-- Earnings -->
                            <div class="bg-green-50 p-4 rounded-lg mb-4">
                                <h5 class="text-md font-medium text-green-800 mb-3">Earnings</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-green-700">Base Amount:</span>
                                        <span class="font-medium">{{ formatCurrency(payment.base_amount) }}</span>
                                    </div>
                                    <div v-if="payment.bonus_amount > 0" class="flex justify-between">
                                        <span class="text-green-700">Bonus Amount:</span>
                                        <span class="font-medium">{{ formatCurrency(payment.bonus_amount) }}</span>
                                    </div>
                                    <div class="border-t border-green-200 pt-2">
                                        <div class="flex justify-between font-semibold">
                                            <span class="text-green-800">Total Gross:</span>
                                            <span>{{ formatCurrency(payment.total_gross_amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions -->
                            <div class="bg-red-50 p-4 rounded-lg mb-4">
                                <h5 class="text-md font-medium text-red-800 mb-3">Deductions</h5>
                                <div class="space-y-2">
                                    <div v-if="payment.loan_deduction > 0" class="flex justify-between">
                                        <span class="text-red-700">Loan Deduction:</span>
                                        <span class="font-medium">{{ formatCurrency(payment.loan_deduction) }}</span>
                                    </div>
                                    <div v-if="payment.discrepancy_deduction > 0" class="flex justify-between">
                                        <span class="text-red-700">Discrepancy Deduction:</span>
                                        <span class="font-medium">{{ formatCurrency(payment.discrepancy_deduction) }}</span>
                                    </div>
                                    <div v-if="payment.other_deductions > 0" class="flex justify-between">
                                        <span class="text-red-700">Other Deductions:</span>
                                        <span class="font-medium">{{ formatCurrency(payment.other_deductions) }}</span>
                                    </div>
                                    <div v-if="payment.total_deductions === 0" class="text-red-700 text-center">
                                        No deductions
                                    </div>
                                    <div v-if="payment.total_deductions > 0" class="border-t border-red-200 pt-2">
                                        <div class="flex justify-between font-semibold">
                                            <span class="text-red-800">Total Deductions:</span>
                                            <span>{{ formatCurrency(payment.total_deductions) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Net Payment -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-xl font-semibold text-blue-800">Net Amount Paid:</span>
                                    <span class="text-2xl font-bold text-blue-900">{{ formatCurrency(payment.net_amount_paid) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="payment.notes" class="mb-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Notes</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ payment.notes }}</p>
                            </div>
                        </div>

                        <!-- Payment Metadata -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Payment Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Created:</span>
                                    <span class="ml-2">{{ new Date(payment.created_at).toLocaleString() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Last Updated:</span>
                                    <span class="ml-2">{{ new Date(payment.updated_at).toLocaleString() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Payment ID:</span>
                                    <span class="ml-2 font-mono">#{{ payment.id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
