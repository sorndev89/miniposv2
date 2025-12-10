// resources/js/stores/auth.js
import { defineStore } from 'pinia';
import axios from 'axios';
import router from '../router';

// ຟັງຊັນຊ່ວຍໃນການກວດສອບວ່າ token ຖືກເກັບໄວ້ຢູ່ localStorage ຫຼື sessionStorage
function getTokenStorage() {
    return localStorage.getItem('token') ? localStorage : (sessionStorage.getItem('token') ? sessionStorage : null);
}

// ຟັງຊັນຊ່ວຍໃນການລຶບ token ແລະ user info ອອກຈາກທັງສອງ storage
function clearTokenStorage() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    sessionStorage.removeItem('token');
    sessionStorage.removeItem('user');
}

// ຟັງຊັນຊ່ວຍໃນການເກັບ token ແລະ user info ຕາມ remember flag
function setTokenStorage(token, user, remember) {
    const storage = remember ? localStorage : sessionStorage;
    storage.setItem('token', token);
    storage.setItem('user', JSON.stringify(user));
}


export const useAuthStore = defineStore('auth', {
    // ເປັນຫຍັງຕ້ອງໃຊ້ Pinia ຢູ່ບ່ອນນີ້?
    // Pinia ລວມສູນການຈັດການສະຖານະການຢືນຢັນຕົວຕົນ (token, ຂໍ້ມູນຜູ້ໃຊ້)
    // ເຮັດໃຫ້ສາມາດເຂົ້າເຖິງ ແລະ ຕອບສະໜອງໄດ້ທົ່ວທັງແອັບພລິເຄຊັນ.
    // ມັນຊ່ວຍໃຫ້ຂະບວນການເຂົ້າສູ່ລະບົບ/ອອກຈາກລະບົບງ່າຍຂຶ້ນ
    // ແລະເປັນແຫຼ່ງຂໍ້ມູນດຽວສຳລັບສະຖານະການຢືນຢັນຕົວຕົນ.
    state: () => {
        const storage = getTokenStorage();
        return {
            token: storage ? storage.getItem('token') : null, // JWT token
            user: storage ? JSON.parse(storage.getItem('user')) : null, // ຂໍ້ມູນຜູ້ໃຊ້
        };
    },

    getters: {
        // ກວດສອບວ່າຜູ້ໃຊ້ໄດ້ເຂົ້າສູ່ລະບົບແລ້ວຫຼືບໍ່
        isAuthenticated: (state) => !!state.token,
        // ກວດສອບວ່າຜູ້ໃຊ້ມີບົດບາດເປັນ Admin ຫຼືບໍ່
        isAdmin: (state) => state.user && state.user.role === 'Admin', // ໃຊ້ຄຸນສົມບັດ 'role' ຈາກ object ຜູ້ໃຊ້
    },

    actions: {
        // ເລີ່ມຕົ້ນ Store ເມື່ອແອັບພລິເຄຊັນໂຫຼດ
        initialize() {
            console.log('Auth Store - Initializing with token:', this.token ? 'Token Found' : 'No Token');
            if (this.token) {
                // ກຳນົດ Authorization header ສຳລັບທຸກໆ Axios requests ຕໍ່ໄປ
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            }
            // ທາງເລືອກ: ກວດສອບ Token ເມື່ອແອັບພລິເຄຊັນໂຫຼດຖ້າມີ
            // try {
            //     // ທ່ານອາດຈະຕ້ອງການເອີ້ນ endpoint /user ຢູ່ທີ່ນີ້ເພື່ອກວດສອບ Token
            //     // ແລະດຶງຂໍ້ມູນຜູ້ໃຊ້ໃໝ່, ຈັດການການໝົດອາຍຸຂອງ Token.
            // } catch (error) {
            //     this.logout(); // ຖ້າ Token ບໍ່ຖືກຕ້ອງ/ໝົດອາຍຸ, ໃຫ້ອອກຈາກລະບົບ
            // }
        },

        // ຈັດການການເຂົ້າສູ່ລະບົບ
        async login(credentials) {
            try {
                const response = await axios.post('/api/auth/login', {
                    email: credentials.email,
                    password: credentials.password,
                    // ສົ່ງ 'remember' ໄປຫາ Backend ເພື່ອອາດຈະມີຜົນຕໍ່ການໝົດອາຍຸຂອງ Token
                    remember: credentials.remember || false,
                });
                // ຂໍ້ມູນການຕອບກັບຈາກ AuthController::respondWithToken ປະກອບມີ access_token ແລະ object ຜູ້ໃຊ້
                this.token = response.data.access_token;
                this.user = response.data.user; // Object ຜູ້ໃຊ້ຈາກ JWT payload

                setTokenStorage(this.token, this.user, credentials.remember); // ເກັບ Token ແລະຂໍ້ມູນຜູ້ໃຊ້

                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`; // ກຳນົດ Header ສໍາລັບ Axios

                router.push({ name: 'Dashboard' }); // ປ່ຽນເສັ້ນທາງໄປໜ້າ Dashboard
                return true;
            } catch (error) {
                console.error('Login failed:', error); // ບັນທຶກຂໍ້ຜິດພາດ
                this.token = null;
                this.user = null;
                clearTokenStorage(); // ລຶບ Token ແລະຂໍ້ມູນຜູ້ໃຊ້ອອກຈາກ Storage
                delete axios.defaults.headers.common['Authorization']; // ລຶບ Header
                return false;
            }
        },

        // ຈັດການການອອກຈາກລະບົບ
        async logout() {
            try {
                // ຍົກເລີກ Token ໃນ Server
                await axios.post('/api/auth/logout');
            } catch (error) {
                console.error('Logout failed on server:', error);
                // ເຖິງແມ່ນວ່າການອອກຈາກລະບົບໃນ Server ລົ້ມເຫຼວ (ເຊັ່ນ: Token ໝົດອາຍຸແລ້ວ),
                // ພວກເຮົາຍັງຄວນລຶບ Storage ໃນທ້ອງຖິ່ນເພື່ອຄວາມປອດໄພ ແລະປະສົບການຜູ້ໃຊ້.
            } finally {
                this.token = null;
                this.user = null;
                clearTokenStorage(); // ລຶບ Token ແລະຂໍ້ມູນຜູ້ໃຊ້ອອກຈາກ Storage
                delete axios.defaults.headers.common['Authorization']; // ລຶບ Header
                router.push({ name: 'Login' }); // ປ່ຽນເສັ້ນທາງໄປໜ້າ Login
            }
        },

        // ຈັດການການ Refresh Token
        async refreshToken() {
            try {
                const response = await axios.post('/api/auth/refresh');
                this.token = response.data.access_token;
                this.user = response.data.user; // ອັບເດດ object ຜູ້ໃຊ້ຖ້າມັນຖືກລວມຢູ່ໃນການຕອບກັບ Refresh
                // ກຳນົດ Storage ຄືນໃໝ່ໂດຍອີງໃສ່ Token ທີ່ມີຢູ່ (ຖ້າມັນຢູ່ໃນ localStorage, ສະແດງວ່າ 'remember' ເປັນ true)
                const remember = localStorage.getItem('token') ? true : false; 
                setTokenStorage(this.token, this.user, remember); // ເກັບ Token ແລະຂໍ້ມູນຜູ້ໃຊ້
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`; // ກຳນົດ Header ສໍາລັບ Axios
                return true;
            } catch (error) {
                console.error('Token refresh failed:', error); // ບັນທຶກຂໍ້ຜິດພາດ
                this.logout(); // ຖ້າ Refresh ລົ້ມເຫຼວ, ໃຫ້ອອກຈາກລະບົບຜູ້ໃຊ້
                return false;
            }
        },
    },
});
