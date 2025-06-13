<template>
    <Head title="Customer Payment Report" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Customer Payment Report</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Report Form -->
                        <form @submit.prevent="submit" class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <InputLabel for="customer" value="Select Customer" />
                                    <SelectInput
                                        id="customer"
                                        v-model="form.customer_id"
                                        :options="customers"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                </div>
                                <div>
                                    <InputLabel for="from_date" value="From Date" />
                                    <TextInput
                                        id="from_date"
                                        v-model="form.from_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                </div>
                                <div>
                                    <InputLabel for="to_date" value="To Date" />
                                    <TextInput
                                        id="to_date"
                                        v-model="form.to_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                </div>
                                <div class="flex items-end">
                                    <PrimaryButton :disabled="form.processing">Generate Report</PrimaryButton>
                                </div>
                            </div>
                        </form>

                        <!-- Report Content -->
                        <div v-if="summary" class="space-y-6">
                            <!-- Summary Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900">Total Sales</h3>
                                    <p class="mt-2 text-2xl font-semibold text-blue-600">{{ formatCurrency(summary.totalSales) }}</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900">Total Payments</h3>
                                    <p class="mt-2 text-2xl font-semibold text-green-600">{{ formatCurrency(summary.totalPayments) }}</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900">Remaining Due</h3>
                                    <p class="mt-2 text-2xl font-semibold" 
                                       :class="summary.remainingPaymentDue > 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ formatCurrency(summary.remainingPaymentDue) }}
                                    </p>
                                </div>
                                <div v-if="summary.lastPayment" class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900">Last Payment</h3>
                                    <div class="mt-2">
                                        <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.lastPayment.amount) }}</p>
                                        <p class="text-sm text-gray-600">{{ summary.lastPayment.date }} ({{ summary.lastPayment.method }})</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Transactions Table -->
                            <div class="mt-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction History</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Amount</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Amount</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="transaction in transactions" :key="transaction.date + transaction.type" 
                                                :class="transaction.type === 'sale' ? 'bg-gray-50' : ''">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ transaction.date }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <span :class="transaction.type === 'sale' ? 'text-blue-600' : 'text-green-600'">
                                                        {{ transaction.type === 'sale' ? 'Sale' : 'Payment' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ transaction.quantity ? transaction.quantity : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ transaction.price ? formatCurrency(transaction.price) : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {{ transaction.sale_amount ? formatCurrency(transaction.sale_amount) : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {{ transaction.payment_amount ? formatCurrency(transaction.payment_amount) : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ transaction.payment_method || '-' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { formatCurrency } from '@/helpers';

const props = defineProps({
    customers: {
        type: Array,
        required: true
    },
    customer: {
        type: Object,
        default: null
    },
    fromDate: {
        type: String,
        default: null
    },
    toDate: {
        type: String,
        default: null
    },
    summary: {
        type: Object,
        default: null
    },
    transactions: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    customer_id: props.customer?.id ?? '',
    from_date: props.fromDate ?? '',
    to_date: props.toDate ?? ''
});

const submit = () => {
    form.get(route('customer-reports.show'), {
        preserveState: true
    });
};
</script>
