<template>
  <AdminLayout>
    <div class="projects-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Proyek</h1>
          <p class="page-subtitle">Kelola proyek portofolio Anda</p>
        </div>
        <Link :href="route('admin.projects.create')" class="btn btn--primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Tambah Proyek
        </Link>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="alert alert--success" role="status">
        {{ $page.props.flash.success }}
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-wrap">
          <table class="data-table" aria-label="Daftar proyek">
            <thead>
              <tr>
                <th style="width:72px">Thumbnail</th>
                <th>Judul</th>
                <th>Tech Stack</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th style="width:100px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!projects.length">
                <td colspan="6" class="table-empty">Belum ada proyek. Klik "Tambah Proyek" untuk memulai.</td>
              </tr>
              <tr v-for="project in projects" :key="project.id">
                <td>
                  <div class="thumb-cell">
                    <img
                      v-if="project.thumbnail_url"
                      :src="project.thumbnail_url"
                      :alt="`Thumbnail ${project.title}`"
                      loading="lazy"
                      class="thumb-img"
                    />
                    <div v-else class="thumb-placeholder" aria-hidden="true">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                      </svg>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="project-title">{{ project.title }}</span>
                </td>
                <td>
                  <div class="tech-pills">
                    <span
                      v-for="tech in parseTechStack(project.tech_stack).slice(0, 3)"
                      :key="tech"
                      class="tech-pill"
                    >{{ tech }}</span>
                    <span
                      v-if="parseTechStack(project.tech_stack).length > 3"
                      class="tech-pill tech-pill--more"
                    >+{{ parseTechStack(project.tech_stack).length - 3 }}</span>
                  </div>
                </td>
                <td>
                  <span class="badge" :class="project.is_published ? 'badge--green' : 'badge--gray'">
                    {{ project.is_published ? 'Publik' : 'Draft' }}
                  </span>
                </td>
                <td class="text-muted text-sm">{{ formatDate(project.created_at) }}</td>
                <td>
                  <div class="action-btns">
                    <Link
                      :href="route('admin.projects.edit', project.id)"
                      class="btn-icon"
                      aria-label="Edit proyek"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </Link>
                    <button
                      type="button"
                      class="btn-icon btn-icon--danger"
                      aria-label="Hapus proyek"
                      @click="confirmDelete(project)"
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
      </div>
    </div>

    <ConfirmDialog
      :is-open="dialog.open"
      title="Hapus Proyek"
      :message="`Hapus proyek &quot;${dialog.target?.title}&quot;? Thumbnail juga akan dihapus dari storage. Tindakan ini tidak dapat dibatalkan.`"
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
  projects: { type: Array, default: () => [] },
});

const dialog = reactive({ open: false, target: null, loading: false });

function parseTechStack(ts) {
  if (!ts) return [];
  if (Array.isArray(ts)) return ts;
  try { return JSON.parse(ts); } catch { return []; }
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function confirmDelete(project) {
  dialog.target = project;
  dialog.open = true;
}

function executeDelete() {
  dialog.loading = true;
  router.delete(route('admin.projects.destroy', dialog.target.id), {
    preserveScroll: true,
    onFinish: () => {
      dialog.loading = false;
      dialog.open = false;
    },
  });
}
</script>

<style scoped>
.projects-index { max-width: 1000px; }

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.2rem;
}

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

/* Thumbnail */
.thumb-cell {
  width: 52px;
  height: 40px;
  border-radius: 6px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
}

.thumb-img { width: 100%; height: 100%; object-fit: cover; }

.thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--muted);
}

.project-title { font-weight: 500; }

/* Tech pills */
.tech-pills { display: flex; flex-wrap: wrap; gap: 0.3rem; }

.tech-pill {
  font-size: 0.7rem;
  font-weight: 500;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  background: rgba(124, 58, 237, 0.1);
  color: #a78bfa;
  border: 1px solid rgba(124, 58, 237, 0.2);
}

.tech-pill--more {
  background: rgba(255, 255, 255, 0.05);
  color: var(--muted);
  border-color: var(--border);
}

/* Badge */
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
.badge--gray { background: rgba(255, 255, 255, 0.06); color: var(--muted); }

.text-muted { color: var(--muted); }
.text-sm { font-size: 0.8125rem; }

/* Action buttons */
.action-btns { display: flex; gap: 0.375rem; }

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  color: var(--muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-icon:hover { color: var(--text); border-color: rgba(255, 255, 255, 0.15); }
.btn-icon:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.btn-icon--danger:hover { color: #f87171; border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.08); }

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

.btn--primary {
  background: linear-gradient(135deg, var(--accent, #7c3aed), #4f46e5);
  color: #fff;
}

.btn--primary:hover { opacity: 0.9; transform: translateY(-1px); }
</style>
