<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    options: {
        type: Array,
        required: true
    },
    valueProp: {
        type: String,
        default: 'id'
    },
    labelProp: {
        type: String,
        default: 'name'
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    }
});

const model = defineModel({
    type: [String, Number],
    required: true,
});

const select = ref(null);

onMounted(() => {
    if (select.value.hasAttribute('autofocus')) {
        select.value.focus();
    }
});

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
    <select
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
        v-model="model"
        ref="select"
    >
        <option value="">{{ placeholder }}</option>
        <option
            v-for="option in options"
            :key="option[valueProp]"
            :value="option[valueProp]"
        >
            {{ option[labelProp] }}
        </option>
    </select>
</template>
