<template>
    <div class="p-4 bg-base-100 dark:bg-gray-800 rounded-lg shadow-md flex flex-col md:flex-row gap-4">
        <!-- Left Section: Product Grid and Search -->
        <div class="md:w-2/3 w-full md:pr-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">ສິນຄ້າ</h2>

            <!-- Search and Category Filter -->
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <input type="text" v-model="searchQuery" placeholder="ຄົ້ນຫາສິນຄ້າ..." class="input input-bordered w-full sm:flex-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                <select v-model="selectedCategory" class="select select-bordered w-full sm:w-auto dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">ທຸກໝວດໝູ່</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
            </div>

            <!-- Product Grid -->
            <div v-if="apiStore.isLoading('pos_data')" class="flex justify-center items-center h-[calc(100vh-250px)]">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 overflow-y-auto  p-2  h-[calc(100vh-250px)]">
                <div v-for="product in filteredProducts" :key="product.id" @click="handleAddToCart(product)" class="card compact bg-base-200 dark:bg-gray-700 shadow-md cursor-pointer hover:shadow-lg transition-shadow duration-200  relative">
                    <span v-if="getCartQuantity(product.id) > 0" class="absolute top-2 right-2 badge badge-primary font-bold z-10">
                        {{ getCartQuantity(product.id) }}
                    </span>
                    <figure class="w-full h-32 flex items-center justify-center rounded-t-lg bg-gray-200 dark:bg-gray-600 text-3xl font-bold text-gray-700 dark:text-gray-200">
                        <img v-if="product.image_url" class="w-full h-32 object-cover rounded-t-lg" :src="apiStore.getFullImageUrl(product.image_url)" :alt="product.name" />
                        <span v-else>{{ product.name?.charAt(0) || '?' }}</span>
                    </figure>
                    <div class="card-body p-3">
                        <h5 class="card-title text-sm text-gray-900 dark:text-white">{{ product.name }}</h5>
                        <p class="text-xs text-gray-700 dark:text-gray-400">ສະຕັອກ: {{ product.stock_quantity }}</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">₭ {{ apiStore.formatNumber(product.selling_price) }}</p>
                    </div>
                </div>
                <div v-if="filteredProducts.length === 0" class="col-span-full text-center text-gray-500 dark:text-gray-400 py-8">
                    ບໍ່ພົບສິນຄ້າທີ່ຄົ້ນຫາ.
                </div>
            </div>
        </div>

        <!-- Right Section: Cart Section -->
        <div class="md:w-1/3 w-full">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">ກະຕ່າ</h2>

            <div class="bg-base-200 dark:bg-gray-700 p-3 rounded-lg mb-4 h-[calc(100vh-350px)] overflow-y-auto">
                <div v-if="cartStore.items.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-8">ກະຕ່າວ່າງເປົ່າ</div>
                <div v-for="item in cartStore.items" :key="item.id" class="flex items-center justify-between py-2 border-b border-base-300 last:border-b-0 dark:border-gray-600">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">₭{{ apiStore.formatNumber(item.price) }} x {{ item.quantity }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="cartStore.decrementQuantity(item.id)" class="btn btn-xs btn-outline btn-circle">-</button>
                        <span class="text-sm text-gray-900 dark:text-white">{{ item.quantity }}</span>
                        <button @click="cartStore.incrementQuantity(item.id)" class="btn btn-xs btn-outline btn-circle">+</button>
                        <button @click="cartStore.removeFromCart(item.id)" class="btn btn-xs btn-ghost text-red-500">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-base-200 dark:bg-gray-700 p-3 rounded-lg mb-4">
                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                    <span>ລວມທັງໝົດ:</span>
                    <span>₭ {{ apiStore.formatNumber(cartStore.totalPrice) }}</span>
                </div>
            </div>

            <button @click="openPaymentModal" :disabled="cartStore.items.length === 0" class="btn btn-success w-full mb-2">
              <CurrencyDollarIcon class="w-5 h-5 mr-2" />
              ຊໍາລະເງິນ
            </button>
            <button @click="cartStore.clearCart()" :disabled="cartStore.items.length === 0" class="btn btn-ghost w-full">
                ລຶບກະຕ່າ
            </button>
        </div>
    </div>

    <!-- Payment Modal -->
    <dialog id="paymentModal" class="modal" ref="paymentModalRef">
        <div class="modal-box p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">ຊໍາລະເງິນ</h3>
            <form @submit.prevent="processPayment">
                <div class="mb-4">
                    <label class="label">ຈຳນວນລວມທັງໝົດ</label>
                    <input type="text" :value="'₭ ' + apiStore.formatNumber(cartStore.totalPrice)" class="input input-bordered w-full bg-gray-100 dark:bg-gray-700" readonly>
                </div>
                <div class="mb-4">
                    <label class="label">ຈຳນວນເງິນທີ່ຮັບ</label>
                    <Cleave v-model="amountReceived" :options="cleaveOptions" class="input input-bordered w-full" required />
                </div>
                <div class="mb-4">
                    <label class="label">ເງິນທອນ</label>
                    <input type="text" :value="'₭ ' + apiStore.formatNumber(changeAmount)" class="input input-bordered w-full bg-gray-100 dark:bg-gray-700" readonly>
                </div>

                <div class="modal-action mt-6">
                    <button type="submit" class="btn btn-primary" :disabled="rawAmountReceived < cartStore.totalPrice || apiStore.isLoading('order_save')">
                        <span v-if="apiStore.isLoading('order_save')" class="loading loading-spinner"></span>
                        <CheckCircleIcon v-else class="w-5 h-5 me-2" /> ຢືນຢັນ
                    </button>
                    <button type="button" class="btn" @click="closePaymentModal">
                        <XMarkIcon class="w-5 h-5 me-2" /> ຍົກເລີກ
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Receipt Modal -->
    <ReceiptModal :orderData="receiptDataForModal" />
