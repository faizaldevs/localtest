<template>
  <Head title="Create Supplier" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Supplier</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit" class="max-w-2xl mx-auto bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
              <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                  Name
                </label>
                <input
                  v-model="form.name"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  id="name"
                  type="text"
                  placeholder="Supplier name"
                >
                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
              </div>

              <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                  Address
                </label>
                <textarea
                  v-model="form.address"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  id="address"
                  placeholder="Supplier address"
                  rows="3"
                ></textarea>
                <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
              </div>

              <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                  Phone
                </label>
                <input
                  v-model="form.phone"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  id="phone"
                  type="text"
                  placeholder="Phone number"
                >
                <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
              </div>

              <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="staff">
                  Staff Member
                </label>
                <select
                  v-model="form.staff_id"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  id="staff"
                >
                  <option value="">Select Staff Member</option>
                  <option v-for="staff in props.staff" :key="staff.id" :value="staff.id">
                    {{ staff.name }}
                  </option>
                </select>
                <div v-if="form.errors.staff_id" class="text-red-500 text-xs mt-1">{{ form.errors.staff_id }}</div>
              </div>

              <div class="flex items-center justify-between">
                <button
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  type="submit"
                  :disabled="form.processing"
                >
                  Create Supplier
                </button>
                <Link
                  :href="route('suppliers.index')"
                  class="text-blue-600 hover:text-blue-900"
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
import { Link, useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  staff: Array,
});

const form = useForm({
  name: '',
  address: '',
  phone: '',
  staff_id: '',
});

const submit = () => {
  form.post(route('suppliers.store'));
};
</script>
