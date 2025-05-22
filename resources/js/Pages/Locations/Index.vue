<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    locations: {
        type: Array,
        default: () => []
    }
});

const showDeleteModal = ref(false);
const locationToDelete = ref(null);

const confirmDelete = (location) => {
    locationToDelete.value = location;
    showDeleteModal.value = true;
};

const deleteForm = useForm({});

const deleteLocation = () => {
    deleteForm.delete(route('locations.destroy', locationToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            locationToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Locations" />

    <AuthenticatedLayout>
        <div class="p-6 flex justify-between items-center border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Locations</h2>
            <Link
                :href="route('locations.create')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200"
            >
                Create Location
            </Link>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="location in locations" :key="location.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ location.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ location.address }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('locations.edit', location.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="confirmDelete(location)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this location?
                </h2>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="mr-3 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>

                    <DangerButton
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                        @click="deleteLocation"
                    >
                        Delete Location
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
