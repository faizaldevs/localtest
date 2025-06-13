<template>
    <Head title="Supplier Product Report" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supplier Product Report</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="generateReport" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">                                <div>
                                    <InputLabel value="Supplier" />
                                    <select v-model="form.supplier_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="">Select Supplier</option>
                                        <option v-for="s in suppliers" :key="s.id" :value="s.id">
                                            {{ s.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <InputLabel value="Product" />
                                    <select v-model="form.product_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="">Select Product</option>
                                        <option v-for="p in products" :key="p.id" :value="p.id">
                                            {{ p.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <InputLabel value="From Date" />
                                    <Datepicker v-model="form.from_date" :enable-time-picker="false" class="mt-1" required />
                                </div>

                                <div>
                                    <InputLabel value="To Date" />
                                    <Datepicker v-model="form.to_date" :enable-time-picker="false" class="mt-1" required />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    :disabled="form.processing"
                                >
                                    Generate Report
                                </button>
                            </div>
                        </form>

                        <div v-if="reportData" class="mt-8">
                            <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">General Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Name</p>
                                        <p class="font-medium">{{ reportData.supplier.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Address</p>
                                        <p class="font-medium">{{ reportData.supplier.address }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Mobile</p>
                                        <p class="font-medium">{{ reportData.supplier.phone }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="row in reportData.report_data" :key="row.date" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ row.date }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ row.quantity }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-100">
                                        <tr class="font-semibold">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ reportData.total_quantity }}
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

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    suppliers: Array,
    products: Array,
});

const form = useForm({
    supplier_id: '',
    product_id: '',
    from_date: '',
    to_date: '',
});

const reportData = ref(null);

const generateReport = async () => {
    try {
        const response = await axios.post(route('reports.supplier-product.generate'), {
            supplier_id: form.supplier_id,
            product_id: form.product_id,
            from_date: form.from_date,
            to_date: form.to_date,
        });
        
        if (response.data.success) {
            reportData.value = response.data.data;
        }
    } catch (error) {
        console.error('Error generating report:', error);
    }
};
</script>
