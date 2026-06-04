import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/api'

export interface DocumentSummary {
  id: number
  title: string
  relation: 'owned' | 'shared'
  is_owner: boolean
  can_edit: boolean
  owner?: { id: number; name: string; email: string } | null
  shared_with: Array<{ id: number; name: string; email: string; permission: string }>
  attachments: Array<{ id: number; original_name: string; size: number; mime_type?: string }>
  updated_at?: string
  content?: string
}

export const useDocumentsStore = defineStore('documents', () => {
  const owned = ref<DocumentSummary[]>([])
  const shared = ref<DocumentSummary[]>([])
  const current = ref<DocumentSummary | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchLists() {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/documents')
      owned.value = data.owned
      shared.value = data.shared
    } catch (err: unknown) {
      error.value = 'Failed to load documents.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id: number) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/documents/${id}`)
      current.value = data.document
      return data.document as DocumentSummary
    } finally {
      loading.value = false
    }
  }

  async function create(title: string) {
    const { data } = await api.post('/documents', { title, content: '<p></p>' })
    await fetchLists()
    return data.document as DocumentSummary
  }

  async function save(id: number, payload: { title?: string; content?: string }) {
    const { data } = await api.put(`/documents/${id}`, payload)
    current.value = data.document
    await fetchLists()
    return data.document as DocumentSummary
  }

  async function remove(id: number) {
    await api.delete(`/documents/${id}`)
    await fetchLists()
  }

  return {
    owned,
    shared,
    current,
    loading,
    error,
    fetchLists,
    fetchOne,
    create,
    save,
    remove,
  }
})
