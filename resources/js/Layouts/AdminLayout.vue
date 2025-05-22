<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="text-xl font-bold">Admin Dashboard</Link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button"
                            class="p-2 text-gray-600 hover:text-gray-800"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar & Main Content -->
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 bg-white shadow h-screen">
                <nav class="mt-5 px-2 space-y-1">
                    <Link 
                        :href="route('dashboard')"
                        class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md"
                        :class="[
                            route().current('dashboard')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <span class="truncate">Dashboard</span>
                    </Link>

                    <Link 
                        :href="route('products.index')"
                        class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md"
                        :class="[
                            route().current('products.*')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <span class="truncate">Products</span>
                    </Link>

                    <Link 
                        :href="route('product-collections.create')"
                        class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md"
                        :class="[
                            route().current('product-collections.*')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <span class="truncate">Product Collections</span>
                    </Link>

                    <Link 
                        :href="route('suppliers.index')"
                        class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md"
                        :class="[
                            route().current('suppliers.*')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <span class="truncate">Suppliers</span>
                    </Link>

                    <Link 
                        :href="route('product-collections.create')"
                        class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md"
                        :class="[
                            route().current('product-collections.*')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <span class="truncate">Product Collections</span>
                    </Link>

                    <Link 
                        :href="route('staff.index')"
                        class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md"
                        :class="[
                            route().current('staff.*')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <span class="truncate">Staff</span>
                    </Link>

                    <Link 
                        :href="route('locations.index')"
                        class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md"
                        :class="[
                            route().current('locations.*')
                                ? 'bg-gray-100 text-gray-900'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <span class="truncate">Locations</span>
                    </Link>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-8">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
