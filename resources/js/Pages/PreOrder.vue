<!-- 修改主要容器的條件式類別 -->
<template>
    <div :class="['min-h-screen', isDarkMode ? 'bg-gray-900 text-white' : 'bg-blue-100 text-gray-800']">
        <!-- Navbar 維持不變 -->
        <!-- 使用共用頂部導覽列 -->
        <Navbar
            pageTitle="預留單"
            menuTitle="功能選單"
            :menuOpen="menuOpen"
            :isDarkMode="isDarkMode"
            :showSessionSelector="true"
            :sessions="sessions"
            :currentSession="selectedSession"
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
                    <Link href="/cashier" class="block p-2 rounded hover:bg-blue-500 hover:text-white">收銀台</Link>
                </div>
                <div class="mb-4 p-2 rounded-lg" :class="isDarkMode ? 'bg-gray-700 text-white' : 'bg-blue-200'">
                    <Link href="/cashier/preorder" class="block p-2 rounded hover:bg-blue-500 hover:text-white">預留單</Link>
                </div>
            </div>
        </div>

        <!-- 主要內容區域 -->
        <div class="max-w-7xl mx-auto p-4">
            <!-- 目前選擇的場次資訊顯示 -->
            <div :class="[isDarkMode ? 'bg-gray-800 text-white' : 'bg-blue-500 text-white', 'rounded-lg p-4 mb-6']">
                <h1 class="text-xl">當前場次：{{ currentSessionName }}</h1>
                <div class="text-sm opacity-75">{{ currentSessionTime }}</div>
            </div>

            <!-- 當資料載入中顯示的狀態 -->
            <div v-if="isLoading" :class="[isDarkMode ? 'bg-gray-800 text-gray-300' : 'bg-white text-gray-600', 'p-8 rounded-lg flex flex-col items-center justify-center']">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2" :class="isDarkMode ? 'border-blue-400' : 'border-blue-600'"></div>
                <p class="mt-4">載入中...</p>
            </div>

            <template v-else>
                <!-- 待處理預留單區塊 -->
                <div class="space-y-8 mb-8">
                    <!-- 區塊標題 -->
                    <div :class="[isDarkMode ? 'bg-gray-800 border-blue-500' : 'bg-blue-500 border-blue-300', 'p-3 rounded-lg mb-4 border-l-4 flex justify-between items-center']">
                        <h2 class="text-xl font-bold" :class="isDarkMode ? 'text-white' : 'text-white'">待處理預留單</h2>
                        <button
                            v-if="getSelectedReservations.length > 0"
                            @click="processBatchReservations"
                            class="text-sm bg-green-600 hover:bg-green-500 py-1 px-3 rounded-lg transition-colors duration-200 text-white"
                        >
                            處理已選項目
                        </button>
                    </div>

                    <!-- 無待處理預留單時顯示 -->
                    <div v-if="filteredPendingReservations.length === 0" :class="[isDarkMode ? 'bg-gray-800 text-gray-300' : 'bg-white text-gray-500', 'p-8 rounded-lg flex flex-col items-center justify-center']">
                        <div class="text-4xl mb-4">📋</div>
                        <p class="text-center">
                            沒有待處理預留單<br>
                            目前無需處理的訂單
                        </p>
                    </div>

                    <!-- 待處理預留單列表 -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="reservation in filteredPendingReservations"
                            :key="reservation.id"
                            :class="[isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-gray-800', 'rounded-lg overflow-hidden relative shadow-md']"
                        >
                            <!-- 內容層 -->
                            <div class="p-4 flex flex-col">
                                <div class="text-xl font-bold mb-2">{{ reservation.preorder_name || '未指定' }}</div>
                                <div class="text-lg mb-2" v-if="reservation.plurk_account">plurk: {{ reservation.plurk_account || '未指定' }}</div>

                                <div class="mb-4">
                                    <div class="text-sm uppercase tracking-wide mb-1" :class="isDarkMode ? 'text-blue-300' : 'text-blue-600'">商品明細</div>
                                    <div v-if="reservation.item_quantities && reservation.item_quantities.length > 0"
                                         :class="[isDarkMode ? 'bg-gray-700 bg-opacity-50 border-blue-400' : 'bg-blue-50 border-blue-300', 'p-3 rounded-lg border-l-2']">
                                        <div v-for="(item, index) in reservation.item_quantities" :key="index"
                                             class="mb-2 last:mb-0 flex justify-between items-center">
                                            <span class="font-medium">{{ item.item_name }}</span>
                                            <span :class="[isDarkMode ? 'bg-blue-600' : 'bg-blue-500', 'px-2 py-1 rounded text-sm font-bold text-white']">x {{ item.quantity }}</span>
                                        </div>
                                    </div>
                                    <div v-else :class="[isDarkMode ? 'bg-gray-700 bg-opacity-50 border-blue-400' : 'bg-blue-50 border-blue-300', 'p-3 rounded-lg border-l-2']">
                                        {{ reservation.name }}
                                    </div>
                                </div>

                                <div :class="[isDarkMode ? 'text-gray-200' : 'text-gray-700', 'text-lg font-semibold mb-1']">訂單總金額：${{ reservation.order_amount }}</div>
                                <div :class="[isDarkMode ? 'text-gray-200' : 'text-gray-700', 'text-lg font-semibold mb-1']">已付定金：${{ reservation.preorder_price || 0 }}</div>
                                <div :class="[isDarkMode ? 'text-gray-200' : 'text-gray-700', 'text-lg font-semibold mb-4']">還需付款：${{ reservation.order_amount - (reservation.preorder_price || 0) }}</div>

                                <div class="mt-auto flex gap-2 justify-end">
                                    <button
                                        @click="processReservation(reservation.id)"
                                        class="bg-green-600 hover:bg-green-500 text-white text-sm py-1 px-3 rounded transition-colors duration-200"
                                    >
                                        處理
                                    </button>
                                    <button
                                        @click="processDeleteReservation(reservation.id)"
                                        class="bg-red-600 hover:bg-red-500 text-white text-sm py-1 px-3 rounded transition-colors duration-200"
                                    >
                                        刪除
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 已完成預留單區塊 -->
                <div class="space-y-8">
                    <!-- 區塊標題 -->
                    <div :class="[isDarkMode ? 'bg-gray-800 border-blue-500' : 'bg-blue-500 border-blue-300', 'p-3 rounded-lg mb-4 border-l-4']">
                        <h2 class="text-xl font-bold text-white">已完成預留單</h2>
                    </div>

                    <!-- 無已完成預留單時顯示 -->
                    <div v-if="filteredCompletedReservations.length === 0" :class="[isDarkMode ? 'bg-gray-800 text-gray-300' : 'bg-white text-gray-500', 'p-8 rounded-lg flex flex-col items-center justify-center']">
                        <div class="text-4xl mb-4">📋</div>
                        <p class="text-center">
                            沒有已完成預留單<br>
                            尚未有處理完畢的訂單
                        </p>
                    </div>

                    <!-- 已完成預留單列表 -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="reservation in filteredCompletedReservations"
                            :key="reservation.id"
                            :class="[isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-gray-800', 'rounded-lg overflow-hidden relative shadow-md']"
                        >
                            <!-- 內容層 -->
                            <div class="p-4 flex flex-col">
                                <div class="flex justify-between flex-wrap">
                                    <div class="text-xl font-bold mb-2">{{ reservation.preorder_name || '未指定' }}</div>
                                    <div class="text-sm px-2 py-1 rounded" :class="isDarkMode ? 'bg-gray-700 text-gray-200' : 'bg-gray-200 text-gray-700'">{{ reservation.trade_no }}</div>
                                </div>

                                <div class="text-lg mb-2" v-if="reservation.plurk_account">plurk: {{ reservation.plurk_account }}</div>

                                <div class="mb-4">
                                    <div class="text-sm uppercase tracking-wide mb-1" :class="isDarkMode ? 'text-blue-300' : 'text-blue-600'">商品明細</div>
                                    <div v-if="reservation.item_quantities && reservation.item_quantities.length > 0"
                                         :class="[isDarkMode ? 'bg-gray-700 bg-opacity-50 border-blue-400' : 'bg-blue-50 border-blue-300', 'p-3 rounded-lg border-l-2']">
                                        <div v-for="(item, index) in reservation.item_quantities" :key="index"
                                             class="mb-2 last:mb-0 flex justify-between items-center">
                                            <span class="font-medium">{{ item.item_name }}</span>
                                            <span :class="[isDarkMode ? 'bg-blue-600' : 'bg-blue-500', 'px-2 py-1 rounded text-sm font-bold text-white']">x {{ item.quantity }}</span>
                                        </div>
                                    </div>
                                    <div v-else :class="[isDarkMode ? 'bg-gray-700 bg-opacity-50 border-blue-400' : 'bg-blue-50 border-blue-300', 'p-3 rounded-lg border-l-2']">
                                        {{ reservation.name }}
                                    </div>
                                </div>

                                <div :class="[isDarkMode ? 'text-gray-200' : 'text-gray-700', 'text-lg font-semibold mb-2']">訂單總金額：${{ reservation.order_amount }}</div>
                                <div class="text-sm mb-4" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">完成日期：{{ formatDate(reservation.updated_at) }}</div>

                                <div class="mt-auto flex gap-2 justify-end">
                                    <button
                                        @click="processRollBackReservation(reservation.id)"
                                        class="bg-amber-600 hover:bg-amber-500 text-white text-sm py-1 px-3 rounded transition-colors duration-200"
                                    >
                                        回退
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import Navbar from './Components/Navbar.vue';

