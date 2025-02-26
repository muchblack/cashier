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
                    <a href="/cashier" class="block p-2 rounded hover:bg-blue-500 hover:text-white">收銀台</a>
                </div>
                <div class="mb-4 p-2 rounded-lg" :class="isDarkMode ? 'bg-gray-700 text-white' : 'bg-blue-200'">
                    <a href="/cashier/preorder" class="block p-2 rounded hover:bg-blue-500 hover:text-white">預留單</a>
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
                                <th class="px-4 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="selectAll"
                                        @change="toggleSelectAll"
                                        :class="[isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300']"
                                    />
                                </th>
                                <th class="px-4 py-3 text-left">客戶</th>
                                <th class="px-4 py-3 text-left">品項</th>
                                <th class="px-4 py-3 text-left">備註</th>
                                <th class="px-4 py-3 text-left">原價</th>
                                <th class="px-4 py-3 text-left">折扣</th>
                                <th class="px-4 py-3 text-left">預付</th>
                                <th class="px-4 py-3 text-left">尾款</th>
                                <th class="px-4 py-3 text-left">總計</th>
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
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        v-model="reservation.selected"
                                        :class="[isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300']"
                                    />
                                </td>
                                <td class="px-4 py-3">{{ reservation.customer || '未指定' }}</td>
                                <td class="px-4 py-3">{{ reservation.name }}</td>
                                <td class="px-4 py-3">{{ reservation.note }}</td>
                                <td class="px-4 py-3">${{ reservation.originalPrice }}</td>
                                <td class="px-4 py-3">{{ reservation.discount }}%</td>
                                <td class="px-4 py-3">${{ reservation.prepaid }}</td>
                                <td class="px-4 py-3">${{ reservation.remaining }}</td>
                                <td class="px-4 py-3">${{ reservation.total }}</td>
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
<!--                                        <button-->
<!--                                            @click="editReservation(reservation.id)"-->
<!--                                            :class="[-->
<!--                                                    isDarkMode ? 'bg-amber-700 hover:bg-amber-600' : 'bg-amber-500 hover:bg-amber-400',-->
<!--                                                    'text-white text-sm py-1 px-3 rounded transition-colors duration-200'-->
<!--                                                ]"-->
<!--                                        >-->
<!--                                            編輯-->
<!--                                        </button>-->
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
                                <th class="px-4 py-3 text-left">品項</th>
                                <th class="px-4 py-3 text-left">備註</th>
                                <th class="px-4 py-3 text-left">原價</th>
                                <th class="px-4 py-3 text-left">折扣</th>
                                <th class="px-4 py-3 text-left">預付</th>
                                <th class="px-4 py-3 text-left">尾款</th>
                                <th class="px-4 py-3 text-left">總計</th>
                                <th class="px-4 py-3 text-left">完成時間</th>
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
                                <td class="px-4 py-3">{{ reservation.orderId }}</td>
                                <td class="px-4 py-3">{{ reservation.customer || '未指定' }}</td>
                                <td class="px-4 py-3">{{ reservation.name }}</td>
                                <td class="px-4 py-3">{{ reservation.note }}</td>
                                <td class="px-4 py-3">${{ reservation.originalPrice }}</td>
                                <td class="px-4 py-3">{{ reservation.discount }}%</td>
                                <td class="px-4 py-3">${{ reservation.prepaid }}</td>
                                <td class="px-4 py-3">${{ reservation.remaining }}</td>
                                <td class="px-4 py-3">${{ reservation.total }}</td>
                                <td class="px-4 py-3">{{ formatDate(reservation.completedAt) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>

        <!-- 預留單表單彈出視窗 -->
<!--        <div v-if="showReservationForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">-->
<!--            <div-->
<!--                :class="[-->
<!--                    isDarkMode ? 'bg-gray-800 text-gray-200' : 'bg-white text-gray-800',-->
<!--                    'rounded-lg shadow-xl w-full max-w-xl p-6'-->
<!--                ]"-->
<!--            >-->
<!--                <h3 class="text-xl font-bold mb-4">{{ isEditing ? '編輯預留單' : '新增預留單' }}</h3>-->

<!--                <div class="space-y-4">-->
<!--                    &lt;!&ndash; 客戶資訊 &ndash;&gt;-->
<!--                    <div>-->
<!--                        <label class="block mb-1">客戶姓名</label>-->
<!--                        <input-->
<!--                            type="text"-->
<!--                            v-model="formData.customer"-->
<!--                            :class="[-->
<!--                                isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                'w-full p-2 rounded border'-->
<!--                            ]"-->
<!--                        />-->
<!--                    </div>-->

