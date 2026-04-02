<template>
  <AdminLayout>
    <div class="messages-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Pesan Masuk</h1>
          <p class="page-subtitle">
            {{ unreadCount > 0 ? `${unreadCount} pesan belum dibaca` : 'Semua pesan sudah dibaca' }}
          </p>
        </div>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="alert alert--success" role="status">
        {{ $page.props.flash.success }}
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-wrap">
          <table class="data-table" aria-label="Daftar pesan masuk">
            <thead>
              <tr>
                <th style="width:16px"></th>
                <th>Pengirim</th>
                <th>Subjek</th>
                <th>Diterima</th>
                <th style="width:80px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!messages.length">
                <td colspan="5" class="table-empty">Belum ada pesan masuk.</td>
              </tr>
              <tr
                v-for="msg in messages"
                :key="msg.id"
                class="msg-row"
                :class="{ 'msg-row--unread': !msg.is_read }"
              >
                <td>
                  <span
                    v-if="!msg.is_read"
                    class="unread-dot"
                    aria-label="Belum dibaca"
                    title="Belum dibaca"
                  ></span>
                </td>
                <td>
                  <div class="sender-info">
                    <span class="sender-name" :class="{ 'font-bold': !msg.is_read }">{{ msg.name }}</span>
                    <span class="sender-email">{{ msg.email }}</span>
                  </div>
                </td>
                <td>
                  <Link
                    :href="route('admin.messages.show', msg.id)"
                    class="subject-link"
                    :class="{ 'font-bold': !msg.is_read }"
                  >
                    {{ msg.subject }}
                  </Link>
                </td>
                <td class="text-muted text-sm">{{ formatDate(msg.created_at) }}</td>
                <td>
                  <div class="action-btns">
                    <Link
                      :href="route('admin.messages.show', msg.id)"
                      class="btn-icon"
                      aria-label="Lihat pesan"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </Link>
                    <button
                      type="button"
                      class="btn-icon btn-icon--danger"
                      aria-label="Hapus pesan"
                      @click="confirmDelete(msg)"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="pagination">
          <Link
            v-if="pagination.prev_page_url"
            :href="pagination.prev_page_url"
            class="page-btn"
            aria-label="Halaman sebelumnya"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </Link>
          <span class="page-info">
            Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
            <span class="page-total">({{ pagination.total }} pesan)</span>
          </span>
          <Link
            v-if="pagination.next_page_url"
            :href="pagination.next_page_url"
            class="page-btn"
            aria-label="Halaman berikutnya"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </Link>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :is-open="dialog.open"
      title="Hapus Pesan"
      :message="`Hapus pesan dari &quot;${dialog.target?.name}&quot;? Tindakan ini tidak dapat dibatalkan.`"
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
  messages:    { type: Array,  default: () => [] },
  unreadCount: { type: Number, default: 0 },
  pagination:  { type: Object, default: null },
});

const dialog = reactive({ open: false, target: null, loading: false });

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

function confirmDelete(msg) {
  dialog.target = msg;
  dialog.open = true;
}

function executeDelete() {
  dialog.loading = true;
  router.delete(route('admin.messages.destroy', dialog.target.id), {
    preserveScroll: true,
    onFinish: () => {
      dialog.loading = false;
      dialog.open = false;
    },
  });
}
</script>

<style scoped>
.messages-index { max-width: 900px; }

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.page-title { font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.2rem; }
.page-subtitle { font-size: 0.875rem; color: var(--muted); }

.alert {
  padding: 0.875rem 1.125rem;
  border-radius: 8px;
  font-size: 0.875rem;
  margin-bottom: 1.25rem;
}

.alert--success {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #34d399;
}

.table-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
}

.table-wrap { overflow-x: auto; }

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.data-table th {
  text-align: left;
  padding: 0.75rem 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--muted);
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid var(--border);
}

.data-table td {
  padding: 0.875rem 1rem;
  color: var(--text);
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  vertical-align: middle;
}

.data-table tr:last-child td { border-bottom: none; }

.table-empty {
  text-align: center;
  color: var(--muted);
  padding: 3rem 1rem !important;
}

.msg-row--unread { background: rgba(124, 58, 237, 0.03); }

.unread-dot {
  display: inline-block;
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--accent, #7c3aed);
  flex-shrink: 0;
}

.sender-info { display: flex; flex-direction: column; gap: 0.15rem; }
.sender-name { font-weight: 500; }
.sender-email { font-size: 0.75rem; color: var(--muted); }
.font-bold { font-weight: 700; }

.subject-link {
  color: var(--text);
  text-decoration: none;
  transition: color 0.2s;
}
.subject-link:hover { color: var(--accent); }
.subject-link:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; border-radius: 3px; }

.text-muted { color: var(--muted); }
.text-sm { font-size: 0.8125rem; }

.action-btns { display: flex; gap: 0.375rem; }

.btn-icon {
  width: 32px; height: 32px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  color: var(--muted);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
  text-decoration: none;
}
.btn-icon:hover { color: var(--text); border-color: rgba(255, 255, 255, 0.15); }
.btn-icon:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.btn-icon--danger:hover { color: #f87171; border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.08); }

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem;
  border-top: 1px solid var(--border);
}

.page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px; height: 32px;
  border-radius: 6px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border);
  color: var(--muted);
  text-decoration: none;
  transition: all 0.2s;
}
.page-btn:hover { color: var(--text); border-color: rgba(255,255,255,0.2); }

.page-info {
  font-size: 0.8125rem;
  color: var(--muted);
}
.page-total { margin-left: 0.25rem; }
</style>
