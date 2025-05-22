<template>
  <AdminLayout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <h1 class="text-3xl font-bold">Suppliers</h1>
      <Link :href="route('suppliers.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
        Add Supplier
      </Link>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="supplier in suppliers.data" :key="supplier.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ supplier.name }}</td>
            <td class="px-6 py-4">{{ supplier.address }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ supplier.phone }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ supplier.staff.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <Link :href="route('suppliers.edit', supplier.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">
                Edit
              </Link>
              <button @click="deleteSupplier(supplier.id)" class="text-red-600 hover:text-red-900">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination :links="suppliers.links" class="mt-6" />
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  suppliers: Object,
});

const deleteSupplier = (id) => {
  if (confirm('Are you sure you want to delete this supplier?')) {
    router.delete(route('suppliers.destroy', id));
  }
};
</script>
