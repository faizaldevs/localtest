&lt;script setup&gt;
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    location: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.location.name,
});

const submit = () => {
    form.put(route('locations.update', props.location.id));
};
&lt;/script&gt;

&lt;template&gt;
    &lt;Head title="Edit Location" /&gt;

    &lt;AuthenticatedLayout&gt;
        &lt;template #header&gt;
            &lt;h2 class="font-semibold text-xl text-gray-800 leading-tight"&gt;Edit Location&lt;/h2&gt;
        &lt;/template&gt;

        &lt;div class="py-12"&gt;
            &lt;div class="max-w-7xl mx-auto sm:px-6 lg:px-8"&gt;
                &lt;div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"&gt;
                    &lt;div class="p-6"&gt;
                        &lt;form @submit.prevent="submit"&gt;
                            &lt;div class="mb-4"&gt;
                                &lt;InputLabel for="name" value="Name" /&gt;
                                &lt;TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                /&gt;
                                &lt;InputError :message="form.errors.name" class="mt-2" /&gt;
                            &lt;/div&gt;

                            &lt;div class="flex items-center justify-end mt-4"&gt;
                                &lt;PrimaryButton :disabled="form.processing"&gt;Update Location&lt;/PrimaryButton&gt;
                            &lt;/div&gt;
                        &lt;/form&gt;
                    &lt;/div&gt;
                &lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/AuthenticatedLayout&gt;
&lt;/template&gt;
