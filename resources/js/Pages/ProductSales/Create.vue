<template>
  <Head title="Create Product Sale" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Product Sale</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit" class="space-y-8">
              <!-- Date, Product, and Staff Selection in single row -->
              <div class="grid grid-cols-3 gap-4">
                <!-- Date Input -->
                <div>
                  <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                  <input
                    type="date"
                    id="date"
                    v-model="form.date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @change="checkExistingEntry"
                  />
                  <div v-if="form.errors.date" class="text-red-500 text-xs mt-1">{{ form.errors.date }}</div>
                </div>

                <!-- Product Selection -->
                <div>
                  <label for="product" class="block text-sm font-medium text-gray-700">Product</label>
                  <select
                    id="product"
                    v-model="form.product_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @change="onProductChange"
                  >
                    <option value="">Select a product</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.product_id" class="text-red-500 text-xs mt-1">{{ form.errors.product_id }}</div>
                </div>

                <!-- Staff Selection -->
                <div>
                  <label for="staff" class="block text-sm font-medium text-gray-700">Staff</label>
                  <select
                    id="staff"
                    v-model="form.staff_id"
                    @change="onStaffChange"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option value="">Select a staff member</option>
                    <option v-for="staffMember in staff" :key="staffMember.id" :value="staffMember.id">
                      {{ staffMember.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.staff_id" class="text-red-500 text-xs mt-1">{{ form.errors.staff_id }}</div>
                </div>
              </div>

              <!-- Customers Table -->
              <div v-if="customers.length > 0" class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Customers</h3>
                
                <!-- Common Price Field -->
                <div class="mb-4">
                  <label for="common-price" class="block text-sm font-medium text-gray-700">Common Price</label>
                  <div class="mt-1 flex rounded-md shadow-sm w-48">
                    <input
                      type="number"
                      id="common-price"
                      v-model="commonPrice"
                      @input="updateAllCustomerPrices"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      step="0.01"
                      min="0"
                    />
                  </div>
                </div>                <div v-if="form.errors.customer_details" class="mb-4 text-sm text-red-600">
                  {{ form.errors.customer_details }}
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Mode</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="customer in customers" :key="customer.id">
                        <td class="px-6 py-4 whitespace-nowrap">{{ customer.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input
                            type="number"
                            v-model="customer.price"
                            class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            step="0.01"
                            min="0"
                            @input="updateForm(customer)"
                          />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input
                            type="number"
                            v-model="customer.quantity"
                            class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            step="0.001"
                            min="0"
                            @input="updateForm(customer)"
                          />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <select
                            v-model="customer.payment_mode"
                            class="w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @change="updateForm(customer)"
                          >
                            <option value="prepaid">Prepaid</option>
                            <option value="cash">Cash</option>
                            <option value="postpaid">Postpaid</option>
                          </select>
                        </td>
                      </tr>                    </tbody>
                    <tfoot class="bg-gray-100">
                      <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">Total</td>
                        <td class="px-6 py-4 whitespace-nowrap"></td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ totalQuantity.toFixed(3) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap"></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="flex items-center justify-end space-x-3">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  :disabled="form.processing"
                >
                  Create Sale
                </button>
                <Link
                  :href="route('product-sales.create')"
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
import { ref, watch, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  products: Array,
  staff: Array,
});

const customers = ref([]);
const selectedProduct = ref(null);
const commonPrice = ref(0);

const form = useForm({
  date: '',
  product_id: '',
  staff_id: '',
  customer_details: [] // Array to hold multiple customer data
});

// Computed property for total quantity
const totalQuantity = computed(() => {
  return customers.value.reduce((sum, customer) => {
    return sum + (parseFloat(customer.quantity) || 0);
  }, 0);
});

const updateAllCustomerPrices = () => {
  if (customers.value.length > 0) {
    customers.value.forEach(customer => {
      customer.price = parseFloat(commonPrice.value) || 0;
      updateForm(customer);
    });
  }
};

const onProductChange = async () => {
  await updateProductPrice();
  await checkExistingEntry();
};

const onStaffChange = async () => {
  await loadCustomers();
  await checkExistingEntry();
};

const checkExistingEntry = async () => {
  if (!form.date || !form.product_id || !form.staff_id) {
    return;
  }

  try {
    const response = await axios.get('/product-sales/check-existing', {
      params: {
        date: form.date,
        product_id: form.product_id,
        staff_id: form.staff_id
      }
    });

    if (response.data && response.data.exists) {
      const existingData = response.data.data;
      commonPrice.value = existingData.customers[0]?.price || 0;
      
      // Update customers with existing data
      customers.value = customers.value.map(customer => {
        const existingCustomer = existingData.customers.find(c => c.customer_id === customer.id);
        return {
          ...customer,
          price: existingCustomer ? existingCustomer.price : commonPrice.value,
          quantity: existingCustomer ? existingCustomer.quantity : 0,
          payment_mode: existingCustomer ? existingCustomer.payment_mode : 'postpaid'
        };
      });

      // Update form customer details
      form.customer_details = customers.value
        .filter(customer => customer.quantity > 0)
        .map(customer => ({
          customer_id: customer.id,
          price: customer.price,
          quantity: customer.quantity,
          total: customer.price * customer.quantity,
          payment_mode: customer.payment_mode
        }));
    } else {
      // Set default values when no existing data is found
      if (customers.value.length > 0) {
        customers.value = customers.value.map(customer => ({
          ...customer,
          price: commonPrice.value,
          quantity: 0,
          payment_mode: 'postpaid' // Default payment mode
        }));

        // Reset form customer details when no existing data
        form.customer_details = [];
      }
    }
  } catch (error) {
    console.error('Error checking existing entry:', error);
  }
};

const updateProductPrice = () => {
  if (!form.product_id) {
    selectedProduct.value = null;
    commonPrice.value = 0;
    return;
  }
  
  const productId = parseInt(form.product_id);
  selectedProduct.value = props.products.find(product => product.id === productId);
  
  // Update common price with the selected product's price
  if (selectedProduct.value) {
    commonPrice.value = selectedProduct.value.price;
  }
  
  // Update all customer prices with the selected product's price
  if (selectedProduct.value && customers.value.length > 0) {
    customers.value.forEach(customer => {
      customer.price = selectedProduct.value.price;
      updateForm(customer);
    });
  }
};

const loadCustomers = async () => {
  if (!form.staff_id) {
    customers.value = [];
    return;
  }
  
  try {    const response = await axios.get(`/staff/${form.staff_id}/customers`);
    customers.value = response.data.map(customer => ({
      ...customer,
      price: commonPrice.value || (selectedProduct.value ? selectedProduct.value.price : 0),
      quantity: 0,
      payment_mode: 'postpaid'
    }));
    form.customer_details = []; // Reset customer details when loading new customers
  } catch (error) {
    console.error('Error loading customers:', error);
    customers.value = [];
  }
};

const updateForm = (customer) => {
  const existingIndex = form.customer_details.findIndex(detail => detail.customer_id === customer.id);
  const total = parseFloat(customer.price) * parseFloat(customer.quantity);
  
  if (existingIndex >= 0) {
    // Update existing customer details
    form.customer_details[existingIndex] = {
      customer_id: customer.id,
      price: customer.price,
      quantity: customer.quantity,
      total: total,
      payment_mode: customer.payment_mode
    };
  } else {
    // Add new customer details
    form.customer_details.push({
      customer_id: customer.id,
      price: customer.price,
      quantity: customer.quantity,
      total: total,
      payment_mode: customer.payment_mode
    });
  }
  // Keep all customers in the form data
  form.customer_details = form.customer_details.filter(
    detail => detail.customer_id === customer.id || (detail.quantity > 0 && detail.price > 0)
  );
};

const submit = () => {
  // Validate that at least one customer has quantity and price
  const hasValidSales = form.customer_details.some(
    detail => detail.quantity > 0 && detail.price > 0
  );

  if (!hasValidSales) {
    form.setError('customer_details', 'At least one customer must have quantity and price greater than 0');
    return;
  }

  // Filter out customers with zero quantity or price before submitting
  form.customer_details = form.customer_details.filter(
    detail => detail.quantity > 0 && detail.price > 0
  );
    form.post(route('product-sales.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      window.location.href = route('product-sales.index');
    },
  });
};

watch(() => form.date, checkExistingEntry);
watch(() => form.product_id, onProductChange);
watch(() => form.staff_id, onStaffChange);
</script>
