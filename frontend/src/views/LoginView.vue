<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const email = ref('alice@example.com')
const password = ref('password')

async function submit() {
  await auth.login(email.value, password.value)
  router.push('/')
}
</script>

<template>
  <div class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-4 py-12">
    <div class="card space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">CollabDocs</h1>
        <p class="mt-1 text-sm text-slate-600">
          Lightweight document editing with sharing and file import.
        </p>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="mb-1 block text-sm font-medium">Email</label>
          <input v-model="email" type="email" class="input" required />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Password</label>
          <input v-model="password" type="password" class="input" required />
        </div>
        <p v-if="auth.error" class="text-sm text-rose-600">{{ auth.error }}</p>
        <button type="submit" class="btn-primary w-full" :disabled="auth.loading">
          {{ auth.loading ? 'Signing in…' : 'Sign in' }}
        </button>
      </form>

      <div class="rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
        <p class="font-medium text-slate-700">Demo accounts (password: <code>password</code>)</p>
        <ul class="mt-2 space-y-1">
          <li><strong>alice@example.com</strong> — document owner</li>
          <li><strong>bob@example.com</strong> — collaborator (Welcome doc shared)</li>
          <li><strong>carol@example.com</strong> — extra user for sharing demos</li>
        </ul>
      </div>
    </div>
  </div>
</template>
