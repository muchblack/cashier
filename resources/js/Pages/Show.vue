<!-- 修改整體容器結構和卡片佈局 -->
<template>
    <div class="min-h-screen bg-gray-900 text-white p-4">
        <div class="max-w-7xl mx-auto">
            <div class="rounded-lg bg-gray-800 p-4 mb-6">
                <h1 class="text-xl">當前場次：{{ currentVenue }}</h1>
            </div>

            <!-- 使用 grid 佈局，設定為兩欄 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="item in productItems"
                    :key="item.id"
                    :class="[!item.item_img_url ? getColorForItem(item.id) : '', 'rounded-lg overflow-hidden relative']"
                    style="height: 280px;"
                >
                    <!-- 固定卡片高度 -->
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
                            backgroundColor: 'rgba(0, 0, 0, 0.5)',
                            backdropFilter: 'blur(1px)'
                        }"
                ></div>

                <!-- 內容層 -->
                <div class="relative z-10 p-4 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-1">
                        <div class="text-xl font-bold">{{ item.title }}</div>
                        <div
                            v-if="item.is_r18"
                            class="bg-red-600 text-white rounded-lg px-3 py-1 text-sm whitespace-nowrap"
                        >
                            18+
                        </div>
                    </div>

                    <div v-if="item.item_name" class="text-xl mb-1">{{ item.item_name }}</div>
                    <div v-if="item.item_name_en" class="text-sm mb-2">{{ item.item_name_en }}</div>
                    <div v-if="item.item_name_jp" class="text-sm mb-2">{{ item.item_name_jp }}</div>

                    <div class="text-xl font-semibold mb-4">${{ item.item_price }}</div>

                    <div class="mt-auto flex gap-2 self-end">
                        <div class="bg-amber-600 rounded-lg px-3 py-1 text-sm whitespace-nowrap">
                            已預訂:{{ item.preOrder }}
                        </div>
                        <div class="bg-green-600 rounded-lg px-3 py-1 text-sm whitespace-nowrap">
                            現場:{{ item.item_stock }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>

<script setup>
import {defineProps, onMounted, ref} from 'vue';

const props = defineProps({
    currentVenue: String,
    eventId: String,
    userId: String,
    events: Array,
});

const isLoading = ref(false);
const productItems = ref([]);

const fetchProductsBySession = async (sessionId) => {
    try {
        isLoading.value = true;
        console.log(`正在擷取場次 ${sessionId} 的商品資料...`);

        // 呼叫API獲取特定場次的商品資料
        const response = await fetch(`/api/items/get/${props.userId}/${sessionId}`);

        if (!response.ok) {
            throw new Error(`無法獲取場次 ${sessionId} 的商品資料: ${response.status}`);
        }

        const data = await response.json();
        console.log(data.data);

        // 更新商品資料
        productItems.value = data.data || [];

        console.log(`已成功載入 ${productItems.value.length} 個商品`);
    } catch (error) {
        console.error('擷取商品資料時發生錯誤:', error);
        // 顯示錯誤訊息給使用者
        alert(`無法載入商品資料，請重新整理頁面或聯絡系統管理員。\n錯誤訊息: ${error.message}`);
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    // 根據選中的場次載入商品資料
    await fetchProductsBySession(props.eventId);
});

const getColorForItem = (id) => {
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

    return darkColors[colorIndex] || 'bg-gray-900';
};
</script>
