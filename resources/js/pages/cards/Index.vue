<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-3xl font-extrabold text-transparent">
                        My Cards
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

                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-gray-800 p-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-300">Status</label>
                            <select v-model="filters.status" class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white">
                                <option value="">All</option>
                                <option value="draft">Draft</option>
                                <option value="voting">In Voting</option>
                                <option value="minted">Minted</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-300">Sort By</label>
                            <select v-model="filters.sort" class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white">
                                <option value="created_desc">Newest First</option>
                                <option value="created_asc">Oldest First</option>
                                <option value="name">Name</option>
                                <option value="rarity_desc">Highest Rarity</option>
                                <option value="rarity_asc">Lowest Rarity</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-300">Search</label>
                            <input
                                type="text"
                                v-model="filters.search"
                                placeholder="Search cards..."
                                class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div v-if="filteredCards.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    <div
                        v-for="card in filteredCards"
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

                        <!-- Quick Actions -->
                        <div class="bg-gray-850 border-t border-gray-700 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div v-if="card.status === 'draft'">
                                    <button @click="submitForVoting(card)" class="text-sm text-purple-400 transition-colors hover:text-purple-300">
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

                <!-- Empty State -->
                <div v-else class="py-12 text-center">
                    <div class="text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4 h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <h3 class="mb-2 text-xl font-medium">No cards found</h3>
                        <p class="text-gray-500">
                            {{ filters.search || filters.status ? 'Try adjusting your filters' : 'Start by uploading your first card' }}
                        </p>
                    </div>
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
import { computed, defineComponent, ref } from 'vue';

export default defineComponent({
    components: {
        AppLayout,
        DevModeBadge,
        Link,
        StatusBadge,
        DevModeBadge,
    },

    props: {
        cards: {
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

        const filteredCards = computed(() => {
            let result = [...props.cards];

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
            filteredCards,
            submitForVoting,
            deleteCard,
            formatDate,
        };
    },
});
</script>
