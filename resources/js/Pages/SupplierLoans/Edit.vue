<template>
    <Head title="Edit Supplier Loan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Supplier Loan</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                                <select
                                    id="supplier"
                                    v-model="form.supplier_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select a supplier</option>
                                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                        {{ supplier.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.supplier_id" class="mt-1 text-red-600 text-sm">
                                    {{ form.errors.supplier_id }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                                <input
                                    id="amount"
                                    type="number"
                                    step="0.01"
                                    v-model="form.amount"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="form.errors.amount" class="mt-1 text-red-600 text-sm">
                                    {{ form.errors.amount }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                <input
                                    id="date"
                                    type="date"
                                    v-model="form.date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="form.errors.date" class="mt-1 text-red-600 text-sm">
                                    {{ form.errors.date }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <div v-if="form.errors.notes" class="mt-1 text-red-600 text-sm">
                                    {{ form.errors.notes }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 ml-4"
                                    :disabled="form.processing"
                                >
                                    Update Loan
                                </button>
                            </div>
                        </form>
                    </div>
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
    loan: Object,
    suppliers: Array,
});

const form = useForm({
    supplier_id: props.loan.supplier_id,
    amount: props.loan.amount,
    date: props.loan.date,
    notes: props.loan.notes,
});

const submit = () => {
    form.put(route('supplier-loans.update', props.loan.id));
};
</script>]]>
