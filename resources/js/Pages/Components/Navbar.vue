<template>
    <header class="flex items-center justify-between p-4 bg-blue-500 text-white">
        <div class="flex items-center">
            <button class="text-xl flex items-center mr-4" @click="$emit('toggleMenu')">
                <svg v-if="!menuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ menuTitle }}</span>
            </button>
            <h1 class="text-2xl font-bold">{{ pageTitle }}</h1>
        </div>
        <div class="flex items-center">
            <!-- 場次下拉選單 (根據需要顯示) -->
            <div v-if="showSessionSelector && sessions.length > 0" class="relative mx-4">
                <select
                    v-model="selectedSession"
                    @change="$emit('sessionChange', selectedSession)"
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
            <!-- 導覽選項 -->
            <div class="flex items-center space-x-4">
                <a href="/admin" class="hover:text-blue-200">
                    回到管理頁
                </a>
            </div>
            <!-- 深色模式切換 -->
            <div class="ml-4 text-2xl cursor-pointer" @click="toggleDarkMode">
                {{ isDarkMode ? '☀️' : '🌙' }}
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, defineProps, defineEmits, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    pageTitle: {
        type: String,
        required: true
    },
    menuTitle: {
        type: String,
        default: '選單'
    },
    menuOpen: {
        type: Boolean,
        default: false
    },
    isDarkMode: {
        type: Boolean,
        default: false
    },
    sessions: {
        type: Array,
        default: () => []
    },
    currentSession: {
        type: Number,
        default: null
    },
    showSessionSelector: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['toggleMenu', 'toggleDarkMode', 'sessionChange']);

// 取得目前頁面資訊
const page = usePage();
const currentPage = computed(() => {
    const url = page.url;
    if (url.includes('/cashier')) return 'cashier';
    if (url.includes('/preorder')) return 'preorder';
    return '';
});

// 深色模式切換
const toggleDarkMode = () => {
    emit('toggleDarkMode');
};

// 綁定當前選擇的場次
const selectedSession = ref(props.currentSession);

// 監聽 props 中的 currentSession 變更
watch(() => props.currentSession, (newValue) => {
    selectedSession.value = newValue;
});
</script>