<!--                    &lt;!&ndash; 品項名稱 &ndash;&gt;-->
<!--                    <div>-->
<!--                        <label class="block mb-1">品項名稱</label>-->
<!--                        <input-->
<!--                            type="text"-->
<!--                            v-model="formData.name"-->
<!--                            :class="[-->
<!--                                isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                'w-full p-2 rounded border'-->
<!--                            ]"-->
<!--                        />-->
<!--                    </div>-->

<!--                    &lt;!&ndash; 備註 &ndash;&gt;-->
<!--                    <div>-->
<!--                        <label class="block mb-1">備註</label>-->
<!--                        <textarea-->
<!--                            v-model="formData.note"-->
<!--                            :class="[-->
<!--                                isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                'w-full p-2 rounded border'-->
<!--                            ]"-->
<!--                            rows="3"-->
<!--                        ></textarea>-->
<!--                    </div>-->

<!--                    &lt;!&ndash; 價格相關 &ndash;&gt;-->
<!--                    <div class="grid grid-cols-2 gap-4">-->
<!--                        <div>-->
<!--                            <label class="block mb-1">原價</label>-->
<!--                            <input-->
<!--                                type="number"-->
<!--                                v-model="formData.originalPrice"-->
<!--                                @input="calculateTotal"-->
<!--                                :class="[-->
<!--                                    isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                    'w-full p-2 rounded border'-->
<!--                                ]"-->
<!--                            />-->
<!--                        </div>-->
<!--                        <div>-->
<!--                            <label class="block mb-1">折扣 (%)</label>-->
<!--                            <input-->
<!--                                type="number"-->
<!--                                v-model="formData.discount"-->
<!--                                @input="calculateTotal"-->
<!--                                :class="[-->
<!--                                    isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                    'w-full p-2 rounded border'-->
<!--                                ]"-->
<!--                            />-->
<!--                        </div>-->
<!--                        <div>-->
<!--                            <label class="block mb-1">預付金額</label>-->
<!--                            <input-->
<!--                                type="number"-->
<!--                                v-model="formData.prepaid"-->
<!--                                @input="calculateRemaining"-->
<!--                                :class="[-->
<!--                                    isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                    'w-full p-2 rounded border'-->
<!--                                ]"-->
<!--                            />-->
<!--                        </div>-->
<!--                        <div>-->
<!--                            <label class="block mb-1">尾款</label>-->
<!--                            <input-->
<!--                                type="number"-->
<!--                                v-model="formData.remaining"-->
<!--                                disabled-->
<!--                                :class="[-->
<!--                                    isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                    'w-full p-2 rounded border opacity-70'-->
<!--                                ]"-->
<!--                            />-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    &lt;!&ndash; 總金額 &ndash;&gt;-->
<!--                    <div>-->
<!--                        <label class="block mb-1">總計</label>-->
<!--                        <input-->
<!--                            type="number"-->
<!--                            v-model="formData.total"-->
<!--                            disabled-->
<!--                            :class="[-->
<!--                                isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',-->
<!--                                'w-full p-2 rounded border opacity-70'-->
<!--                            ]"-->
<!--                        />-->
<!--                    </div>-->
<!--                </div>-->

