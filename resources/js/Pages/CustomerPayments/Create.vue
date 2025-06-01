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
const customers = ref([]);
const loading = ref(false);

const uniqueDates = computed(() => {
    const dates = new Set();
    customers.value.forEach(customer => {
        if (customer.daily_quantities) {
            Object.keys(customer.daily_quantities).forEach(date => dates.add(date));
        }
    });
    return Array.from(dates).sort();
});

const totals = computed(() => {
    return customers.value.reduce((acc, customer) => ({
        quantity: acc.quantity + Number(customer.total_quantity || 0),
        amount: acc.amount + Number(customer.total_amount || 0),
        previousPayments: acc.previousPayments + Number(customer.total_previous_payments || 0),
        payment: acc.payment + Number(customer.payment_amount || 0)
    }), { quantity: 0, amount: 0, previousPayments: 0, payment: 0 });
});

const fetchCustomerData = async () => {
    if (!selectedStaff.value || !dateRange.value) return;
    
    loading.value = true;
    try {
        // Format dates to maintain local timezone dates
        const fromDate = new Date(dateRange.value[0]);
        const toDate = new Date(dateRange.value[1]);
        
        // Format dates in YYYY-MM-DD format to avoid timezone issues
        const formatToLocalDate = (date) => {
            return date.getFullYear() + '-' + 
                   String(date.getMonth() + 1).padStart(2, '0') + '-' +
                   String(date.getDate()).padStart(2, '0');
        };

        // Fetch both customer data and existing payments for the period
        const [customersResponse, paymentsResponse] = await Promise.all([
            axios.get('/customer-payments/get-customers', {
                params: {
                    staff_id: selectedStaff.value,
                    from_date: formatToLocalDate(fromDate),
                    to_date: formatToLocalDate(toDate)
                }
            }),
            axios.get('/customer-payments/get-existing-payments', {
                params: {
                    staff_id: selectedStaff.value,
                    from_date: formatToLocalDate(fromDate),
                    to_date: formatToLocalDate(toDate)
                }
            })
        ]);

        // Create a map of customer ID to their payment data
        const paymentMap = new Map(
            paymentsResponse.data.map(payment => [payment.customer_id, payment])
        );

        customers.value = customersResponse.data.map(customer => {
            const existingPayment = paymentMap.get(customer.id);
            return {
                ...customer,
                payment_amount: 0,
                payment_method: 'postpaid',
                notes: existingPayment ? existingPayment.notes : '',
                payment_id: null,
                staff_id: selectedStaff.value
            };
        });
    } catch (error) {
        console.error('Error fetching customer data:', error);
    } finally {
        loading.value = false;
    }
};

watch([selectedStaff, dateRange], () => {
    fetchCustomerData();
});

const savePayments = async () => {
    if (!paymentDate.value) {
        alert('Please select a payment date');
        return;
    }

    try {
        // Format dates using the same function to ensure consistency
        const formatToLocalDate = (date) => {
            const d = new Date(date);
            return d.getFullYear() + '-' + 
                   String(d.getMonth() + 1).padStart(2, '0') + '-' +
                   String(d.getDate()).padStart(2, '0');
        };

        await axios.post('/customer-payments/store', {
            payment_date: formatToLocalDate(paymentDate.value),
            customers: customers.value.map(customer => {
                const data = {
                    id: customer.id,
                    staff_id: selectedStaff.value,
                    payment_amount: customer.payment_amount,
                    payment_method: customer.payment_method,
                    notes: customer.notes,
                    payment_id: customer.payment_id || null
                };
                return data;
            }),
            date_range: [
                formatToLocalDate(dateRange.value[0]),
                formatToLocalDate(dateRange.value[1])
            ]
        });
        
        router.visit(window.location.pathname, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alert('Payments saved successfully');
                // Reset form
                customers.value = customers.value.map(c => ({
                    ...c,
                    payment_amount: 0,
                    payment_method: 'postpaid',
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
    <Head title="Create Customer Payments" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Customer Payments
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 min-h-[800px]">
                <h2 class="text-2xl font-semibold mb-6">Create Customer Payments</h2>
                
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

                <div class="overflow-x-auto">
                    <table v-if="customers.length" class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th v-for="date in uniqueDates" :key="date" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ new Date(date).toLocaleDateString() }}
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous Payments</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Amount</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="customer in customers" :key="customer.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ customer.name }}
                                </td>
                                <td v-for="date in uniqueDates" :key="date" class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ customer.daily_quantities[date] || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ customer.total_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ formatCurrency(customer.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ formatCurrency(customer.total_previous_payments || 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <TextInput
                                        v-model="customer.payment_amount"
                                        type="number"
                                        class="w-32"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select
                                        v-model="customer.payment_method"
                                        class="border-gray-300 rounded-md shadow-sm w-32"
                                    >
                                        <option value="cash">Cash</option>
                                        <option value="prepaid">Prepaid</option>
                                        <option value="postpaid">Postpaid</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <TextInput
                                        v-model="customer.notes"
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
                                    {{ customers.reduce((sum, customer) => sum + Number(customer.daily_quantities[date] || 0), 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ totals.quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(totals.amount) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(totals.previousPayments) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(totals.payment) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div v-else-if="loading" class="text-center py-4">
                        Loading...
                    </div>
                    
                    <div v-else class="text-center py-4 text-gray-500">
                        Select a staff member and date range to view customers
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <PrimaryButton @click="savePayments" :disabled="!customers.length">
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
