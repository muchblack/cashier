<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            系統更新說明
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getUpdate() as $update)
                <div class="flex items-start p-2 rounded-lg dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-300 transition-all hover:translate-x-1">
                    <div class="dark:text-white text-gray-800">
                        <div>
                            <span class="font-medium">[{{ $update->created_at->format('Y-m-d') }}]</span>
                            <span class="ml-1">{{ $update->title }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex items-center justify-center p-4 rounded-lg dark:bg-gray-800 bg-gray-100">
                    <x-filament::icon
                        icon="heroicon-o-information-circle"
                        class="w-5 h-5 mr-2 text-gray-400"
                    />
                    <span class="dark:text-gray-400 text-gray-500">目前尚無系統更新記錄</span>
                </div>
            @endforelse

            <div class="rounded-lg dark:border-amber-800 border-amber-300 p-3 mt-2 dark:bg-amber-900/50 bg-amber-50">
                <div class="flex items-start">
                    <x-filament::icon
                        icon="heroicon-o-light-bulb"
                        class="w-5 h-5 mr-2 dark:text-amber-300 text-amber-500"
                    />
                    <div class="text-sm dark:text-amber-100 text-amber-800">
                        看到什麼改什麼，有想法也可以跟我說。
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
