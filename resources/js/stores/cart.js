// resources/js/stores/cart.js
import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    // ເປັນຫຍັງຕ້ອງໃຊ້ Pinia ຢູ່ບ່ອນນີ້?
    // Pinia ສະໜອງບ່ອນເກັບຂໍ້ມູນສູນກາງສຳລັບການຈັດການສະຖານະກະຕ່າສິນຄ້າ
    // ໃນທົ່ວອົງປະກອບຕ່າງໆ ໂດຍບໍ່ຕ້ອງມີ prop drilling ຫຼື complex event emissions.
    // ມັນເຮັດໃຫ້ logic ຂອງກະຕ່າສິນຄ້າສາມາດນຳໃຊ້ຄືນໃໝ່ໄດ້, ສາມາດທົດສອບໄດ້,
    // ແລະລະບົບ reactivity ຮັບປະກັນວ່າ UI ຈະຖືກອັບເດດໂດຍອັດຕະໂນມັດ.
    state: () => ({
        items: [], // ລາຍການສິນຄ້າໃນກະຕ່າ
    }),

    getters: {
        // ຄິດໄລ່ລາຄາລວມຂອງສິນຄ້າທັງໝົດໃນກະຕ່າ
        totalPrice: (state) => {
            return state.items.reduce((total, item) => total + item.price * item.quantity, 0);
        },
    },

    actions: {
        // ເພີ່ມສິນຄ້າເຂົ້າກະຕ່າ
        addToCart(product) {
            const existingItem = this.items.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.quantity++; // ຖ້າມີສິນຄ້າແລ້ວ, ເພີ່ມຈຳນວນ
            } else {
                this.items.push({ ...product, quantity: 1 }); // ຖ້າບໍ່ມີ, ເພີ່ມສິນຄ້າໃໝ່ດ້ວຍຈຳນວນ 1
            }
        },

        // ລຶບສິນຄ້າອອກຈາກກະຕ່າ
        removeFromCart(productId) {
            this.items = this.items.filter(item => item.id !== productId);
        },

        // ເພີ່ມຈຳນວນສິນຄ້າໃນກະຕ່າ
        incrementQuantity(productId) {
            const item = this.items.find(item => item.id === productId);
            if (item) {
                item.quantity++;
            }
        },

        // ຫຼຸດຈຳນວນສິນຄ້າໃນກະຕ່າ
        decrementQuantity(productId) {
            const item = this.items.find(item => item.id === productId);
            if (item && item.quantity > 1) {
                item.quantity--;
            } else if (item && item.quantity === 1) {
                // ທາງເລືອກ: ລຶບອອກຖ້າຈຳນວນລົງເປັນ 0 ຫຼືໜ້ອຍກວ່າ
                this.removeFromCart(productId);
            }
        },

        // ລຶບລ້າງສິນຄ້າທັງໝົດໃນກະຕ່າ
        clearCart() {
            this.items = [];
        },
    },
});