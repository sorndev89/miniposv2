<template>
    <div class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 ">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">ພາບລວມລະບົບ</h1>

        <!-- ແຖບສະຖິຕິ (Stats Cards) -->
        <div v-if="apiStore.isLoading('dashboard_summary')" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            <div v-for="n in 4" :key="n" class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm flex items-center justify-between border border-gray-200 dark:border-gray-700 animate-pulse">
                <div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
                <div class="p-3 bg-gray-200 dark:bg-gray-700 rounded-full w-12 h-12"></div>
            </div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm flex items-center justify-between border border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400 mb-1">ຍອດຂາຍທັງໝົດ</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">₭ {{ apiStore.formatNumber(summaryData.totalSales) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full dark:bg-blue-900">
                    <CurrencyDollarIcon class="w-8 h-8 text-blue-600 dark:text-blue-300" />
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm flex items-center justify-between border border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400 mb-1">ສິນຄ້າທັງໝົດ</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summaryData.totalProducts }} ລາຍການ</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full dark:bg-green-900">
                    <ArchiveBoxIcon class="w-8 h-8 text-green-600 dark:text-green-300" />
                </div>
            </div>

            

            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm flex items-center justify-between border border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400 mb-1">ກຳໄລ</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">₭ {{ apiStore.formatNumber(summaryData.totalProfit) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full dark:bg-purple-900">
                    <ChartBarIcon class="w-8 h-8 text-purple-600 dark:text-purple-300" />
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm flex items-center justify-between border border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400 mb-1">ຜູ້ໃຊ້</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summaryData.newCustomers }} ຄົນ</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full dark:bg-yellow-900">
                    <UserPlusIcon class="w-8 h-8 text-yellow-600 dark:text-yellow-300" />
                </div>
            </div>
        </div>

        <!-- ພາກສ່ວນກິດຈະກໍາຫຼ້າສຸດ ແລະ ການສັ່ງຊື້ດ່ວນ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <!-- ກິດຈະກໍາຫຼ້າສຸດ (Recent Activities) -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">ກິດຈະກໍາຫຼ້າສຸດ</h2>
                <div v-if="apiStore.isLoading('dashboard_activities')" class="flex justify-center items-center h-48">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                </div>
                <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li v-for="activity in recentActivities" :key="activity.id" class="py-3 flex items-center justify-between">
                        <div class="flex items-center">
                            <component :is="getActivityIcon(activity.type)" class="w-5 h-5 text-gray-500 dark:text-gray-400 me-3" />
                            <div>
                                <p class="text-gray-900 dark:text-white">{{ activity.description }}</p>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ activity.time }}</span>
                            </div>
                        </div>
                        <span :class="getActivityValueClass(activity.type)">{{ activity.value }}</span>
                    </li>
                    <li v-if="recentActivities.length === 0 && !apiStore.isLoading('dashboard_activities')" class="py-3 text-center text-gray-500 dark:text-gray-400">
                        ບໍ່ມີກິດຈະກໍາຫຼ້າສຸດ.
                    </li>
                </ul>
            </div>

            <!-- ການສັ່ງຊື້ດ່ວນ (Quick Actions) -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">ການສັ່ງຊື້ດ່ວນ</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button @click="router.push('/pos')" class="flex items-center justify-center p-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                        <ShoppingCartIcon class="w-6 h-6 me-2" />
                        ຂາຍສິນຄ້າ
                    </button>
                    <button @click="router.push('/products')" class="flex items-center justify-center p-4 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                        <ArchiveBoxIcon class="w-6 h-6 me-2" />
                        ຈັດການສິນຄ້າ
                    </button>
                    <button @click="router.push('/settings/users')" class="flex items-center justify-center p-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors duration-200">
                        <UserIcon class="w-6 h-6 me-2" />
                        ຈັດການຜູ້ໃຊ້
                    </button>
                    <button @click="router.push('/reports')" class="flex items-center justify-center p-4 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                        <DocumentTextIcon class="w-6 h-6 me-2" />
                        ລາຍງານ
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {
    CurrencyDollarIcon,
    ArchiveBoxIcon,
    UserPlusIcon,
    ChartBarIcon,
    ShoppingCartIcon,
    ArrowDownTrayIcon,
    PlusCircleIcon,
    UserIcon,
    DocumentTextIcon,
    TagIcon // Added for product-related activities
} from '@heroicons/vue/20/solid';
import { useApiStore } from '../stores/api';
import { useModalStore } from '../stores/modal';
import { useRouter } from 'vue-router';

// Dashboard.vue
// Component ນີ້ສະແດງພາບລວມຂອງລະບົບ, ລວມມີບັດສະຖິຕິ, ກິດຈະກຳຫຼ້າສຸດ, ແລະປຸ່ມສັ່ງຊື້ດ່ວນ.
// ມັນດຶງຂໍ້ມູນຈາກ API ແລະໃຊ້ store ຕ່າງໆສຳລັບການຈັດການສະຖານະ.

const apiStore = useApiStore();     // ເລີ່ມຕົ້ນ Api Store ເພື່ອດຶງຂໍ້ມູນຈາກ API
const modalStore = useModalStore(); // ເລີ່ມຕົ້ນ Modal Store ເພື່ອສະແດງການແຈ້ງເຕືອນຕ່າງໆ
const router = useRouter();         // ເລີ່ມຕົ້ນ Router ເພື່ອໃຊ້ສຳລັບການນຳທາງ

// Data for summary cards (ຂໍ້ມູນສຳລັບບັດສະຫຼຸບສະຖິຕິ)
const summaryData = ref({
    totalSales: 0,      // ຍອດຂາຍທັງໝົດ
    totalProducts: 0,   // ຈຳນວນສິນຄ້າທັງໝົດ
    newCustomers: 0,    // ລູກຄ້າໃໝ່
    totalProfit: 0,     // ກຳໄລທັງໝົດ
});

// Data for recent activities (ຂໍ້ມູນສຳລັບກິດຈະກຳຫຼ້າສຸດ)
const recentActivities = ref([]);

// Function to fetch dashboard data (ຟັງຊັນສຳລັບດຶງຂໍ້ມູນ Dashboard)
const fetchDashboardData = async () => {
    try {
        // Fetch summary data (ດຶງຂໍ້ມູນສະຫຼຸບສະຖິຕິ)
        const summaryResponse = await apiStore.fetch('/dashboard/summary', null, 'dashboard_summary');
        if (summaryResponse) {
            summaryData.value = summaryResponse; // ອັບເດດຂໍ້ມູນສະຫຼຸບ
        }

        // Fetch recent activities (ດຶງຂໍ້ມູນກິດຈະກຳຫຼ້າສຸດ)
        const activitiesResponse = await apiStore.fetch('/dashboard/activities', null, 'dashboard_activities');
        if (activitiesResponse) {
            recentActivities.value = activitiesResponse; // ອັບເດດຂໍ້ມູນກິດຈະກຳຫຼ້າສຸດ
        }

    } catch (error) {
        // ສະແດງຂໍ້ຜິດພາດດ້ວຍ Modal ຫາກການດຶງຂໍ້ມູນລົ້ມເຫຼວ
        modalStore.showErrorAlert(apiStore.error || 'ເກີດຂໍ້ຜິດພາດໃນການດຶງຂໍ້ມູນ Dashboard', 'ຂໍ້ຜິດພາດ');
    }
};

// Helper function to get icon based on activity type (ຟັງຊັນຊ່ວຍໃນການກຳນົດ Icon ຕາມປະເພດກິດຈະກຳ)
const getActivityIcon = (type) => {
    switch (type) {
        case 'sale': return ShoppingCartIcon;    // Icon ສຳລັບການຂາຍ
        case 'stock_in': return ArrowDownTrayIcon; // Icon ສຳລັບການນຳເຂົ້າສິນຄ້າ
        case 'new_customer': return UserPlusIcon; // Icon ສຳລັບລູກຄ້າໃໝ່
        case 'new_product': return TagIcon;       // Icon ສຳລັບສິນຄ້າໃໝ່
        default: return ChartBarIcon;             // Icon ເລີ່ມຕົ້ນ
    }
};

// Helper function to get class for activity value based on type (ຟັງຊັນຊ່ວຍໃນການກຳນົດ Class ສີຕາມປະເພດກິດຈະກຳ)
const getActivityValueClass = (type) => {
    switch (type) {
        case 'sale': return 'text-green-600 dark:text-green-400';
        case 'stock_in': return 'text-blue-600 dark:text-blue-400';
        case 'new_customer': return 'text-yellow-600 dark:text-yellow-400';
        case 'new_product': return 'text-purple-600 dark:text-purple-400';
        default: return 'text-gray-900 dark:text-white';
    }
};

// Fetch data when component is mounted (ດຶງຂໍ້ມູນເມື່ອ Component ຖືກ Mounted)
onMounted(() => {
    fetchDashboardData();
});
</script>

<style scoped>
/* Scoped styles for Dashboard.vue if needed */
</style>