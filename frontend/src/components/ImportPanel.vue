<script setup lang="ts">
import { ref } from 'vue'
import api from '@/lib/api'

const props = defineProps<{
  documentId?: number
}>()

const emit = defineEmits<{
  imported: [documentId?: number]
}>()

const file = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const mode = ref<'append' | 'replace'>('append')
const title = ref('')
const message = ref<string | null>(null)
const error = ref<string | null>(null)
const loading = ref(false)

const allowedExtensions = ['txt', 'md', 'markdown']

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  const chosen = target.files?.[0] ?? null
  error.value = null

  if (!chosen) {
    file.value = null
    return
  }

  const ext = chosen.name.split('.').pop()?.toLowerCase() ?? ''
  if (!allowedExtensions.includes(ext)) {
    file.value = null
    error.value = 'Unsupported file type. Import only supports .txt and .md files.'
    target.value = ''
    return
  }

  file.value = chosen
}

function openFilePicker() {
  fileInput.value?.click()
}

async function submit() {
  if (!file.value) {
    error.value = 'Click the upload icon to choose a .txt or .md file, then click Import.'
    return
  }

  const form = new FormData()
  form.append('file', file.value)
  if (!props.documentId && title.value) {
    form.append('title', title.value)
  }
  if (props.documentId) {
    form.append('mode', mode.value)
  }

  loading.value = true
  error.value = null
  message.value = null

  try {
    const url = props.documentId
      ? `/documents/${props.documentId}/import`
      : '/documents/import'
    const { data } = await api.post(url, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    message.value = data.message
    file.value = null
    if (fileInput.value) fileInput.value.value = ''
    emit('imported', data.document?.id)
  } catch (err: unknown) {
    error.value = 'Import failed. Supported types: .txt, .md (max 5MB).'
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="card space-y-3">
    <h3 class="text-sm font-semibold text-slate-800">
      {{ documentId ? 'Import into this document' : 'Import file as new document' }}
    </h3>
    <p class="text-xs text-slate-500">
      Supported import types: <strong>.txt</strong>, <strong>.md</strong>. Attachments also support .pdf and images from the editor page.
    </p>

    <input
      v-if="!documentId"
      v-model="title"
      type="text"
      class="input"
      placeholder="Optional title (defaults to filename)"
    />

    <select v-if="documentId" v-model="mode" class="input">
      <option value="append">Append to existing content</option>
      <option value="replace">Replace existing content</option>
    </select>

    <div class="flex flex-wrap items-center gap-3">
      <input
        ref="fileInput"
        type="file"
        accept=".txt,.md,.markdown,text/plain,text/markdown"
        class="sr-only"
        @change="onFileChange"
      />
      <button
        type="button"
        class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 shadow-sm transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        title="Choose .txt or .md file"
        aria-label="Choose .txt or .md file"
        @click="openFilePicker"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="17 8 12 3 7 8" />
          <line x1="12" y1="3" x2="12" y2="15" />
        </svg>
      </button>
      <p v-if="file" class="text-sm text-slate-600">
        Selected: <strong>{{ file.name }}</strong>
      </p>
      <p v-else class="text-sm text-slate-500">Click the icon to choose a file</p>
    </div>

    <button type="button" class="btn-primary" :disabled="loading || !file" @click="submit">
      {{
        loading
          ? 'Importing…'
          : documentId
            ? 'Import into document'
            : 'Import as new document'
      }}
    </button>

    <p v-if="error" class="text-sm text-rose-600">{{ error }}</p>
    <p v-if="message" class="text-sm text-emerald-600">{{ message }}</p>
  </div>
</template>
