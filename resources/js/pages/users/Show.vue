<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Back button -->
                <div class="mb-4 sm:mb-6">
                    <InertiaLink :href="route('users.index')" class="flex items-center text-blue-400 hover:text-blue-300">
                        <ArrowLeft class="mr-1 h-4 w-4" />
                        <span class="whitespace-nowrap">Back to User Directory</span>
                    </InertiaLink>
                </div>

                <!-- User Profile Header -->
                <div class="mb-4 overflow-hidden rounded-lg bg-gray-800 shadow-lg sm:mb-6">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div class="mb-4 sm:mb-0">
                                <div class="flex flex-wrap items-center">
                                    <h1 class="mr-2 text-xl font-bold text-white sm:text-2xl">{{ profileUser.name }}</h1>
                                    <div
                                        v-if="profileUser.is_admin"
                                        class="inline-block rounded-full border border-purple-700/50 bg-purple-900/60 px-2 py-0.5 text-xs font-semibold text-purple-300"
                                    >
                                        Admin
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-400">Member since {{ profileUser.created_at }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <InertiaLink
                                    v-if="canTrade"
                                    :href="route('trades.create', { recipient: profileUser.id })"
                                    class="flex w-full items-center justify-center rounded-md bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-2 text-white transition-all hover:from-blue-600 hover:to-blue-700 sm:w-auto"
                                >
                                    <SendHorizonal class="mr-1 h-4 w-4" />
                                    <span class="whitespace-nowrap">Trade with {{ truncatedName }}</span>
                                </InertiaLink>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collection Stats Overview -->
                <div class="mb-4 grid grid-cols-1 gap-3 sm:mb-6 sm:grid-cols-2 sm:gap-4 md:grid-cols-3 md:gap-6">
                    <div class="flex flex-col rounded-lg bg-gray-800 p-4 sm:p-6">
                        <h3 class="mb-1 text-sm text-gray-400">Total Cards</h3>
                        <div class="text-xl font-bold text-white sm:text-2xl">{{ collection.total_cards }}</div>
                        <div class="mt-auto pt-2 text-sm text-gray-500">Cards in collection</div>
                    </div>

                    <div class="flex flex-col rounded-lg bg-gray-800 p-4 sm:p-6">
                        <h3 class="mb-1 text-sm text-gray-400">Average Rarity</h3>
                        <div class="flex items-center text-xl font-bold text-white sm:text-2xl">
                            <Star class="mr-1 h-4 w-4 text-yellow-400 sm:h-5 sm:w-5" />
                            {{ collection.avg_rarity }}/10
                        </div>
                        <div class="mt-auto pt-2 text-sm text-gray-500">Higher is rarer</div>
                    </div>

                    <div class="flex flex-col rounded-lg bg-gray-800 p-4 sm:col-span-2 sm:p-6 md:col-span-1">
                        <h3 class="mb-1 text-sm text-gray-400">Rarity Distribution</h3>
                        <div class="flex h-6 w-full overflow-hidden rounded-md bg-gray-700 sm:h-8">
                            <div
                                v-for="(count, index) in collection.rarity_distribution"
                                :key="index"
                                :style="{ width: `${getRarityBarWidth(index)}%` }"
                                :class="getRarityBarColor(index)"
                                class="h-full"
                                :title="`Rarity ${index + 1}: ${count} cards`"
                            ></div>
                        </div>
                        <div class="mt-auto flex justify-between pt-2 text-sm text-gray-500">
                            <span>Common (1)</span>
                            <span>Rare (10)</span>
                        </div>
                    </div>
                </div>

                <!-- Card Collection -->
                <div>
                    <!-- Owned Cards Section -->
                    <div class="mb-8">
                        <div class="mb-3 flex flex-col gap-2 sm:mb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                            <h2 class="text-lg font-bold text-white sm:text-xl">Owned Cards</h2>

                            <!-- Filters -->
                            <div class="flex items-center">
                                <select
                                    v-model="sortBy"
                                    @change="sortCards"
                                    class="w-full rounded-md border border-gray-700 bg-gray-800 px-3 py-1.5 text-sm text-white focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none sm:w-auto"
                                >
                                    <option value="name">Sort by Name</option>
                                    <option value="rarity-desc">Sort by Rarity (High to Low)</option>
                                    <option value="rarity-asc">Sort by Rarity (Low to High)</option>
                                    <option value="quantity">Sort by Quantity</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="ownedCards.length === 0" class="rounded-lg bg-gray-800 p-4 text-center text-gray-400 sm:p-6">
                            This user doesn't own any cards yet.
                        </div>

                        <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
                            <div
                                v-for="ownership in ownedCards"
                                :key="ownership.id"
                                class="overflow-hidden rounded-lg bg-gray-800 shadow transition-all hover:shadow-purple-500/20"
                            >
                                <!-- Card Image -->
                                <div class="relative h-36 overflow-hidden sm:h-40 md:h-48">
                                    <img
                                        :src="`/storage/${ownership.card.image_path}`"
                                        :alt="ownership.card.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div class="bg-opacity-75 absolute top-0 right-0 bg-black px-2 py-1">
                                        <div class="flex items-center">
                                            <Star class="mr-1 h-3.5 w-3.5 text-yellow-400" />
                                            <span class="text-sm font-bold text-white">{{ ownership.card.rarity }}/10</span>
                                        </div>
                                    </div>
                                    <div class="absolute right-0 bottom-0 left-0 bg-gradient-to-t from-black to-transparent p-3">
                                        <div class="text-sm font-bold text-white">{{ ownership.card.name }}</div>
                                    </div>
                                </div>

                                <!-- Card Details -->
                                <div class="p-3 sm:p-4">
                                    <div class="mb-2 grid grid-cols-3 gap-1 text-xs sm:mb-3 sm:gap-2 sm:text-sm">
                                        <div class="rounded bg-gray-700 p-1 text-center sm:p-2">
                                            <div class="text-red-400">HP</div>
                                            <div class="font-bold text-white">{{ ownership.card.hp }}</div>
                                        </div>
                                        <div class="rounded bg-gray-700 p-1 text-center sm:p-2">
                                            <div class="text-blue-400">STR</div>
                                            <div class="font-bold text-white">{{ ownership.card.strength }}</div>
                                        </div>
                                        <div class="rounded bg-gray-700 p-1 text-center sm:p-2">
                                            <div class="text-green-400">DEF</div>
                                            <div class="font-bold text-white">{{ ownership.card.defense }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm text-gray-400">
                                                {{ ownership.quantity }} card{{ ownership.quantity !== 1 ? 's' : '' }}
                                            </div>
                                            <div class="mt-1 flex flex-wrap gap-1 text-xs text-gray-500">
                                                <span
                                                    v-for="serial in ownership.serial_numbers.slice(0, 2)"
                                                    :key="serial"
                                                    class="rounded bg-gray-700 px-1 py-0.5 text-[10px] sm:px-1.5 sm:text-xs"
                                                >
                                                    #{{ serial }}
                                                </span>
                                                <span
                                                    v-if="ownership.serial_numbers.length > 2"
                                                    class="rounded bg-gray-700 px-1 py-0.5 text-[10px] sm:px-1.5 sm:text-xs"
                                                >
                                                    +{{ ownership.serial_numbers.length - 2 }}
                                                </span>
                                            </div>
                                        </div>
                                        <div
                                            v-if="ownership.card.created_by"
                                            class="rounded border border-purple-700/50 bg-purple-900/60 px-2 py-0.5 text-xs text-purple-300"
                                        >
                                            Creator
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Created Cards Section -->
                        <div>
                            <div class="mb-3 flex flex-col gap-2 sm:mb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                                <h2 class="text-lg font-bold text-white sm:text-xl">Created Cards</h2>
                            </div>

                            <div v-if="createdCards.length === 0" class="rounded-lg bg-gray-800 p-4 text-center text-gray-400 sm:p-6">
                                This user hasn't created any cards yet.
                            </div>

                            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
                                <div
                                    v-for="ownership in createdCards"
                                    :key="ownership.id"
                                    class="overflow-hidden rounded-lg bg-gray-800 shadow transition-all hover:shadow-purple-500/20"
                                >
                                    <!-- Card Image -->
                                    <div class="relative h-36 overflow-hidden sm:h-40 md:h-48">
                                        <img :src="`/storage/${ownership.image_path}`" :alt="ownership.name" class="h-full w-full object-cover" />
                                        <div class="bg-opacity-75 absolute top-0 right-0 bg-black px-2 py-1">
                                            <div class="flex items-center">
                                                <Star class="mr-1 h-3.5 w-3.5 text-yellow-400" />
                                                <span class="text-sm font-bold text-white">{{ ownership.rarity }}/10</span>
                                            </div>
                                        </div>
                                        <div class="absolute right-0 bottom-0 left-0 bg-gradient-to-t from-black to-transparent p-3">
                                            <div class="text-sm font-bold text-white">{{ ownership.name }}</div>
                                        </div>
                                    </div>

                                    <!-- Card Details -->
                                    <div class="p-3 sm:p-4">
                                        <div class="mb-2 grid grid-cols-3 gap-1 text-xs sm:mb-3 sm:gap-2 sm:text-sm">
                                            <div class="rounded bg-gray-700 p-1 text-center sm:p-2">
                                                <div class="text-red-400">HP</div>
                                                <div class="font-bold text-white">{{ ownership.hp }}</div>
                                            </div>
                                            <div class="rounded bg-gray-700 p-1 text-center sm:p-2">
                                                <div class="text-blue-400">STR</div>
                                                <div class="font-bold text-white">{{ ownership.strength }}</div>
                                            </div>
                                            <div class="rounded bg-gray-700 p-1 text-center sm:p-2">
                                                <div class="text-green-400">DEF</div>
                                                <div class="font-bold text-white">{{ ownership.defense }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div class="rounded border border-purple-700/50 bg-purple-900/60 px-2 py-0.5 text-xs text-purple-300">
                                                Creator
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link as InertiaLink } from '@inertiajs/vue3';
import { ArrowLeft, SendHorizonal, Star } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    profileUser: Object,
    collection: Object,
    canTrade: Boolean,
});

