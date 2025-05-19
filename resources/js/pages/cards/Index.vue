<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-3xl font-extrabold text-transparent">
                        My Card Collection
                    </h1>

                    <Link
                        v-if="uploadAllowed"
                        :href="route('cards.create')"
                        class="rounded-md bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-all hover:from-purple-600 hover:to-pink-600"
                    >
                        <div class="flex flex-col items-center">
                            <span>Upload New Card</span>
                            <DevModeBadge
                                v-if="$page.props.devMode && !isWeekday"
                                class="mt-1"
                                tooltip="Uploads are only available during weekdays in production"
                            />
                        </div>
                    </Link>
                    <div v-else class="text-sm text-gray-400">Card uploads are only allowed Monday through Friday</div>
                </div>

                <!-- Collection Stats -->
                <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 md:grid-cols-3 md:gap-6">
                    <div class="flex flex-col rounded-lg bg-gray-800 p-4 sm:p-6">
                        <h3 class="mb-1 text-sm text-gray-400">Total Cards</h3>
                        <div class="text-xl font-bold text-white sm:text-2xl">{{ totalCards }}</div>
                        <div class="mt-auto pt-2 text-sm text-gray-500">Cards in collection</div>
                    </div>

                    <div class="flex flex-col rounded-lg bg-gray-800 p-4 sm:p-6">
                        <h3 class="mb-1 text-sm text-gray-400">Created Cards</h3>
                        <div class="text-xl font-bold text-white sm:text-2xl">{{ createdCards.length }}</div>
                        <div class="mt-auto pt-2 text-sm text-gray-500">Cards you've created</div>
                    </div>

                    <div class="flex flex-col rounded-lg bg-gray-800 p-4 sm:p-6">
                        <h3 class="mb-1 text-sm text-gray-400">Average Rarity</h3>
                        <div class="flex items-center text-xl font-bold text-white sm:text-2xl">
                            <Star class="mr-1 h-4 w-4 text-yellow-400 sm:h-5 sm:w-5" />
                            {{ avgRarity }}/10
                        </div>
                        <div class="mt-auto pt-2 text-sm text-gray-500">Higher is rarer</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-8">
                    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <div class="flex flex-wrap gap-2 sm:gap-4">
                            <!-- Status Filter -->
                            <select
                                v-model="filters.status"
                                class="rounded-md border border-gray-600 bg-gray-800 px-3 py-1.5 text-sm text-white focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            >
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="voting">In Voting</option>
                                <option value="minted">Minted</option>
                            </select>

                            <!-- Sort Filter -->
                            <select
                                v-model="filters.sort"
                                class="rounded-md border border-gray-600 bg-gray-800 px-3 py-1.5 text-sm text-white focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            >
                                <option value="created_desc">Newest First</option>
                                <option value="created_asc">Oldest First</option>
                                <option value="name">Sort by Name</option>
                                <option value="rarity_desc">Highest Rarity</option>
                                <option value="rarity_asc">Lowest Rarity</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="relative max-w-xs flex-1">
                            <input
                                type="text"
                                v-model="filters.search"
                                placeholder="Search cards..."
                                class="w-full rounded-md border border-gray-600 bg-gray-800 py-1.5 pr-3 pl-9 text-sm text-white focus:border-transparent focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            />
                            <Search class="absolute top-1.5 left-2.5 h-4 w-4 text-gray-400" />
                        </div>
                    </div>
                </div>

                <!-- Owned Cards Section -->
                <div>
                    <h2 class="mb-4 text-xl font-bold text-white">Cards I Own</h2>
                    <div v-if="ownedCards.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        <div
                            v-for="card in ownedCards"
                            :key="card.id"
                            class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800 transition-colors hover:border-purple-500"
                        >
                            <Link :href="route('cards.show', card.id)">
                                <!-- Card Image -->
                                <div class="aspect-w-3 aspect-h-4 bg-gray-900">
                                    <img :src="`/storage/${card.image_path}`" :alt="card.name" class="h-full w-full object-cover" />
                                </div>

                                <!-- Card Info -->
                                <div class="p-4">
                                    <div class="mb-2 flex items-center justify-between">
                                        <h3 class="truncate text-lg font-bold text-white">{{ card.name }}</h3>
                                        <StatusBadge :status="card.status" />
                                    </div>

                                    <div v-if="card.status === 'minted'" class="mt-2 space-y-1">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-400">Rarity:</span>
                                            <span class="text-white">{{ card.rarity }}/10</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-400">Copies:</span>
                                            <span class="text-white">#{{ card.serial_numbers?.join(', #') }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between text-sm">
                                        <span class="text-gray-400">Created:</span>
                                        <span class="text-gray-300">{{ formatDate(card.created_at) }}</span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                    <div v-else class="rounded-lg bg-gray-800 p-4 text-center text-gray-400 sm:p-6">You don't own any cards yet.</div>
                </div>

                <!-- Created Cards Section -->
                <div class="mt-8 mb-8">
                    <h2 class="mb-4 text-xl font-bold text-white">Cards I Created</h2>
                    <div v-if="createdCards.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        <div
                            v-for="card in createdCards"
                            :key="card.id"
                            class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800 transition-colors hover:border-purple-500"
                        >
                            <Link :href="route('cards.show', card.id)">
                                <!-- Card Image -->
                                <div class="aspect-w-3 aspect-h-4 bg-gray-900">
                                    <img :src="`/storage/${card.image_path}`" :alt="card.name" class="h-full w-full object-cover" />
                                </div>

                                <!-- Card Info -->
                                <div class="p-4">
                                    <div class="mb-2 flex items-center justify-between">
                                        <h3 class="truncate text-lg font-bold text-white">{{ card.name }}</h3>
                                        <StatusBadge :status="card.status" />
                                    </div>

                                    <div v-if="card.status === 'minted'" class="mt-2 space-y-1">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-400">Rarity:</span>
                                            <span class="text-white">{{ card.rarity }}/10</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between text-sm">
                                        <span class="text-gray-400">Created:</span>
                                        <span class="text-gray-300">{{ formatDate(card.created_at) }}</span>
                                    </div>
                                </div>
                            </Link>

                            <!-- Quick Actions -->
                            <div class="bg-gray-850 border-t border-gray-700 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <div v-if="card.status === 'draft'">
                                        <button
                                            @click="submitForVoting(card)"
                                            class="text-sm text-purple-400 transition-colors hover:text-purple-300"
                                        >
                                            Submit for Voting
                                        </button>
                                    </div>
                                    <div v-else-if="card.status === 'voting'" class="text-sm text-gray-400">In voting phase</div>
                                    <div v-else class="text-sm text-gray-400">{{ card.minted_count }} Minted</div>

                                    <button
                                        v-if="card.status === 'draft'"
                                        @click="deleteCard(card)"
                                        class="text-sm text-red-400 transition-colors hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="rounded-lg bg-gray-800 p-4 text-center text-gray-400 sm:p-6">You haven't created any cards yet.</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import DevModeBadge from '@/components/DevModeBadge.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Link, router } from '@inertiajs/vue3';
