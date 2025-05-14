<template>
    <div :class="['flex flex-col h-screen', isDarkMode ? 'dark-mode bg-gray-900' : 'bg-blue-500']">
        <!-- 使用共用頂部導覽列 -->
        <Navbar
            pageTitle="收銀台"
            menuTitle="功能選單"
            :menuOpen="menuOpen"
            :isDarkMode="isDarkMode"
            :sessions="sessions"
            :currentSession="selectedSession"
            :showSessionSelector="true"
            @toggleMenu="menuOpen = !menuOpen"
            @toggleDarkMode="toggleDarkMode"
            @sessionChange="handleSessionChange"
        />

        <!-- 側邊選單 (收合時隱藏) -->
        <div
            v-if="menuOpen"
            :class="[
                isDarkMode ? 'bg-gray-800' : 'bg-blue-100',
                'fixed inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out pt-20'
            ]"
        >
            <div class="px-4">
                <div class="mb-4 p-2 rounded-lg" :class="isDarkMode ? 'bg-gray-700 text-white' : 'bg-blue-200'">
                    <a href="/cashier" class="block p-2 rounded hover:bg-blue-500 hover:text-white">收銀台</a>
                </div>
                <div class="mb-4 p-2 rounded-lg" :class="isDarkMode ? 'bg-gray-700 text-white' : 'bg-blue-200'">
                    <a href="/cashier/preorder" class="block p-2 rounded hover:bg-blue-500 hover:text-white">預留單</a>
                </div>
                <!-- 添加其他功能選單項目 -->
            </div>
        </div>

        <!-- 主要內容區域 - 不再預留購物車空間 -->
        <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'flex-1 w-full flex justify-center overflow-hidden']">
            <div class="w-full max-w-[95%] xl:max-w-[90%] 2xl:max-w-[85%] flex h-full">
                <!-- 左側產品網格 (現在佔據全部寬度) -->
                <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'w-full p-4 overflow-y-auto']">
                    <!-- 左側添加場次信息顯示 -->
                    <div :class="[isDarkMode ? 'bg-gray-700 text-gray-200' : 'bg-blue-100 text-blue-800', 'mb-4 p-3 rounded-lg']">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">當前場次：{{ currentSessionName }}</span>
                            <span class="text-sm" :class="isDarkMode ? 'text-gray-400' : 'text-blue-600'">
                        {{ currentSessionTime }}
                    </span>
                        </div>
                    </div>

                    <!-- 商品資料載入中的提示 -->
                    <div v-if="isLoading" :class="[isDarkMode ? 'bg-gray-700' : 'bg-blue-50', 'p-8 rounded-lg flex flex-col items-center justify-center']">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 mb-4" :class="isDarkMode ? 'border-blue-400' : 'border-blue-600'"></div>
                        <p :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">正在載入商品資料...</p>
                    </div>

                    <!-- 無商品資料的提示 -->
                    <div v-else-if="productItems.length === 0" :class="[isDarkMode ? 'bg-gray-700' : 'bg-blue-50', 'p-8 rounded-lg flex flex-col items-center justify-center']">
                        <div class="text-4xl mb-4">📦</div>
                        <p :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'" class="text-center">
                            此場次沒有可用的商品<br>
                            請選擇其他場次或聯絡管理員新增商品
                        </p>
                    </div>

                    <div v-if="productCategories.length === 0" :class="[isDarkMode ? 'bg-gray-700' : 'bg-blue-50', 'p-8 rounded-lg flex flex-col items-center justify-center']">
                        <div class="text-4xl mb-4">📋</div>
                        <p :class="[isDarkMode ? 'text-gray-300' : 'text-gray-700']" class="text-center">
                            沒有找到商品類別<br>
                            請確認商品數據是否包含類型資訊
                        </p>
                    </div>

                    <!-- 商品網格區域 -->
                    <div v-else class="space-y-6">
                        <!-- 針對每個商品類別創建一個區塊 -->
                        <div v-for="category in productCategories" :key="category.id" class="mb-4">
                            <!-- 類別標題 -->
                            <div :class="[isDarkMode ? 'bg-gray-700 text-gray-200' : 'bg-blue-100 text-blue-800', 'p-3 rounded-lg mb-3']">
                                <h3 class="text-lg font-bold">{{ category.name }}</h3>
                            </div>

                            <!-- 該類別的商品網格 - 優化網格列數 -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 xs:gap-2">
                                <div
                                    v-for="item in category.items"
                                    :key="item.id"
                                    :class="[
                                getColorForItem(item.id, isDarkMode),
                                'rounded-lg overflow-hidden cursor-pointer transform transition-transform duration-200 hover:scale-105 relative xs:text-sm shadow-md'
                            ]"
                                    @click="addToCart(item.id)"
                                >
                                    <!-- 商品內容不變 -->
                                    <!-- 背景圖片層 -->
                                    <div
                                        v-if="item.item_img_url"
                                        class="absolute inset-0 z-0 w-full h-full"
                                        :style="{
                                    backgroundImage: `url(${item.item_img_url})`,
                                    backgroundSize: 'cover',
                                    backgroundPosition: 'center'
                                }"
                                    ></div>

                                    <!-- 半透明覆蓋層 -->
                                    <div
                                        v-if="item.item_img_url"
                                        class="absolute inset-0 z-0"
                                        :style="{
                                    backgroundColor: 'rgba(0, 0, 0, 0.35)',
                                    backdropFilter: 'blur(0.5px)'
                                }"
                                    ></div>

                                    <div class="relative">
                                        <div
                                            :class="[
                                        getCartQuantity(item.id) > 0 ? 'bg-blue-600' : isDarkMode ? 'bg-gray-700' : 'bg-gray-500',
                                        'absolute top-2 left-2 w-10 h-10 rounded-full flex items-center justify-center text-white z-10'
                                    ]"
                                        >
                                            {{ getCartQuantity(item.id) > 0 ? getCartQuantity(item.id) : 0 }}
                                        </div>
                                        <div class="bg-green-600 text-white p-2 m-2 rounded inline-block float-right">
                                            現場:{{ item.item_stock - getCartQuantity(item.id) }}
                                        </div>
                                        <div class="bg-amber-600 text-white p-2 m-2 rounded inline-block float-right">
                                            已預訂:{{ item.preOrder }}
                                        </div>
                                    </div>

                                    <div :class="[!item.item_img_url ? getColorForItem(item.id, isDarkMode) : '', 'p-4 mt-12 text-white relative z-10']">
                                        <div class="text-xl font-bold mb-1">{{ item.title }}</div>
                                        <h3 class="text-xl font-bold mb-2">{{ item.item_name }}</h3>
                                        <h4 v-if="item.item_name_jp" class="text-m font-bold mb-2">{{ item.item_name_jp }}</h4>
                                        <h4 v-if="item.item_name_en" class="text-m font-bold mb-2">{{ item.item_name_en }}</h4>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xl">${{ item.item_price }}</span>
                                            <span v-if="item.is_r18" class="bg-red-600 text-white px-2 py-1 rounded-lg text-sm">18+</span>
                                        </div>
                                    </div>

                                    <!-- 商品添加視覺反饋 -->
                                    <div
                                        v-if="recentlyAdded === item.id"
                                        class="absolute inset-0 bg-white bg-opacity-30 flex items-center justify-center z-20"
                                    >
                                        <div class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg">
                                            已加入購物車
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右側漂浮購物車 (替換原有的購物車區塊) -->
        <div :class="[
                isDarkMode ? 'bg-gray-700' : 'bg-gray-100',
                'w-full xs:w-full sm:w-full md:w-96 lg:w-96 overflow-y-auto fixed right-0 top-20 bottom-0 z-20 transition-transform duration-300 transform shadow-xl',
                isCartVisible ? 'translate-x-0' : 'translate-x-full'
            ]" style="max-height: calc(100vh - 80px);">
            <!-- 購物車內容 -->
            <div class="h-full flex flex-col">
                <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'p-4 flex-1 overflow-y-auto']">
                    <div class="sticky top-0 pb-4 z-10" :class="isDarkMode ? 'bg-gray-800' : 'bg-white'">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b" :class="isDarkMode ? 'border-gray-700' : 'border-gray-200'">
                            <h2 :class="[isDarkMode ? 'text-gray-200' : 'text-gray-800', 'text-xl font-bold']">
                                購物車
                            </h2>
                            <!-- 關閉購物車按鈕 -->
                            <button
                                @click="toggleCart"
                                class="rounded-full p-2"
                                :class="isDarkMode ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-200'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="flex flex-col py-3 border-b"
                        :class="isDarkMode ? 'border-gray-700' : 'border-gray-200'"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <span :class="[isDarkMode ? 'text-gray-200' : 'text-gray-800', 'text-lg font-medium']">{{ item.item_name }}</span>
                                <span
                                    v-if="item.is_r18"
                                    class="bg-red-600 text-white px-2 py-1 rounded-full text-sm ml-2"
                                >18+</span>
                            </div>
                            <span :class="[isDarkMode ? 'text-gray-300' : 'text-gray-700', 'font-medium']">${{ item.item_price }}</span>
                        </div>
                        <div class="flex items-center justify-end">
                            <button
                                @click.stop="decreaseQuantity(item.id)"
                                :class="[isDarkMode ? 'bg-gray-600 text-gray-200' : 'bg-gray-200 text-gray-700', 'w-8 h-8 rounded-md flex items-center justify-center text-lg']"
                            >
                                −
                            </button>
                            <div :class="[isDarkMode ? 'border-gray-600 text-gray-200' : 'border-gray-300', 'w-16 h-8 mx-2 border rounded-md flex items-center justify-center']">
                                {{ item.quantity }}
                            </div>
                            <button
                                @click.stop="increaseQuantity(item.id)"
                                :class="[isDarkMode ? 'bg-gray-600 text-gray-200' : 'bg-gray-200 text-gray-700', 'w-8 h-8 rounded-md flex items-center justify-center text-lg']"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    <!-- 購物車為空的提示 -->
                    <div v-if="cartItems.length === 0" :class="[isDarkMode ? 'text-gray-400' : 'text-gray-500', 'py-8 text-center']">
                        購物車是空的<br>點擊商品加入購物車
                    </div>

                    <div v-else>
                        <!-- 年齡驗證 -->
                        <div v-if="hasAdultItems" class="bg-red-600 text-white p-3 my-4 rounded-lg flex items-center text-sm">
                            <span class="text-xl mr-2">⚠️</span>
                            <span>請確認購買者年滿18歲（民國 {{ r18Date }} 以前出生）</span>
                        </div>

                        <div class="flex justify-between items-center mt-6 text-xl font-bold" :class="{ 'text-gray-200': isDarkMode }">
                            <span>總計</span>
                            <span>${{ total }}</span>
                        </div>
                    </div>
                </div>

                <!-- 操作按鈕區 (固定在底部) -->
                <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'p-4 border-t', isDarkMode ? 'border-gray-700' : 'border-gray-200']">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <button
                            @click="clearCart"
                            :class="[
                        'py-3 px-4 rounded-lg border-2 text-lg font-medium transition-colors duration-200',
                        cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : '',
                        isDarkMode ? 'border-pink-700 text-pink-400 hover:bg-pink-900 hover:bg-opacity-30' : 'border-pink-500 text-pink-500 hover:bg-pink-50'
                    ]"
                            :disabled="cartItems.length === 0"
                        >
                            清空
                        </button>
                        <button
                            @click="showCheckoutModal"
                            :class="[
                        'py-3 px-4 rounded-lg text-white text-lg font-medium transition-colors duration-200',
                        cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : '',
                        isDarkMode ? 'bg-blue-700 hover:bg-blue-600' : 'bg-blue-500 hover:bg-blue-400'
                    ]"
                            :disabled="cartItems.length === 0"
                        >
                            結帳
                        </button>
                    </div>

                    <!-- 預留單按鈕 -->
                    <div v-if="cartItems.length > 0">
                        <button
                            @click="createPreOrder"
                            :class="[
                        'w-full py-3 px-4 rounded-lg text-lg font-medium transition-colors duration-200',
                        isDarkMode ? 'bg-green-700 hover:bg-green-600 text-white' : 'bg-green-500 hover:bg-green-400 text-white'
                    ]"
                        >
                            建立預留單
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 固定在右下角的購物車按鈕 -->
        <button
            @click="toggleCart"
            class="fixed z-30 bottom-6 right-6 rounded-full w-16 h-16 flex items-center justify-center shadow-lg"
            :class="isDarkMode ? 'bg-blue-700 hover:bg-blue-600' : 'bg-blue-500 hover:bg-blue-400'"
        >
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <div v-if="cartTotalQuantity > 0" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">
                    {{ cartTotalQuantity }}
                </div>
            </div>
        </button>

        <!-- 引入付款元件 -->
        <CheckoutModal
            :quickAmounts="quickAmounts"
            :payment="payment"
            :is-visible="isCheckoutModalVisible"
            :is-dark-mode="isDarkMode"
            :cart-items="cartItems"
            :total="total"
            :r18Date="r18Date"
            :has-adult-items="hasAdultItems"
            :session-id="selectedSession"
            :session-name="currentSessionName"
            @close="closeCheckoutModal"
            @complete="handleTransactionComplete"
        />
    </div>
