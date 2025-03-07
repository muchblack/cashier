<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            系統使用指南
        </x-slot>

        <div class="space-y-4">
            <div class="flex items-start p-2 rounded-lg dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-300">
                <x-filament::icon
                    icon="heroicon-o-list-bullet"
                    class="w-6 h-6 mr-3 text-primary-400"
                />
                <div class="dark:text-white text-gray-800 font-medium">1. 在「場次列表」中建立您要參加的場次名稱和時間。</div>
            </div>

            <div class="flex items-start p-2 rounded-lg dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-300">
                <x-filament::icon
                    icon="heroicon-o-shopping-bag"
                    class="w-6 h-6 mr-3 text-primary-400"
                />
                <div class="dark:text-white text-gray-800 font-medium">2. 在「商品列表」中建立您要銷售的商品內容。</div>
            </div>

            <div class="flex items-start p-2 rounded-lg dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-300">
                <x-filament::icon
                    icon="heroicon-o-credit-card"
                    class="w-6 h-6 mr-3 text-primary-400"
                />
                <div class="dark:text-white text-gray-800 font-medium">3. 在「付款方式」中建立您的收款項目（與跳收報表相同）。</div>
            </div>

            <div class="flex items-start p-2 rounded-lg dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-300">
                <x-filament::icon
                    icon="heroicon-o-currency-dollar"
                    class="w-6 h-6 mr-3 text-primary-400"
                />
                <div class="dark:text-white text-gray-800 font-medium">4. 在「常用面額」中建立項目（與跳收報表相同）。</div>
            </div>

            <div class="flex items-start p-2 rounded-lg dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-300">
                <x-filament::icon
                    icon="heroicon-o-document-text"
                    class="w-6 h-6 mr-3 text-primary-400"
                />
                <div class="dark:text-white text-gray-800 font-medium">5. 從「主控台」點選「前往收銀計算頁面」開始操作。</div>
            </div>

            <div class="flex items-start p-2 rounded-lg dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-300">
                <x-filament::icon
                    icon="heroicon-o-document-text"
                    class="w-6 h-6 mr-3 text-primary-400"
                />
                <div class="dark:text-white text-gray-800 font-medium">備註：庫存頁面網址在新增場次後，可在場次列表頁中看到</div>
            </div>

            <div class="rounded-lg dark:border-blue-800 border-blue-300 p-3 mt-2 dark:bg-blue-900 bg-blue-50">
                <div class="flex items-start">
                    <x-filament::icon
                        icon="heroicon-o-information-circle"
                        class="w-5 h-5 mr-2 dark:text-blue-300 text-blue-500"
                    />
                    <div class="text-sm dark:text-blue-100 text-blue-800">
                        完成以上設定後，您就可以開始使用收銀系統進行交易了。如有任何問題，請聯絡系統管理員。
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