import { Search, Star } from 'lucide-vue-next';
import { computed, defineComponent, ref } from 'vue';

export default defineComponent({
    components: {
        AppLayout,
        DevModeBadge,
        Link,
        Search,
        StatusBadge,
        Star,
    },

    props: {
        cards: {
            type: Array,
            required: true,
        },
        createdCards: {
            type: Array,
            required: true,
        },
        uploadAllowed: {
            type: Boolean,
            required: true,
        },
        isWeekday: {
            type: Boolean,
            required: true,
        },
    },

    setup(props) {
        const filters = ref({
            status: '',
            sort: 'created_desc',
            search: '',
        });

        const filterAndSortCards = (cards) => {
            let result = [...cards];

            // Apply status filter
            if (filters.value.status) {
                result = result.filter((card) => card.status === filters.value.status);
            }

            // Apply search filter
            if (filters.value.search) {
                const searchTerm = filters.value.search.toLowerCase();
                result = result.filter(
                    (card) =>
                        card.name.toLowerCase().includes(searchTerm) || (card.description && card.description.toLowerCase().includes(searchTerm)),
                );
            }

            // Apply sorting
            result.sort((a, b) => {
                switch (filters.value.sort) {
                    case 'created_asc':
                        return new Date(a.created_at) - new Date(b.created_at);
                    case 'created_desc':
                        return new Date(b.created_at) - new Date(a.created_at);
                    case 'name':
                        return a.name.localeCompare(b.name);
                    case 'rarity_desc':
                        return (b.rarity || 0) - (a.rarity || 0);
                    case 'rarity_asc':
                        return (a.rarity || 0) - (b.rarity || 0);
                    default:
                        return 0;
                }
            });

            return result;
        };

        const createdCards = computed(() => {
            return filterAndSortCards(props.createdCards);
        });

        const ownedCards = computed(() => {
            return filterAndSortCards(props.cards);
        });

        const submitForVoting = (card) => {
            if (confirm("Are you sure you want to submit this card for voting? You won't be able to edit it afterwards.")) {
                router.post(route('cards.submit-for-voting', card.id));
            }
        };

        const deleteCard = (card) => {
            if (confirm('Are you sure you want to delete this card? This action cannot be undone.')) {
                router.delete(route('cards.destroy', card.id));
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            }).format(date);
        };

        return {
            filters,
            createdCards,
            ownedCards,
            totalCards: computed(() => props.cards.length),
            avgRarity: computed(() => {
                const cards = props.cards.filter((card) => card.rarity);
                if (!cards.length) return 0;
                const avg = cards.reduce((sum, card) => sum + card.rarity, 0) / cards.length;
                return avg.toFixed(1);
            }),
            submitForVoting,
            deleteCard,
            formatDate,
        };
    },
});
</script>