</template>

<script setup>
import {ref, computed, onMounted, watch, onUnmounted} from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Navbar from './Components/Navbar.vue';
import CheckoutModal from './Components/CheckoutModel.vue';

// 從 Inertia props 中獲取商品資料
const page = usePage();
const quickAmounts = computed(() => page.props.quickAmounts || []);
const r18Date = computed(() => page.props.r18Date);
const payment = computed(() => page.props.payment);
const userID = computed(() => page.props.user);

// 場次資料
const sessions = computed(() => page.props.events);

// 商品資料狀態
const productItems = ref([]);
const isLoading = ref(false);

// 側邊選單狀態
const menuOpen = ref(false);

// 購物車資料（本地狀態）
const cart = ref([]);
// 追蹤最近添加的商品
const recentlyAdded = ref(null);
// 深夜模式狀態
const isDarkMode = ref(false);
// 結帳彈出視窗狀態
const isCheckoutModalVisible = ref(false);
// 選中的場次
const selectedSession = ref(1);

// 新增行動裝置購物車顯示狀態
const isCartVisible = ref(false);

// 計算當前場次名稱
const currentSessionName = computed(() => {
    const session = sessions.value.find(s => s.id === selectedSession.value);
    return session ? session.event_name : '未選擇場次';
});

// 計算當前場次時間
const currentSessionTime = computed(() => {
    const session = sessions.value.find(s => s.id === selectedSession.value);
    return session ? session.time : '';
});

