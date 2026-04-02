<template>
  <AdminLayout>
    <div class="message-show">
      <!-- Header -->
      <div class="page-header">
        <Link :href="route('admin.messages.index')" class="back-link" aria-label="Kembali ke daftar pesan">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
          Kembali
        </Link>
        <div class="header-row">
          <h1 class="page-title">Detail Pesan</h1>
          <button
            type="button"
            class="btn btn--danger"
            @click="dialog.open = true"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
              <path d="M10 11v6"/><path d="M14 11v6"/>
            </svg>
            Hapus Pesan
          </button>
        </div>
      </div>

      <!-- Message card -->
      <div class="message-card">
        <!-- Meta -->
        <div class="message-meta">
          <div class="meta-row">
            <span class="meta-label">Dari</span>
            <span class="meta-value">
              {{ message.name }}
              <a :href="`mailto:${message.email}`" class="meta-email">{{ message.email }}</a>
            </span>
          </div>
          <div class="meta-row">
            <span class="meta-label">Subjek</span>
            <span class="meta-value font-semibold">{{ message.subject }}</span>
          </div>
          <div class="meta-row">
            <span class="meta-label">Diterima</span>
            <span class="meta-value text-muted">{{ formatDate(message.created_at) }}</span>
          </div>
          <div class="meta-row">
            <span class="meta-label">Status</span>
            <span class="badge" :class="message.is_read ? 'badge--green' : 'badge--amber'">
              {{ message.is_read ? 'Sudah Dibaca' : 'Belum Dibaca' }}
            </span>
          </div>
        </div>

        <hr class="divider" />

        <!-- Body -->
        <div class="message-body">
          <p class="message-body__text">{{ message.body }}</p>
        </div>

        <!-- Reply shortcut -->
        <div class="message-reply">
          <a
            :href="`mailto:${message.email}?subject=Re: ${encodeURIComponent(message.subject)}`"
            class="btn btn--primary"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <polyline points="9 17 4 12 9 7"/>
              <path d="M20 18v-2a4 4 0 0 0-4-4H4"/>
            </svg>
            Balas via Email
          </a>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :is-open="dialog.open"
      title="Hapus Pesan"
      :message="`Hapus pesan dari &quot;${message.name}&quot;? Tindakan ini tidak dapat dibatalkan.`"
      :loading="dialog.loading"
      @confirm="executeDelete"
      @cancel="dialog.open = false"
    />
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue';

const props = defineProps({
  message: { type: Object, required: true },
});

const dialog = reactive({ open: false, loading: false });

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

function executeDelete() {
  dialog.loading = true;
  router.delete(route('admin.messages.destroy', props.message.id), {
    onFinish: () => {
      dialog.loading = false;
      dialog.open = false;
    },
  });
}
</script>

<style scoped>
.message-show { max-width: 720px; }

.page-header { margin-bottom: 1.75rem; }

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8125rem;
  color: var(--muted);
  text-decoration: none;
  margin-bottom: 0.75rem;
  transition: color 0.2s;
}

.back-link:hover { color: var(--text); }
.back-link:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; border-radius: 4px; }

.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.page-title { font-size: 1.75rem; font-weight: 700; color: var(--text); }

/* Message card */
.message-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
}

.message-meta {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.meta-row {
  display: flex;
  align-items: baseline;
  gap: 1rem;
}

.meta-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--muted);
  min-width: 72px;
  flex-shrink: 0;
}

.meta-value {
  font-size: 0.9rem;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex-wrap: wrap;
}

.meta-email {
  font-size: 0.8125rem;
  color: var(--accent);
  text-decoration: none;
}

.meta-email:hover { text-decoration: underline; }

.font-semibold { font-weight: 600; }
.text-muted { color: var(--muted); }

.badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.badge--green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.badge--amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }

.divider {
  border: none;
  border-top: 1px solid var(--border);
  margin: 0;
}

.message-body {
  padding: 1.5rem;
}

.message-body__text {
  font-size: 0.9375rem;
  color: var(--text);
  line-height: 1.8;
  white-space: pre-wrap;
  word-break: break-word;
}

.message-reply {
  padding: 1.25rem 1.5rem;
  border-top: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.01);
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.25rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  border: none;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }

.btn--primary {
  background: linear-gradient(135deg, var(--accent, #7c3aed), #4f46e5);
  color: #fff;
}

.btn--primary:hover { opacity: 0.9; transform: translateY(-1px); }

.btn--danger {
  background: rgba(239, 68, 68, 0.1);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.25);
}

.btn--danger:hover { background: rgba(239, 68, 68, 0.18); border-color: rgba(239, 68, 68, 0.4); }
</style>
