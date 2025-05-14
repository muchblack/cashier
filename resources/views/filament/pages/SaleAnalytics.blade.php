<x-filament-panels::page>
    <div class="space-y-6">
        {{-- 頁面標題區 --}}
        <div class="p-6 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white">
            <h1 class="text-2xl font-bold">銷售分析儀表板</h1>
            <p class="mt-1 text-primary-100">查看您的銷售表現與趨勢分析</p>
        </div>

        {{-- 頭部 Widget 區域 --}}
        <div>
            {{ $this->header ?? '' }}
        </div>

        {{-- 中間自訂內容區域（如果需要） --}}
        <x-filament::section>
            <x-slot name="heading">
                銷售洞察
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p>這個儀表板提供您的關鍵銷售指標和趨勢分析。您可以查看目前的銷售表現，了解熱銷商品，以及追蹤銷售增長情況。</p>
                <p>如需更詳細的報表，請使用下方的「銷售報表」功能，或前往「報表中心」查詢更多歷史數據。</p>
            </div>
        </x-filament::section>

        {{-- 底部 Widget 區域 --}}
        <div>
            {{ $this->footer ?? ''  }}
        </div>
    </div>
</x-filament-panels::page>
