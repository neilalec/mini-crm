<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
});
</script>

<template>
  <Head title="Mini CRM" />

  <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex max-w-6xl flex-col px-6 py-10">
      <header class="mb-16 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Mini CRM</h1>
        <div class="space-x-3" v-if="canLogin">
          <Link :href="route('login')" class="rounded border border-slate-700 px-4 py-2 text-sm hover:bg-slate-800">Log in</Link>
          <Link v-if="canRegister" :href="route('register')" class="rounded bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-cyan-400">Start free</Link>
        </div>
      </header>

      <main class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <section>
          <p class="mb-3 text-sm uppercase tracking-[0.2em] text-cyan-300">Lead Capture for Service Businesses</p>
          <h2 class="mb-4 text-4xl font-semibold leading-tight">Capture enquiries. Track quotes. Win more jobs.</h2>
          <p class="mb-8 max-w-xl text-slate-300">A lightweight CRM where you can collect public enquiries, manage lead status, store quote notes, and set follow-up reminders.</p>
          <div class="flex gap-3">
            <Link :href="route('register')" class="rounded bg-cyan-500 px-5 py-3 font-medium text-slate-950 hover:bg-cyan-400">Create account</Link>
            <Link :href="route('login')" class="rounded border border-slate-700 px-5 py-3 hover:bg-slate-800">Sign in</Link>
          </div>
        </section>

        <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
          <h3 class="mb-4 text-lg font-semibold">MVP includes</h3>
          <ul class="space-y-2 text-slate-300">
            <li>Public enquiry form per business</li>
            <li>Lead inbox + statuses</li>
            <li>Quote amount and notes</li>
            <li>Follow-up reminders</li>
            <li>Internal lead notes</li>
            <li>Reply templates</li>
          </ul>
        </section>
      </main>
    </div>
  </div>
</template>
