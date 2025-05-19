<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-3xl font-extrabold text-transparent">
                        Welcome, {{ $page.props.auth.user.name }}!
                    </h1>
                    <p class="mt-2 text-gray-300">Your private card collection and trading platform.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Card Stats -->
                    <div
                        class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800 p-6 shadow-lg transition-colors hover:border-purple-500"
                    >
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white">My Collection</h2>
                            <div class="rounded-full bg-purple-600 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-white">{{ stats.totalCards }}</div>
                            <div class="text-sm text-gray-400">Cards in your collection</div>
                        </div>
                        <Link :href="route('cards.index')" class="mt-4 flex items-center text-sm font-medium text-purple-400 hover:text-purple-300">
                            View all cards
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </Link>
                    </div>

                    <!-- Invitations -->
                    <div
                        class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800 p-6 shadow-lg transition-colors hover:border-purple-500"
                    >
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white">Invitations</h2>
                            <div class="rounded-full bg-pink-600 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-white">{{ $page.props.auth.user.invitation_count }}</div>
                            <div class="text-sm text-gray-400">Available invites (of {{ $page.props.auth.user.invitation_limit }})</div>
                        </div>
                        <Link :href="route('invitations.index')" class="mt-4 flex items-center text-sm font-medium text-pink-400 hover:text-pink-300">
                            Manage invitations
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </Link>
                    </div>

                    <!-- Trades -->
                    <div
                        class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800 p-6 shadow-lg transition-colors hover:border-purple-500"
                    >
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white">Active Trades</h2>
                            <div class="rounded-full bg-indigo-600 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-white">{{ stats.activeTrades }}</div>
                            <div class="text-sm text-gray-400">Pending trades</div>
                        </div>
                        <Link :href="route('trades.index')" class="mt-4 flex items-center text-sm font-medium text-indigo-400 hover:text-indigo-300">
                            View all trades
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="mt-12">
                    <h2 class="mb-6 text-2xl font-bold text-white">Recent Activity</h2>
                    <div class="rounded-lg border border-gray-700 bg-gray-800 shadow-lg">
                        <div v-if="recentActivity.length" class="divide-y divide-gray-700">
                            <div
                                v-for="activity in recentActivity"
                                :key="activity.id"
                                class="hover:bg-gray-750 flex items-center p-4 transition-colors"
                            >
                                <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-md bg-gray-700">
                                    <img
                                        v-if="activity.type === 'card'"
                                        :src="'/storage/' + activity.image_path"
                                        class="h-full w-full object-cover"
                                        :alt="activity.name"
                                    />
                                    <div v-else class="flex h-full w-full items-center justify-center bg-gray-600">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-grow">
                                    <div class="font-medium text-white">{{ activity.title }}</div>
                                    <div class="text-sm text-gray-400">{{ activity.description }}</div>
                                </div>
                                <div class="text-xs text-gray-500">{{ formatDate(activity.date) }}</div>
                            </div>
                        </div>
                        <div v-else class="p-8 text-center text-gray-500">No recent activity to show.</div>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="mt-12 mb-8">
                    <h2 class="mb-6 text-2xl font-bold text-white">Quick Actions</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                        <!-- Upload Card Action -->
                        <Link
                            v-if="uploadAllowed"
                            :href="route('cards.create')"
                            class="flex transform items-center justify-center rounded-lg bg-gradient-to-br from-purple-600 to-pink-600 p-4 shadow-lg transition-all hover:scale-105 hover:from-purple-500 hover:to-pink-500"
                        >
                            <div class="text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white/20 p-3">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="font-medium text-white">Upload New Card</div>
                                    <DevModeBadge
                                        v-if="$page.props.devMode && !isWeekday"
                                        class="mt-2"
                                        tooltip="Uploads are only available during weekdays in production"
                                    />
                                </div>
                            </div>
                        </Link>

                        <!-- Vote on Cards Action -->
                        <Link
                            :href="route('cards.voting-feed')"
                            class="flex transform items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 p-4 shadow-lg transition-all hover:scale-105 hover:from-blue-500 hover:to-indigo-500"
                        >
                            <div class="text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white/20 p-3">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="font-medium text-white">Vote on Cards</div>
                            </div>
                        </Link>

                        <!-- Start Trading Action -->
                        <Link
                            :href="route('trades.create')"
                            class="flex transform items-center justify-center rounded-lg bg-gradient-to-br from-green-600 to-teal-600 p-4 shadow-lg transition-all hover:scale-105 hover:from-green-500 hover:to-teal-500"
                        >
                            <div class="text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white/20 p-3">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                                        />
                                    </svg>
                                </div>
                                <div class="font-medium text-white">Start a Trade</div>
                            </div>
                        </Link>

                        <!-- Invite Friend Action -->
                        <Link
                            :href="route('invitations.index')"
                            class="flex transform items-center justify-center rounded-lg bg-gradient-to-br from-amber-600 to-orange-600 p-4 shadow-lg transition-all hover:scale-105 hover:from-amber-500 hover:to-orange-500"
                        >
                            <div class="text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white/20 p-3">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="font-medium text-white">Invite a Friend</div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { defineComponent } from 'vue';

export default defineComponent({
    components: {
        AppLayout,
        Link,
    },

    props: {
        stats: {
            type: Object,
            default: () => ({
                totalCards: 0,
                activeTrades: 0,
            }),
        },
        isWeekday: {
            type: Boolean,
            required: true,
        },
        recentActivity: {
            type: Array,
            default: () => [],
        },
        uploadAllowed: {
            type: Boolean,
            required: true,
        },
    },

    setup() {
        const formatDate = (date) => {
            if (!date) return '';
            const d = new Date(date);
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(d);
        };

        return {
            formatDate,
        };
    },
});
</script>