</template>

<script setup>
import { useCartStore } from '../stores/cart';
import { useApiStore } from '../stores/api';
import { ref, computed, onMounted } from 'vue';
import { TrashIcon, CurrencyDollarIcon, CheckCircleIcon, XMarkIcon } from '@heroicons/vue/20/solid';
import { useUiStore } from '../stores/ui';
import { useRouter } from 'vue-router';
import { useModalStore } from '../stores/modal';
import Cleave from 'vue-cleave-component';
import ReceiptModal from '../components/ReceiptModal.vue';

// ເກັບກຳຂໍ້ມູນຈາກ stores ຕ່າງໆ
const cartStore = useCartStore();
const uiStore = useUiStore();
const apiStore = useApiStore();
const router = useRouter();
const modalStore = useModalStore();

// ຕົວປ່ຽນສະຖານະສຳລັບຂໍ້ມູນສິນຄ້າ, ໝວດໝູ່, ການຄົ້ນຫາ ແລະ ໃບຮັບເງິນ
const products = ref([]); // ລາຍການສິນຄ້າທັງໝົດ
const categories = ref([]); // ລາຍການໝວດໝູ່ສິນຄ້າ
const selectedCategory = ref(''); // ໝວດໝູ່ທີ່ຖືກເລືອກສຳລັບການກັ່ນຕອງ
const searchQuery = ref(''); // ຂໍ້ຄວາມຄົ້ນຫາສິນຄ້າ

const receiptDataForModal = ref({}); // ຂໍ້ມູນໃບຮັບເງິນທີ່ຈະສົ່ງໃຫ້ ReceiptModal

// ຕົວເລືອກສຳລັບ Cleave.js ເພື່ອຈັດຮູບແບບຕົວເລກ
const cleaveOptions = {
    numeral: true, // ເປີດໃຊ້ງານການຈັດຮູບແບບຕົວເລກ
    numeralPositiveOnly: true, // ຮັບສະເພາະຕົວເລກບວກ
    numeralThousandsGroupStyle: 'thousand', // ຈັດກຸ່ມຕົວເລກຫຼັກພັນ
    prefix: '₭ ', // ເພີ່ມເຄື່ອງໝາຍ "₭ " ເປັນຄໍານໍາຫນ້າ
    rawValueTrimPrefix: true, // ຕັດຄໍານໍາຫນ້າອອກຈາກຄ່າດິບ
};

// ຟັງຊັນດຶງຂໍ້ມູນສິນຄ້າ ແລະ ໝວດໝູ່ຈາກ API
const fetchData = async () => {
    try {
        // ດຶງຂໍ້ມູນສິນຄ້າ ແລະ ໝວດໝູ່ພ້ອມກັນ
        const [prods, cats] = await Promise.all([
            apiStore.fetch('/products', { per_page: -1 }, 'pos_data'),
            apiStore.fetch('/categories', { per_page: -1 }, 'pos_data')
        ]);
        products.value = prods.data || prods; // ກໍານົດຄ່າສິນຄ້າ
        categories.value = cats.data || cats; // ກໍານົດຄ່າໝວດໝູ່
    } catch (error) {
        // ສະແດງຂໍ້ຜິດພາດຫາກການດຶງຂໍ້ມູນລົ້ມເຫຼວ
        modalStore.showErrorAlert(apiStore.error, 'Error fetching data');
    }
};

// ເມື່ອ component ຖືກ mounted ແລ້ວ
onMounted(() => {
    uiStore.closeSidebar(); // ປິດ sidebar
    fetchData(); // ເລີ່ມດຶງຂໍ້ມູນ
});

// ຄຸນສົມບັດຄຳນວນສຳລັບການກັ່ນຕອງສິນຄ້າຕາມໝວດໝູ່ ແລະ ຄຳຄົ້ນຫາ
const filteredProducts = computed(() => {
    let filtered = products.value;

    // ກັ່ນຕອງຕາມໝວດໝູ່ທີ່ເລືອກ
    if (selectedCategory.value) {
        filtered = filtered.filter(p => p.category_id === selectedCategory.value);
    }

    // ກັ່ນຕອງຕາມຄຳຄົ້ນຫາ
    if (searchQuery.value) {
        const lowerCaseQuery = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => p.name.toLowerCase().includes(lowerCaseQuery));
    }

    return filtered;
});

