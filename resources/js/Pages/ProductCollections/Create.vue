<template>
  <Head title="Create Product Collection" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Product Collection</h2>
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

              <!-- Suppliers Table -->
              <div v-if="suppliers.length > 0" class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Suppliers</h3>
                
                <!-- Common Cost Field -->
                <div class="mb-4">
                  <label for="common-cost" class="block text-sm font-medium text-gray-700">Common Cost</label>
                  <div class="mt-1 flex rounded-md shadow-sm w-48">
                    <input
                      type="number"
                      id="common-cost"
                      v-model="commonCost"
                      @input="updateAllSupplierCosts"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      step="0.01"
                      min="0"
                    />
                  </div>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="supplier in suppliers" :key="supplier.id">
                        <td class="px-6 py-4 whitespace-nowrap">{{ supplier.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input
                            type="number"
                            v-model="supplier.cost"
                            class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            step="0.01"
                            min="0"
                            @input="updateForm(supplier)"
                          />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input
                            type="number"
                            v-model="supplier.quantity"
                            class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            step="0.001"
                            min="0"
                            @input="updateForm(supplier)"
                          />
                        </td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-100">
                      <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">Total</td>
                        <td class="px-6 py-4 whitespace-nowrap"></td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ totalQuantity.toFixed(3) }}</td>
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
                  Create Collection
                </button>
                <Link
                  :href="route('product-collections.create')"
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
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  products: Array,
  staff: Array,
});

const suppliers = ref([]);
const selectedProduct = ref(null);
const commonCost = ref(0);

const form = useForm({
  date: '',
  product_id: '',
  staff_id: '',
  supplier_details: [] // Array to hold multiple supplier data
});

// Computed properties for totals
const totalCost = computed(() => {
  return suppliers.value.reduce((sum, supplier) => {
    return sum + (parseFloat(supplier.cost) || 0);
  }, 0);
});

const totalQuantity = computed(() => {
  return suppliers.value.reduce((sum, supplier) => {
    return sum + (parseFloat(supplier.quantity) || 0);
  }, 0);
});

const updateAllSupplierCosts = () => {
  if (suppliers.value.length > 0) {
    suppliers.value.forEach(supplier => {
      supplier.cost = parseFloat(commonCost.value) || 0;
      updateForm(supplier);
    });
  }
};

const onProductChange = async () => {
  await updateProductCost();
  await checkExistingEntry();
};

const onStaffChange = async () => {
  await loadSuppliers();
  await checkExistingEntry();
};

const checkExistingEntry = async () => {
  if (!form.date || !form.product_id || !form.staff_id) {
    return;
  }

  try {
    const response = await axios.get('/product-collections/check-existing', {
      params: {
        date: form.date,
        product_id: form.product_id,
        staff_id: form.staff_id
      }
    });

    if (response.data && response.data.exists) {
      const existingData = response.data.data;
      commonCost.value = existingData.suppliers[0]?.cost || 0;
      
      // Update suppliers with existing data
      suppliers.value = suppliers.value.map(supplier => {
        const existingSupplier = existingData.suppliers.find(s => s.supplier_id === supplier.id);
        if (existingSupplier) {
          return {
            ...supplier,
            cost: existingSupplier.cost,
            quantity: existingSupplier.quantity
          };
        }
        return supplier;
      });

      // Update form supplier details
      form.supplier_details = existingData.suppliers.map(supplier => ({
        supplier_id: supplier.supplier_id,
        cost: supplier.cost,
        quantity: supplier.quantity,
        total: supplier.cost * supplier.quantity
      }));
    } else {
      // Set default values when no existing data is found
      if (suppliers.value.length > 0) {
        suppliers.value = suppliers.value.map(supplier => ({
          ...supplier,
          cost: commonCost.value,
          quantity: 0
        }));

        // Update form supplier details with default values
        form.supplier_details = suppliers.value.map(supplier => ({
          supplier_id: supplier.id,
          cost: commonCost.value,
          quantity: 0,
          total: 0
        }));
      }
    }
  } catch (error) {
    console.error('Error checking existing entry:', error);
  }
};

const updateProductCost = () => {
  if (!form.product_id) {
    selectedProduct.value = null;
    commonCost.value = 0;
    return;
  }
  
  const productId = parseInt(form.product_id);
  selectedProduct.value = props.products.find(product => product.id === productId);
  
  // Update common cost with the selected product's cost
  if (selectedProduct.value) {
    commonCost.value = selectedProduct.value.cost;
  }
  
  // Update all supplier costs with the selected product's cost
  if (selectedProduct.value && suppliers.value.length > 0) {
    suppliers.value.forEach(supplier => {
      supplier.cost = selectedProduct.value.cost;
      updateForm(supplier);
    });
  }
};

const loadSuppliers = async () => {
  if (!form.staff_id) {
    suppliers.value = [];
    return;
  }
  
  try {
    const response = await axios.get(`/staff/${form.staff_id}/suppliers`);
    suppliers.value = response.data.map(supplier => ({
      ...supplier,
      cost: commonCost.value || (selectedProduct.value ? selectedProduct.value.cost : 0),
      quantity: 0
    }));
    form.supplier_details = []; // Reset supplier details when loading new suppliers
  } catch (error) {
    console.error('Error loading suppliers:', error);
    suppliers.value = [];
  }
};

const updateForm = (supplier) => {
  const existingIndex = form.supplier_details.findIndex(detail => detail.supplier_id === supplier.id);
  const total = parseFloat(supplier.cost) * parseFloat(supplier.quantity);
  
  if (existingIndex >= 0) {
    // Update existing supplier details
    form.supplier_details[existingIndex] = {
      supplier_id: supplier.id,
      cost: supplier.cost,
      quantity: supplier.quantity,
      total: total
    };
  } else {
    // Add new supplier details
    form.supplier_details.push({
      supplier_id: supplier.id,
      cost: supplier.cost,
      quantity: supplier.quantity,
      total: total
    });
  }

  // Remove suppliers with zero quantity or cost
  form.supplier_details = form.supplier_details.filter(
    detail => detail.quantity > 0 && detail.cost > 0
  );
};

const submit = () => {
  form.post(route('product-collections.store'));
};

watch(() => form.date, checkExistingEntry);
watch(() => form.product_id, onProductChange);
watch(() => form.staff_id, onStaffChange);
</script>
