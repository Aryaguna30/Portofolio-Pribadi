<template>
  <div class="rte-wrapper">
    <div ref="editorRef" class="rte-editor"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Tulis deskripsi proyek...',
  },
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
let quill = null;
let ignoreNextChange = false;

onMounted(async () => {
  // Dynamically import Quill to avoid SSR issues
  const { default: Quill } = await import('quill');

  quill = new Quill(editorRef.value, {
    theme: 'snow',
    placeholder: props.placeholder,
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean'],
      ],
    },
  });

  // Set initial content
  if (props.modelValue) {
    quill.root.innerHTML = props.modelValue;
  }

  // Emit on change
  quill.on('text-change', () => {
    if (ignoreNextChange) {
      ignoreNextChange = false;
      return;
    }
    const html = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
    emit('update:modelValue', html);
  });
});

// Sync external modelValue changes into Quill without triggering emit loop
watch(
  () => props.modelValue,
  (val) => {
    if (!quill) return;
    const current = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
    if (val !== current) {
      ignoreNextChange = true;
      quill.root.innerHTML = val ?? '';
    }
  },
);

onBeforeUnmount(() => {
  quill = null;
});
</script>

<style>
/* Quill snow theme overrides for dark admin UI */
.rte-wrapper .ql-toolbar {
  background: var(--bg, #0a0e1a);
  border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
  border-bottom: none;
  border-radius: 8px 8px 0 0;
}

.rte-wrapper .ql-container {
  background: var(--bg, #0a0e1a);
  border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
  border-radius: 0 0 8px 8px;
  font-family: 'Inter', sans-serif;
  font-size: 0.9rem;
  min-height: 180px;
}

.rte-wrapper .ql-editor {
  color: var(--text, #e8edf5);
  min-height: 180px;
  line-height: 1.7;
}

.rte-wrapper .ql-editor.ql-blank::before {
  color: var(--muted, #6b7a99);
  font-style: normal;
}

.rte-wrapper .ql-toolbar .ql-stroke {
  stroke: var(--muted, #6b7a99);
}

.rte-wrapper .ql-toolbar .ql-fill {
  fill: var(--muted, #6b7a99);
}

.rte-wrapper .ql-toolbar button:hover .ql-stroke,
.rte-wrapper .ql-toolbar button.ql-active .ql-stroke {
  stroke: var(--accent, #7c3aed);
}

.rte-wrapper .ql-toolbar button:hover .ql-fill,
.rte-wrapper .ql-toolbar button.ql-active .ql-fill {
  fill: var(--accent, #7c3aed);
}

.rte-wrapper .ql-toolbar .ql-picker-label {
  color: var(--muted, #6b7a99);
}

.rte-wrapper .ql-toolbar .ql-picker-options {
  background: var(--bg-card, #131929);
  border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
  border-radius: 6px;
}

.rte-wrapper .ql-toolbar .ql-picker-item {
  color: var(--text, #e8edf5);
}

.rte-wrapper .ql-container:focus-within {
  border-color: var(--accent, #7c3aed);
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}
</style>
