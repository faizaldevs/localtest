<template>
    <Head :title="supplier ? `Payment Report - ${supplier.name}` : 'Payment Report'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ supplier ? `Payment Report - ${supplier.name}` : 'Payment Report' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Date Range Filter -->
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="generateReport" class="flex space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Select Supplier</label>
                            <select 
                                v-model="form.supplier_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select a supplier</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">
                                    {{ s.name }}
                                </option>
                            </select>
                        </div>
                        
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">From Date</label>
                            <input 
                                type="date" 
                                v-model="form.from_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">To Date</label>
                            <input 
                                type="date" 
                                v-model="form.to_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        
                        <div class="flex items-end">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Generate Report
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Show report only if supplier is selected -->
                <template v-if="supplier">
                    <!-- Summary Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Summary</h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="border rounded p-4 bg-white">
                                    <div class="text-sm text-gray-600 mb-1">Remaining Loan Due</div>
                                    <div class="text-xl font-semibold" :class="summary.remainingLoanDue > 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ formatCurrency(summary.remainingLoanDue) }}
                                    </div>
                                </div>                                <div class="border rounded p-4 bg-white">
                                    <div class="text-sm text-gray-600 mb-1">Remaining Payment Due</div>
                                    <div class="text-xl font-semibold" :class="summary.remainingPaymentDue > 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ formatCurrency(summary.remainingPaymentDue) }}
                                        <div class="text-sm text-gray-500 mt-1">
                                            Total Collections: {{ formatCurrency(summary.totalCollections) }}<br>
                                            Total Payments: {{ formatCurrency(summary.totalPayments) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="border rounded p-4 bg-white">
                                    <div class="text-sm text-gray-600 mb-1">Last Payment Details</div>
                                    <div v-if="summary.lastPayment" class="text-xl font-semibold text-gray-900">
                                        {{ formatCurrency(summary.lastPayment.amount) }}
                                        <div class="text-sm text-gray-500 mt-1">
                                            on {{ formatDate(summary.lastPayment.date) }}
                                        </div>
                                    </div>
                                    <div v-else class="text-lg font-medium text-gray-400">
                                        No payments yet
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Transaction History</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Loan</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Loan Repayment</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(transaction, index) in transactions" :key="index">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ formatDate(transaction.date) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ transaction.payment_amount ? formatCurrency(transaction.payment_amount) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ transaction.loan_amount ? formatCurrency(transaction.loan_amount) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ transaction.loan_repayment ? formatCurrency(transaction.loan_repayment) : '-' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ transaction.notes }}
                                            </td>
                                        </tr>
                                        <tr v-if="transactions.length === 0">
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                                No transactions found for the selected period.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Show message when no supplier is selected -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-500 text-center">Select a supplier and date range to generate the report.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    suppliers: {
        type: Array,
        required: true
    },
    supplier: {
        type: Object,
        required: false,
        default: null
    },
    fromDate: {
        type: String,
        required: false,
        default: ''
    },
    toDate: {
        type: String,
        required: false,
        default: ''
    },
    summary: {
        type: Object,
        required: false,
        default: () => ({
            remainingLoanDue: 0,
            remainingPaymentDue: 0,
            lastPayment: null
        })
    },
    transactions: {
        type: Array,
        required: false,
        default: () => []
    }
});

const form = useForm({
    supplier_id: '',
    from_date: '',
    to_date: ''
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const generateReport = () => {
    form.get(route('supplier-reports.show'), {
        preserveState: true
    });
};
</script>
