<!-- CheckoutModal.vue -->
<template>
    <div v-if="isVisible" class="fixed inset-0 flex items-center justify-center z-50">
        <!-- 半透明背景 -->
        <div class="absolute inset-0 bg-black opacity-50" @click="closeModal"></div>

        <!-- 彈出視窗內容 -->
        <div :class="[isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-gray-800', 'relative w-11/12 sm:w-11/12 md:w-10/12 lg:w-8/12 max-w-lg rounded-lg shadow-xl p-4 sm:p-3 md:p-4 lg:p-5 max-h-[90vh] overflow-y-auto']">
            <!-- 購物車商品列表 -->
            <div class="border rounded-lg p-3 mb-3">
                <div v-for="item in cartItems" :key="item.id" class="flex justify-between py-1 sm:text-sm md:text-base">
                    <div class="flex items-center">
                        <span>{{ item.item_name }}</span>
                        <span v-if="item.is_r18" class="bg-red-600 text-white px-1 py-0.5 rounded-full text-xs ml-1">18+</span>
                        <span class="ml-2">x {{ item.quantity }}</span>
                    </div>
                    <span>${{ item.item_price * item.quantity }}</span>
                </div>

                <div class="border-t mt-2 pt-2 flex justify-between font-bold">
                    <span>總計</span>
                    <span>${{ total }}</span>
                </div>
            </div>

            <!-- 年齡驗證警告 -->
            <div v-if="hasAdultItems" class="bg-red-600 text-white p-2 mb-3 rounded-lg flex items-center text-xs sm:text-sm">
                <span class="text-xl mr-2">⚠️</span>
                <span>請確認購買者年齡滿18歲（民國 {{ r18Date }} 以前出生）</span>
            </div>

            <!-- 支付方式選擇 -->
            <div class="mb-3">
                <label :class="[isDarkMode ? 'text-gray-300' : 'text-gray-600', 'text-xs sm:text-sm block mb-1']">支付方式</label>
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2">
                    <button
                        v-for="method in payment"
                        :key="method.id"
                        @click="selectPaymentMethod(method)"
                        :class="[
                            selectedPayment && selectedPayment.id === method.id
                                ? (isDarkMode ? 'bg-blue-700 text-white' : 'bg-blue-100 text-blue-800')
                                : (isDarkMode ? 'bg-gray-700 text-gray-300' : 'bg-gray-100 text-gray-800'),
                            'py-2 px-3 rounded-md text-center text-sm transition-colors'
                        ]"
                    >
                        {{ method.payment_name }}
                    </button>
                </div>
            </div>

            <!-- 找零計算區域 -->
            <div class="mb-3">
                <div class="flex justify-between text-green-600 font-bold mb-2">
                    <span>找零: ${{ calculateChange() }}</span>
                </div>

                <div class="flex flex-col sm:flex-col md:flex-row mb-2">
                    <div class="w-full md:w-1/2 pr-0 md:pr-1 mb-1 md:mb-0">
                        <label :class="[isDarkMode ? 'text-gray-300' : 'text-gray-600', 'text-sm']">收款金額</label>
                    </div>
                    <div class="w-full md:w-1/2 pl-0 md:pl-1">
                        <input
                            v-model="receivedAmount"
                            type="text"
                            :class="[
            isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-gray-800 border-gray-300',
            'w-full border rounded-md p-2 text-right'
        ]"
                            readonly
                        />
                    </div>
                </div>
            </div>

            <!-- 快速金額按鈕 -->
            <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 gap-1 sm:gap-2 mb-3">
                <button
                    v-for="amount in quickAmounts"
                    :key="amount"
                    @click="setReceivedAmount(amount)"
                    :class="[
            isDarkMode ? 'bg-green-800 hover:bg-green-700' : 'bg-white hover:bg-gray-100',
            isDarkMode ? 'text-white border-green-700' : 'text-green-600 border-green-500',
            'border rounded-md py-1 sm:py-2 text-sm font-medium'
          ]"
                >
                    ${{ amount }}
                </button>
            </div>

            <!-- 數字鍵盤 -->
            <div class="grid grid-cols-3 gap-1 sm:gap-2 mb-3">
                <!-- 清除按鈕 -->
                <button
                    @click="clearReceivedAmount"
                    :class="[
            isDarkMode ? 'bg-blue-800 hover:bg-blue-700' : 'bg-blue-500 hover:bg-blue-400',
            'text-white rounded-md py-2 sm:py-2 md:py-3 font-medium'
          ]"
                >
                    C
                </button>

                <!-- 加號按鈕 -->
                <button
                    @click="appendToReceivedAmount('+')"
                    :class="[
            isDarkMode ? 'bg-green-800 hover:bg-green-700' : 'bg-green-600 hover:bg-green-500',
            'text-white rounded-md py-2 sm:py-2 md:py-3 font-medium'
          ]"
                >
                    +
                </button>

                <!-- 減號按鈕 -->
                <button
                    @click="appendToReceivedAmount('-')"
                    :class="[
            isDarkMode ? 'bg-red-800 hover:bg-red-700' : 'bg-red-400 hover:bg-red-300',
            'text-white rounded-md py-1 sm:py-2 text-sm font-medium'
          ]"
                >
                    -
                </button>

                <!-- 數字按鈕 -->
                <button
                    v-for="num in [7, 8, 9, 4, 5, 6, 1, 2, 3, 0, '00', '.']"
                    :key="num"
                    @click="appendToReceivedAmount(num.toString())"
                    :class="[
            isDarkMode ? 'bg-gray-700 hover:bg-gray-600 text-white' : 'bg-white hover:bg-gray-100 text-gray-800',
            'border border-gray-300 rounded-md py-3 font-medium'
          ]"
                >
                    {{ num }}
                </button>
            </div>

            <!-- 備註欄位 -->
            <div class="mb-3">
        <textarea
            v-model="checkoutNote"
            :class="[
            isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-gray-800 border-gray-300',
            'w-full border rounded-md p-2 text-sm'
          ]"
            rows="1"
            placeholder="備註"
        ></textarea>
            </div>

            <!-- 底部按鈕 -->
            <div class="flex md:flex-row justify-end space-x-2">
                <button
                    @click="closeModal"
                    :class="[
                        isDarkMode ? 'bg-gray-700 hover:bg-gray-600' : 'bg-gray-200 hover:bg-gray-300',
                        isDarkMode ? 'text-white' : 'text-gray-800',
                        'w-auto px-3 py-1 sm:py-2 text-sm rounded-md'
                    ]"
                >
                    取消
                </button>
                <button
                    @click="completeTransaction"
                    :class="[
            isDarkMode ? 'bg-blue-700 hover:bg-blue-600' : 'bg-blue-500 hover:bg-blue-400',
            'text-white px-4 py-2 rounded-md',
            !canCompleteTransaction ? 'opacity-50 cursor-not-allowed' : ''
          ]"
                    :disabled="!canCompleteTransaction"
                >
                    完成交易
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// 元件接收的屬性
const props = defineProps({
    // 是否顯示結帳視窗
    isVisible: {
        type: Boolean,
        required: true
    },
    // 深夜模式狀態
    isDarkMode: {
        type: Boolean,
        default: false
    },
    // 購物車商品
    cartItems: {
        type: Array,
        required: true
    },
    // 購物車總金額
    total: {
        type: Number,
        required: true
    },
    // 是否包含成人商品
    hasAdultItems: {
        type: Boolean,
        default: false
    },
    // 快速金額選項
    quickAmounts: {
        type: Array,
    },
    // 支付方式選項
    payment: {
        type: Array,
        default: () => []
    },
    r18Date: {
        type: String
    }
})

