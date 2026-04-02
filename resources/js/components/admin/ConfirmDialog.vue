<template>
  <Transition name="confirm-dialog">
    <div
      v-if="isOpen"
      class="confirm-backdrop"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="titleId"
      :aria-describedby="descId"
      @click.self="cancel"
    >
      <div
        ref="trapRef"
        class="confirm-panel"
        @keydown.esc="cancel"
      >
        <!-- Icon -->
        <div class="confirm-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
            <path d="M10 11v6"/><path d="M14 11v6"/>
            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
          </svg>
        </div>

        <!-- Text -->
        <h2 :id="titleId" class="confirm-title">{{ title }}</h2>
        <p :id="descId" class="confirm-desc">{{ message }}</p>

        <!-- Actions -->
        <div class="confirm-actions">
          <button
            ref="cancelBtnRef"
            type="button"
            class="confirm-btn confirm-btn--cancel"
            @click="cancel"
          >
            Batal
          </button>
          <button
            type="button"
            class="confirm-btn confirm-btn--danger"
            :disabled="loading"
            :aria-busy="loading"
            @click="confirm"
          >
            <svg v-if="loading" class="confirm-spinner" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ loading ? 'Menghapus...' : confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useFocusTrap } from '@/composables/useFocusTrap.js';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Konfirmasi Hapus',
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.',
  },
  confirmLabel: {
    type: String,
    default: 'Hapus',
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['confirm', 'cancel']);

const titleId = 'confirm-dialog-title';
const descId = 'confirm-dialog-desc';

const { trapRef, activateTrap, deactivateTrap } = useFocusTrap();

watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      setTimeout(activateTrap, 50);
    } else {
      deactivateTrap();
    }
  },
);

function handleKeydown(e) {
  if (e.key === 'Escape' && props.isOpen) cancel();
}

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));

function confirm() {
  emit('confirm');
}

function cancel() {
  emit('cancel');
}
</script>

<style scoped>
.confirm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 300;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
}

.confirm-panel {
  width: 100%;
  max-width: 420px;
  background: var(--bg-card, #131929);
  border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
  border-radius: 14px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
}

.confirm-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: rgba(239, 68, 68, 0.12);
  color: #f87171;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.25rem;
}

.confirm-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text, #e8edf5);
  margin-bottom: 0.5rem;
}

.confirm-desc {
  font-size: 0.875rem;
  color: var(--muted, #6b7a99);
  line-height: 1.6;
  margin-bottom: 1.75rem;
}

.confirm-actions {
  display: flex;
  gap: 0.75rem;
  width: 100%;
}

.confirm-btn {
  flex: 1;
  padding: 0.7rem 1.25rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.confirm-btn:focus-visible {
  outline: 2px solid var(--accent, #7c3aed);
  outline-offset: 2px;
}

.confirm-btn--cancel {
  background: transparent;
  color: var(--muted, #6b7a99);
  border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
}

.confirm-btn--cancel:hover {
  color: var(--text, #e8edf5);
  border-color: rgba(255, 255, 255, 0.2);
}

.confirm-btn--danger {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.confirm-btn--danger:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.25);
  border-color: rgba(239, 68, 68, 0.5);
}

.confirm-btn--danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.confirm-spinner {
  width: 16px;
  height: 16px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Transition */
.confirm-dialog-enter-active,
.confirm-dialog-leave-active {
  transition: opacity 0.2s ease;
}

.confirm-dialog-enter-active .confirm-panel,
.confirm-dialog-leave-active .confirm-panel {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.confirm-dialog-enter-from,
.confirm-dialog-leave-to {
  opacity: 0;
}

.confirm-dialog-enter-from .confirm-panel,
.confirm-dialog-leave-to .confirm-panel {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}
</style>
