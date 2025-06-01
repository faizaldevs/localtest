<template>
    <Head title="Create Customer" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Customer</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="name" v-model="form.name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea id="address" v-model="form.address"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required></textarea>
                                <div v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</div>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="text" id="phone" v-model="form.phone"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required />
                                <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
                            </div>

                            <div>
                                <label for="staff_id" class="block text-sm font-medium text-gray-700">Staff</label>
                                <select id="staff_id" v-model="form.staff_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>
                                    <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
                                        {{ staff.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.staff_id" class="text-red-500 text-sm mt-1">{{ form.errors.staff_id }}</div>
                            </div>

                            <div class="flex items-center justify-end">
                                <Link :href="route('customers.index')"
                                    class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded mr-2">
                                    Cancel
                                </Link>
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Create Customer
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const staffList = ref([]);

const form = useForm({
    name: '',
    address: '',
    phone: '',
    staff_id: ''
});

const submit = () => {
    form.post(route('customers.store'));
};

onMounted(async () => {    const response = await fetch('/staff-list');
    staffList.value = await response.json();
});
</script>
