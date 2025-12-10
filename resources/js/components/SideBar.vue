<template>
    <!-- ແຖບດ້ານຂ້າງ (Sidebar) ຫຼັກຂອງລະບົບ -->
    <aside id="logo-sidebar"
        :class="{
            '-translate-x-full': !uiStore.isSidebarOpen, // ເຊື່ອງ sidebar ເມື່ອປິດ
            'translate-x-0': uiStore.isSidebarOpen,      // ສະແດງ sidebar ເມື່ອເປີດ
            'w-64': uiStore.isSidebarOpen,               // ຄວາມກວ້າງເຕັມເມື່ອເປີດ
            'w-20': !uiStore.isSidebarOpen               // ຄວາມກວ້າງນ້ອຍລົງເມື່ອປິດ (mini-sidebar)
        }"
        class="fixed top-0 left-0 z-40 h-screen pt-20 transition-all duration-300 ease-in-out bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700"
        aria-label="Sidebar">
        <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
            
            <ul class="space-y-2 font-medium">
                <li v-for="(menuItem, index) in menuItems" :key="index">
                    <!-- ກວດສອບວ່າເປັນເມນູທີ່ມີລາຍການຍ່ອຍ (dropdown) ຫຼືບໍ່ -->
                    <template v-if="menuItem.children">
                        <button type="button"
                            @click="toggleSubMenu(menuItem)"  
                            class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                            :aria-expanded="menuItem.isOpen ? 'true' : 'false'">
                            <component :is="menuItem.icon" class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></component>
                            <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">{{ menuItem.name }}</span>
                            <svg class="w-3 h-3 transition-transform" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6"
                                :class="{ 'rotate-180': menuItem.isOpen }"> <!-- ໝຸນລູກສອນຂຶ້ນ-ລົງ -->
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                        </button>
                        <ul :id="'dropdown-example-' + index"
                            :style="{ 'max-height': menuItem.isOpen ? '999px' : '0px', 'display': menuItem.isOpen ? 'block' : 'none' }" 
                            :class="{
                                'py-2 space-y-2': menuItem.isOpen // ໃຊ້ padding ສະເພາະຕອນເປີດ
                            }"
                            class="transition-all duration-300 ease-in-out overflow-hidden">
                            <li v-for="(childItem, childIndex) in menuItem.children" :key="childIndex">
                                <router-link :to="childItem.path" class="flex items-center w-full p-2 text-gray-900 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" active-class="font-semibold text-blue-700 bg-blue-100 dark:text-blue-400 dark:bg-blue-800">
                                    <component :is="childItem.icon" class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></component>
                                    <span class="ms-3">{{ childItem.name }}</span>
                                </router-link>
                            </li>
                        </ul>
                    </template>

                    <!-- ລາຍການເມນູປົກກະຕິ (ບໍ່ມີລາຍການຍ່ອຍ) -->
                    <template v-else>
                        <router-link :to="menuItem.path" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group" active-class="font-semibold text-blue-700 bg-blue-100 dark:text-blue-400 dark:bg-blue-800" exact-active-class="font-semibold text-blue-700 bg-blue-100 dark:text-blue-400 dark:bg-blue-800">
                            <component :is="menuItem.icon" class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true"></component>
                            <span class="flex-1 ms-3 whitespace-nowrap">{{ menuItem.name }}</span>
                            <span v-if="menuItem.badge" class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">
                                {{ menuItem.badge }}
                            </span>
                        </router-link>
                    </template>
                </li>
            </ul>
        </div>
    </aside>
</template>

<script setup>
import { useUiStore } from '../stores/ui'; // ນຳເຂົ້າ Store ສຳລັບການຈັດການ UI
import { ref, onMounted, onUnmounted, watchEffect } from 'vue'; // ນຳເຂົ້າຟັງຊັນ Vue ທີ່ຈຳເປັນ
import { useRoute } from 'vue-router'; // ນຳເຂົ້າ useRoute ເພື່ອເຂົ້າເຖິງຂໍ້ມູນ Route ປັດຈຸບັນ
import {
    HomeIcon,
    ArchiveBoxIcon,
    ArrowDownTrayIcon,
    ShoppingCartIcon,
    DocumentTextIcon,
    StarIcon,
    Cog6ToothIcon, // Icon ສຳລັບການຕັ້ງຄ່າ
    TagIcon,        // Icon ສຳລັບໝວດໝູ່ສິນຄ້າ
    UsersIcon       // Icon ສຳລັບຈັດການຜູ້ໃຊ້
} from '@heroicons/vue/20/solid'; // ນຳເຂົ້າ Icons ທີ່ໃຊ້ໃນເມນູ

