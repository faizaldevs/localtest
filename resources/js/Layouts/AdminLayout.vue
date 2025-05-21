<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold">Admin Dashboard</h1>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <button class="p-2 text-gray-600 hover:text-gray-800">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar & Main Content -->
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 bg-white shadow h-screen">
                <nav class="mt-5 px-2">
                    <a href="/admin/users" class="group flex items-center px-2 py-2 text-base leading-6 font-medium text-gray-900 hover:text-gray-900 hover:bg-gray-50">
                        Users
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-8">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
// Component logic will go here
</script>
