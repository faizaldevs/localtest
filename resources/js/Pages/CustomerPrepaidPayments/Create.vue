<script setup>
import { ref, watch } from 'vue';
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
    z-index: 9999 !important;
}
.dp__menu {
    z-index: 9999 !important;
}
.dp__overlay {
    z-index: 9998 !important;
}
.dp__calendar_header {
    z-index: 9999 !important;
}
.dp__calendar {
    z-index: 9999 !important;
}
`;
document.head.appendChild(style);

const props = defineProps({
    staff: {
        type: Array,
        required: true
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
};

const selectedStaff = ref(null);
const paymentDate = ref(null);
const staffCustomers = ref([]);
const customerPayments = ref({});

const loadCustomers = async () => {
    if (!selectedStaff.value || !paymentDate.value) {
        staffCustomers.value = [];
        return;
    }

    const formatToLocalDate = (date) => {
        const d = new Date(date);
        return d.getFullYear() + '-' + 
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0');
    };      try {
        // Load customers and their existing prepaid payments if any
        const response = await fetch(`/api/staff/${selectedStaff.value}/customers-with-prepaid?date=${formatToLocalDate(paymentDate.value)}`);
        const data = await response.json();
        staffCustomers.value = data;
        
        // Initialize payment amounts for each customer with their existing prepaid amount
        data.forEach(customer => {
            customerPayments.value[customer.id] = customer.prepaid_amount?.toString() || '';
        });
    } catch (error) {
        console.error('Error loading customers:', error);
        alert('Error loading customers. Please try again.');
    }
};

watch(selectedStaff, loadCustomers);
watch(paymentDate, loadCustomers);

const savePayment = async () => {
    if (!paymentDate.value) {
        alert('Please select a payment date');
        return;
    }

    if (!selectedStaff.value) {
        alert('Please select a staff member');
        return;
    }

    // Filter out customers with no payment or invalid payment
    const customersWithPayments = Object.entries(customerPayments.value)
        .filter(([_, amount]) => amount && parseFloat(amount) > 0)
        .map(([customerId, amount]) => ({
            id: parseInt(customerId),
            staff_id: selectedStaff.value,
            payment_amount: parseFloat(amount),
            payment_method: 'prepaid'
        }));

    if (customersWithPayments.length === 0) {
        alert('Please enter at least one valid payment amount');
        return;
    }

    try {
        const formatToLocalDate = (date) => {
            const d = new Date(date);
            return d.getFullYear() + '-' + 
                   String(d.getMonth() + 1).padStart(2, '0') + '-' +
                   String(d.getDate()).padStart(2, '0');
        };        await router.post('/api/customer-prepaid-payments/store', {
            payment_date: formatToLocalDate(paymentDate.value),
            customers: customersWithPayments
        });

        // Form will be reset automatically by Inertia refresh
    } catch (error) {
        console.error('Error saving prepaid payment:', error);
        alert('Error saving prepaid payment. Please try again.');
    }
};
</script>

<template>
    <Head title="Record Prepaid Payment" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Record Prepaid Payment
            </h2>
        </template>

        <div class="py-12">            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 " style="min-height: 500px;">
                    <h2 class="text-2xl font-semibold mb-6">Record Customer Prepaid Payment</h2>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                            <InputLabel value="Payment Date" />
                            <Datepicker
                                v-model="paymentDate"
                                :enable-time-picker="false"
                                class="mt-1"
                            />
                        </div>
                    </div>

                    <!-- Customer List with Payment Inputs -->
                    <div v-if="staffCustomers.length > 0" class="mt-6">
                        <h3 class="text-lg font-medium mb-4">Customer Payments</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left py-2">Customer Name</th>
                                        <th class="text-right py-2">Payment Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in staffCustomers" :key="customer.id" class="border-t">
                                        <td class="py-2">{{ customer.name }}</td>
                                        <td class="py-2">
                                            <TextInput
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                v-model="customerPayments[customer.id]"
                                                class="w-full text-right"
                                                placeholder="0.00"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="staffCustomers.length > 0" class="border-t">
                                    <tr>
                                        <td class="py-2 font-medium">Total</td>
                                        <td class="py-2 text-right font-medium">
                                            {{ formatCurrency(Object.values(customerPayments).reduce((sum, amount) => sum + (parseFloat(amount) || 0), 0)) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <PrimaryButton 
                            @click="savePayment" 
                            :disabled="!selectedStaff || !paymentDate || Object.values(customerPayments).every(amount => !amount || parseFloat(amount) <= 0)"
                        >
                            Record Prepaid Payments
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
