<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '@/lib/api'

const props = defineProps<{
  documentId: number
  sharedWith: Array<{ id: number; name: string; email: string; permission: string }>
}>()

const emit = defineEmits<{
  close: []
  updated: []
}>()

const users = ref<Array<{ id: number; name: string; email: string }>>([])
const selectedUserId = ref<number | ''>('')
const permission = ref('edit')
const message = ref<string | null>(null)
const error = ref<string | null>(null)
const loading = ref(false)

onMounted(async () => {
  const { data } = await api.get('/users')
  users.value = data.users
})

async function share() {
  if (!selectedUserId.value) {
    error.value = 'Select a user to share with.'
    return
  }
  loading.value = true
  error.value = null
  message.value = null
  try {
    await api.post(`/documents/${props.documentId}/share`, {
      user_id: selectedUserId.value,
      permission: permission.value,
    })
    message.value = 'Document shared successfully.'
    emit('updated')
  } catch (err: unknown) {
    error.value = 'Could not share document.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

async function revoke(userId: number) {
  loading.value = true
  try {
    await api.delete(`/documents/${props.documentId}/share/${userId}`)
    emit('updated')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
    <div class="card w-full max-w-lg space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Share document</h2>
        <button type="button" class="btn-secondary px-2 py-1" @click="emit('close')">Close</button>
      </div>

      <p class="text-sm text-slate-600">Grant another seeded user edit access to this document.</p>

      <div class="flex flex-col gap-2 sm:flex-row">
        <select v-model="selectedUserId" class="input">
          <option value="">Select user</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }} ({{ user.email }})
          </option>
        </select>
        <select v-model="permission" class="input sm:w-36">
          <option value="edit">Can edit</option>
          <option value="view">View only</option>
        </select>
        <button type="button" class="btn-primary" :disabled="loading" @click="share">Share</button>
      </div>

      <p v-if="error" class="text-sm text-rose-600">{{ error }}</p>
      <p v-if="message" class="text-sm text-emerald-600">{{ message }}</p>

      <div>
        <h3 class="mb-2 text-sm font-medium text-slate-700">Currently shared with</h3>
        <ul v-if="sharedWith.length" class="space-y-2">
          <li
            v-for="person in sharedWith"
            :key="person.id"
            class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 text-sm"
          >
            <span>{{ person.name }} · {{ person.permission }}</span>
            <button type="button" class="text-rose-600 hover:underline" @click="revoke(person.id)">
              Remove
            </button>
          </li>
        </ul>
        <p v-else class="text-sm text-slate-500">Not shared yet.</p>
      </div>
    </div>
  </div>
</template>
