<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    products: {
        type: Array,
        default: () => []
    }
});

const showDeleteModal = ref(false);
const productToDelete = ref(null);

const confirmDelete = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const deleteForm = useForm({});

const deleteProduct = () => {
    deleteForm.delete(route('products.destroy', productToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            productToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <div class="p-6 flex justify-between items-center border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Products</h2>
            <Link
                :href="route('products.create')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200"
            >
                Create Product
            </Link>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ product.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ product.price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ product.cost }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('products.edit', product.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                                        >Edit</Link>
                                        <button
                                            @click="confirmDelete(product)"
                                            class="text-red-600 hover:text-red-900"
                                        >Delete</button>
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
                    Are you sure you want to delete this product?
                </h2>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="mr-3 px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <DangerButton @click="deleteProduct">Delete Product</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
