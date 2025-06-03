<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    staff: {
        type: Array,
        required: true
    },
    locations: {
        type: Array,
        required: true
    }
});

const form = useForm({
    staff_id: '',
    location_id: '',
    date: new Date().toISOString().slice(0, 10),
    amount: '',
    notes: ''
});

const submit = () => {
    form.post(route('staff-cash-transfers.store'));
};
</script>

<template>
    <Head title="Create Cash Transfer" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Cash Transfer
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="staff_id" value="Staff" />
                                <select
                                    id="staff_id"
                                    v-model="form.staff_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select Staff</option>
                                    <option v-for="staff in staff" :key="staff.id" :value="staff.id">
                                        {{ staff.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.staff_id" />
                            </div>

                            <div>
                                <InputLabel for="location_id" value="Location" />
                                <select
                                    id="location_id"
                                    v-model="form.location_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select Location</option>
                                    <option v-for="location in locations" :key="location.id" :value="location.id">
                                        {{ location.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.location_id" />
                            </div>

                            <div>
                                <InputLabel for="date" value="Date" />
                                <TextInput
                                    id="date"
                                    type="date"
                                    v-model="form.date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.date" />
                            </div>

                            <div>
                                <InputLabel for="amount" value="Amount" />
                                <TextInput
                                    id="amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    v-model="form.amount"
                                    class="mt-1 block w-full"
                                    placeholder="0.00"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>

                            <div>
                                <InputLabel for="notes" value="Notes (Optional)" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="3"
                                    placeholder="Additional notes about this transfer..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>

                            <div class="flex items-center justify-end space-x-4">
                                <SecondaryButton @click="$inertia.visit(route('staff-cash-transfers.index'))">
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create Transfer
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
