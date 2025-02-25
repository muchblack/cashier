<template>
    <div :class="['flex flex-col min-h-screen', isDarkMode ? 'dark-mode bg-gray-900' : 'bg-blue-500']">
        <!-- 頂部導航欄 -->
        <header class="flex items-center justify-between p-4 bg-blue-500 text-white">
            <button class="text-3xl"></button>
            <h1 class="text-2xl font-bold">收銀台</h1>
            <div class="flex items-center">
                <div class="ml-4 text-2xl cursor-pointer" @click="toggleDarkMode">
                    {{ isDarkMode ? '☀️' : '🌙' }}
                </div>
            </div>
        </header>

        <!-- 產品網格 -->
        <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'grid grid-cols-2 gap-4 p-4']">
            <div
                v-for="item in productItems"
                :key="item.id"
                :class="[getColorForItem(item.id, isDarkMode), 'rounded-lg overflow-hidden cursor-pointer transform transition-transform duration-200 hover:scale-105']"
                @click="addToCart(item.id)"
            >
                <div class="relative">
                    <div
                        :class="[
                            getCartQuantity(item.id) > 0 ? 'bg-blue-600' : isDarkMode ? 'bg-gray-700' : 'bg-gray-500',
                            'absolute top-2 left-2 w-10 h-10 rounded-full flex items-center justify-center text-white'
                        ]"
                    >
                        {{ getCartQuantity(item.id) > 0 ? getCartQuantity(item.id) : 0 }}
                    </div>
                    <div class="bg-green-600 text-white p-2 m-2 rounded inline-block float-right">
                        庫存:{{ item.item_stock - getCartQuantity(item.id)}}
                    </div>
                </div>
                <div :class="[getColorForItem(item.id, isDarkMode), 'p-4 mt-12 text-white']">
                    <h3 class="text-xl font-bold mb-2">{{ item.item_name }}</h3>
                    <div class="flex items-center justify-between">
                        <span class="text-xl">${{ item.item_price }}</span>
                        <span v-if="item.is_r18" class="bg-red-600 text-white px-2 py-1 rounded-full text-sm">18+</span>
                    </div>
                </div>
                <!-- 商品添加視覺反饋 -->
                <div
                    v-if="recentlyAdded === item.id"
                    class="absolute inset-0 bg-white bg-opacity-30 flex items-center justify-center"
                >
                    <div class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg">
                        已加入購物車
                    </div>
                </div>
            </div>
        </div>

        <!-- 購物車 -->
        <div :class="[isDarkMode ? 'bg-gray-700' : 'bg-gray-100', 'flex-grow p-4']">
            <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'rounded-lg p-4 shadow']">
                <div
                    v-for="item in cartItems"
                    :key="item.id"
                    class="flex items-center justify-between py-4 border-b"
                >
                    <div class="flex items-center">
                        <span :class="[isDarkMode ? 'text-gray-200' : 'text-gray-800', 'text-lg font-medium']">{{ item.item_name }}</span>
                        <span
                            v-if="item.is_r18"
                            class="bg-red-600 text-white px-2 py-1 rounded-full text-sm ml-2"
                        >18+</span>
                    </div>
                    <div class="flex items-center">
                        <button
                            @click.stop="decreaseQuantity(item.id)"
                            :class="[isDarkMode ? 'bg-gray-600 text-gray-200' : 'bg-gray-200 text-gray-700', 'w-10 h-10 rounded-md flex items-center justify-center text-2xl']"
                        >
                            −
                        </button>
                        <div :class="[isDarkMode ? 'border-gray-600 text-gray-200' : 'border-gray-300', 'w-24 h-10 mx-2 border rounded-md flex items-center justify-center']">
                            {{ item.quantity }}
                        </div>
                        <button
                            @click.stop="increaseQuantity(item.id)"
                            :class="[isDarkMode ? 'bg-gray-600 text-gray-200' : 'bg-gray-200 text-gray-700', 'w-10 h-10 rounded-md flex items-center justify-center text-2xl']"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- 購物車為空的提示 -->
                <div v-if="cartItems.length === 0" :class="[isDarkMode ? 'text-gray-400' : 'text-gray-500', 'py-8 text-center']">
                    購物車是空的，點擊商品加入購物車
                </div>

                <div v-else class="flex justify-between items-center mt-6 text-xl font-bold" :class="{ 'text-gray-200': isDarkMode }">
                    <span>總計</span>
                    <span>${{ total }}</span>
                </div>

                <!-- 年齡驗證 -->
                <div v-if="hasAdultItems" class="bg-red-600 text-white p-4 my-6 rounded-lg flex items-center">
                    <span class="text-2xl mr-2">⚠️</span>
                    <span>請確認購買者年滿18歲（民國 {{ r18Date }} 以前出生）</span>
                </div>

                <!-- 操作按鈕 -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <button
                        @click="clearCart"
                        :class="[
                            'py-3 px-4 rounded-lg border-2 border-pink-500 text-pink-500 text-lg font-medium',
                            cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                        :disabled="cartItems.length === 0"
                    >
                        清空
                    </button>
                    <button
                        @click="showCheckoutModal"
                        :class="[
                            isDarkMode ? 'bg-blue-700' : 'bg-blue-500',
                            'py-3 px-4 rounded-lg text-white text-lg font-medium',
                            cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                        :disabled="cartItems.length === 0"
                    >
                        結帳
                    </button>
                </div>
            </div>
        </div>

        <!-- 引入付款元件 -->
        <CheckoutModal
            :quickAmounts="quickAmounts"
            :payment="payment"
            :is-visible="isCheckoutModalVisible"
            :is-dark-mode="isDarkMode"
            :cart-items="cartItems"
            :total="total"
            :has-adult-items="hasAdultItems"
            @close="closeCheckoutModal"
            @complete="handleTransactionComplete"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import CheckoutModal from './Components/CheckoutModel.vue'

