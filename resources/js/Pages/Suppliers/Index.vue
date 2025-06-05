<template>
  <Head title="Suppliers" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supplier Management</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <div class="flex-1 flex items-center space-x-4">
            <Link :href="route('suppliers.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
              Add Supplier
            </Link>
            
            <!-- Staff Filter -->
            <div class="min-w-0 flex-1 max-w-xs">
              <label for="staff-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Staff</label>
              <select
                id="staff-filter"
                v-model="staffFilter"
                @change="filterByStaff"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All Staff</option>
                <option v-for="staffMember in staff" :key="staffMember.id" :value="staffMember.id">
                  {{ staffMember.name }}
                </option>
              </select>
            </div>
            
            <!-- Clear Filter Button -->
            <button
              v-if="staffFilter"
              @click="clearFilter"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Clear Filter
            </button>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <!-- Filter Status -->
          <div v-if="staffFilter" class="bg-blue-50 border-b border-blue-200 px-6 py-3">
            <p class="text-sm text-blue-700">
              Showing suppliers for: <span class="font-semibold">{{ getStaffName(staffFilter) }}</span>
              ({{ suppliers.data.length }} supplier{{ suppliers.data.length !== 1 ? 's' : '' }})
            </p>
          </div>
          
          <div class="p-6">
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
        </div>
        <Pagination :links="suppliers.links" class="mt-6" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  suppliers: Object,
  staff: Array,
  filters: Object,
});

const staffFilter = ref(props.filters.staff_id || '');

const filterByStaff = () => {
  router.get(route('suppliers.index'), {
    staff_id: staffFilter.value
  }, {
    preserveState: true,
    replace: true
  });
};

const clearFilter = () => {
  staffFilter.value = '';
  router.get(route('suppliers.index'), {}, {
    preserveState: true,
    replace: true
  });
};

const getStaffName = (staffId) => {
  const staff = props.staff.find(s => s.id == staffId);
  return staff ? staff.name : '';
};

const deleteSupplier = (id) => {
  if (confirm('Are you sure you want to delete this supplier?')) {
    router.delete(route('suppliers.destroy', id));
  }
};
</script>