const productCategories = computed(() => {
    // 從商品列表中獲取所有不重複的類型 ID
    const categoryIds = [...new Set(productItems.value.map(item => item.item_type_id))];

    // 依照類型 ID 為每個類別創建對象
    return categoryIds.map(typeId => {
        // 找出該類別的第一個商品來取得類別名稱
        const categoryItem = productItems.value.find(item => item.item_type_id === typeId);
        const categoryName = categoryItem?.item_type_name || `類別 ${typeId}`;

        // 過濾出屬於這個類別的所有商品
        const items = productItems.value.filter(item => item.item_type_id === typeId);

        return {
            id: typeId,
            name: categoryName,
            items: items
        };
    }).sort((a, b) => a.id - b.id); // 依照 ID 排序
});

// 處理場次變更
const handleSessionChange = (sessionId) => {
    console.log('場次已變更為：', sessionId);
    selectedSession.value = sessionId;
    // 重新載入該場次的商品資料
    fetchProductsBySession(sessionId);
    // 清空購物車，避免跨場次的商品混合
    clearCart();
    // 保存選擇的場次到本地儲存
    localStorage.setItem('selectedSession', sessionId);
};

// 根據場次ID擷取對應的商品資料
const fetchProductsBySession = async (sessionId) => {
    try {
        isLoading.value = true;
        console.log(`正在擷取場次 ${sessionId} 的商品資料...`);

        // 呼叫API獲取特定場次的商品資料
        const response = await fetch(`/api/items/get/${userID.value}/${sessionId}`);

        if (!response.ok) {
            throw new Error(`無法獲取場次 ${sessionId} 的商品資料: ${response.status}`);
        }

        const data = await response.json();
        console.log(data.data);

        // 更新商品資料
        productItems.value = data.data || [];

        console.log(`已成功載入 ${productItems.value.length} 個商品`);

        // 重新初始化購物車（保持相同結構但數量為0）
        initializeCart();
    } catch (error) {
        console.error('擷取商品資料時發生錯誤:', error);
        // 顯示錯誤訊息給使用者
        alert(`無法載入商品資料，請重新整理頁面或聯絡系統管理員。\n錯誤訊息: ${error.message}`);
    } finally {
        isLoading.value = false;
    }
};

