<template>
    <div class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <Header />

        <!-- Main content area wrapper, including Sidebar and main Router View -->
        <div class="flex flex-1">
            <!-- Sidebar (fixed positioned) -->
            <SideBar />

            <!-- Content Area (main + footer), dynamically adjusting margin for sidebar -->
            <div :class="{ 'sm:ml-64': uiStore.isSidebarOpen }" class="flex flex-col flex-1 transition-all duration-300">
                <main class="flex-1 p-4 pt-20"> <!-- pt-20 to account for fixed Header -->
                    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
                        <router-view />
                    </div>
                </main>

                <!-- Footer, dynamically adjusting margin for sidebar -->
                <Footer />
            </div>
        </div>
        <FlowbiteModal /> <!-- Global Flowbite Modal -->
    </div>
</template>

<script setup>
import Header from '../components/Header.vue';
import SideBar from '../components/SideBar.vue';
import Footer from '../components/Footer.vue';
import FlowbiteModal from '../components/FlowbiteModal.vue'; // Import FlowbiteModal
import { useUiStore } from '../stores/ui';

// AppLayout.vue
// This layout provides the main structure for authenticated users,
// including a Header, Sidebar, main content area, and a sticky Footer.
// The layout dynamically adjusts for sidebar visibility.

const uiStore = useUiStore();
</script>

<style scoped>
/* Scoped styles for AppLayout if needed */
</style>
