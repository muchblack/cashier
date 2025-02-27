<template>
    <div :class="['flex flex-col h-screen', isDarkMode ? 'dark-mode bg-gray-900' : 'bg-blue-500']">
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
        <div :class="[isDarkMode ? 'bg-gray-800 text-gray-200' : 'bg-white text-gray-800', 'flex-1 p-6 overflow-auto']">
            <!-- 當資料載入中顯示的狀態 -->
            <div v-if="isLoading" class="flex justify-center items-center py-16">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2" :class="isDarkMode ? 'border-blue-400' : 'border-blue-600'"></div>
                <span class="ml-3" :class="isDarkMode ? 'text-gray-300' : 'text-gray-600'">載入中...</span>
            </div>

            <template v-else>
                <!-- 目前選擇的場次資訊顯示 -->
                <div :class="[isDarkMode ? 'bg-gray-700 text-gray-200' : 'bg-blue-100 text-blue-800', 'mb-4 p-3 rounded-lg']">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">當前場次：{{ currentSessionName }}</span>
                        <span class="text-sm" :class="isDarkMode ? 'text-gray-400' : 'text-blue-600'">
                            {{ currentSessionTime }}
                        </span>
                    </div>
                </div>

                <!-- 待處理預留單區塊 -->
                <div :class="[isDarkMode ? 'bg-gray-700' : 'bg-white', 'rounded-lg shadow-md mb-6 overflow-hidden']">
                    <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-blue-500', 'py-3 px-4 text-white font-bold flex justify-between items-center']">
                        <span>待處理預留單</span>
                        <div class="flex items-center">
                            <button
                                v-if="getSelectedReservations.length > 0"
                                @click="processBatchReservations"
                                class="text-sm bg-green-600 hover:bg-green-500 py-1 px-3 rounded-lg ml-2 transition-colors duration-200"
                            >
                                處理已選項目
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                            <tr :class="[isDarkMode ? 'bg-gray-800 text-gray-300' : 'bg-gray-100 text-gray-700']">
                                <th class="px-4 py-3 text-left">客戶</th>
                                <th class="px-4 py-3 text-left">商品資訊</th>
                                <th class="px-4 py-3 text-left">價格</th>
                                <th class="px-4 py-3 text-left">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="filteredPendingReservations.length === 0">
                                <td
                                    colspan="10"
                                    class="px-4 py-8 text-center"
                                    :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'"
                                >
                                    沒有待處理預留單
                                </td>
                            </tr>
                            <tr
                                v-for="reservation in filteredPendingReservations"
                                :key="reservation.id"
                                :class="[
                                        isDarkMode ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-200 hover:bg-gray-50',
                                        'border-b transition-colors duration-150'
                                    ]"
                            >
                                <td class="px-4 py-3">{{ reservation.preorder_name || '未指定' }}</td>
                                <td class="px-4 py-3">
                                    <div v-if="reservation.item_quantities && reservation.item_quantities.length > 0">
                                        <div v-for="(item, index) in reservation.item_quantities" :key="index" class="mb-1 last:mb-0">
                                            <span class="font-medium">{{ item.item_name }}</span> x <span class="font-bold">{{ item.quantity }}</span>
                                        </div>
                                    </div>
                                    <div v-else>{{ reservation.name }}</div>
                                </td>
                                <td class="px-4 py-3">${{ reservation.order_amount }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="processReservation(reservation.id)"
                                            :class="[
                                                    isDarkMode ? 'bg-blue-700 hover:bg-blue-600' : 'bg-blue-500 hover:bg-blue-400',
                                                    'text-white text-sm py-1 px-3 rounded transition-colors duration-200'
                                                ]"
                                        >
                                            處理
                                        </button>
                                        <button
                                            @click="processDeleteReservation(reservation.id)"
                                            :class="[
                                                    isDarkMode ? 'bg-red-700 hover:bg-blue-600' : 'bg-blue-500 hover:bg-blue-400',
                                                    'text-white text-sm py-1 px-3 rounded transition-colors duration-200'
                                                ]"
                                        >
                                            刪除
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 已完成預留單區塊 -->
                <div :class="[isDarkMode ? 'bg-gray-700' : 'bg-white', 'rounded-lg shadow-md overflow-hidden']">
                    <div :class="[isDarkMode ? 'bg-gray-800' : 'bg-blue-500', 'py-3 px-4 text-white font-bold']">
                        已完成預留單
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                            <tr :class="[isDarkMode ? 'bg-gray-800 text-gray-300' : 'bg-gray-100 text-gray-700']">
                                <th class="px-4 py-3 text-left">訂單</th>
                                <th class="px-4 py-3 text-left">客戶</th>
                                <th class="px-4 py-3 text-left">商品資訊</th>
                                <th class="px-4 py-3 text-left">價格</th>
                                <th class="px-4 py-3 text-left">完成時間</th>
                                <th class="px-4 py-3 text-left">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="filteredCompletedReservations.length === 0">
                                <td
                                    colspan="10"
                                    class="px-4 py-8 text-center"
                                    :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'"
                                >
                                    沒有已完成預留單
                                </td>
                            </tr>
                            <tr
                                v-for="reservation in filteredCompletedReservations"
                                :key="reservation.id"
                                :class="[
                                        isDarkMode ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-200 hover:bg-gray-50',
                                        'border-b transition-colors duration-150'
                                    ]"
                            >
                                <td class="px-4 py-3">{{ reservation.trade_no }}</td>
                                <td class="px-4 py-3">{{ reservation.preorder_name || '未指定' }}</td>
                                <td class="px-4 py-3">
                                    <div v-if="reservation.item_quantities && reservation.item_quantities.length > 0">
                                        <div v-for="(item, index) in reservation.item_quantities" :key="index" class="mb-1 last:mb-0">
                                            <span class="font-medium">{{ item.item_name }}</span> x <span class="font-bold">{{ item.quantity }}</span>
                                        </div>
                                    </div>
                                    <div v-else>{{ reservation.name }}</div>
                                </td>
                                <td class="px-4 py-3">${{ reservation.order_amount }}</td>
                                <td class="px-4 py-3">{{ formatDate(reservation.updated_at) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="processRollBackReservation(reservation.id)"
                                            :class="[
                                                    isDarkMode ? 'bg-blue-700 hover:bg-blue-600' : 'bg-blue-500 hover:bg-blue-400',
                                                    'text-white text-sm py-1 px-3 rounded transition-colors duration-200'
                                                ]"
                                        >
                                            回退
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
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

// 切換全選/取消全選
const toggleSelectAll = () => {
    selectAll.value = !selectAll.value;
    pendingReservations.value.forEach(reservation => {
        reservation.selected = selectAll.value;
    });
};

// 切換深夜模式
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('darkMode', JSON.stringify(isDarkMode.value));
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
    // 載入深夜模式偏好
    const darkModePref = localStorage.getItem('darkMode');
    if (darkModePref !== null) {
        isDarkMode.value = JSON.parse(darkModePref);
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

/* 過渡效果 */
.dark-mode,
.dark-mode *,
body,
* {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* 表格樣式 */
table {
    border-collapse: separate;
    border-spacing: 0;
}

th, td {
    white-space: nowrap;
}

/* 多項商品樣式 */
.last\:mb-0:last-child {
    margin-bottom: 0;
}

/* 響應式調整 */
@media (max-width: 1280px) {
    table {
        display: block;
        overflow-x: auto;
    }

    .px-4 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
}

@media (max-width: 768px) {
    .flex-wrap {
        flex-wrap: wrap;
    }

    button {
        margin-bottom: 0.5rem;
    }
}
</style>