<!--                &lt;!&ndash; 表單按鈕 &ndash;&gt;-->
<!--                <div class="flex justify-end space-x-3 mt-6">-->
<!--                    <button-->
<!--                        @click="cancelForm"-->
<!--                        :class="[-->
<!--                            isDarkMode ? 'bg-gray-600 hover:bg-gray-500' : 'bg-gray-300 hover:bg-gray-200',-->
<!--                            'px-4 py-2 rounded-lg'-->
<!--                        ]"-->
<!--                    >-->
<!--                        取消-->
<!--                    </button>-->
<!--                    <button-->
<!--                        @click="submitForm"-->
<!--                        :class="[-->
<!--                            isDarkMode ? 'bg-blue-700 hover:bg-blue-600' : 'bg-blue-500 hover:bg-blue-400',-->
<!--                            'px-4 py-2 rounded-lg text-white'-->
<!--                        ]"-->
<!--                    >-->
<!--                        {{ isEditing ? '儲存變更' : '新增預留單' }}-->
<!--                    </button>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Navbar from './Components/Navbar.vue';

// 從 Inertia props 中獲取資料
const page = usePage();
const userID = computed(() => page.props.user);

// 場次資料
const sessions = computed(() => page.props.events || []);

// 側邊選單狀態
const menuOpen = ref(false);

// 深夜模式狀態
const isDarkMode = ref(false);

// 搜尋查詢
const searchQuery = ref('');

// 資料載入狀態
const isLoading = ref(false);

// 選中的場次
const selectedSession = ref(1);

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

// 待處理品項資料
const pendingItems = ref([
    { id: 1, name: '高山茶 - 清香型', reserved: 5, stock: 20, available: 15 },
    { id: 2, name: '烏龍茶 - 重發酵', reserved: 3, stock: 8, available: 5 },
    { id: 3, name: '四季春茶', reserved: 2, stock: 12, available: 10 },
    { id: 4, name: '金萱茶', reserved: 1, stock: 15, available: 14 }
]);

// 待處理預留單資料
const pendingReservations = ref([
    {
        id: 1,
        customer: '張先生',
        name: '龍井茶 - 特級',
        note: '客戶已確認',
        originalPrice: 2500,
        discount: 10,
        prepaid: 1000,
        remaining: 1250,
        total: 2250,
        selected: false,
        createdAt: '2025-02-20T08:30:00'
    },
    {
        id: 2,
        customer: '李小姐',
        name: '阿里山茶',
        note: '春季限定',
        originalPrice: 3200,
        discount: 5,
        prepaid: 1500,
        remaining: 1540,
        total: 3040,
        selected: false,
        createdAt: '2025-02-22T14:15:00'
    },
    {
        id: 3,
        customer: '王先生',
        name: '鐵觀音',
        note: '禮盒包裝',
        originalPrice: 1800,
        discount: 0,
        prepaid: 900,
        remaining: 900,
        total: 1800,
        selected: false,
        createdAt: '2025-02-25T09:45:00'
    }
]);

// 已完成預留單資料
const completedReservations = ref([
    {
        id: 101,
        orderId: 'ORD-4289',
        customer: '陳先生',
        name: '凍頂烏龍茶',
        note: '自取',
        originalPrice: 1800,
        discount: 0,
        prepaid: 600,
        remaining: 1200,
        total: 1800,
        completedAt: '2025-02-15T10:30:00'
    },
    {
        id: 102,
        orderId: 'ORD-4302',
        customer: '林小姐',
        name: '碧螺春',
        note: '貨到付款',
        originalPrice: 2200,
        discount: 5,
        prepaid: 1000,
        remaining: 1090,
        total: 2090,
        completedAt: '2025-02-18T16:15:00'
    }
]);

// 表單相關狀態
const showReservationForm = ref(false);
const isEditing = ref(false);
const formData = ref({
    id: null,
    customer: '',
    name: '',
    note: '',
    originalPrice: 0,
    discount: 0,
    prepaid: 0,
    remaining: 0,
    total: 0
});

// 全選功能
const selectAll = ref(false);

