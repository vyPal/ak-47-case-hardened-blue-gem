<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 mb-6">
          Invitations
        </h1>
        
        <div class="bg-gray-800 overflow-hidden shadow-xl rounded-lg">
          <div class="p-6 sm:px-20 bg-gray-900 border-b border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-xl text-white font-semibold mb-1">Invite your friends</h2>
                <p class="text-sm text-gray-400">
                  You have {{ $page.props.auth.user.invitation_count }} invites available out of 
                  {{ $page.props.auth.user.invitation_limit }} total allotted invites.
                </p>
              </div>
              <button 
                @click="generateInvite" 
                class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-md hover:from-purple-600 hover:to-pink-600 transition-all disabled:opacity-50"
                :disabled="$page.props.auth.user.invitation_count <= 0"
              >
                Generate New Invite
              </button>
            </div>
          </div>
          
          <div class="bg-gray-800 text-white">
            <div v-if="invitations.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Invitation Link
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Generated
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Expires
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                  <tr v-for="invitation in invitations" :key="invitation.id" class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="text-sm font-medium max-w-md truncate">
                          <span class="bg-gray-700 py-1 px-2 rounded-md">
                            {{ getInviteUrl(invitation.token) }}
                          </span>
                        </div>
                        <button 
                          @click="copyToClipboard(getInviteUrl(invitation.token))"
                          class="ml-2 text-gray-400 hover:text-gray-200 focus:outline-none"
                          title="Copy to clipboard"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                          </svg>
                        </button>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-300">{{ formatDate(invitation.created_at) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-300">{{ formatDate(invitation.expires_at) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span v-if="!invitation.used" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900 text-green-200">
                        Active
                      </span>
                      <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-700 text-gray-400">
                        Used
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button 
                        v-if="!invitation.used"
                        @click="revokeInvitation(invitation.id)" 
                        class="text-red-400 hover:text-red-300 focus:outline-none"
                        title="Revoke invitation"
                      >
                        Revoke
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="p-6 text-center text-gray-400">
              <p>You haven't created any invitations yet.</p>
            </div>
          </div>
        </div>
        
        <div class="mt-8 bg-gray-800 overflow-hidden shadow-xl rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-4">How Invitations Work</h3>
          <ul class="text-gray-300 list-disc pl-5 space-y-2">
            <li>Each user gets a limited number of invitations to send to friends.</li>
            <li>When you generate an invitation link, your available invitation count decreases by one.</li>
            <li>Invitation links are valid for 7 days after creation.</li>
            <li>If you revoke an unused invitation, your available invitation count will be refunded.</li>
            <li>Only people with a valid invitation link can register for the app.</li>
            <li>If you need more invitations, contact an admin.</li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { defineComponent } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

export default defineComponent({
  components: {
    AppLayout,
  },
  
  props: {
    invitations: {
      type: Array,
      default: () => [],
    },
    availableInvitations: {
      type: Number,
      default: 0,
    },
    invitationLimit: {
      type: Number,
      default: 0,
    },
  },
  
  methods: {
    generateInvite() {
      router.post(route('invitations.store'));
    },
    
    revokeInvitation(id) {
      if (confirm('Are you sure you want to revoke this invitation?')) {
        router.delete(route('invitations.destroy', id));
      }
    },
    
    getInviteUrl(token) {
      return `${window.location.origin}/register/${token}`;
    },
    
    copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        alert('Invitation link copied to clipboard!');
      }, (err) => {
        console.error('Could not copy text: ', err);
      });
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
  },
});
</script>