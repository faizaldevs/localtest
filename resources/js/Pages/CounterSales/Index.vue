<template>
  <Head title="Counter Sales" />
  
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Counter Sales</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Add New Sale Button -->
            <div class="mb-6">
              <Link :href="route('counter-sales.create')" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                New Sale
              </Link>
            </div>

            <!-- Sales Table -->
            <div class="flex flex-col">
              <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                  <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">                        <tr v-for="sale in sales.data" :key="sale.id">
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ formatDate(sale.date) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ sale.product ? sale.product.name : 'N/A' }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ sale.location ? sale.location.name : 'N/A' }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ formatQuantity(sale.quantity) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ formatPrice(sale.total) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span :class="paymentStatusClass(sale.payment_mode)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                              {{ sale.payment_mode }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
              <Pagination :links="sales.links" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { defineComponent } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

export default defineComponent({
  components: {
    AuthenticatedLayout,
    Head,
    Link,
    Pagination
  },

  props: {
    sales: Object,
  },

  mounted() {
    console.log('Counter Sales component mounted');
    console.log('Sales data:', this.sales);
    
    // Check for any missing data in sales
    if (this.sales && this.sales.data) {
      this.sales.data.forEach((sale, index) => {
        if (!sale.product || !sale.location) {
          console.error(`Sale ${sale.id} at index ${index} has missing relationships:`, {
            product: sale.product,
            location: sale.location,
            sale: sale
          });
        }
      });
    }
  },

  // Add error boundary
  errorCaptured(err, instance, info) {
    console.error('Vue error captured in Counter Sales:', err, info);
    return false; // Let the error propagate
  },

  methods: {
    formatDate(date) {
      try {
        return new Date(date).toLocaleDateString()
      } catch (error) {
        console.error('Error formatting date:', date, error);
        return 'Invalid Date';
      }
    },

    formatPrice(amount) {
      try {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD'
        }).format(amount)
      } catch (error) {
        console.error('Error formatting price:', amount, error);
        return '$0.00';
      }
    },

    formatQuantity(quantity) {
      try {
        return new Intl.NumberFormat('en-US', {
          minimumFractionDigits: 3,
          maximumFractionDigits: 3
        }).format(quantity)
      } catch (error) {
        console.error('Error formatting quantity:', quantity, error);
        return '0.000';
      }
    },

    paymentStatusClass(mode) {
      try {
        return {
          'bg-green-100 text-green-800': mode === 'cash',
          'bg-blue-100 text-blue-800': mode === 'prepaid',
          'bg-yellow-100 text-yellow-800': mode === 'postpaid'
        }
      } catch (error) {
        console.error('Error determining payment status class:', mode, error);
        return 'bg-gray-100 text-gray-800';
      }
    }
  }
})
</script>
