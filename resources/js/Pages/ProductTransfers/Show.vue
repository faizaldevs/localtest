<template>
  <Head title="View Transfer" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transfer Details</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="mb-6">
              <Link
                :href="route('product-transfers.index')"
                class="text-blue-600 hover:text-blue-900"
              >
                ← Back to Transfers
              </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-medium text-gray-900">Transfer Information</h3>
                <div class="mt-4 space-y-4">
                  <div>
                    <label class="text-sm font-medium text-gray-500">Date</label>
                    <p class="mt-1">{{ new Date(transfer.created_at).toLocaleString() }}</p>
                  </div>
                  
                  <div>
                    <label class="text-sm font-medium text-gray-500">Product</label>
                    <p class="mt-1">{{ transfer.product.name }}</p>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-gray-500">Quantity</label>
                    <p class="mt-1">{{ transfer.quantity }}</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900">Transfer Details</h3>
                <div class="mt-4 space-y-4">
                  <div>
                    <label class="text-sm font-medium text-gray-500">From Staff</label>
                    <p class="mt-1">{{ transfer.from_staff ? transfer.from_staff.name : 'N/A' }}</p>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-gray-500">To</label>
                    <p class="mt-1">
                      {{ transfer.to_staff ? transfer.to_staff.name : transfer.location ? transfer.location.name : 'N/A' }}
                    </p>
                  </div>

                  <div v-if="transfer.notes">
                    <label class="text-sm font-medium text-gray-500">Notes</label>
                    <p class="mt-1">{{ transfer.notes }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
  transfer: Object,
});
</script>
