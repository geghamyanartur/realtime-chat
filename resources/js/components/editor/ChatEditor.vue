<template>
  <div class="rounded-xl border border-slate-800/70 bg-slate-900/80">
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-800/70 px-3 py-2">
      <button
        type="button"
        class="h-8 w-8 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition"
        :class="{ 'bg-slate-800/70 text-white': editor?.isActive('bold') }"
        @click="editor?.chain().focus().toggleBold().run()"
      >
        <span class="font-semibold">B</span>
      </button>
      <button
        type="button"
        class="h-8 w-8 rounded-md italic text-slate-300 hover:text-white hover:bg-slate-800/60 transition"
        :class="{ 'bg-slate-800/70 text-white': editor?.isActive('italic') }"
        @click="editor?.chain().focus().toggleItalic().run()"
      >
        I
      </button>
      <button
        type="button"
        class="h-8 w-8 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition"
        :class="{ 'bg-slate-800/70 text-white': editor?.isActive('bulletList') }"
        @click="editor?.chain().focus().toggleBulletList().run()"
      >
        •
      </button>
      <button
        type="button"
        class="h-8 w-8 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition"
        :class="{ 'bg-slate-800/70 text-white': editor?.isActive('orderedList') }"
        @click="editor?.chain().focus().toggleOrderedList().run()"
      >
        1.
      </button>
      <button
        type="button"
        class="h-8 w-8 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition"
        :class="{ 'bg-slate-800/70 text-white': editor?.isActive('blockquote') }"
        title="Blockquote"
        @click="editor?.chain().focus().toggleBlockquote().run()"
      >
        ❝
      </button>
      <button
        type="button"
        class="h-8 w-8 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition"
        :class="{ 'bg-slate-800/70 text-white': editor?.isActive('codeBlock') }"
        title="Code block"
        @click="editor?.chain().focus().toggleCodeBlock().run()"
      >
        {{ '{ }' }}
      </button>
      <button
        type="button"
        class="h-8 px-2 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition"
        @click="editor?.chain().focus().unsetAllMarks().clearNodes().run()"
      >
        Clear
      </button>
      <div class="ml-auto flex items-center gap-1">
        <button
          type="button"
          class="h-8 px-2 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition disabled:opacity-40"
          :disabled="!editor?.can().chain().focus().undo().run()"
          @click="editor?.chain().focus().undo().run()"
        >
          Undo
        </button>
        <button
          type="button"
          class="h-8 px-2 rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition disabled:opacity-40"
          :disabled="!editor?.can().chain().focus().redo().run()"
          @click="editor?.chain().focus().redo().run()"
        >
          Redo
        </button>
      </div>
    </div>
    <div class="min-h-[120px] px-3 py-2 text-sm text-slate-50">
      <EditorContent :editor="editor" class="prose-invert [&_.ProseMirror]:min-h-[110px]" />
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, watch } from "vue";
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Placeholder from "@tiptap/extension-placeholder";

const props = defineProps({
  modelValue: { type: String, default: "" },
  placeholder: { type: String, default: "Share updates, questions, or @ mention the AI." },
});

const emit = defineEmits(["update:modelValue", "submit"]);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Placeholder.configure({
      placeholder: props.placeholder,
    }),
  ],
  editorProps: {
    attributes: {
      class:
        "prose-sm focus:outline-none prose-p:m-0 prose-ul:my-2 prose-li:my-0 prose-li:list-disc",
    },
    handleKeyDown(view, event) {
      if ((event.metaKey || event.ctrlKey) && event.key === "Enter") {
        emit("submit");
        return true;
      }
      return false;
    },
  },
  onUpdate({ editor }) {
    emit("update:modelValue", editor.getHTML());
  },
});

watch(
  () => props.modelValue,
  (val) => {
    if (editor.value && editor.value.getHTML() !== val) {
      editor.value.commands.setContent(val || "");
    }
  },
);

onBeforeUnmount(() => {
  editor.value?.destroy();
});
</script>

<style scoped>
:deep(.ProseMirror) {
  white-space: pre-wrap;
}

:deep(.ProseMirror blockquote) {
  border-left: 3px solid rgba(255, 255, 255, 0.25);
  margin: 1rem 0;
  padding-left: 1rem;
}

:deep(.ProseMirror ul) {
  list-style: disc;
  padding-left: 1.25rem;
  margin: 0.5rem 0;
}

:deep(.ProseMirror ol) {
  list-style: decimal;
  padding-left: 1.25rem;
  margin: 0.5rem 0;
}

:deep(.ProseMirror li) {
  margin: 0.15rem 0;
}

:deep(.ProseMirror pre) {
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.5rem;
  color: #e2e8f0;
  font-family: 'JetBrainsMono', monospace;
  margin: 1.5rem 0;
  padding: 0.75rem 1rem;
}

:deep(.ProseMirror pre code) {
  background: none;
  color: inherit;
  font-size: 0.85rem;
  padding: 0;
}
</style>
