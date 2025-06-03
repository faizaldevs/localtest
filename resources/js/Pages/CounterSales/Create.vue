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
            <form @submit.prevent="submit">
              <!-- Date and Product Selection -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Date</label>
                  <input type="date" 
                         v-model="form.date"
                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Product</label>
                  <select v-model="form.product_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" @change="updatePrice">
                    <option value="">Select Product</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }} - {{ formatPrice(product.price) }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Location</label>
                  <select v-model="form.location_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Select Location</option>
                    <option v-for="location in locations" :key="location.id" :value="location.id">
                      {{ location.name }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Quantity and Payment Mode -->
              <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Quantity</label>
                  <input type="number" 
                        v-model="form.quantity" 
                        @input="calculateTotal"
                        step="0.001" 
                        min="0.001" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Payment Mode</label>
                  <select v-model="form.payment_mode" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="cash">Cash</option>
                    <option value="prepaid">Prepaid</option>
                    <option value="postpaid">Postpaid</option>
                  </select>
                </div>
              </div>

              <!-- Price and Total -->
              <div class="mt-6sho grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                  <div class="mt-1 text-xl font-semibold">{{ formatPrice(form.price) }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                  <div class="mt-1 text-2xl font-bold text-green-600">{{ formatPrice(form.total) }}</div>
                </div>
              </div>              <!-- Submit Buttons -->
              <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        @click="submitAndNew"
                        :disabled="processing"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                  Save & New
                </button>
                <button type="submit" 
                        :disabled="processing"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                  Complete Sale
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  products: Array,
  locations: Array,
})

// Set initial form values including today's date
const form = useForm({
  date: new Date().toISOString().split('T')[0],
  product_id: props.products.length ? props.products[0].id : '',
  location_id: props.locations.length ? props.locations[0].id : '',
  quantity: 0,
  payment_mode: 'cash',
  price: props.products.length ? props.products[0].price : 0,
  total: 0,
})

// Initialize price and total when component mounts
onMounted(() => {
  if (form.product_id) {
    updatePrice()
  }
})

function updatePrice() {
  const product = props.products.find(p => p.id === form.product_id)
  if (product) {
    form.price = product.price
    calculateTotal()
  }
}

function calculateTotal() {
  form.total = form.price * form.quantity
}

function formatPrice(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'INR'
  }).format(amount)
}

function resetForm() {
  form.date = new Date().toISOString().split('T')[0]
  form.product_id = props.products.length ? props.products[0].id : ''
  form.location_id = props.locations.length ? props.locations[0].id : ''
  form.quantity = 0
  form.payment_mode = 'cash'
  form.price = props.products.length ? props.products[0].price : 0
  form.total = 0
  updatePrice()
}

function submitSale(shouldRedirect = true) {
  const formData = {
    date: form.date,
    product_id: form.product_id,
    location_id: form.location_id,
    quantity: form.quantity,
    payment_mode: form.payment_mode,
    price: form.price,
    total: form.total,
  }

  if (!shouldRedirect) {
    // Add save_and_new parameter for the backend
    formData.save_and_new = true
    
    router.post(route('counter-sales.store'), formData, {
      onSuccess: () => {
        resetForm()
      },
      onError: (errors) => {
        console.error('Submission errors:', errors)
      }
    })
  } else {
    form.post(route('counter-sales.store'), {
      onError: (errors) => {
        console.error('Submission errors:', errors)
      }
    })
  }
}

function submit() {
  submitSale(true)
}

function submitAndNew() {
  submitSale(false)
}
</script>
