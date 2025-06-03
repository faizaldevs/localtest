<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const props = defineProps({
    transfer: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Cash Transfer Details" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Cash Transfer Details
                </h2>
                <div class="space-x-2">
                    <Link :href="route('staff-cash-transfers.edit', transfer.id)">
                        <SecondaryButton>
                            Edit
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('staff-cash-transfers.index')">
                        <SecondaryButton>
                            Back to List
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Transfer ID</label>
                                    <div class="mt-1 text-sm text-gray-900">#{{ transfer.id }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ formatDate(transfer.date) }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Staff</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ transfer.staff.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Location</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ transfer.location.name }}</div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount</label>
                                <div class="mt-1 text-lg font-semibold text-green-600">
                                    {{ formatCurrency(transfer.amount) }}
                                </div>
                            </div>

                            <div v-if="transfer.notes">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <div class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                                    {{ transfer.notes }}
                                </div>
                            </div>

                            <div class="border-t pt-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-gray-500">
                                    <div>
                                        <label class="block font-medium">Created</label>
                                        <div class="mt-1">{{ new Date(transfer.created_at).toLocaleString() }}</div>
                                    </div>
                                    <div>
                                        <label class="block font-medium">Last Updated</label>
                                        <div class="mt-1">{{ new Date(transfer.updated_at).toLocaleString() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
