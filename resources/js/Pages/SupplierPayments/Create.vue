<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

// Add custom styles to fix datepicker
const style = document.createElement('style');
style.textContent = `
.dp__outer_menu_wrap {
    z-index: 50 !important;
}
.dp__menu {
    z-index: 50 !important;
}
`;
document.head.appendChild(style);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
};

const props = defineProps({
    staff: {
        type: Array,
        required: true
    }
});

const selectedStaff = ref(null);
const dateRange = ref(null);
const paymentDate = ref(null);
const suppliers = ref([]);
const loading = ref(false);

const uniqueDates = computed(() => {
    const dates = new Set();
    suppliers.value.forEach(supplier => {
        if (supplier.daily_quantities) {
            Object.keys(supplier.daily_quantities).forEach(date => dates.add(date));
        }
    });
    return Array.from(dates).sort();
});

const totals = computed(() => {
    return suppliers.value.reduce((acc, supplier) => ({
        quantity: acc.quantity + Number(supplier.total_quantity || 0),
        amount: acc.amount + Number(supplier.total_amount || 0),
        payment: acc.payment + Number(supplier.payment_amount || 0),
        deduction: acc.deduction + Number(supplier.loan_deduction || 0)
    }), { quantity: 0, amount: 0, payment: 0, deduction: 0 });
});

const fetchSupplierData = async () => {
    if (!selectedStaff.value || !dateRange.value) return;
    
    loading.value = true;
    try {
        // Format dates to ensure they include the full day range
        const fromDate = new Date(dateRange.value[0]);
        fromDate.setHours(0, 0, 0, 0);
        
        const toDate = new Date(dateRange.value[1]);
        toDate.setHours(23, 59, 59, 999);
        
        const response = await axios.get('/api/supplier-payments/get-suppliers', {
            params: {
                staff_id: selectedStaff.value,
                from_date: fromDate.toISOString(),
                to_date: toDate.toISOString()
            }
        });
        console.log('Supplier data from API:', response.data);  // Added debug log
        suppliers.value = response.data.map(supplier => {
            console.log('Processing supplier:', supplier.name, 'total_amount:', supplier.total_amount); // Added debug log
            return {
                ...supplier,
                payment_amount: 0,
                loan_deduction: 0,
                notes: ''
            };
        });
    } catch (error) {
        console.error('Error fetching supplier data:', error);
    } finally {
        loading.value = false;
    }
};

watch([selectedStaff, dateRange], () => {
    fetchSupplierData();
});

const savePayments = async () => {
    if (!paymentDate.value) {
        alert('Please select a payment date');
        return;
    }

    try {
        await axios.post('/api/supplier-payments/store', {
            payment_date: paymentDate.value,
            suppliers: suppliers.value,
            date_range: dateRange.value
        });
        
        router.visit(window.location.pathname, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alert('Payments saved successfully');
                // Reset form
                suppliers.value = suppliers.value.map(s => ({
                    ...s,
                    payment_amount: 0,
                    loan_deduction: 0,
                    notes: ''
                }));
            }
        });
    } catch (error) {
        console.error('Error saving payments:', error);
        alert('Error saving payments. Please try again.');
    }
};
</script>

<template>
    <Head title="Create Supplier Payments" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Supplier Payments
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 min-h-[800px]">
                <h2 class="text-2xl font-semibold mb-6">Create Supplier Payments</h2>
                
                <div class="grid grid-cols-3 gap-6 mb-6">
                    <div>
                        <InputLabel for="staff" value="Select Staff" />
                        <select
                            v-model="selectedStaff"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="">Select Staff</option>
                            <option v-for="s in staff" :key="s.id" :value="s.id">
                                {{ s.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <InputLabel value="Date Range" />
                        <Datepicker
                            v-model="dateRange"
                            range
                            :enable-time-picker="false"
                            class="mt-1"
                        />
                    </div>
                    
                    <div>
                        <InputLabel value="Payment Date" />
                        <Datepicker
                            v-model="paymentDate"
                            :enable-time-picker="false"
                            class="mt-1"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">                    <table v-if="suppliers.length" class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th v-for="date in uniqueDates" :key="date" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ new Date(date).toLocaleDateString() }}
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Amount</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Deduction</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="supplier in suppliers" :key="supplier.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ supplier.name }}
                                </td>
                                <td v-for="date in uniqueDates" :key="date" class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ supplier.daily_quantities[date] || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ supplier.total_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ formatCurrency(supplier.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <TextInput
                                        v-model="supplier.payment_amount"
                                        type="number"
                                        class="w-32"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <TextInput
                                        v-model="supplier.loan_deduction"
                                        type="number"
                                        class="w-32"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <TextInput
                                        v-model="supplier.notes"
                                        type="text"
                                        class="w-48"
                                    />
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-6 py-4 whitespace-nowrap">Totals</td>
                                <td v-for="date in uniqueDates" :key="date" class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ suppliers.reduce((sum, supplier) => sum + Number(supplier.daily_quantities[date] || 0), 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ totals.quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(totals.amount) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(totals.payment) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(totals.deduction) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div v-else-if="loading" class="text-center py-4">
                        Loading...
                    </div>
                    
                    <div v-else class="text-center py-4 text-gray-500">
                        Select a staff member and date range to view suppliers
                    </div>
                </div>                <div class="mt-6 flex justify-end">
                    <PrimaryButton @click="savePayments" :disabled="!suppliers.length">
                        Save Payments
                    </PrimaryButton>
                </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.dp__input {
    background-color: white !important;
    border-color: #d1d5db !important;
    border-radius: 0.375rem !important;
    min-height: 38px !important;
}

.dp__input:focus {
    box-shadow: 0 0 0 2px #e5e7eb !important;
    outline: 2px solid transparent !important;
    outline-offset: 2px !important;
    border-color: #9ca3af !important;
}

.dp__main {
    width: 100% !important;
}
</style>