// 初始化空購物車
const initializeCart = () => {
    cart.value = productItems.value.map(item => ({
        id: item.id,
        quantity: 0
    }));
    saveCart();
};

// 建立預留單功能
const createPreOrder = async () => {
    // 準備預留單資料
    const preOrderData = {
        id:generateTransactionId(),
        items: cartItems.value.map(item => ({
            id: item.id,
            quantity: item.quantity,
        })),
        total: total.value,
        sessionId: selectedSession.value,
        ownerId: userID.value,
        createdAt: new Date().toISOString()
    };

    try {
        // 方法一：使用 fetch API
        const response = await fetch('/api/order/preorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(preOrderData)
        });

        if (!response.ok) {
            throw new Error(`API 回應錯誤: ${response.status}`);
        }

        const result = await response.json();
        console.log('API回應結果:', result);

        // 顯示交易成功訊息
        alert('已成功建立預留單！');

        // 清空購物車
        clearCart();

        ////更新商品
        await fetchProductsBySession(selectedSession.value)
    } catch (error) {
        console.error('傳送交易資料時發生錯誤:', error);
        alert(`交易記錄儲存失敗，請聯絡系統管理員。錯誤訊息: ${error.message}`);
    }

};

// 初始化購物車與深夜模式
onMounted(async () => {
    document.title = page.props.title
    // 載入深夜模式偏好
    const darkModePref = localStorage.getItem('darkMode');
    if (darkModePref !== null) {
        isDarkMode.value = JSON.parse(darkModePref);
    }

    // 載入上次選擇的場次
    const savedSession = localStorage.getItem('selectedSession');
    if (savedSession !== null) {
        selectedSession.value = parseInt(savedSession);
    }

    // 根據選中的場次載入商品資料
    await fetchProductsBySession(selectedSession.value);

    // 載入購物車資料（在商品資料載入後）
    const savedCart = localStorage.getItem('shoppingCart');
    if (savedCart) {
        const parsedCart = JSON.parse(savedCart);
        // 檢查購物車中的商品是否存在於當前場次
        cart.value = parsedCart.filter(cartItem =>
            productItems.value.some(product => product.id === cartItem.id)
        );
        saveCart(); // 保存過濾後的購物車
    } else {
        // 初始化空購物車
        initializeCart();
    }

    isCartVisible.value = false;

    // 監聽視窗大小變化，大螢幕時自動關閉背景滾動限制
    const handleResize = () => {
        if (window.innerWidth >= 768) {
            document.body.style.overflow = '';
        } else if (isCartVisible.value) {
            document.body.style.overflow = 'hidden';
        }
    };

    // 監聽視窗大小變化
    window.addEventListener('resize', handleResize);

    // 組件卸載時移除事件監聽
    onUnmounted(() => {
        window.removeEventListener('resize', handleResize);
        document.body.style.overflow = '';
    });
});

