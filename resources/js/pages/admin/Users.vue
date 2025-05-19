<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-3xl font-extrabold text-transparent">
                        Manage Users
                    </h1>
                    <Link :href="route('admin.dashboard')" class="text-gray-400 transition-colors hover:text-white"> Back to Dashboard </Link>
                </div>

                <!-- Users Table -->
                <div class="overflow-hidden rounded-lg bg-gray-800 shadow-lg">
                    <div class="border-b border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search users..."
                                    class="rounded-lg bg-gray-700 py-2 pr-4 pl-10 text-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                />
                                <div class="absolute top-2.5 left-3 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    v-for="filter in filters"
                                    :key="filter.value"
                                    @click="currentFilter = filter.value"
                                    class="rounded-full px-3 py-1 text-sm"
                                    :class="
                                        currentFilter === filter.value ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-400 hover:text-white'
                                    "
                                >
                                    {{ filter.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">Cards</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">
                                        Invitations
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-300 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700 bg-gray-800">
                                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-750">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ user.name }}</div>
                                                <div class="text-sm text-gray-400">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                            :class="user.is_admin ? 'bg-purple-900 text-purple-200' : 'bg-green-900 text-green-200'"
                                        >
                                            {{ user.is_admin ? 'Admin' : 'User' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-white">{{ user.cards_count }} uploaded</div>
                                        <div class="text-sm text-gray-400">{{ user.owned_cards_count }} owned</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-white">{{ user.invitation_count }} available</div>
                                        <div class="text-sm text-gray-400">Limit: {{ user.invitation_limit }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-3">
                                            <button @click="openInvitationModal(user)" class="text-purple-400 hover:text-purple-300">
                                                Add Invites
                                            </button>
                                            <Link :href="route('admin.users.edit', user.id)" class="text-blue-400 hover:text-blue-300"> Edit </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Invitations Modal -->
        <Modal :show="showInvitationModal" @close="showInvitationModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-xl font-bold text-white">Add Invitations</h2>
                <p class="mb-4 text-gray-300">Add invitations for {{ selectedUser?.name }}</p>
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-300"> Number of Invitations to Add </label>
                    <input
                        type="number"
                        v-model="invitationsToAdd"
                        min="1"
                        class="w-full rounded-md border border-gray-600 bg-gray-700 px-3 py-2 text-white"
                    />
                </div>
                <div class="flex justify-end space-x-4">
                    <button
                        @click="showInvitationModal = false"
                        class="rounded-md bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="addInvitations"
                        :disabled="!invitationsToAdd || invitationsToAdd < 1"
                        class="rounded-md bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-colors hover:from-purple-600 hover:to-pink-600 disabled:opacity-50"
                    >
                        Add Invitations
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/components/Modal.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, defineComponent, ref } from 'vue';

export default defineComponent({
    components: {
        AppLayout,
        Link,
        Modal,
    },

    props: {
        users: {
            type: Array,
            required: true,
        },
    },

    setup(props) {
        const search = ref('');
        const currentFilter = ref('all');
        const showInvitationModal = ref(false);
        const selectedUser = ref(null);
        const invitationsToAdd = ref(1);

        const filters = [
            { label: 'All', value: 'all' },
            { label: 'Admins', value: 'admin' },
            { label: 'Users', value: 'user' },
        ];

        const filteredUsers = computed(() => {
            let result = [...props.users];

            // Apply search filter
            if (search.value) {
                const searchTerm = search.value.toLowerCase();
                result = result.filter((user) => user.name.toLowerCase().includes(searchTerm) || user.email.toLowerCase().includes(searchTerm));
            }

            // Apply type filter
            if (currentFilter.value === 'admin') {
                result = result.filter((user) => user.is_admin);
            } else if (currentFilter.value === 'user') {
                result = result.filter((user) => !user.is_admin);
            }

            return result;
        });

        const openInvitationModal = (user) => {
            selectedUser.value = user;
            invitationsToAdd.value = 1;
            showInvitationModal.value = true;
        };

        const addInvitations = () => {
            if (!selectedUser.value || invitationsToAdd.value < 1) return;

            router.post(
                route('admin.users.increase-invitation-limit', selectedUser.value.id),
                {
                    amount: invitationsToAdd.value,
                },
                {
                    onSuccess: () => {
                        showInvitationModal.value = false;
                        selectedUser.value = null;
                        invitationsToAdd.value = 1;
                    },
                },
            );
        };

        return {
            search,
            currentFilter,
            filters,
            filteredUsers,
            showInvitationModal,
            selectedUser,
            invitationsToAdd,
            openInvitationModal,
            addInvitations,
        };
    },
});
</script>
