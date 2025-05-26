<template>
    <Head title="Customers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Customers</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Customer List</h3>
                            <Link :href="route('customers.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Add Customer
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-4 py-2">Name</th>
                                        <th class="px-4 py-2">Address</th>
                                        <th class="px-4 py-2">Phone</th>
                                        <th class="px-4 py-2">Staff</th>
                                        <th class="px-4 py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in customers" :key="customer.id" class="border-b">
                                        <td class="px-4 py-2">{{ customer.name }}</td>
                                        <td class="px-4 py-2">{{ customer.address }}</td>
                                        <td class="px-4 py-2">{{ customer.phone }}</td>
                                        <td class="px-4 py-2">{{ customer.staff?.name }}</td>
                                        <td class="px-4 py-2">
                                            <Link :href="route('customers.edit', customer.id)"
                                                class="text-blue-600 hover:text-blue-900 mr-2">Edit</Link>
                                            <button @click="deleteCustomer(customer)"
                                                class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    customers: Array
});

const deleteCustomer = (customer) => {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(route('customers.destroy', customer.id));
    }
};
</script>