// 修改關閉結帳彈出視窗方法，同時關閉購物車
const closeCheckoutModal = () => {
    isCheckoutModalVisible.value = false;
    isCartVisible.value = false;
};

// 修改顯示結帳彈出視窗方法，確保購物車也會關閉
const showCheckoutModal = () => {
    isCheckoutModalVisible.value = true;
    isCartVisible.value = false;
};

// 獲取商品在購物車中的數量
const getCartQuantity = (id) => {
    const cartItem = cart.value.find(item => item.id === id);
    return cartItem ? cartItem.quantity : 0;
};

// 購物車中的商品（數量大於0）
const cartItems = computed(() => {
    return cart.value
        .filter(cartItem => cartItem.quantity > 0)
        .map(cartItem => {
            const productItem = productItems.value.find(p => p.id === cartItem.id);
            return {
                ...productItem,
                quantity: cartItem.quantity
            };
        });
});

// 計算總金額
const total = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (item.item_price * item.quantity), 0);
});

// 檢查購物車中是否有成人商品
const hasAdultItems = computed(() => {
    return cartItems.value.some(item => item.is_r18);
});

const cartTotalQuantity = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

const toggleCart = () => {
    isCartVisible.value = !isCartVisible.value;

    // 如果購物車顯示，禁止背景滾動以提升移動裝置體驗
    if (isCartVisible.value && window.innerWidth < 768) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

// 生成唯一交易 ID
const generateTransactionId = () => {
    return 'TR-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
};

// 處理交易完成
const handleTransactionComplete = async (transaction) => {
    console.log('交易完成：', transaction);

    // 記錄交易資料
    const transactionRecord = {
        id: generateTransactionId(), // 生成唯一交易ID
        timestamp: transaction.timestamp,
        items: transaction.items,
        total: total.value,
        receivedAmount: transaction.amount,
        change: transaction.change,
        note: transaction.note,
        hasAdultItems: hasAdultItems.value,
        paymentMethod: transaction.paymentMethod,
        sessionId: selectedSession.value,
        sessionName: currentSessionName.value,
        ownerId: userID.value
    };
    console.log('交易資料:', transactionRecord);

    // 使用 Inertia 發送交易資料到後端
    try {
        // 方法一：使用 fetch API
        const response = await fetch('/api/order/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(transactionRecord)
        });

        if (!response.ok) {
            throw new Error(`API 回應錯誤: ${response.status}`);
        }

        const result = await response.json();
        console.log('API回應結果:', result);

        // 顯示交易成功訊息
        alert(`交易完成！總金額: $${total.value}, 收款: $${transaction.amount}, 找零: $${transaction.change}`);

        // 清空購物車
        clearCart();

        // 關閉彈出視窗
        closeCheckoutModal();

        //更新商品
        await fetchProductsBySession(selectedSession.value)
    } catch (error) {
        console.error('傳送交易資料時發生錯誤:', error);
        alert(`交易記錄儲存失敗，請聯絡系統管理員。錯誤訊息: ${error.message}`);
    }
};

