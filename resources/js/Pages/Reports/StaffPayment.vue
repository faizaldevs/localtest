<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { formatCurrency } from '@/helpers.js';

const props = defineProps({
    staff: {
        type: Array,
        required: true
    }
});

const form = useForm({
    staff_id: '',
    from_date: '',
    to_date: ''
});

const isLoading = ref(false);
const reportData = ref(null);

const generateReport = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post(route('reports.staff-payment.generate'), form.data());
        reportData.value = response.data.data;
    } catch (error) {
        console.error('Error generating report:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head title="Staff Payment Report" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff Payment Report</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Report Form -->
                        <form @submit.prevent="generateReport" class="max-w-xl space-y-6">                            <div>
                                <InputLabel for="staff_id" value="Staff" />
                                <SelectInput
                                    id="staff_id"
                                    v-model="form.staff_id"
                                    :options="staff"
                                    placeholder="Select Staff Member"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.staff_id" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="from_date" value="From Date" />
                                    <TextInput
                                        id="from_date"
                                        type="date"
                                        v-model="form.from_date"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.from_date" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="to_date" value="To Date" />
                                    <TextInput
                                        id="to_date"
                                        type="date"
                                        v-model="form.to_date"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.to_date" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <PrimaryButton :disabled="isLoading">
                                    {{ isLoading ? 'Generating...' : 'Generate Report' }}
                                </PrimaryButton>
                            </div>
                        </form>

                        <!-- Report Display -->
                        <div v-if="reportData" class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">
                                Report for {{ reportData.staff.name }} ({{ reportData.from_date }} - {{ reportData.to_date }})
                            </h3>

                            <!-- Section 1: Summary -->
                            <div class="mb-6 bg-gray-50 p-4 rounded">
                                <h4 class="font-semibold mb-3">Summary</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Total Loan Dues:</p>
                                        <p class="font-medium">{{ formatCurrency(reportData.section_one.loan_dues) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Undeducted Discrepancies:</p>
                                        <p class="font-medium">{{ formatCurrency(reportData.section_one.undeducted_discrepancies) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Payment Details -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Base Amount</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Bonus Amount</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Deduction</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discrepancy Deduction</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="payment in reportData.section_two.payments" :key="payment.date">
                                            <td class="px-4 py-2 whitespace-nowrap">{{ payment.date }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatCurrency(payment.base_amount) }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatCurrency(payment.bonus_amount) }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatCurrency(payment.loan_deduction) }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatCurrency(payment.deducted_discrepancy) }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatCurrency(payment.net_amount_paid) }}</td>
                                            <td class="px-4 py-2">{{ payment.note }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
