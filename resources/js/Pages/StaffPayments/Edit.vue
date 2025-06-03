<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
    }).format(amount || 0);
};

const props = defineProps({
    payment: {
        type: Object,
        required: true
    },
    staff: {
        type: Array,
        required: true
    }
});

// Form data
const form = ref({
    staff_id: props.payment.staff_id,
    payment_date: new Date(props.payment.payment_date),
    period_from: new Date(props.payment.period_from),
    period_to: new Date(props.payment.period_to),
    base_amount: props.payment.base_amount,
    bonus_amount: props.payment.bonus_amount,
    loan_deduction: props.payment.loan_deduction,
    discrepancy_deduction: props.payment.discrepancy_deduction,
    other_deductions: props.payment.other_deductions,
    notes: props.payment.notes || ''
});

// Staff data from API
const staffData = ref(null);
const loading = ref(false);

// Computed values
const totalGrossAmount = computed(() => {
    return Number(form.value.base_amount || 0) + Number(form.value.bonus_amount || 0);
});

const totalDeductions = computed(() => {
    return Number(form.value.loan_deduction || 0) + 
           Number(form.value.discrepancy_deduction || 0) + 
           Number(form.value.other_deductions || 0);
});

const netAmountPaid = computed(() => {
    return totalGrossAmount.value - totalDeductions.value;
});

const selectedStaff = computed(() => {
    return props.staff.find(s => s.id == form.value.staff_id);
});

// Fetch staff data when component mounts
onMounted(() => {
    fetchStaffData();
});

// Watch for staff selection and date range changes
watch([() => form.value.staff_id, () => form.value.period_from, () => form.value.period_to], () => {
    if (form.value.staff_id && form.value.period_from && form.value.period_to) {
        fetchStaffData();
    }
});

const fetchStaffData = async () => {
    if (!form.value.staff_id || !form.value.period_from || !form.value.period_to) return;
    
    loading.value = true;
    try {
        const formatToLocalDate = (date) => {
            const d = new Date(date);
            return d.getFullYear() + '-' + 
                   String(d.getMonth() + 1).padStart(2, '0') + '-' +
                   String(d.getDate()).padStart(2, '0');
        };

        const response = await axios.get('/staff-payments/get-staff-data', {
            params: {
                staff_id: form.value.staff_id,
                from_date: formatToLocalDate(form.value.period_from),
                to_date: formatToLocalDate(form.value.period_to)
            }
        });

        staffData.value = response.data;
    } catch (error) {
        console.error('Error fetching staff data:', error);
    } finally {
        loading.value = false;
    }
};

const updatePayment = async () => {
    if (!form.value.payment_date) {
        alert('Please select a payment date');
        return;
    }

    if (!form.value.period_from || !form.value.period_to) {
        alert('Please select period dates');
        return;
    }

    try {
        const formatToLocalDate = (date) => {
            const d = new Date(date);
            return d.getFullYear() + '-' + 
                   String(d.getMonth() + 1).padStart(2, '0') + '-' +
                   String(d.getDate()).padStart(2, '0');
        };        const payload = {
            ...form.value,
            payment_date: formatToLocalDate(form.value.payment_date),
            period_from: formatToLocalDate(form.value.period_from),
            period_to: formatToLocalDate(form.value.period_to),
            total_gross_amount: totalGrossAmount.value,
            total_deductions: totalDeductions.value,
            net_amount_paid: netAmountPaid.value
        };

        router.put(`/staff-payments/${props.payment.id}`, payload, {
            onSuccess: () => {
                alert('Payment updated successfully');
                router.visit('/staff-payments');
            },
            onError: (errors) => {
                console.error('Error updating payment:', errors);
                alert('Error updating payment. Please check the form and try again.');
            }
        });
    } catch (error) {
        console.error('Error updating payment:', error);
        alert('An unexpected error occurred. Please try again.');
    }
};
</script>

<template>
    <Head title="Edit Staff Payment" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Staff Payment
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="updatePayment" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="staff" value="Select Staff" />
                                <select
                                    v-model="form.staff_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Select Staff</option>
                                    <option v-for="s in staff" :key="s.id" :value="s.id">
                                        {{ s.name }} (Salary: {{ formatCurrency(s.salary) }})
                                    </option>
                                </select>                            </div>

                            <div>
                                <InputLabel value="Payment Date" />
                                <Datepicker
                                    v-model="form.payment_date"
                                    :enable-time-picker="false"
                                    class="mt-1"
                                    required
                                />
                            </div>

                            <div>
                                <InputLabel value="Period From" />
                                <Datepicker
                                    v-model="form.period_from"
                                    :enable-time-picker="false"
                                    class="mt-1"
                                    required
                                />
                            </div>

                            <div>
                                <InputLabel value="Period To" />
                                <Datepicker
                                    v-model="form.period_to"
                                    :enable-time-picker="false"
                                    class="mt-1"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Staff Information (if loaded) -->
                        <div v-if="staffData && !loading" class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Staff Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Base Salary:</span>
                                    <span class="ml-2">{{ formatCurrency(staffData.base_salary) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Loan Balance:</span>
                                    <span class="ml-2 text-red-600">{{ formatCurrency(staffData.loan_balance) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Pending Discrepancies:</span>
                                    <span class="ml-2 text-orange-600">{{ formatCurrency(staffData.pending_discrepancies) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Loading indicator -->
                        <div v-if="loading" class="text-center py-4">
                            <div class="text-gray-500">Loading staff data...</div>
                        </div>

                        <!-- Payment Amounts -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="base_amount" value="Base Amount" />
                                <TextInput
                                    id="base_amount"
                                    v-model.number="form.base_amount"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    required
                                />
                            </div>

                            <div>
                                <InputLabel for="bonus_amount" value="Bonus Amount" />
                                <TextInput
                                    id="bonus_amount"
                                    v-model.number="form.bonus_amount"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                />
                            </div>

                            <div>
                                <InputLabel for="loan_deduction" value="Loan Deduction" />
                                <TextInput
                                    id="loan_deduction"
                                    v-model.number="form.loan_deduction"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                />
                            </div>

                            <div>
                                <InputLabel for="discrepancy_deduction" value="Discrepancy Deduction" />
                                <TextInput
                                    id="discrepancy_deduction"
                                    v-model.number="form.discrepancy_deduction"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                />
                            </div>

                            <div>
                                <InputLabel for="other_deductions" value="Other Deductions" />
                                <TextInput
                                    id="other_deductions"
                                    v-model.number="form.other_deductions"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                />
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <InputLabel for="notes" value="Notes" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                rows="3"
                            ></textarea>
                        </div>

                        <!-- Payment Summary -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Summary</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-sm text-gray-600">Total Gross Amount</div>
                                    <div class="text-xl font-semibold text-green-600">
                                        {{ formatCurrency(totalGrossAmount) }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm text-gray-600">Total Deductions</div>
                                    <div class="text-xl font-semibold text-red-600">
                                        {{ formatCurrency(totalDeductions) }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm text-gray-600">Net Amount Paid</div>
                                    <div class="text-xl font-semibold text-blue-600">
                                        {{ formatCurrency(netAmountPaid) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <Link :href="route('staff-payments.show', payment.id)">
                                <PrimaryButton type="button" class="bg-gray-600 hover:bg-gray-700">
                                    Cancel
                                </PrimaryButton>
                            </Link>
                            <PrimaryButton type="submit" :disabled="loading">
                                Update Payment
                            </PrimaryButton>
                        </div>
                    </form>
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
