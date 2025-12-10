<template>
    <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">ລາຍງານການຂາຍ</h1>

        <!-- Date Range Filter -->
         <div class=" flex justify-between items-center">
        <div class="mb-4 flex flex-col md:flex-row gap-4 items-center">
                <div class="w-full md:w-auto">
                    <label for="start-date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">ວັນທີເລີ່ມຕົ້ນ</label>
                    <input type="date" id="start-date" v-model="startDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                </div>
                <div class="w-full md:w-auto">
                    <label for="end-date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">ວັນທີສິ້ນສຸດ</label>
                    <input type="date" id="end-date" v-model="endDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                </div>
            
            </div>
            <div>
            <button @click="fetchReports" class="mt-auto btn btn-primary text-white me-2">
                    <ArrowPathIcon class="w-5 h-5 me-2" />
                    ສະແດງລາຍງານ
            </button>
            <button @click="exportPdf" class="mt-auto btn btn-warning text-white">
              <InboxArrowDownIcon class="w-5 h-5 me-2" />
                ສົ່ງອອກ PDF
            </button>
            </div>
        </div>

        <!-- Report Summary Cards (DaisyUI Stats) -->
        <div v-if="apiStore.isLoading('sales_report_fetch')" class="flex justify-center items-center h-48">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>
        <div v-else class="stats stats-vertical lg:stats-horizontal shadow mb-6">

        

            <div class="stat">
                <div class="stat-title">ຍອດຂາຍລວມ</div>
                <div class="stat-value text-primary">₭{{ totalSales.toLocaleString() }}</div>
                <div class="stat-desc">ໃນໄລຍະເວລາທີ່ເລືອກ</div>
            </div>
            
            <div class="stat">
                <div class="stat-title">ຈຳນວນຄຳສັ່ງຊື້</div>
                <div class="stat-value text-secondary">{{ totalOrders }}</div>
                <div class="stat-desc">ລາຍການ</div>
            </div>
            
            <div class="stat">
                <div class="stat-title">ກຳໄລລວມ</div>
                <div class="stat-value text-accent">₭{{ totalProfit.toLocaleString() }}</div>
                <div class="stat-desc">ໂດຍປະມານ</div>
            </div>
        </div>

        <!-- Sales Report Table -->
        <div v-if="apiStore.isLoading('sales_report_fetch')" class="flex justify-center items-center h-48">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>
        <div v-else class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100">
            <table class="table table-zebra w-full">
                <!-- head -->
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ລະຫັດຄໍາສັ່ງ</th>
                        <th>ຜູ້ຂາຍ</th>
                        <th>ລາຍການສິນຄ້າ</th>
                        <th>ຈໍານວນທັງໝົດ</th>
                        <th>ເງິນທີ່ໄດ້ຮັບ</th>
                        <th>ເງິນທອນ</th>
                        <th>ກຳໄລ</th>
                        <th>ວັນທີຂາຍ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(report, index) in salesReports" :key="report.order_id">
                        <th>{{ index + 1 }}</th>
                        <td>{{ report.order_id }}</td>
                        <td>{{ report.seller }}</td>
                        <td>
                            <ul class="list-disc list-inside">
                                <li v-for="item in report.items" :key="item.product_id">
                                    {{ item.name }} (x{{ item.quantity }}) - ₭ {{ apiStore.formatNumber(item.price) }}
                                </li>
                            </ul>
                        </td>
                        <td>₭ {{ apiStore.formatNumber(report.total_amount) }}</td>
                        <td>₭ {{ apiStore.formatNumber(report.amount_received) }}</td>
                        <td>₭ {{ apiStore.formatNumber(report.change_amount) }}</td>
                        <td>₭ {{ report.profit ? apiStore.formatNumber(report.profit) : '0' }}</td>
                        <td>{{ apiStore.formatDate(report.sale_date) }}</td>
                    </tr>
                    <tr v-if="salesReports.length === 0">
                        <td colspan="9" class="text-center py-4">ບໍ່ມີຂໍ້ມູນລາຍງານໃນໄລຍະເວລານີ້.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ArrowPathIcon, InboxArrowDownIcon } from '@heroicons/vue/20/solid';
import { useModalStore } from '../stores/modal'; // Import modal store
import { useApiStore } from '../stores/api'; // Import api store

const apiStore = useApiStore(); // Initialize api store

const today = new Date();
const year = today.getFullYear();
const month = String(today.getMonth() + 1).padStart(2, '0');
const day = String(today.getDate()).padStart(2, '0');
const formattedToday = `${year}-${month}-${day}`;

const startDate = ref(formattedToday);
const endDate = ref(formattedToday);
const modalStore = useModalStore(); // Initialize modal store

// Data for sales reports
const salesReports = ref([]); // Now a ref to store fetched reports
const totalSales = ref(0);
const totalOrders = ref(0);
const totalProfit = ref(0);

// ຟັງຊັນດຶງຂໍ້ມູນລາຍງານການຂາຍ
const fetchReports = async () => {
    if (!startDate.value || !endDate.value) {
        modalStore.showErrorAlert('ກະລຸນາເລືອກວັນທີເລີ່ມຕົ້ນ ແລະ ວັນທີສິ້ນສຸດ', 'ຂໍ້ຜິດພາດ');
        return;
    }

    try {
        const response = await apiStore.fetch('/reports/sales', {
            start_date: startDate.value,
            end_date: endDate.value
        }, 'sales_report_fetch');

        salesReports.value = response.reports;
        totalSales.value = response.summary.total_sales;
        totalOrders.value = response.summary.total_orders;
        totalProfit.value = response.summary.total_profit;
        
        modalStore.showSuccessAlert('ການດຶງລາຍງານສໍາເລັດ!', 'ສໍາເລັດ',null,1000);
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error || 'ເກີດຂໍ້ຜິດພາດໃນການດຶງລາຍງານ', 'ຂໍ້ຜິດພາດ');
    }
};

const exportPdf = async () => {
    if (!startDate.value || !endDate.value) {
        modalStore.showErrorAlert('ກະລຸນາເລືອກວັນທີເລີ່ມຕົ້ນ ແລະ ວັນທີສິ້ນສຸດ', 'ຂໍ້ຜິດພາດ');
        return;
    }

    try {
        const filename = `sales_report_${startDate.value}_to_${endDate.value}.pdf`;
        await apiStore.download('/reports/sales/pdf', filename, 'sales_report_pdf_export', {
            start_date: startDate.value,
            end_date: endDate.value
        });

        modalStore.showSuccessAlert('ລາຍງານ PDF ຖືກສົ່ງອອກສຳເລັດ!', 'ສຳເລັດ', null, 1000);
    } catch (error) {
        modalStore.showErrorAlert(apiStore.error || 'ເກີດຂໍ້ຜິດພາດໃນການສົ່ງອອກ PDF', 'ຂໍ້ຜິດພາດ');
    }
};

onMounted(() => {
    fetchReports(); // ດຶງຂໍ້ມູນລາຍງານເມື່ອ component ຖືກ mounted
});

</script>