// ຟັງຊັນສຳລັບການເພີ່ມສິນຄ້າເຂົ້າກະຕ່າ
const handleAddToCart = (product) => {
    // ກວດສອບສະຕັອກສິນຄ້າ
    if (product.stock_quantity <= 0) {
        modalStore.showErrorAlert('ສິນຄ້າໝົດແລ້ວ', 'ບໍ່ສາມາດເພີ່ມໄດ້');
        return;
    }
    // ກວດສອບວ່າຈຳນວນສິນຄ້າໃນກະຕ່າບໍ່ເກີນສະຕັອກ
    const cartItem = cartStore.items.find(item => item.id === product.id);
    if (cartItem && cartItem.quantity >= product.stock_quantity) {
        modalStore.showErrorAlert('ຈຳນວນໃນກະຕ່າເກີນສະຕັອກ', 'ບໍ່ສາມາດເພີ່ມໄດ້');
        return;
    }
    // ເພີ່ມສິນຄ້າເຂົ້າກະຕ່າ
    cartStore.addToCart({
        ...product,
        price: product.selling_price // ຮັບປະກັນວ່າກະຕ່າໃຊ້ລາຄາຂາຍ
    });
};

// ຟັງຊັນເພື່ອກວດສອບຈຳນວນສິນຄ້າໃນກະຕ່າ
const getCartQuantity = (productId) => {
    const item = cartStore.items.find(item => item.id === productId);
    return item ? item.quantity : 0;
};

// Payment Modal Logic - ການຈັດການ Modal ຊໍາລະເງິນ
const paymentModalRef = ref(null); // Ref ສໍາລັບ Payment Modal
const amountReceived = ref(0); // ຈໍານວນເງິນທີ່ໄດ້ຮັບ

// ຄຸນສົມບັດຄຳນວນສຳລັບຄ່າເງິນທີ່ໄດ້ຮັບແບບດິບ (ບໍ່ມີເຄື່ອງໝາຍສະກຸນເງິນ)
const rawAmountReceived = computed(() => Number(String(amountReceived.value).replace(/[^0-9.-]+/g, "")));
// ຄຸນສົມບັດຄຳນວນສຳລັບເງິນທອນ
const changeAmount = computed(() => {
    return rawAmountReceived.value >= cartStore.totalPrice ? rawAmountReceived.value - cartStore.totalPrice : 0;
});

// ຟັງຊັນເປີດ Payment Modal
const openPaymentModal = () => {
    amountReceived.value = 0; // ຕັ້ງຄ່າເງິນທີ່ໄດ້ຮັບເປັນ 0
    paymentModalRef.value.showModal(); // ສະແດງ modal
};
// ຟັງຊັນປິດ Payment Modal
const closePaymentModal = () => paymentModalRef.value.close();

// ຟັງຊັນດໍາເນີນການຊໍາລະເງິນ
const processPayment = async () => {
    // ກວດສອບວ່າເງິນທີ່ໄດ້ຮັບພຽງພໍຫຼືບໍ່
    if (rawAmountReceived.value < cartStore.totalPrice) {
        modalStore.showErrorAlert('ຈຳນວນເງິນທີ່ຮັບບໍ່ພຽງພໍ!', 'ຂໍ້ຜິດພາດ');
        return;
    }
    
    // ສ້າງ payload ສໍາລັບຄໍາສັ່ງຊື້
    const orderPayload = {
        items: cartStore.items.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
        })),
        amount_received: rawAmountReceived.value,
        change_amount: changeAmount.value,
    };

    try {
        // ສົ່ງຄໍາສັ່ງຊື້ໄປຍັງ API
        const orderData = await apiStore.post('/orders', orderPayload, 'order_save');
        
        // ສ້າງຂໍ້ມູນຄໍາສັ່ງຊື້ສຸດທ້າຍລວມເງິນທີ່ໄດ້ຮັບ ແລະ ເງິນທອນ
        const finalOrderData = {
            ...orderData,
            amount_received: rawAmountReceived.value,
            change_amount: changeAmount.value
        };
        
        // ກໍານົດຂໍ້ມູນໃບຮັບເງິນສຳລັບ Modal
        receiptDataForModal.value = finalOrderData;

        cartStore.clearCart(); // ລ້າງກະຕ່າ
        closePaymentModal(); // ປິດ Payment Modal
        fetchData(); // ໂຫຼດລາຍການສິນຄ້າຄືນໃໝ່ເພື່ອສະແດງສະຕັອກທີ່ອັບເດດ
    } catch (error) {
        // ສະແດງຂໍ້ຜິດພາດຫາກການສັ່ງຊື້ຜິດພາດ
        modalStore.showErrorAlert(apiStore.error, 'ການສັ່ງຊື້ຜິດພາດ');
    }
};
</script>
