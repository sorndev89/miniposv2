// resources/js/stores/api.js
import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth'; // ນຳເຂົ້າ Auth Store

export const useApiStore = defineStore('api', {
    // ເປັນຫຍັງຕ້ອງໃຊ້ Pinia ຢູ່ບ່ອນນີ້?
    // Pinia ລວມສູນໂລຈິກການຮ້ອງຂໍ API, ເຮັດໃຫ້ສາມາດນຳໃຊ້ຄືນໃໝ່ໄດ້ໃນທົ່ວອົງປະກອບຕ່າງໆ
    // ແລະປ້ອງກັນການຊ້ຳກັນຂອງໂຄດ. ມັນສະໜອງບ່ອນດຽວເພື່ອຈັດການ
    // ສະຖານະການໂຫຼດ, ຂໍ້ຜິດພາດ, ແລະ base URL ສຳລັບການໂຕ້ຕອບ API ທັງໝົດ.
    state: () => ({
        loadingStates: {}, // Object ເກັບສະຖານະການໂຫຼດສຳລັບ key ຕ່າງໆ
        error: null,
        baseUrl: '/api', // Base URL API ເລີ່ມຕົ້ນ
        globalConfig: {  // ສຳລັບຕົວປ່ຽນ/ການຕັ້ງຄ່າທົ່ວໂລກ
            appName: 'MiniPOS',
            currencySymbol: '₭',
            // ... ການຕັ້ງຄ່າທົ່ວໂລກອື່ນໆ
        },
    }),

    getters: {
        getGlobalConfig: (state) => (key) => state.globalConfig[key],
        // ກວດສອບສະຖານະການໂຫຼດສຳລັບ key ສະເພາະ
        isLoading: (state) => (key) => state.loadingStates[key] || false,
    },

    actions: {
        formatNumber(value) {
           // format example: 10,000,000.00
           if (value === null || value === undefined) return '0';
            return new Intl.NumberFormat('lo-LA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },
        formatDate(dateString) {
          if (!dateString) return 'N/A';
          const date = new Date(dateString);
          return new Intl.DateTimeFormat('lo-LA', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
          }).format(date);
        },
        getFullImageUrl(url) {
            if (!url) return null;
            if (url.startsWith('http')) return url;
            return window.location.origin + url;
        },
        // ກຳນົດສະຖານະການໂຫຼດສຳລັບ key ສະເພາະ
        setLoading(key, status) {
            this.loadingStates[key] = status;
        },
        // ກຳນົດຂໍ້ຄວາມຜິດພາດ
        setError(message) {
            this.error = message;
        },
        // ລຶບລ້າງຂໍ້ຜິດພາດ
        clearError() {
            this.error = null;
        },

        // ຟັງຊັນສຳລັບການຮ້ອງຂໍ API ທົ່ວໄປ
        async request(method, endpoint, data = null, params = null, loadingKey = 'global') {
            this.setLoading(loadingKey, true); // ຕັ້ງຄ່າສະຖານະການໂຫຼດເປັນ true
            this.clearError(); // ລຶບລ້າງຂໍ້ຜິດພາດກ່ອນໜ້າ
            try {
                const url = `${this.baseUrl}${endpoint}`;
                const config = {
                    method: method,
                    url: url,
                    data: data,
                    params: params,
                };
                const response = await axios(config);
                return response.data;
            } catch (err) {
                // ຈັດການຂໍ້ຜິດພາດ ແລະກຳນົດຂໍ້ຄວາມຜິດພາດ
                const errorMessage = err.response?.data?.message || err.message || 'ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່ API.';
                this.setError(errorMessage);

                // ກວດສອບສະຖານະ 401 Unauthorized
                if (err.response && err.response.status === 401) {
                    const authStore = useAuthStore(); // ເອີ້ນໃຊ້ Auth Store
                    authStore.logout(); // ທຳການ logout ຜູ້ໃຊ້
                }
                
                throw err; // ຖິ້ມຂໍ້ຜິດພາດຄືນເພື່ອໃຫ້ອົງປະກອບສາມາດຈັດການໄດ້
            } finally {
                this.setLoading(loadingKey, false); // ຕັ້ງຄ່າສະຖານະການໂຫຼດເປັນ false ສະເໝີ
            }
        },

        // ການດຳເນີນງານ CRUD
        // ດຶງຂໍ້ມູນ (GET)
        async fetch(endpoint, params = null, loadingKey = 'global') {
            return this.request('get', endpoint, null, params, loadingKey);
        },

        // ສົ່ງຂໍ້ມູນ (POST)
        async post(endpoint, data, loadingKey = 'global') {
            return this.request('post', endpoint, data, null, loadingKey);
        },

        // ອັບເດດຂໍ້ມູນ (PUT) - ສົມມຸດວ່າຂໍ້ມູນລວມມີ ID ຫຼື endpoint ແມ່ນສະເພາະ
        async put(endpoint, data, loadingKey = 'global') {
            return this.request('put', endpoint, data, null, loadingKey);
        },

        // ລຶບຂໍ້ມູນ (DELETE) - ສົມມຸດວ່າ endpoint ແມ່ນຄື /products/ ແລະ ID ຖືກເພີ່ມໃສ່
        async destroy(endpoint, id, loadingKey = 'global') {
            return this.request('delete', `${endpoint}/${id}`, null, null, loadingKey);
        },

        // ດາວໂຫຼດໄຟລ໌
        async download(endpoint, filename = 'download', loadingKey = 'global') {
            this.setLoading(loadingKey, true);
            this.clearError();
            try {
                const url = `${this.baseUrl}${endpoint}`;
                const response = await axios({
                    method: 'get',
                    url: url,
                    responseType: 'blob', // ສຳຄັນສຳລັບການດາວໂຫຼດໄຟລ໌
                });

                // ສ້າງ blob URL ແລະກະຕຸ້ນການດາວໂຫຼດ
                const blob = new Blob([response.data]);
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href); // ລຶບລ້າງ URL

                return true;
            } catch (err) {
                // ຈັດການຂໍ້ຜິດພາດໃນການດາວໂຫຼດ
                const errorMessage = err.response?.data?.message || err.message || 'ເກີດຂໍ້ຜິດພາດໃນການດາວໂຫຼດໄຟລ໌.';
                this.setError(errorMessage);

                // ກວດສອບສະຖານະ 401 Unauthorized
                if (err.response && err.response.status === 401) {
                    const authStore = useAuthStore(); // ເອີ້ນໃຊ້ Auth Store
                    authStore.logout(); // ທຳການ logout ຜູ້ໃຊ້
                }
                throw err;
            } finally {
                this.setLoading(loadingKey, false); // ຕັ້ງຄ່າສະຖານະການໂຫຼດເປັນ false ສະເໝີ
            }
        },

        // ວິທີການອັບເດດການຕັ້ງຄ່າທົ່ວໂລກແບບໄດນາມິກ
        setGlobalConfig(key, value) {
            if (this.globalConfig.hasOwnProperty(key)) {
                this.globalConfig[key] = value;
            } else {
                console.warn(`Global config key "${key}" does not exist. Adding it.`); // ຄຳເຕືອນຖ້າ key ບໍ່ມີຢູ່
                this.globalConfig[key] = value;
            }
        },
    },
});