// 點擊商品添加到購物車
const addToCart = (id) => {
    const productItem = productItems.value.find(item => item.id === id);
    const cartItem = cart.value.find(item => item.id === id);

    if (productItem && productItem.item_stock > 0 && cartItem) {
        if (cartItem.quantity < productItem.item_stock) {
            cartItem.quantity += 1;
            // 保存購物車狀態
            saveCart();
            // 顯示添加成功的視覺反饋
            recentlyAdded.value = id;
            setTimeout(() => {
                recentlyAdded.value = null;
            }, 500);
        } else {
            // 顯示庫存不足提示
            alert(`商品「${productItem.item_name}」庫存不足，無法再增加數量。`);
        }
    }
};

// 增加商品數量
const increaseQuantity = (id) => {
    const productItem = productItems.value.find(item => item.id === id);
    const cartItem = cart.value.find(item => item.id === id);

    if (productItem && cartItem && cartItem.quantity < productItem.item_stock) {
        cartItem.quantity++;
        // 保存購物車狀態
        saveCart();
        // 顯示添加成功的視覺反饋
        recentlyAdded.value = id;
        setTimeout(() => {
            recentlyAdded.value = null;
        }, 500);
    } else if (productItem && cartItem) {
        // 顯示庫存不足提示
        alert(`商品「${productItem.item_name}」庫存不足，無法再增加數量。`);
    }
};

// 減少商品數量
const decreaseQuantity = (id) => {
    const cartItem = cart.value.find(item => item.id === id);

    if (cartItem && cartItem.quantity > 0) {
        cartItem.quantity--;
        // 保存購物車狀態
        saveCart();
        // 如果數量減為0，可以顯示移除的視覺反饋
        if (cartItem.quantity === 0) {
            recentlyAdded.value = id;
            setTimeout(() => {
                recentlyAdded.value = null;
            }, 500);
        }
    }
};

// 切換深夜模式
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('darkMode', JSON.stringify(isDarkMode.value));
};

// 保存購物車到 localStorage
const saveCart = () => {
    localStorage.setItem('shoppingCart', JSON.stringify(cart.value));
};

// 清空購物車
const clearCart = () => {
    cart.value.forEach(item => {
        item.quantity = 0;
    });
    // 保存購物車狀態
    saveCart();
};