const uiStore = useUiStore(); // ໃຊ້ UI Store
const windowWidth = ref(window.innerWidth); // ຕົວປ່ຽນ reactive ເພື່ອເກັບຄ່າຄວາມກວ້າງຂອງໜ້າຕ່າງ
const route = useRoute(); // ໃຊ້ useRoute ເພື່ອເຂົ້າເຖິງ Route ປັດຈຸບັນ

// ລາຍການເມນູສຳລັບ Sidebar
const menuItems = ref([
    {
        name: 'ໜ້າຫຼັກ',
        path: '/',
        icon: HomeIcon,
    },
    {
        name: 'ສິນຄ້າ',
        path: '/products',
        icon: ArchiveBoxIcon,
        badge: 'New',
    },
    {
        name: 'ນໍາເຂົ້າສິນຄ້າ',
        path: '/stock/import',
        icon: ArrowDownTrayIcon,
    },
    {
        name: 'ຂາຍ',
        path: '/pos',
        icon: ShoppingCartIcon,
        badge: 'Pro',
    },
    {
        name: 'ລາຍງານ',
        path: '/reports',
        icon: DocumentTextIcon,
    },
    {
        name: 'ການຕັ້ງຄ່າ',
        icon: Cog6ToothIcon,
        isOpen: false, // ສະຖານະເປີດ/ປິດສຳລັບລາຍການຍ່ອຍ
        children: [
            {
                name: 'ໝວດໝູ່/ຫົວໜ່ວຍ',
                path: '/settings/categories-unit',
                icon: TagIcon,
            },
            {
                name: 'ຈັດການຜູ້ໃຊ້',
                path: '/settings/users',
                icon: UsersIcon,
            },
        ],
    },
     {
        name: 'ລາຍການພິເສດ',
        icon: StarIcon,
        isOpen: false, // ສະຖານະເປີດ/ປິດສຳລັບລາຍການຍ່ອຍ
        children: [
            {
                name: 'ລາຍການຍ່ອຍ 1',
                path: '/extra/item1',
                icon: HomeIcon,
            },
            {
                name: 'ລາຍການຍ່ອຍ 2',
                path: '/extra/item2',
                icon: ArchiveBoxIcon,
            },
        ],
    },
]);

// ໃຊ້ watchEffect ເພື່ອຕິດຕາມການປ່ຽນແປງຂອງ Route
watchEffect(() => {
    menuItems.value.forEach(menuItem => {
        if (menuItem.children) {
            // ກວດສອບວ່າລູກຂອງເມນູໃດໜຶ່ງ active ຢູ່ ຫຼືບໍ່
            const isActiveChild = menuItem.children.some(child => route.path.startsWith(child.path));
            if (isActiveChild) {
                menuItem.isOpen = true; // ເປີດເມນູຫຼັກຖ້າລູກ active
            }
        }
    });
});

// ຟັງຊັນສຳລັບການສະຫຼັບການເປີດ/ປິດລາຍການຍ່ອຍ
const toggleSubMenu = (menuItem) => {
    menuItem.isOpen = !menuItem.isOpen;
};

// ຟັງຊັນສຳລັບອັບເດດຄວາມກວ້າງຂອງໜ້າຕ່າງ ແລະ ຄວບຄຸມ Sidebar
const updateWindowWidth = () => {
    windowWidth.value = window.innerWidth;
    if (windowWidth.value >= 640) {
        uiStore.openSidebar(); // ເປີດ Sidebar ໃນໜ້າຈໍໃຫຍ່
    } else {
        uiStore.closeSidebar(); // ປິດ Sidebar ໃນໜ້າຈໍນ້ອຍ
    }
};

// Hook ເມື່ອ Component ຖືກ Mounted
onMounted(() => {
    window.addEventListener('resize', updateWindowWidth); // ເພີ່ມ Event Listener ສຳລັບການປ່ຽນຂະໜາດໜ້າຕ່າງ
    updateWindowWidth(); // ເອີ້ນໃຊ້ຄັ້ງທໍາອິດເມື່ອ Component ໂຫຼດ
});

// Hook ເມື່ອ Component ຖືກ Unmounted
onUnmounted(() => {
    window.removeEventListener('resize', updateWindowWidth); // ລຶບ Event Listener ເມື່ອ Component ຖືກທໍາລາຍ
});
</script>

<style scoped>
/*
    ສ່ວນຂອງ Style (CSS) ສະເພາະສໍາລັບ Component ນີ້.
    'scoped' ໝາຍຄວາມວ່າ Styles ເຫຼົ່ານີ້ຈະມີຜົນສະເພາະກັບ Component SideBar.vue ເທົ່ານັ້ນ.
    ໃນກໍລະນີນີ້, Styles ສ່ວນໃຫຍ່ແມ່ນຖືກຈັດການໂດຍ Tailwind CSS Classes ໂດຍກົງໃນ Template.
*/
</style>