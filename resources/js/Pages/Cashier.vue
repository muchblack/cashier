<template>
    <div :class="['flex flex-col h-screen', isDarkMode ? 'dark-mode bg-gray-900' : 'bg-blue-500']">
        <!-- 頂部導航欄 -->
        <header class="flex items-center justify-between p-4 bg-blue-500 text-white">
            <button class="text-3xl"></button>
            <h1 class="text-2xl font-bold">收銀台</h1>
            <div class="flex items-center">
                <!-- 場次下拉選單 -->
                <div class="relative mx-4">
                    <select
                        v-model="selectedSession"
                        @change="handleSessionChange"
                        :class="[
                            isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-blue-600 text-white border-blue-400',
                            'appearance-none border rounded-lg py-2 px-4 pr-8 cursor-pointer focus:outline-none focus:ring-2',
                            isDarkMode ? 'focus:ring-blue-400' : 'focus:ring-blue-300'
                        ]"
                    >
                        <option
                            v-for="session in sessions"
                            :key="session.id"
                            :value="session.id"
                            :class="isDarkMode ? 'bg-gray-700' : 'bg-blue-600'"
                        >
                            {{ session.event_name }}
                        </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                        </svg>
                    </div>
                </div>

                <div class="ml-4 text-2xl cursor-pointer" @click="toggleDarkMode">
                    {{ isDarkMode ? '☀️' : '🌙' }}
                </div>
            </div>
        </header>

        <!-- 主要內容區域 - 置中並設定70%寬度 -->
        <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'flex-1 w-full flex justify-center overflow-hidden']">
            <div class="w-full max-w-[70%] flex h-full">
                <!-- 左側產品網格 -->
                <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'w-2/3 p-4 overflow-y-auto']">
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

                    <div v-else class="grid grid-cols-2 gap-4">
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
                </div>

                <!-- 右側購物車和結帳 -->
                <div :class="[isDarkMode ? 'bg-gray-700' : 'bg-gray-100', 'w-1/3 p-4 overflow-y-auto border-l h-full', isDarkMode ? 'border-gray-600' : 'border-gray-300']">
                    <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-white', 'rounded-lg p-4 shadow sticky top-4']">
                        <h2 :class="[isDarkMode ? 'text-gray-200' : 'text-gray-800', 'text-xl font-bold mb-4 pb-2 border-b', isDarkMode ? 'border-gray-700' : 'border-gray-200']">
                            購物車
                        </h2>

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

                        <!-- 操作按鈕 -->
                        <div class="grid grid-cols-2 gap-4 mt-6">
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
import { ref, computed, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import CheckoutModal from './Components/CheckoutModel.vue'

// 從 Inertia props 中獲取商品資料
const page = usePage()
const quickAmounts = computed(() => page.props.quickAmounts || [])
const r18Date = computed(() => page.props.r18Date)
const payment = computed(() => page.props.payment)
const userID = computed( () => page.props.user)

// 場次資料
const sessions = computed(() => page.props.events)

// 商品資料狀態
const productItems = ref([])
const isLoading = ref(false)


// 購物車資料（本地狀態）
const cart = ref([])
// 追蹤最近添加的商品
const recentlyAdded = ref(null)
// 深夜模式狀態
const isDarkMode = ref(false)
// 結帳彈出視窗狀態
const isCheckoutModalVisible = ref(false)
// 選中的場次
const selectedSession = ref(1)

// 計算當前場次名稱
const currentSessionName = computed(() => {
    const session = sessions.value.find(s => s.id === selectedSession.value)
    return session ? session.event_name : '未選擇場次'
})

// 計算當前場次時間
const currentSessionTime = computed(() => {
    const session = sessions.value.find(s => s.id === selectedSession.value)
    return session ? session.time : ''
})

// 處理場次變更
const handleSessionChange = () => {
    console.log('場次已變更為：', currentSessionName.value)
    // 重新載入該場次的商品資料
    fetchProductsBySession(selectedSession.value)
    // 清空購物車，避免跨場次的商品混合
    clearCart()
    // 保存選擇的場次到本地儲存
    localStorage.setItem('selectedSession', selectedSession.value)
}

// 根據場次ID擷取對應的商品資料
const fetchProductsBySession = async (sessionId) => {
    try {
        isLoading.value = true
        console.log(`正在擷取場次 ${sessionId} 的商品資料...`)

        // 呼叫API獲取特定場次的商品資料
        const response = await fetch(`/api/items/get/${userID.value}/${sessionId}`)

        if (!response.ok) {
            throw new Error(`無法獲取場次 ${sessionId} 的商品資料: ${response.status}`)
        }

        const data = await response.json()
        console.log(data)

        // 更新商品資料
        productItems.value = data || []

        console.log(`已成功載入 ${productItems.value.length} 個商品`)

        // 重新初始化購物車（保持相同結構但數量為0）
        initializeCart()
    } catch (error) {
        console.error('擷取商品資料時發生錯誤:', error)
        // 顯示錯誤訊息給使用者
        alert(`無法載入商品資料，請重新整理頁面或聯絡系統管理員。\n錯誤訊息: ${error.message}`)
    } finally {
        isLoading.value = false
    }
}

// 初始化空購物車
const initializeCart = () => {
    cart.value = productItems.value.map(item => ({
        id: item.id,
        quantity: 0
    }))
    saveCart()
}

// 初始化購物車與深夜模式
onMounted(async () => {
    // 載入深夜模式偏好
    const darkModePref = localStorage.getItem('darkMode')
    if (darkModePref !== null) {
        isDarkMode.value = JSON.parse(darkModePref)
    }

    // 載入上次選擇的場次
    const savedSession = localStorage.getItem('selectedSession')
    if (savedSession !== null) {
        selectedSession.value = parseInt(savedSession)
    }

    // 根據選中的場次載入商品資料
    await fetchProductsBySession(selectedSession.value)

    // 載入購物車資料（在商品資料載入後）
    const savedCart = localStorage.getItem('shoppingCart')
    if (savedCart) {
        const parsedCart = JSON.parse(savedCart)
        // 檢查購物車中的商品是否存在於當前場次
        cart.value = parsedCart.filter(cartItem =>
            productItems.value.some(product => product.id === cartItem.id)
        )
        saveCart() // 保存過濾後的購物車
    } else {
        // 初始化空購物車
        initializeCart()
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
        paymentMethod: transaction.paymentMethod,
        sessionId: selectedSession.value,
        sessionName: currentSessionName.value,
        ownerId: userID.value
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

// 根據商品ID和深夜模式獲取背景顏色
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
@media (max-width: 1200px) {
    .max-w-[70%] {
    max-width: 90%;
}
}

@media (max-width: 768px) {
    .max-w-[70%] {
    max-width: 100%;
}

    .flex-grow {
        flex-direction: column;
    }

    .w-2/3, .w-1/3 {
    width: 100%;
}
}
</style>