// 元件發出的事件
const emit = defineEmits(['close', 'complete'])

// 收款金額
const receivedAmount = ref('')
// 結帳備註
const checkoutNote = ref('')
// 選擇的支付方式
const selectedPayment = ref(null)

const quickAmounts = props.quickAmounts

// 監聽視窗顯示狀態變化，當開啟視窗時重置輸入內容
watch(() => props.isVisible, (newVal) => {
    if (newVal === true) {
        receivedAmount.value = ''
        checkoutNote.value = ''
        selectedPayment.value = null
    }
})

// 判斷是否可以完成交易（收款金額必須大於等於總金額且已選擇支付方式）
const canCompleteTransaction = computed(() => {
    const received = parseFloat(receivedAmount.value || 0)
    return !isNaN(received) && received >= props.total && selectedPayment.value !== null
})

// 計算找零金額
const calculateChange = () => {
    const received = parseFloat(receivedAmount.value || 0)
    return Math.max(0, received - props.total)
}

// 關閉結帳彈出視窗
const closeModal = () => {
    emit('close')
}

// 設置收款金額（快速按鈕）
const setReceivedAmount = (amount) => {
    receivedAmount.value = amount.toString()
}

// 清除收款金額
const clearReceivedAmount = () => {
    receivedAmount.value = ''
}

// 選擇支付方式
const selectPaymentMethod = (method) => {
    selectedPayment.value = method
}

// 附加數字到收款金額
const appendToReceivedAmount = (value) => {
    if (value === '+' || value === '-') {
        // 計算當前輸入值
        try {
            receivedAmount.value = eval(receivedAmount.value) || '0'
        } catch (e) {
            receivedAmount.value = '0'
        }
        return
    }

    // 附加數字或小數點
    if (value === '.' && receivedAmount.value.includes('.')) {
        return // 防止多個小數點
    }

    receivedAmount.value = (receivedAmount.value || '') + value
}

// 完成交易
const completeTransaction = () => {
    // 準備購物清單資料，包含商品 ID 和數量
    const items = props.cartItems.map(item => ({
        id: item.id,
        item_name: item.item_name,
        quantity: item.quantity,
        price: item.item_price,
        subtotal: item.quantity * item.item_price
    }))

    emit('complete', {
        amount: parseFloat(receivedAmount.value),
        change: calculateChange(),
        note: checkoutNote.value,
        items: items,
        timestamp: new Date().toISOString(),
        paymentMethod: selectedPayment.value
    })
}
</script>

<style scoped>
/* 結帳彈出視窗動畫 */
@keyframes modal-fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fixed {
    animation: modal-fade-in 0.3s ease-out;
}
@media (max-width: 640px) {
    button {
        min-height: 36px; /* 縮減按鈕高度但保持足夠的觸控區域 */
        font-size: 0.875rem;
    }

    input, textarea {
        padding: 0.375rem; /* 縮減輸入框的內距 */
    }
}

/* 確保視窗內容不會超出螢幕 */
.max-h-[90vh] {
    max-height: 90vh;
}

/* 優化滾動條樣式 */
.max-h-[90vh]::-webkit-scrollbar {
                 width: 4px;
             }

.max-h-[90vh]::-webkit-scrollbar-thumb {
                 background-color: rgba(156, 163, 175, 0.5);
                 border-radius: 2px;
             }
</style>
