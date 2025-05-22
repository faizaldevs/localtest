<template>
  <Head title="Edit Product Transfer" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Product Transfer</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date -->
                <div>
                  <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                  <input 
                    type="date" 
                    id="date" 
                    v-model="form.date" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required
                  >
                  <div v-if="form.errors.date" class="text-red-500 text-xs mt-1">
                    {{ form.errors.date }}
                  </div>
                </div>

                <!-- From Staff -->
                <div>
                  <label for="fromStaff" class="block text-sm font-medium text-gray-700">From Staff</label>
                  <select
                    id="fromStaff"
                    v-model="form.from_staff_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option value="">Select Staff</option>
                    <option v-for="member in staff" :key="member.id" :value="member.id">
                      {{ member.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.from_staff_id" class="text-red-500 text-xs mt-1">
                    {{ form.errors.from_staff_id }}
                  </div>
                </div>

                <!-- Transfer Type Selection -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Transfer To</label>
                  <div class="mt-2 space-x-4">
                    <label class="inline-flex items-center">
                      <input
                        type="radio"
                        v-model="transferType"
                        value="staff"
                        class="form-radio"
                        @change="onTransferTypeChange"
                      >
                      <span class="ml-2">Staff</span>
                    </label>
                    <label class="inline-flex items-center">
                      <input
                        type="radio"
                        v-model="transferType"
                        value="location"
                        class="form-radio"
                        @change="onTransferTypeChange"
                      >
                      <span class="ml-2">Location</span>
                    </label>
                  </div>

                  <!-- To Staff -->
                  <div v-if="transferType === 'staff'" class="mt-2">
                    <select
                      v-model="form.to_staff_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option value="">Select Staff</option>
                      <option 
                        v-for="member in staff" 
                        :key="member.id" 
                        :value="member.id"
                        :disabled="member.id === form.from_staff_id"
                      >
                        {{ member.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.to_staff_id" class="text-red-500 text-xs mt-1">
                      {{ form.errors.to_staff_id }}
                    </div>
                  </div>

                  <!-- To Location -->
                  <div v-if="transferType === 'location'" class="mt-2">
                    <select
                      v-model="form.location_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option value="">Select Location</option>
                      <option v-for="location in locations" :key="location.id" :value="location.id">
                        {{ location.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.location_id" class="text-red-500 text-xs mt-1">
                      {{ form.errors.location_id }}
                    </div>
                  </div>
                  <div v-if="form.errors.transfer_target" class="text-red-500 text-xs mt-1">
                    {{ form.errors.transfer_target }}
                  </div>
                </div>

                <!-- Product -->
                <div>
                  <label for="product" class="block text-sm font-medium text-gray-700">Product</label>
                  <select
                    id="product"
                    v-model="form.product_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option value="">Select Product</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.product_id" class="text-red-500 text-xs mt-1">
                    {{ form.errors.product_id }}
                  </div>
                </div>

                <!-- Quantity -->
                <div>
                  <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                  <input
                    type="number"
                    id="quantity"
                    v-model="form.quantity"
                    step="0.001"
                    min="0.001"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                  <div v-if="form.errors.quantity" class="text-red-500 text-xs mt-1">
                    {{ form.errors.quantity }}
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                ></textarea>
                <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">
                  {{ form.errors.notes }}
                </div>
              </div>

              <div class="flex justify-end space-x-3">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  :disabled="form.processing"
                >
                  Update Transfer
                </button>
                <Link
                  :href="route('product-transfers.index')"
                  class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                >
                  Cancel
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  transfer: Object,
  staff: Array,
  locations: Array,
  products: Array,
});

const transferType = ref(props.transfer.location_id ? 'location' : 'staff');

const form = useForm({
  date: props.transfer.date || new Date(props.transfer.created_at).toISOString().split('T')[0],
  from_staff_id: props.transfer.from_staff_id,
  to_staff_id: props.transfer.to_staff_id,
  location_id: props.transfer.location_id,
  product_id: props.transfer.product_id,
  quantity: props.transfer.quantity,
  notes: props.transfer.notes,
});

const onTransferTypeChange = () => {
  form.to_staff_id = '';
  form.location_id = '';
};

const submit = () => {
  form.put(route('product-transfers.update', props.transfer.id));
};
</script>
