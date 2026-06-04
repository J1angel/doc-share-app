<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ImportPanel from '@/components/ImportPanel.vue'
import RichTextEditor from '@/components/RichTextEditor.vue'
import ShareModal from '@/components/ShareModal.vue'
import api from '@/lib/api'
import { useDocumentsStore } from '@/stores/documents'

const route = useRoute()
const router = useRouter()
const docs = useDocumentsStore()

const title = ref('')
const content = ref('<p></p>')
const saving = ref(false)
const saveMessage = ref<string | null>(null)
const showShare = ref(false)
const attachmentFile = ref<File | null>(null)
const attachmentFileInput = ref<HTMLInputElement | null>(null)
const attachmentError = ref<string | null>(null)
const uploadingAttachment = ref(false)
const uploadMessage = ref<string | null>(null)

const allowedAttachmentExtensions = ['txt', 'md', 'markdown', 'pdf', 'png', 'jpg', 'jpeg']

const documentId = computed(() => Number(route.params.id))

const doc = computed(() => docs.current)
const canEdit = computed(() => doc.value?.can_edit ?? false)
const isOwner = computed(() => doc.value?.is_owner ?? false)

onMounted(() => load())
watch(documentId, () => load())

async function load() {
  const loaded = await docs.fetchOne(documentId.value)
  title.value = loaded.title
  content.value = loaded.content || '<p></p>'
}

async function save() {
  if (!canEdit.value) return
  saving.value = true
  saveMessage.value = null
  try {
    await docs.save(documentId.value, { title: title.value, content: content.value })
    saveMessage.value = 'Saved'
    setTimeout(() => (saveMessage.value = null), 2000)
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!isOwner.value || !confirm('Delete this document permanently?')) return
  await docs.remove(documentId.value)
  router.push('/')
}

function onShareUpdated() {
  docs.fetchOne(documentId.value)
}

function onImported() {
  load()
}

function onAttachmentChange(event: Event) {
  const target = event.target as HTMLInputElement
  const chosen = target.files?.[0] ?? null
  attachmentError.value = null
  uploadMessage.value = null

  if (!chosen) {
    attachmentFile.value = null
    return
  }

  const ext = chosen.name.split('.').pop()?.toLowerCase() ?? ''
  if (!allowedAttachmentExtensions.includes(ext)) {
    attachmentFile.value = null
    attachmentError.value = 'Unsupported file type. Allowed: .txt, .md, .pdf, .png, .jpg'
    target.value = ''
    return
  }

  attachmentFile.value = chosen
}

function openAttachmentPicker() {
  attachmentFileInput.value?.click()
}

async function uploadAttachment() {
  if (!attachmentFile.value || !canEdit.value) {
    attachmentError.value = 'Click the upload icon to choose a file, then click Upload attachment.'
    return
  }

  uploadingAttachment.value = true
  attachmentError.value = null
  uploadMessage.value = null

  try {
    const form = new FormData()
    form.append('file', attachmentFile.value)
    const { data } = await api.post(`/documents/${documentId.value}/attachments`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    uploadMessage.value = data.message
    attachmentFile.value = null
    if (attachmentFileInput.value) attachmentFileInput.value.value = ''
    await load()
  } catch {
    attachmentError.value = 'Upload failed. Allowed: .txt, .md, .pdf, .png, .jpg (max 10MB).'
  } finally {
    uploadingAttachment.value = false
  }
}

function downloadAttachment(attachmentId: number) {
  window.open(`/api/documents/${documentId.value}/attachments/${attachmentId}/download`, '_blank')
}
</script>

<template>
  <div class="mx-auto max-w-5xl px-4 py-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <button type="button" class="btn-secondary" @click="router.push('/')">← Back</button>
      <div class="flex flex-wrap gap-2">
        <span v-if="doc?.relation === 'owned'" class="badge-owned">Owned</span>
        <span v-else-if="doc" class="badge-shared">Shared · {{ doc.owner?.name }}</span>
        <button v-if="isOwner" type="button" class="btn-secondary" @click="showShare = true">Share</button>
        <button v-if="canEdit" type="button" class="btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Saving…' : 'Save' }}
        </button>
        <button v-if="isOwner" type="button" class="btn-danger" @click="remove">Delete</button>
      </div>
    </div>

    <p v-if="saveMessage" class="mb-3 text-sm text-emerald-600">{{ saveMessage }}</p>
    <p v-if="!canEdit" class="mb-3 text-sm text-amber-700">You have view-only access to this document.</p>

    <input
      v-model="title"
      type="text"
      class="input mb-4 text-xl font-semibold"
      :readonly="!canEdit"
      @blur="canEdit && save()"
    />

    <RichTextEditor v-model="content" :editable="canEdit" />

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
      <ImportPanel v-if="canEdit" :document-id="documentId" @imported="onImported" />

      <div class="card space-y-3">
        <h3 class="text-sm font-semibold">Attachments</h3>
        <p class="text-xs text-slate-500">Allowed: .txt, .md, .pdf, .png, .jpg (max 10MB)</p>
        <ul v-if="doc?.attachments?.length" class="space-y-2 text-sm">
          <li
            v-for="file in doc.attachments"
            :key="file.id"
            class="flex items-center justify-between rounded border border-slate-100 px-3 py-2"
          >
            <span>{{ file.original_name }}</span>
            <button type="button" class="text-indigo-600 hover:underline" @click="downloadAttachment(file.id)">
              Download
            </button>
          </li>
        </ul>
        <p v-else class="text-sm text-slate-500">No attachments yet.</p>
        <template v-if="canEdit">
          <div class="flex flex-wrap items-center gap-3">
            <input
              ref="attachmentFileInput"
              type="file"
              accept=".txt,.md,.markdown,.pdf,.png,.jpg,.jpeg"
              class="sr-only"
              @change="onAttachmentChange"
            />
            <button
              type="button"
              class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 shadow-sm transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              title="Choose attachment file"
              aria-label="Choose attachment file"
              @click="openAttachmentPicker"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" y1="3" x2="12" y2="15" />
              </svg>
            </button>
            <p v-if="attachmentFile" class="text-sm text-slate-600">
              Selected: <strong>{{ attachmentFile.name }}</strong>
            </p>
            <p v-else class="text-sm text-slate-500">Click the icon to choose a file</p>
          </div>
          <button
            type="button"
            class="btn-secondary"
            :disabled="uploadingAttachment || !attachmentFile"
            @click="uploadAttachment"
          >
            {{ uploadingAttachment ? 'Uploading…' : 'Upload attachment' }}
          </button>
          <p v-if="attachmentError" class="text-sm text-rose-600">{{ attachmentError }}</p>
          <p v-if="uploadMessage" class="text-sm text-emerald-600">{{ uploadMessage }}</p>
        </template>
      </div>
    </div>

    <ShareModal
      v-if="showShare && doc"
      :document-id="documentId"
      :shared-with="doc.shared_with"
      @close="showShare = false"
      @updated="onShareUpdated(); showShare = false"
    />
  </div>
</template>