// 從 Inertia props 中獲取資料
const page = usePage();
const user = computed(() => page.props.user);

// 場次資料
const sessions = computed(() => page.props.events || []);

// 側邊選單狀態
const menuOpen = ref(false);

// 深夜模式狀態
const isDarkMode = ref(false);

// 搜尋查詢
const searchQuery = ref('');

// 資料載入狀態
const isLoading = ref(true);

// 選中的場次
const selectedSession = ref(null);

// 待處理預留單資料
const pendingReservations = ref([]);

// 已完成預留單資料
const completedReservations = ref([]);

// 全選功能
const selectAll = ref(false);

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

// 搜尋過濾功能 - 增強搜尋邏輯以支援多項商品
const filteredPendingReservations = computed(() => {
    if (!searchQuery.value) return pendingReservations.value;

    return pendingReservations.value.filter(item => {
        // 搜尋客戶、備註、單一品項名稱
        const basicMatch =
            (item.customer && item.customer.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (item.note && item.note.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (item.name && item.name.toLowerCase().includes(searchQuery.value.toLowerCase()));

        // 搜尋多項商品中的任一品項
        const itemsMatch = item.items && item.items.some(product =>
            product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );

        return basicMatch || itemsMatch;
    });
});

const filteredCompletedReservations = computed(() => {
    if (!searchQuery.value) return completedReservations.value;

    return completedReservations.value.filter(item => {
        // 搜尋客戶、備註、訂單編號、單一品項名稱
        const basicMatch =
            (item.customer && item.customer.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (item.note && item.note.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (item.orderId && item.orderId.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (item.name && item.name.toLowerCase().includes(searchQuery.value.toLowerCase()));

        // 搜尋多項商品中的任一品項
        const itemsMatch = item.items && item.items.some(product =>
            product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );

        return basicMatch || itemsMatch;
    });
});

// 計算已選擇的預留單
const getSelectedReservations = computed(() => {
    return pendingReservations.value.filter(r => r.selected);
});

// 格式化日期
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('zh-TW', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

// 切換深夜模式
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('darkMode', JSON.stringify(isDarkMode.value));

    // 將深色模式狀態應用到 document.body
    if (isDarkMode.value) {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
};

// 處理單筆預留單
const processReservation = async (id) => {
    try {
        console.log(id)
        // 使用 Axios 呼叫 API 處理預留單
        const response = await axios.post(`/api/order/checkpreorder/${id}`, {
            sessionId: selectedSession.value,
            user_id: user.value,
        });

        if (response.data.success) {
            // 找到指定的預留單
            const index = pendingReservations.value.findIndex(item => item.id === id);

            if (index !== -1) {
                // 從 API 回傳的資料更新預留單資訊
                const processedItem = response.data.reservation;

                // 添加到已完成預留單
                completedReservations.value.push(processedItem);

                // 從待處理預留單中移除
                pendingReservations.value.splice(index, 1);

                alert('預留單已處理')
            }
        } else {
            alert('處理預留單時發生錯誤: ' + response.data.message);
        }
    } catch (error) {
        console.error('處理預留單時發生錯誤:', error);
        alert(`處理預留單時發生錯誤: ${error.response?.data?.message || error.message}`);
    }
};

const processRollBackReservation = async (id) => {
    try {
        // 使用 Axios 呼叫 API 處理預留單
        const response = await axios.post(`/api/order/rollbackpreorder/${id}`, {
            sessionId: selectedSession.value,
            user_id: user.value,
        });

        if (response.data.success) {
            // 找到指定的預留單
            const index = completedReservations.value.findIndex(item => item.id === id);
            console.log(index)
            if (index !== -1) {
                // 從 API 回傳的資料更新預留單資訊
                const processedItem = response.data.reservation;

                // 添加到待完成預留單
                pendingReservations.value.push(processedItem);

                // 從已處理預留單中移除
                completedReservations.value.splice(index, 1);

                alert('預留單已回退')
            }
        } else {
            alert('回退預留單時發生錯誤: ' + response.data.message);
        }
    } catch (error) {
        console.error('回退預留單時發生錯誤:', error);
        alert(`回退預留單時發生錯誤: ${error.response?.data?.message || error.message}`);
    }
};

const processDeleteReservation = async (id) => {
    try {
        // 使用 Axios 呼叫 API 處理預留單
        const response = await axios.post(`/api/order/deletepreorder/${id}`, {
            sessionId: selectedSession.value,
            user_id: user.value,
        });

        if (response.data.success) {
            // 找到指定的預留單
            const index = pendingReservations.value.findIndex(item => item.id === id);
            console.log(index)
            if (index !== -1) {
                // 從已處理預留單中移除
                pendingReservations.value.splice(index, 1);
                alert('預留單已刪除')
            }
        } else {
            alert('刪除預留單時發生錯誤: ' + response.data.message);
        }
    } catch (error) {
        console.error('刪除預留單時發生錯誤:', error);
        alert(`刪除預留單時發生錯誤: ${error.response?.data?.message || error.message}`);
    }
};
// 轉換舊格式預留單為新格式（處理後端 API 回傳的資料格式）
const transformReservationData = (reservations) => {
    return reservations.map(reservation => {
        // 如果已經有 items 屬性且為陣列，則不需要轉換
        if (reservation.items && Array.isArray(reservation.items)) {
            return reservation;
        }

        // 否則將單一商品轉換為 items 陣列格式
        return {
            ...reservation,
            items: [
                {
                    name: reservation.name,
                    quantity: reservation.quantity || 1 // 預設數量為 1
                }
            ]
        };
    });
};

// 初始化
onMounted(() => {
    document.title = page.props.title;
    // 載入深夜模式偏好
    const darkModePref = localStorage.getItem('darkMode');
    if (darkModePref !== null) {
        isDarkMode.value = JSON.parse(darkModePref);

        // 將初始深色模式狀態套用到 body
        if (isDarkMode.value) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    }

    // 載入上次選擇的場次，或使用第一個可用場次
    const savedSession = localStorage.getItem('selectedSession');
    if (savedSession !== null) {
        selectedSession.value = parseInt(savedSession);
    } else if (sessions.value.length > 0) {
        selectedSession.value = sessions.value[0].id;
    }

    // 載入當前場次的預留單資料
    if (selectedSession.value) {
        loadPreOrderData(selectedSession.value);
    } else {
        isLoading.value = false;
    }
});

// 處理場次變更
const handleSessionChange = (sessionId) => {
    selectedSession.value = sessionId;

    // 保存選擇的場次到本地儲存
    localStorage.setItem('selectedSession', sessionId);

    // 重新載入該場次的預留單資料
    loadPreOrderData(sessionId);
};

// 載入預留單資料
const loadPreOrderData = async (sessionId) => {
    isLoading.value = true;
    try {
        // 使用 Axios 從 API 獲取預留單資料
        const response = await axios.get(`/api/order/getpreorder/${user.value}/${sessionId}`, {});

        // 轉換並更新預留單資料
        pendingReservations.value = transformReservationData(response.data.pendingReservations).map(item => ({
            ...item,
            selected: false // 初始化選擇狀態
        }));

        completedReservations.value = transformReservationData(response.data.completedReservations);
    } catch (error) {
        console.error('擷取預留單資料時發生錯誤:', error);
        alert(`無法載入預留單資料，請重新整理頁面或聯絡系統管理員。\n錯誤訊息: ${error.response?.data?.message || error.message}`);
    } finally {
        isLoading.value = false;
    }
};
</script>

<style>
/* 深夜模式相關樣式 */
.dark-mode {
    color-scheme: dark;
}

/* 卡片懸停效果 */
.rounded-lg {
    transition: transform 0.2s, box-shadow 0.2s;
}

.rounded-lg:hover {
    transform: translateY(-2px);
}

/* 深色模式下的卡片懸停陰影 */
.dark-mode .rounded-lg:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
}

/* 淺色模式下的卡片懸停陰影 */
.rounded-lg:hover:not(.dark-mode .rounded-lg) {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* 保留原有功能的樣式 */
.last\:mb-0:last-child {
    margin-bottom: 0;
}

/* 響應式調整 */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }

    button {
        padding: 0.5rem 1rem;
    }
}

/* 動畫效果 */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div {
    animation: fadeIn 0.3s ease-in-out;
}

/* 按鈕樣式美化 */
button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    transition: all 0.2s;
}

button:active {
    transform: scale(0.97);
}

/* 商品明細樣式 */
.border-l-2 {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.dark-mode .border-l-2 {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.border-l-2:hover {
    border-left-width: 4px;
    transform: translateX(2px);
}

.uppercase.tracking-wide {
    font-weight: 600;
    letter-spacing: 0.05em;
    position: relative;
    display: inline-block;
}

.uppercase.tracking-wide:after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 30px;
    height: 2px;
}

.dark-mode .uppercase.tracking-wide:after {
    background-color: #60a5fa;
}

.uppercase.tracking-wide:not(.dark-mode .uppercase.tracking-wide):after {
    background-color: #3b82f6;
}

/* 貨幣數值顯示強調 */
.font-semibold {
    position: relative;
}

/* 改進淺色/深色模式切換的過渡效果 */
body, *, .dark-mode, .dark-mode * {
    transition: background-color 0.3s ease,
    color 0.3s ease,
    border-color 0.3s ease,
    box-shadow 0.3s ease;
}
</style>
