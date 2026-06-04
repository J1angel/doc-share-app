<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import ImportPanel from '@/components/ImportPanel.vue'
import { useAuthStore } from '@/stores/auth'
import { useDocumentsStore } from '@/stores/documents'

const auth = useAuthStore()
const docs = useDocumentsStore()
const router = useRouter()
const creating = ref(false)

onMounted(() => {
  docs.fetchLists()
})

async function createDocument() {
  creating.value = true
  try {
    const doc = await docs.create('Untitled document')
    router.push(`/documents/${doc.id}`)
  } finally {
    creating.value = false
  }
}

function openDocument(id: number) {
  router.push(`/documents/${id}`)
}

function onImported(documentId?: number) {
  docs.fetchLists()
  if (documentId) {
    router.push(`/documents/${documentId}`)
  }
}
</script>

<template>
  <div class="mx-auto max-w-6xl px-4 py-8">
    <header class="mb-8 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold">My documents</h1>
        <p class="text-sm text-slate-600">Signed in as {{ auth.user?.name }} ({{ auth.user?.email }})</p>
      </div>
      <div class="flex gap-2">
        <button type="button" class="btn-primary" :disabled="creating" @click="createDocument">
          {{ creating ? 'Creating…' : 'New document' }}
        </button>
        <button type="button" class="btn-secondary" @click="auth.logout().then(() => router.push('/login'))">
          Sign out
        </button>
      </div>
    </header>

    <div class="mb-8 grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-8">
        <section>
          <div class="mb-3 flex items-center gap-2">
            <h2 class="text-lg font-semibold">Owned by me</h2>
            <span class="badge-owned">Owner</span>
          </div>
          <div v-if="docs.loading" class="text-sm text-slate-500">Loading…</div>
          <div v-else-if="!docs.owned.length" class="card text-sm text-slate-500">No owned documents yet.</div>
          <ul v-else class="grid gap-3 sm:grid-cols-2">
            <li
              v-for="doc in docs.owned"
              :key="doc.id"
              class="card cursor-pointer transition hover:border-indigo-300 hover:shadow"
              @click="openDocument(doc.id)"
            >
              <div class="flex items-start justify-between gap-2">
                <h3 class="font-medium">{{ doc.title }}</h3>
                <span class="badge-owned">Owned</span>
              </div>
              <p class="mt-2 text-xs text-slate-500">
                Shared with {{ doc.shared_with.length }} · {{ doc.attachments.length }} attachments
              </p>
            </li>
          </ul>
        </section>

        <section>
          <div class="mb-3 flex items-center gap-2">
            <h2 class="text-lg font-semibold">Shared with me</h2>
            <span class="badge-shared">Shared</span>
          </div>
          <div v-if="!docs.shared.length" class="card text-sm text-slate-500">
            No shared documents yet. Ask an owner to share with your email.
          </div>
          <ul v-else class="grid gap-3 sm:grid-cols-2">
            <li
              v-for="doc in docs.shared"
              :key="doc.id"
              class="card cursor-pointer border-sky-100 transition hover:border-sky-300 hover:shadow"
              @click="openDocument(doc.id)"
            >
              <div class="flex items-start justify-between gap-2">
                <h3 class="font-medium">{{ doc.title }}</h3>
                <span class="badge-shared">Shared</span>
              </div>
              <p class="mt-2 text-xs text-slate-500">Owner: {{ doc.owner?.name }}</p>
            </li>
          </ul>
        </section>
      </div>

      <ImportPanel @imported="onImported" />
    </div>
  </div>
</template>
