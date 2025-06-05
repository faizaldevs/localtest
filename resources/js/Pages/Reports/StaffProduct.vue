<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    staff: Array,
    products: Array
});

const form = useForm({
    staff_id: '',
    product_id: '',
    from_date: '',
    to_date: ''
});

const reportData = ref(null);
const isLoading = ref(false);
const errorMessage = ref('');

const generateReport = () => {
    if (!form.staff_id || !form.product_id || !form.from_date || !form.to_date) {
        errorMessage.value = 'Please fill in all fields.';
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';
    reportData.value = null;

    // Use axios for the API call
    window.axios.post(route('reports.staff-product.generate'), {
        staff_id: form.staff_id,
        product_id: form.product_id,
        from_date: form.from_date,
        to_date: form.to_date
    })    .then(response => {
        console.log('API Response:', response.data); // Debug log
        if (response.data.success) {
            reportData.value = response.data.data;
            console.log('Report Data:', reportData.value); // Debug log
        } else {
            errorMessage.value = 'Failed to generate report.';
        }
    })
    .catch(error => {
        errorMessage.value = error.response?.data?.message || 'An error occurred while generating the report.';
        console.error('Error:', error);
    })
    .finally(() => {
        isLoading.value = false;
    });
};

const clearReport = () => {
    reportData.value = null;
    errorMessage.value = '';
    form.reset();
};

const formatQuantity = (quantity) => {
    // Log the incoming value for debugging
    console.log('Formatting quantity:', quantity, typeof quantity);
    
    // Handle null, undefined, or empty values
    if (quantity === null || quantity === undefined || quantity === '') {
        return '0.000';
    }
    
    // If it's already a string in the right format, return it
    if (typeof quantity === 'string' && quantity.match(/^\d+\.\d{3}$/)) {
        return quantity;
    }
    
    // Convert to number and format
    const num = parseFloat(quantity);
    if (isNaN(num)) {
        console.warn('Invalid quantity value:', quantity);
        return '0.000';
    }
    
    return num.toFixed(3);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <Head title="Staff Product Report" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff Product Report</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Report Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Report</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <!-- Staff Selection -->
                            <div>
                                <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Staff Member
                                </label>
                                <select
                                    id="staff_id"
                                    v-model="form.staff_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">Select Staff</option>
                                    <option v-for="staff in props.staff" :key="staff.id" :value="staff.id">
                                        {{ staff.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Product Selection -->
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Product
                                </label>
                                <select
                                    id="product_id"
                                    v-model="form.product_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">Select Product</option>
                                    <option v-for="product in props.products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- From Date -->
                            <div>
                                <label for="from_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    From Date
                                </label>
                                <input
                                    type="date"
                                    id="from_date"
                                    v-model="form.from_date"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <!-- To Date -->
                            <div>
                                <label for="to_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    To Date
                                </label>
                                <input
                                    type="date"
                                    id="to_date"
                                    v-model="form.to_date"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ errorMessage }}
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <PrimaryButton @click="generateReport" :disabled="isLoading">
                                <span v-if="isLoading">Generating...</span>
                                <span v-else>Generate Report</span>
                            </PrimaryButton>
                            <SecondaryButton @click="clearReport" v-if="reportData">
                                Clear Report
                            </SecondaryButton>
                        </div>
                    </div>
                </div>

                <!-- Report Results -->
                <div v-if="reportData" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Report Header -->
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Staff Product Report</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">Staff:</span> {{ reportData.staff.name }}
                                </div>
                                <div>
                                    <span class="font-medium">Product:</span> {{ reportData.product.name }}
                                </div>
                                <div>
                                    <span class="font-medium">From:</span> {{ reportData.from_date }}
                                </div>
                                <div>
                                    <span class="font-medium">To:</span> {{ reportData.to_date }}
                                </div>
                            </div>
                        </div>

                        <!-- Report Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Collected
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sold
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Transferred
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Received
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="row in reportData.report_data" :key="row.date" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ row.formatted_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ formatQuantity(row.collected) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ formatQuantity(row.sold) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ formatQuantity(row.transferred) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ formatQuantity(row.received) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-100">
                                    <tr class="font-semibold">
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <strong>TOTAL</strong>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                            <strong>{{ formatQuantity(reportData.totals.collected) }}</strong>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                            <strong>{{ formatQuantity(reportData.totals.sold) }}</strong>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                            <strong>{{ formatQuantity(reportData.totals.transferred) }}</strong>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                            <strong>{{ formatQuantity(reportData.totals.received) }}</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Summary Cards -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="text-blue-600 text-sm font-medium">Total Collected</div>
                                <div class="text-blue-900 text-2xl font-bold">{{ formatQuantity(reportData.totals.collected) }}</div>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="text-green-600 text-sm font-medium">Total Sold</div>
                                <div class="text-green-900 text-2xl font-bold">{{ formatQuantity(reportData.totals.sold) }}</div>
                            </div>
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <div class="text-orange-600 text-sm font-medium">Total Transferred</div>
                                <div class="text-orange-900 text-2xl font-bold">{{ formatQuantity(reportData.totals.transferred) }}</div>
                            </div>
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <div class="text-purple-600 text-sm font-medium">Total Received</div>
                                <div class="text-purple-900 text-2xl font-bold">{{ formatQuantity(reportData.totals.received) }}</div>
                            </div>
                        </div>

                        <!-- Sales Summary -->
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Summary by Payment Mode</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Payment Mode
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantity
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="row in reportData.sales_summary.data" :key="row.payment_mode" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ row.payment_mode }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ row.quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ row.amount }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-100">
                                        <tr class="font-semibold">
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <strong>TOTAL</strong>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                                <strong>{{ reportData.sales_summary.totals.quantity }}</strong>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                                <strong>{{ reportData.sales_summary.totals.amount }}</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
