<template>
  <Head title="Product Transfers" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Product Transfers</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <Link
            :href="route('product-transfers.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            New Transfer
          </Link>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transfer in transfers.data" :key="transfer.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ transfer.date || new Date(transfer.created_at).toLocaleDateString() }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ transfer.product.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ transfer.from_staff ? transfer.from_staff.name : 'N/A' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ transfer.to_staff ? transfer.to_staff.name : transfer.location ? transfer.location.name : 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ transfer.quantity }}</td>
                  <td class="px-6 py-4 whitespace-nowrap space-x-2">
                    <Link
                      :href="route('product-transfers.show', transfer.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      View
                    </Link>
                    <Link
                      :href="route('product-transfers.edit', transfer.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Edit
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <Pagination :links="transfers.links" class="mt-6" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
  transfers: Object,
});
</script>
