<template>
    <div class="flex min-h-screen flex-col bg-gray-900 text-white">
        <!-- Top Navigation -->
        <header class="bg-gray-800 text-white shadow-lg shadow-purple-500/20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Left side with logo and name -->
                    <div class="flex items-center">
                        <Link :href="route('home')" class="flex flex-shrink-0 items-center">
                            <span class="bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-xl font-bold text-transparent">
                                Life, Is Roblox
                            </span>
                        </Link>
                        <!-- Main Navigation - Desktop -->
                        <div class="ml-10 hidden md:flex items-center space-x-4">
                            <Link
                                v-for="navItem in navigationItems"
                                :key="navItem.name"
                                :href="navItem.href"
                                :class="[
                                    route().current(navItem.current) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200',
                                ]"
                            >
                                {{ navItem.name }}
                            </Link>

                            <!-- Admin link shown only to admins -->
                            <Link
                                v-if="$page.props.auth?.user?.is_admin"
                                :href="route('admin.dashboard')"
                                :class="[
                                    route().current('admin.*') ? 'bg-purple-700 text-white' : 'bg-purple-600 text-white hover:bg-purple-700',
                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200',
                                ]"
                            >
                                Admin
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="flex md:hidden">
                        <button 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-purple-500 transition-colors"
                            aria-expanded="false"
                        >
                            <span class="sr-only">Open main menu</span>
                            <svg 
                                v-if="!mobileMenuOpen" 
                                class="block h-6 w-6" 
                                xmlns="http://www.w3.org/2000/svg" 
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor" 
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg 
                                v-else 
                                class="block h-6 w-6" 
                                xmlns="http://www.w3.org/2000/svg" 
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor" 
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Right side with profile dropdown -->
                    <div class="hidden md:flex items-center">
                        <div class="relative ml-3" v-if="$page.props.auth?.user">
                            <Dropdown>
                                <template #trigger>
                                    <button
                                        class="flex items-center text-sm font-medium text-gray-300 transition duration-150 ease-in-out hover:text-white"
                                    >
                                        <span>{{ $page.props.auth.user.name }}</span>
                                        <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-4 py-3">
                                        <span class="block text-sm text-gray-300">{{ $page.props.auth.user.name }}</span>
                                        <span class="block text-xs text-gray-400">{{ $page.props.auth.user.email }}</span>
                                    </div>

                                    <div class="border-t border-gray-700"></div>

                                    <Link :href="route('dashboard')" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">
                                        Dashboard
                                    </Link>

                                    <Link :href="route('invitations.index')" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">
                                        Invitations
                                    </Link>

                                    <form @submit.prevent="logout">
                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-gray-700">
                                            Sign out
                                        </button>
                                    </form>
                                </template>
                            </Dropdown>
                        </div>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-3 py-2 text-sm font-medium text-gray-300 transition-colors duration-200 hover:text-white"
                            >
                                Log in
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile menu -->
        <div 
            v-show="mobileMenuOpen" 
            class="md:hidden bg-gray-800 border-t border-gray-700 shadow-lg transition-all duration-200"
        >
            <div class="px-4 pt-3 pb-4 space-y-2 sm:px-5">
                <Link
                    v-for="navItem in navigationItems"
                    :key="navItem.name"
                    :href="navItem.href"
                    :class="[
                        route().current(navItem.current) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                        'block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200',
                    ]"
                >
                    {{ navItem.name }}
                </Link>
                
                <!-- Admin link in mobile menu -->
                <Link
                    v-if="$page.props.auth?.user?.is_admin"
                    :href="route('admin.dashboard')"
                    :class="[
                        route().current('admin.*') ? 'bg-purple-700 text-white' : 'bg-purple-600 text-white hover:bg-purple-700',
                        'block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200',
                    ]"
                >
                    Admin
                </Link>
                
                <!-- Mobile profile section -->
                <div v-if="$page.props.auth?.user" class="pt-5 pb-3 border-t border-gray-700 mt-2">
                    <div class="flex items-center px-2">
                        <div class="ml-3">
                            <div class="text-base font-medium text-white">{{ $page.props.auth.user.name }}</div>
                            <div class="text-sm font-medium text-gray-400">{{ $page.props.auth.user.email }}</div>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 px-2">
                        <Link :href="route('dashboard')" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                            Dashboard
                        </Link>
                        <Link :href="route('invitations.index')" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                            Invitations
                        </Link>
                        <button @click="logout" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-red-400 hover:bg-gray-700 hover:text-red-300">
                            Sign out
                        </button>
                    </div>
                </div>
                
                <!-- Mobile login link -->
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white"
                    >
                        Log in
                    </Link>
                </template>
            </div>
        </div>
        
        <!-- Flash message -->
        <div v-if="$page.props.flash.success || $page.props.flash.error" class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8 z-10">
            <div v-if="$page.props.flash.success" class="relative rounded border border-green-700 bg-green-900 px-4 py-3 text-green-100" role="alert">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash.error" class="relative rounded border border-red-700 bg-red-900 px-4 py-3 text-red-100" role="alert">
                {{ $page.props.flash.error }}
            </div>
        </div>

        <!-- Page Content -->
        <main class="flex-grow">
            <slot></slot>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-400">
                    <p>Life, Is Roblox - A private card trading platform for friends</p>
                    <p class="mt-2">© {{ new Date().getFullYear() }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
import Dropdown from '@/components/Dropdown.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, onBeforeUnmount } from 'vue';

export default {
    components: {
        Link,
        Dropdown,
    },

    setup() {
        const mobileMenuOpen = ref(false);

        // Close mobile menu when screen size changes to desktop
        const handleResize = () => {
            if (window.innerWidth >= 768) { // md breakpoint
                mobileMenuOpen.value = false;
            }
        };
        
        const navigationItems = computed(() => [
            { name: 'Dashboard', href: route('dashboard'), current: 'dashboard' },
            { name: 'My Cards', href: route('cards.index'), current: 'cards.index' },
            { name: 'Vote', href: route('cards.voting-feed'), current: 'cards.voting-feed' },
            { name: 'Trades', href: route('trades.index'), current: 'trades.index' },
            { name: 'Users', href: route('users.index'), current: 'users.*' },
        ]);

        const logout = () => {
            router.post(route('logout'));
        };
        
        // Close mobile menu when route changes or window resizes
        router.on('finish', () => {
            mobileMenuOpen.value = false;
        });
        
        window.addEventListener('resize', handleResize);
        onBeforeUnmount(() => {
            window.removeEventListener('resize', handleResize);
        });

        return {
            navigationItems,
            logout,
            mobileMenuOpen,
        };
    },
};
</script>
