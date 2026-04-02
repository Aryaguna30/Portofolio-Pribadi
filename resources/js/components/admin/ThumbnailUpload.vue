<template>
  <div class="thumb-upload">
    <!-- Preview area -->
    <div
      class="thumb-upload__preview"
      :class="{ 'thumb-upload__preview--has-image': previewUrl }"
      role="img"
      :aria-label="previewUrl ? 'Preview thumbnail' : 'Area upload thumbnail'"
    >
      <img
        v-if="previewUrl"
        :src="previewUrl"
        alt="Preview thumbnail"
        class="thumb-upload__img"
      />
      <div v-else class="thumb-upload__placeholder" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/>
          <polyline points="21 15 16 10 5 21"/>
        </svg>
        <span>Belum ada gambar</span>
      </div>
    </div>

    <!-- Input & actions -->
    <div class="thumb-upload__controls">
      <label class="thumb-upload__label" :for="inputId">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        {{ previewUrl ? 'Ganti Gambar' : 'Pilih Gambar' }}
        <input
          :id="inputId"
          ref="inputRef"
          type="file"
          accept="image/jpeg,image/png,image/webp"
          class="sr-only"
          :aria-label="previewUrl ? 'Ganti thumbnail' : 'Pilih thumbnail'"
          @change="onFileChange"
        />
      </label>

      <button
        v-if="previewUrl"
        type="button"
        class="thumb-upload__remove"
        aria-label="Hapus gambar"
        @click="removeImage"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <polyline points="3 6 5 6 21 6"/>
          <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
          <path d="M10 11v6"/><path d="M14 11v6"/>
          <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
        </svg>
        Hapus
      </button>
    </div>

    <!-- Validation error -->
    <p v-if="error" class="thumb-upload__error" role="alert">{{ error }}</p>

    <!-- Hint -->
    <p class="thumb-upload__hint">JPEG, PNG, atau WebP · Maks. 2MB</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: [File, null],
    default: null,
  },
  /** Existing image URL (for edit forms) */
  existingUrl: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'error']);

const inputRef = ref(null);
const localPreview = ref(null);
const error = ref('');

// Unique id for label/input association
const inputId = `thumb-upload-${Math.random().toString(36).slice(2, 8)}`;

const previewUrl = computed(() => localPreview.value ?? props.existingUrl ?? null);

const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2 MB

function onFileChange(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  error.value = '';

  if (!ALLOWED_TYPES.includes(file.type)) {
    error.value = 'Format tidak didukung. Gunakan JPEG, PNG, atau WebP.';
    emit('error', error.value);
    resetInput();
    return;
  }

  if (file.size > MAX_SIZE_BYTES) {
    error.value = 'Ukuran file melebihi 2MB.';
    emit('error', error.value);
    resetInput();
    return;
  }

  localPreview.value = URL.createObjectURL(file);
  emit('update:modelValue', file);
}

function removeImage() {
  localPreview.value = null;
  error.value = '';
  emit('update:modelValue', null);
  resetInput();
}

function resetInput() {
  if (inputRef.value) inputRef.value.value = '';
}

// Clean up object URL on unmount
watch(localPreview, (newVal, oldVal) => {
  if (oldVal && oldVal.startsWith('blob:')) {
    URL.revokeObjectURL(oldVal);
  }
});
</script>

<style scoped>
.thumb-upload {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.thumb-upload__preview {
  width: 100%;
  height: 180px;
  border-radius: 10px;
  border: 2px dashed var(--border, rgba(255, 255, 255, 0.08));
  overflow: hidden;
  background: var(--bg, #0a0e1a);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s;
}

.thumb-upload__preview--has-image {
  border-style: solid;
  border-color: rgba(124, 58, 237, 0.3);
}

.thumb-upload__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb-upload__placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: var(--muted, #6b7a99);
  font-size: 0.8125rem;
}

.thumb-upload__controls {
  display: flex;
  gap: 0.625rem;
  flex-wrap: wrap;
}

.thumb-upload__label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--accent, #7c3aed);
  background: rgba(124, 58, 237, 0.1);
  border: 1px solid rgba(124, 58, 237, 0.25);
  cursor: pointer;
  transition: all 0.2s ease;
}

.thumb-upload__label:hover {
  background: rgba(124, 58, 237, 0.18);
  border-color: rgba(124, 58, 237, 0.4);
}

.thumb-upload__label:focus-within {
  outline: 2px solid var(--accent, #7c3aed);
  outline-offset: 2px;
}

.thumb-upload__remove {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #f87171;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.thumb-upload__remove:hover {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.35);
}

.thumb-upload__remove:focus-visible {
  outline: 2px solid #ef4444;
  outline-offset: 2px;
}

.thumb-upload__error {
  font-size: 0.8rem;
  color: #f87171;
}

.thumb-upload__hint {
  font-size: 0.75rem;
  color: var(--muted, #6b7a99);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>