const sortBy = ref('name');
const isMobile = ref(window.innerWidth < 640);
const truncatedName = computed(() => {
    return isMobile.value ? props.profileUser.name.substring(0, 6) + '...' : props.profileUser.name;
});

// Handle window resize
const handleResize = () => {
    isMobile.value = window.innerWidth < 640;
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
});
// Separate owned and created cards
const ownedCards = computed(() => {
    if (!props.collection.cards) return [];

    return sortCardList(props.collection.cards);
});

const createdCards = computed(() => {
    if (!props.collection.cards) return [];

    // Filter cards that the user created
    return sortCardList(props.collection.createdCards);
});

const sortedCards = computed(() => {
    if (!props.collection.cards) return [];
    return sortCardList([...props.collection.cards]);
});

// Helper function to sort cards
function sortCardList(cards) {
    switch (sortBy.value) {
        case 'name':
            return cards.sort((a, b) => a.card.name.localeCompare(b.card.name));
        case 'rarity-desc':
            return cards.sort((a, b) => (b.card.rarity || 0) - (a.card.rarity || 0));
        case 'rarity-asc':
            return cards.sort((a, b) => (a.card.rarity || 0) - (b.card.rarity || 0));
        case 'quantity':
            return cards.sort((a, b) => b.quantity - a.quantity);
        default:
            return cards;
    }
}

function sortCards() {
    // The sort is automatically applied through the computed properties
}

function getRarityBarWidth(rarityIndex) {
    const total = props.collection.total_cards || 0;
    if (total === 0) return 0;

    const count = props.collection.rarity_distribution[rarityIndex];
    return (count / total) * 100;
}

function getRarityBarColor(rarityIndex) {
    const colors = [
        'bg-green-600', // 1 - Common
        'bg-green-500',
        'bg-blue-600',
        'bg-blue-500',
        'bg-blue-400',
        'bg-purple-600',
        'bg-purple-500',
        'bg-pink-600',
        'bg-pink-500',
        'bg-red-500', // 10 - Rare
    ];

    return colors[rarityIndex] || 'bg-gray-600';
}
</script>
