<template>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Edit Staff Member</h1>

            <form @submit.prevent="form.put(route('staff.update', staff.id))">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input v-model="form.name" type="text" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <div v-if="form.errors.name" class="text-red-500 text-xs italic">{{ form.errors.name }}</div>
                </div>

                <div class="mb-4">
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address</label>
                    <textarea v-model="form.address" id="address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                    <div v-if="form.errors.address" class="text-red-500 text-xs italic">{{ form.errors.address }}</div>
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                    <input v-model="form.phone" type="text" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <div v-if="form.errors.phone" class="text-red-500 text-xs italic">{{ form.errors.phone }}</div>
                </div>

                <div class="mb-4">
                    <label for="location_id" class="block text-gray-700 text-sm font-bold mb-2">Location</label>
                    <select v-model="form.location_id" id="location_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">Select Location</option>
                        <option v-for="location in locations" :key="location.id" :value="location.id">
                            {{ location.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.location_id" class="text-red-500 text-xs italic">{{ form.errors.location_id }}</div>
                </div>

                <div class="mb-6">
                    <label for="salary" class="block text-gray-700 text-sm font-bold mb-2">Salary</label>
                    <input v-model="form.salary" type="number" id="salary" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <div v-if="form.errors.salary" class="text-red-500 text-xs italic">{{ form.errors.salary }}</div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" :disabled="form.processing">
                        Update Staff
                    </button>
                    <Link :href="route('staff.index')" class="text-gray-600 hover:text-gray-800">Cancel</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    staff: Object,
    locations: Array,
});

const form = useForm({
    name: props.staff.name,
    address: props.staff.address,
    phone: props.staff.phone,
    location_id: props.staff.location_id,
    salary: props.staff.salary,
});
</script>