// 根據商品ID和深夜模式獲取背景顏色
const getColorForItem = (id, darkMode) => {
    const lightColors = {
        1: 'bg-red-800',
        2: 'bg-blue-800',
        3: 'bg-green-800',
        4: 'bg-amber-800',
        5: 'bg-purple-900'
    };

    const darkColors = {
        1: 'bg-red-900',
        2: 'bg-blue-900',
        3: 'bg-green-900',
        4: 'bg-amber-900',
        5: 'bg-purple-950'
    };

    // 取餘數確保每個商品都能對應到一個顏色（循環使用）
    const colorIndex = (id % 5) || 5;

    return darkMode
        ? (darkColors[colorIndex] || 'bg-gray-900')
        : (lightColors[colorIndex] || 'bg-gray-800');
};
</script>

<style>
/* 深夜模式相關樣式 */
.dark-mode {
    color-scheme: dark;
}

.dark-mode .border-b {
    border-color: #3a3a3a !important;
}

/* 深暗色背景下的輸入框和按鈕 */
.dark-mode input,
.dark-mode button,
.dark-mode textarea,
.dark-mode select {
    color-scheme: dark;
}

/* 下拉選單樣式 */
select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

/* 過渡效果 */
.dark-mode,
.dark-mode *,
body,
* {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* 自適應佈局 */
/* 自適應佈局 - 使用 Tailwind 的響應式類別替代 */
@media (max-width: 1280px) {
    .max-w-[70%] {
    max-width: 85%;
}
}

@media (max-width: 768px) {
    .max-w-[70%] {
    max-width: 95%;
}
}

@media (max-width: 640px) {
    .max-w-[70%] {
    max-width: 100%;
}

    /* 改善小螢幕上的間距 */
    .p-4 {
        padding: 0.75rem;
    }

    /* 改善小螢幕上的文字大小 */
    .text-xl {
        font-size: 1.1rem;
    }
}
@media (max-width: 639px) {
    /* 確保容器不會溢出 */
    .w-full {
        width: 100% !important;
    }

    /* 調整內邊距 */
    .p-4 {
        padding: 0.75rem !important;
    }

    /* 確保按鈕有足夠的點擊區域 */
    button {
        min-height: 40px;
    }

    /* 減小文字大小 */
    .text-xl {
        font-size: 1rem !important;
    }

    /* 確保容器內的元素排列正確 */
    .flex-col {
        flex-direction: column !important;
    }
}
/* 購物車按鈕樣式增強 */
.fixed.z-30.bottom-6.right-6 {
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
    transition: transform 0.2s, filter 0.2s;
    animation: float 3s ease-in-out infinite;
}

.fixed.z-30.bottom-6.right-6:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 6px 8px rgba(0, 0, 0, 0.2));
    animation-play-state: paused;
}

.fixed.z-30.bottom-6.right-6:active {
    transform: scale(0.95);
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-5px);
    }
    100% {
        transform: translateY(0px);
    }
}

/* 滑入滑出動畫優化 */
.transition-transform.duration-300 {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* 項目增刪動畫 */
.flex-col.py-3 {
    transition: all 0.3s ease;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 購物車按鈕徽章動畫 */
.absolute.-top-2.-right-2 {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* 改進滾動條樣式 */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.dark-mode .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

/* 購物車覆蓋層 */
.fixed.right-0.top-20.bottom-0.z-20 {
    box-shadow: -5px 0 25px rgba(0, 0, 0, 0.25);
}

/* 購物車容器內部元素反應式調整 */
@media (max-width: 640px) {
    .fixed.right-0.top-20.bottom-0.z-20 {
        width: 100%;
    }
}

/* 改善按鈕觸感 */
button {
    position: relative;
    overflow: hidden;
}

button::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%, -50%);
    transform-origin: 50% 50%;
}

button:active::after {
    opacity: 0.3;
    transform: scale(50, 50) translate(-50%, -50%);
    transition: transform 0.5s, opacity 1s;
}

/* 購物車內容容器在行動裝置上的邊距調整 */
@media (max-width: 640px) {
    .p-4 {
        padding: 0.75rem;
    }

    .h-8 {
        height: 2rem;
    }

    .w-8 {
        width: 2rem;
    }
}

/* 購物車彈出時的背景覆蓋層 */
.cart-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 15;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.cart-backdrop.active {
    opacity: 1;
    visibility: visible;
}
</style>