// 搜尋過濾功能
const filteredPendingItems = computed(() => {
    if (!searchQuery.value) return pendingItems.value;

    return pendingItems.value.filter(item =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const filteredPendingReservations = computed(() => {
    if (!searchQuery.value) return pendingReservations.value;

    return pendingReservations.value.filter(item =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (item.customer && item.customer.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (item.note && item.note.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

const filteredCompletedReservations = computed(() => {
    if (!searchQuery.value) return completedReservations.value;

    return completedReservations.value.filter(item =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (item.customer && item.customer.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (item.note && item.note.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        item.orderId.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
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

// 計算總金額和尾款
const calculateTotal = () => {
    const originalPrice = parseFloat(formData.value.originalPrice) || 0;
    const discount = parseFloat(formData.value.discount) || 0;

    // 計算折扣後金額
    formData.value.total = originalPrice * (1 - discount / 100);

    // 更新尾款
    calculateRemaining();
};

const calculateRemaining = () => {
    const total = parseFloat(formData.value.total) || 0;
    const prepaid = parseFloat(formData.value.prepaid) || 0;

    formData.value.remaining = total - prepaid;
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

// 新增預留單
const addReservation = () => {
    isEditing.value = false;

    // 重置表單資料
    formData.value = {
        id: null,
        customer: '',
        name: '',
        note: '',
        originalPrice: 0,
        discount: 0,
        prepaid: 0,
        remaining: 0,
        total: 0
    };

    // 顯示表單
    showReservationForm.value = true;
};

// 編輯預留單
const editReservation = (id) => {
    const reservation = pendingReservations.value.find(item => item.id === id);

    if (reservation) {
        isEditing.value = true;

        // 將預留單資料填入表單
        formData.value = { ...reservation };

        // 顯示表單
        showReservationForm.value = true;
    }
};

// 取消表單
const cancelForm = () => {
    showReservationForm.value = false;
};

// 提交表單
const submitForm = () => {
    if (isEditing.value) {
        // 編輯模式：更新現有預留單
        const index = pendingReservations.value.findIndex(item => item.id === formData.value.id);

        if (index !== -1) {
            pendingReservations.value[index] = {
                ...pendingReservations.value[index],
                ...formData.value,
                selected: false // 重設選擇狀態
            };
        }
    } else {
        // 新增模式：添加新預留單
        const newId = Date.now(); // 使用時間戳作為臨時ID

        pendingReservations.value.push({
            ...formData.value,
            id: newId,
            selected: false,
            createdAt: new Date().toISOString()
        });
    }

    // 關閉表單
    showReservationForm.value = false;

    // 實際應用中，這裡應該使用 Inertia.js 將資料發送到後端
    if (isEditing.value) {
        // 使用 Inertia.js 更新資料
        /*
        router.put(`/preorder/${formData.value.id}`, {
            ...formData.value,
            sessionId: selectedSession.value,
            userId: userID.value
        }, {
            preserveState: true,
            onSuccess: () => {
                // 成功處理
            }
        });
        */
    } else {
        // 使用 Inertia.js 新增資料
        /*
        router.post('/preorder', {
            ...formData.value,
            sessionId: selectedSession.value,
            userId: userID.value
        }, {
            preserveState: true,
            onSuccess: () => {
                // 成功處理
            }
        });
        */
    }
};

// 處理單筆預留單
const processReservation = (id) => {
    // 找到指定的預留單
    const index = pendingReservations.value.findIndex(item => item.id === id);

    if (index !== -1) {
        // 複製預留單資料並添加訂單ID和完成時間
        const processedItem = {
            ...pendingReservations.value[index],
            orderId: generateOrderId(),
            completedAt: new Date().toISOString()
        };

        // 添加到已完成預留單
        completedReservations.value.push(processedItem);

       // 從待處理預留單中移除
        pendingReservations.value.splice(index, 1);

        // 實際應用中，這裡應該使用 Inertia.js 將狀態變更發送到後端
        /*
        router.post(`/preorder/${id}/process`, {}, {
            preserveState: true,
            onSuccess: () => {
                // 成功處理
            }
        });
        */
    }
};

// 批次處理多筆預留單
const processBatchReservations = () => {
    // 獲取所有已選擇的預留單
    const selectedIds = getSelectedReservations.value.map(r => r.id);

    // 依序處理每一筆
    selectedIds.forEach(id => processReservation(id));

    // 重設全選狀態
    selectAll.value = false;
};

// 生成訂單編號
const generateOrderId = () => {
    return 'ORD-' + Math.floor(1000 + Math.random() * 9000);
};

// 初始化
onMounted(() => {
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

    // 載入當前場次的預留單資料
    loadPreOrderData(selectedSession.value);
});

// 處理場次變更
const handleSessionChange = (sessionId) => {
    console.log('預留單頁面：場次已變更為', sessionId);
    selectedSession.value = sessionId;

    // 保存選擇的場次到本地儲存
    localStorage.setItem('selectedSession', sessionId);

    // 重新載入該場次的預留單資料
    loadPreOrderData(sessionId);
};

// 載入預留單資料
const loadPreOrderData = (sessionId) => {
    isLoading.value = true;

    // 實際應用中，這裡應該從 Inertia props 獲取資料或使用 fetch API 獲取資料
    /*
    fetch(`/api/preorder/get/${userID.value}/${sessionId}`)
        .then(response => response.json())
        .then(data => {
            pendingItems.value = data.pendingItems || [];
            pendingReservations.value = data.pendingReservations || [];
            completedReservations.value = data.completedReservations || [];
        })
        .catch(error => {
            console.error('擷取預留單資料時發生錯誤:', error);
            alert(`無法載入預留單資料，請重新整理頁面或聯絡系統管理員。\n錯誤訊息: ${error.message}`);
        })
        .finally(() => {
            isLoading.value = false;
        });
    */

    // 模擬載入不同場次的資料
    setTimeout(() => {
        console.log(`載入場次 ${sessionId} 的預留單資料`);

        // 模擬不同場次有不同的預留單資料
        if (sessionId % 2 === 0) {
            pendingItems.value = [
                { id: 3, name: '四季春茶 - 場次' + sessionId, reserved: 2, stock: 12, available: 10 },
                { id: 4, name: '金萱茶 - 場次' + sessionId, reserved: 1, stock: 15, available: 14 }
            ];

            pendingReservations.value = [
                {
                    id: 2,
                    customer: '李小姐',
                    name: '阿里山茶 - 場次' + sessionId,
                    note: '春季限定',
                    originalPrice: 3200,
                    discount: 5,
                    prepaid: 1500,
                    remaining: 1540,
                    total: 3040,
                    selected: false,
                    createdAt: '2025-02-22T14:15:00'
                }
            ];
        } else {
            pendingItems.value = [
                { id: 1, name: '高山茶 - 清香型 - 場次' + sessionId, reserved: 5, stock: 20, available: 15 },
                { id: 2, name: '烏龍茶 - 重發酵 - 場次' + sessionId, reserved: 3, stock: 8, available: 5 }
            ];

            pendingReservations.value = [
                {
                    id: 1,
                    customer: '張先生',
                    name: '龍井茶 - 特級 - 場次' + sessionId,
                    note: '客戶已確認',
                    originalPrice: 2500,
                    discount: 10,
                    prepaid: 1000,
                    remaining: 1250,
                    total: 2250,
                    selected: false,
                    createdAt: '2025-02-20T08:30:00'
                },
                {
                    id: 3,
                    customer: '王先生',
                    name: '鐵觀音 - 場次' + sessionId,
                    note: '禮盒包裝',
                    originalPrice: 1800,
                    discount: 0,
                    prepaid: 900,
                    remaining: 900,
                    total: 1800,
                    selected: false,
                    createdAt: '2025-02-25T09:45:00'
                }
            ];
        }

        // 已完成的預留單在所有場次都顯示
        completedReservations.value = [
            {
                id: 101,
                orderId: 'ORD-' + sessionId + '-4289',
                customer: '陳先生',
                name: '凍頂烏龍茶 - 場次' + sessionId,
                note: '自取',
                originalPrice: 1800,
                discount: 0,
                prepaid: 600,
                remaining: 1200,
                total: 1800,
                completedAt: '2025-02-15T10:30:00'
            }
        ];

        isLoading.value = false;
    }, 800);
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