// 從 Inertia props 中獲取商品資料
const page = usePage()
const productItems = computed(() => page.props.items || [])
const quickAmounts = computed(() => page.props.quickAmounts || [])
const r18Date = computed(() => page.props.r18Date)
const payment = computed(() => page.props.payment)

// 購物車資料（本地狀態）
const cart = ref([])
// 追蹤最近添加的商品
const recentlyAdded = ref(null)
// 深夜模式狀態
const isDarkMode = ref(false)
// 結帳彈出視窗狀態
const isCheckoutModalVisible = ref(false)

// 初始化購物車與深夜模式
onMounted(() => {
    // 載入購物車資料
    const savedCart = localStorage.getItem('shoppingCart')
    if (savedCart) {
        cart.value = JSON.parse(savedCart)
    } else {
        // 初始化空購物車
        cart.value = productItems.value.map(item => ({
            id: item.id,
            quantity: 0
        }))
    }

    // 載入深夜模式偏好
    const darkModePref = localStorage.getItem('darkMode')
    if (darkModePref !== null) {
        isDarkMode.value = JSON.parse(darkModePref)
    }
})

// 獲取商品在購物車中的數量
const getCartQuantity = (id) => {
    const cartItem = cart.value.find(item => item.id === id)
    return cartItem ? cartItem.quantity : 0
}

// 購物車中的商品（數量大於0）
const cartItems = computed(() => {
    return cart.value
        .filter(cartItem => cartItem.quantity > 0)
        .map(cartItem => {
            const productItem = productItems.value.find(p => p.id === cartItem.id)
            return {
                ...productItem,
                quantity: cartItem.quantity
            }
        })
})

// 計算總金額
const total = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (item.item_price * item.quantity), 0)
})

// 檢查購物車中是否有成人商品
const hasAdultItems = computed(() => {
    return cartItems.value.some(item => item.is_r18)
})

// 顯示結帳彈出視窗
const showCheckoutModal = () => {
    isCheckoutModalVisible.value = true
}

// 關閉結帳彈出視窗
const closeCheckoutModal = () => {
    isCheckoutModalVisible.value = false
}

// 生成唯一交易 ID
const generateTransactionId = () => {
    return 'TR-' + Date.now() + '-' + Math.floor(Math.random() * 1000)
}

// 處理交易完成
const handleTransactionComplete = async (transaction) => {
    console.log('交易完成：', transaction)

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
        paymentMethod: transaction.paymentMethod
    }
    console.log('交易資料:', transactionRecord)

    // 儲存交易記錄並傳送到API
    try {
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
    } catch (error) {
        console.error('傳送交易資料至API時發生錯誤:', error);
        alert(`交易記錄儲存失敗，請聯絡系統管理員。錯誤訊息: ${error.message}`);
    }
}

// 點擊商品添加到購物車
const addToCart = (id) => {
    const productItem = productItems.value.find(item => item.id === id)
    const cartItem = cart.value.find(item => item.id === id)

    if (productItem && productItem.item_stock > 0 && cartItem) {
        if (cartItem.quantity < productItem.item_stock) {
            cartItem.quantity += 1
            // 保存購物車狀態
            saveCart()
            // 顯示添加成功的視覺反饋
            recentlyAdded.value = id
            setTimeout(() => {
                recentlyAdded.value = null
            }, 500)
        }
    }
}

// 增加商品數量
const increaseQuantity = (id) => {
    const productItem = productItems.value.find(item => item.id === id)
    const cartItem = cart.value.find(item => item.id === id)

    if (productItem && cartItem && cartItem.quantity < productItem.item_stock) {
        cartItem.quantity++
        // 保存購物車狀態
        saveCart()
        // 顯示添加成功的視覺反饋
        recentlyAdded.value = id
        setTimeout(() => {
            recentlyAdded.value = null
        }, 500)
    }
}

// 減少商品數量
const decreaseQuantity = (id) => {
    const cartItem = cart.value.find(item => item.id === id)

    if (cartItem && cartItem.quantity > 0) {
        cartItem.quantity--
        // 保存購物車狀態
        saveCart()
        // 如果數量減為0，可以顯示移除的視覺反饋
        if (cartItem.quantity === 0) {
            recentlyAdded.value = id
            setTimeout(() => {
                recentlyAdded.value = null
            }, 500)
        }
    }
}

// 切換深夜模式
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value
    localStorage.setItem('darkMode', JSON.stringify(isDarkMode.value))
}

// 保存購物車到 localStorage
const saveCart = () => {
    localStorage.setItem('shoppingCart', JSON.stringify(cart.value))
}

// 清空購物車
const clearCart = () => {
    cart.value.forEach(item => {
        item.quantity = 0
    })
    // 保存購物車狀態
    saveCart()
}

// A根據商品ID和深夜模式獲取背景顏色
const getColorForItem = (id, darkMode) => {
    const lightColors = {
        1: 'bg-red-800',
        2: 'bg-blue-800',
        3: 'bg-green-800',
        4: 'bg-amber-800',
        5: 'bg-purple-900'
    }

    const darkColors = {
        1: 'bg-red-900',
        2: 'bg-blue-900',
        3: 'bg-green-900',
        4: 'bg-amber-900',
        5: 'bg-purple-950'
    }

    return darkMode
        ? (darkColors[id] || 'bg-gray-900')
        : (lightColors[id] || 'bg-gray-800')
}
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
.dark-mode textarea {
    color-scheme: dark;
}

/* 過渡效果 */
.dark-mode,
.dark-mode *,
body,
* {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}
</style>
