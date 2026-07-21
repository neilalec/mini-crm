<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const isDark = ref(document.documentElement.classList.contains('dark'));

function toggleTheme() {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-100 dark:bg-slate-950">
      <nav class="border-b border-gray-100 bg-white dark:border-slate-700 dark:bg-slate-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <div class="flex">
              <div class="flex shrink-0 items-center">
                <Link :href="route('dashboard')">
                  <ApplicationLogo class="block h-9 w-auto" />
                </Link>
              </div>
              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</NavLink>
                <NavLink :href="route('leads.index')" :active="route().current('leads.*')">Leads</NavLink>
                <NavLink :href="route('templates.index')" :active="route().current('templates.*')">Templates</NavLink>
              </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
              <div class="relative ms-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 hover:text-indigo-600 dark:bg-slate-900 dark:text-gray-300 dark:hover:text-indigo-300">
                        {{ $page.props.auth.user.name }}
                      </button>
                    </span>
                  </template>
                  <template #content>
                    <div class="flex items-center justify-between px-4 py-2 text-sm text-gray-700">
                      <span>Dark mode</span>
                      <button @click.stop="toggleTheme" class="relative h-6 w-11 rounded-full" :class="isDark ? 'bg-indigo-600' : 'bg-gray-300'">
                        <span class="absolute top-0.5 h-5 w-5 rounded-full bg-white transition" :class="isDark ? 'right-0.5' : 'left-0.5'"></span>
                      </button>
                    </div>
                    <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
              <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
          <div class="space-y-1 pb-3 pt-2">
            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
            <ResponsiveNavLink :href="route('leads.index')" :active="route().current('leads.*')">Leads</ResponsiveNavLink>
            <ResponsiveNavLink :href="route('templates.index')" :active="route().current('templates.*')">Templates</ResponsiveNavLink>
          </div>

          <div class="border-t border-gray-200 pb-1 pt-4 dark:border-slate-700">
            <div class="px-4">
              <div class="text-base font-medium text-gray-800 dark:text-gray-100">{{ $page.props.auth.user.name }}</div>
              <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $page.props.auth.user.email }}</div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <header class="bg-white shadow dark:bg-slate-900" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <main>
        <slot />
      </main>
    </div>
  </div>
</template>
