<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue'
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Heading from '@tiptap/extension-heading'

const props = defineProps<{
  modelValue: string
  editable?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const editor = new Editor({
  extensions: [
    StarterKit.configure({
      heading: false,
    }),
    Heading.configure({ levels: [1, 2, 3] }),
    Underline,
  ],
  content: props.modelValue,
  editable: props.editable !== false,
  onUpdate: ({ editor: ed }) => {
    emit('update:modelValue', ed.getHTML())
  },
})

watch(
  () => props.modelValue,
  (value) => {
    if (editor.getHTML() !== value) {
      editor.commands.setContent(value, false)
    }
  },
)

watch(
  () => props.editable,
  (value) => {
    editor.setEditable(value !== false)
  },
)

onBeforeUnmount(() => {
  editor.destroy()
})

function run(command: () => boolean) {
  command()
  editor.commands.focus()
}
</script>

<template>
  <div class="space-y-3">
    <div
      v-if="editable !== false"
      class="flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-slate-50 p-2"
    >
      <button type="button" class="btn-secondary px-3 py-1" @click="run(() => editor.chain().focus().toggleBold().run())">
        Bold
      </button>
      <button type="button" class="btn-secondary px-3 py-1" @click="run(() => editor.chain().focus().toggleItalic().run())">
        Italic
      </button>
      <button type="button" class="btn-secondary px-3 py-1" @click="run(() => editor.chain().focus().toggleUnderline().run())">
        Underline
      </button>
      <button type="button" class="btn-secondary px-3 py-1" @click="run(() => editor.chain().focus().toggleHeading({ level: 1 }).run())">
        H1
      </button>
      <button type="button" class="btn-secondary px-3 py-1" @click="run(() => editor.chain().focus().toggleHeading({ level: 2 }).run())">
        H2
      </button>
      <button type="button" class="btn-secondary px-3 py-1" @click="run(() => editor.chain().focus().toggleBulletList().run())">
        Bullets
      </button>
      <button type="button" class="btn-secondary px-3 py-1" @click="run(() => editor.chain().focus().toggleOrderedList().run())">
        Numbered
      </button>
    </div>
    <EditorContent :editor="editor" class="editor-content prose max-w-none" />
  </div>
</template>
