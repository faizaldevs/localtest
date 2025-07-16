<template>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Staff Details</h1>
                <div class="flex space-x-3">
                    <Link :href="route('staff.edit', staff.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('staff.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back
                    </Link>
                </div>
            </div>

            <!-- Staff Basic Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <h3 class="text-gray-600 text-sm font-bold mb-2">Name</h3>
                        <p class="text-gray-900 text-lg">{{ staff.name }}</p>
                    </div>
                    <div>
                        <h3 class="text-gray-600 text-sm font-bold mb-2">Phone</h3>
                        <p class="text-gray-900 text-lg">{{ staff.phone }}</p>
                    </div>
                    <div>
                        <h3 class="text-gray-600 text-sm font-bold mb-2">Location</h3>
                        <p class="text-gray-900 text-lg">{{ staff.location?.name }}</p>
                    </div>
                    <div>
                        <h3 class="text-gray-600 text-sm font-bold mb-2">Salary</h3>
                        <p class="text-gray-900 text-lg">{{ staff.salary ? formatCurrency(staff.salary) : 'N/A' }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-gray-600 text-sm font-bold mb-2">Address</h3>
                    <p class="text-gray-900">{{ staff.address }}</p>
                </div>
            </div>
        </div>

       

        <!-- Detailed Information Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Product Quantities Details -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Product Quantities in Hand</h2>
                </div>
                <div class="p-6">
                    <!-- Summary Stats -->
                    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="text-blue-600 text-xs font-medium">Total Collected</div>
                            <div class="text-blue-900 text-lg font-bold">{{ getTotalCollected() }}</div>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                            <div class="text-purple-600 text-xs font-medium">Total Received</div>
                            <div class="text-purple-900 text-lg font-bold">{{ getTotalReceived() }}</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="text-green-600 text-xs font-medium">Total Sold</div>
                            <div class="text-green-900 text-lg font-bold">{{ getTotalSold() }}</div>
                        </div>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <div class="text-orange-600 text-xs font-medium">Total Sent</div>
                            <div class="text-orange-900 text-lg font-bold">{{ getTotalSent() }}</div>
                        </div>
                    </div>



                    <div v-if="productQuantities.products.length > 0" class="space-y-4">
                        <div v-for="product in productQuantities.products" :key="product.product_id" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ product.product_name }}</h3>
                                    <p class="text-sm text-gray-600">
                                        Collected: {{ product.total_collected }} | 
                                        Received: {{ product.total_received }} | 
                                        Sold: {{ product.total_sold }} | 
                                        Sent: {{ product.total_sent }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-blue-600">{{ product.quantity }}</p>
                                    <p class="text-sm text-gray-500">in hand</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="mt-2">No products currently in hand</p>
                    </div>
                </div>
            </div>

            <!-- Cash and Financial Details -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Financial Summary</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Cash Flow -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-3">Cash Flow</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Cash Sales:</span>
                                <span class="font-medium text-green-600">{{ formatCurrency(cashFromSales.total_cash_sales) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Cash Transferred:</span>
                                <span class="font-medium text-red-600">-{{ formatCurrency(cashFromSales.total_cash_transfers) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="font-medium">Cash in Hand:</span>
                                <span class="font-bold text-green-600">{{ formatCurrency(cashFromSales.cash_in_hand) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Amounts -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-3">Pending Amounts</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Loan Balance:</span>
                                <span class="font-medium text-red-600">{{ formatCurrency(pendingAmounts.loan_balance) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pending Discrepancies:</span>
                                <span class="font-medium text-red-600">{{ formatCurrency(pendingAmounts.pending_discrepancies) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="font-medium">Total Pending:</span>
                                <span class="font-bold text-red-600">{{ formatCurrency(pendingAmounts.total_pending) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loan History -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-3">Loan History</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Loans Given:</span>
                                <span class="font-medium">{{ formatCurrency(pendingAmounts.total_loans_given) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Repayments:</span>
                                <span class="font-medium text-green-600">{{ formatCurrency(pendingAmounts.total_loan_repayments) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="mt-8 bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Activities</h2>
            </div>
            <div class="p-6">
                <div v-if="recentActivities.length > 0" class="space-y-4">
                    <div v-for="activity in recentActivities" :key="`${activity.type}-${activity.date}`" class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="getActivityIconClass(activity.type)">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="activity.type === 'sale'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    <path v-else-if="activity.type === 'cash_transfer'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    <path v-else-if="activity.type === 'product_collected'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
                            <p class="text-sm text-gray-500">{{ formatDate(activity.date) }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <span v-if="activity.amount" class="text-sm font-medium text-gray-900">{{ formatCurrency(activity.amount) }}</span>
                            <span v-if="activity.quantity" class="text-sm font-medium text-gray-900">Qty: {{ activity.quantity }}</span>
                            <span v-if="activity.payment_mode" class="text-xs text-gray-500 block">{{ activity.payment_mode }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="mt-2">No recent activities</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    staff: Object,
    productQuantities: Object,
    cashFromSales: Object,
    pendingAmounts: Object,
    recentActivities: Array,
});

const formatCurrency = (value) => {
    if (!value && value !== 0) return 'N/A';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const netPosition = computed(() => {
    return props.cashFromSales.cash_in_hand - props.pendingAmounts.total_pending;
});

const getTotalCollected = () => {
    const value = props.productQuantities.summary?.total_collected || 0;
    return typeof value === 'number' ? value.toFixed(3) : parseFloat(value || 0).toFixed(3);
};

const getTotalReceived = () => {
    const value = props.productQuantities.summary?.total_received || 0;
    return typeof value === 'number' ? value.toFixed(3) : parseFloat(value || 0).toFixed(3);
};

const getTotalSold = () => {
    const value = props.productQuantities.summary?.total_sold || 0;
    return typeof value === 'number' ? value.toFixed(3) : parseFloat(value || 0).toFixed(3);
};

const getTotalSent = () => {
    const value = props.productQuantities.summary?.total_sent || 0;
    return typeof value === 'number' ? value.toFixed(3) : parseFloat(value || 0).toFixed(3);
};

const getActivityIconClass = (type) => {
    switch (type) {
        case 'sale':
            return 'bg-green-500';
        case 'cash_transfer':
            return 'bg-blue-500';
        case 'product_received':
            return 'bg-purple-500';
        case 'product_collected':
            return 'bg-orange-500';
        default:
            return 'bg-gray-500';
    }
};
</script>
