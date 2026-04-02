<template>
  <AdminLayout>
    <div class="project-form-page">
      <!-- Header -->
      <div class="page-header">
        <Link :href="route('admin.projects.index')" class="back-link" aria-label="Kembali ke daftar proyek">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
          Kembali
        </Link>
        <h1 class="page-title">Tambah Proyek</h1>
      </div>

      <form @submit.prevent="submit" enctype="multipart/form-data">
        <div class="form-layout">
          <!-- Left column: main fields -->
          <div class="form-main">
            <!-- Title -->
            <div class="form-field">
              <label for="proj-title" class="form-label">Judul Proyek <span class="required">*</span></label>
              <input
                id="proj-title"
                v-model="form.title"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': form.errors.title }"
                placeholder="Nama proyek"
                required
              />
              <p v-if="form.errors.title" class="form-error">{{ form.errors.title }}</p>
            </div>

            <!-- Description (Rich Text Editor) -->
            <div class="form-field">
              <label class="form-label">Deskripsi <span class="required">*</span></label>
              <RichTextEditor
                v-model="form.description"
                placeholder="Tulis deskripsi lengkap proyek..."
              />
              <p v-if="form.errors.description" class="form-error">{{ form.errors.description }}</p>
            </div>

            <!-- Tech Stack -->
            <div class="form-field">
              <label class="form-label">Tech Stack <span class="required">*</span></label>
              <div class="tag-list">
                <div v-for="(tech, idx) in form.tech_stack" :key="idx" class="tag-item">
                  <input
                    v-model="form.tech_stack[idx]"
                    type="text"
                    class="form-input tag-input"
                    :aria-label="`Teknologi ${idx + 1}`"
                    placeholder="Contoh: Laravel"
                  />
                  <button
                    type="button"
                    class="tag-remove"
                    :aria-label="`Hapus teknologi ${idx + 1}`"
                    @click="removeTech(idx)"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                  </button>
                </div>
                <button type="button" class="btn-add-tag" @click="addTech">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                       fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
                  Tambah Teknologi
                </button>
              </div>
              <p v-if="form.errors.tech_stack" class="form-error">{{ form.errors.tech_stack }}</p>
            </div>

            <!-- URLs -->
            <div class="form-row">
              <div class="form-field">
                <label for="proj-demo" class="form-label">URL Demo <span class="optional">(opsional)</span></label>
                <input
                  id="proj-demo"
                  v-model="form.demo_url"
                  type="url"
                  class="form-input"
                  :class="{ 'form-input--error': form.errors.demo_url }"
                  placeholder="https://demo.example.com"
                />
                <p v-if="form.errors.demo_url" class="form-error">{{ form.errors.demo_url }}</p>
              </div>
              <div class="form-field">
                <label for="proj-repo" class="form-label">URL Repositori <span class="optional">(opsional)</span></label>
                <input
                  id="proj-repo"
                  v-model="form.repo_url"
                  type="url"
                  class="form-input"
                  :class="{ 'form-input--error': form.errors.repo_url }"
                  placeholder="https://github.com/user/repo"
                />
                <p v-if="form.errors.repo_url" class="form-error">{{ form.errors.repo_url }}</p>
              </div>
            </div>
          </div>

          <!-- Right column: thumbnail + publish -->
          <div class="form-sidebar">
            <div class="sidebar-card">
              <h3 class="sidebar-card__title">Thumbnail <span class="required">*</span></h3>
              <ThumbnailUpload
                v-model="form.thumbnail"
                @error="thumbnailError = $event"
              />
              <p v-if="form.errors.thumbnail" class="form-error">{{ form.errors.thumbnail }}</p>
              <p v-if="thumbnailError" class="form-error">{{ thumbnailError }}</p>
            </div>

            <div class="sidebar-card">
              <h3 class="sidebar-card__title">Publikasi</h3>
              <label class="toggle-label">
                <input
                  v-model="form.is_published"
                  type="checkbox"
                  class="toggle-input"
                  role="switch"
                  :aria-checked="form.is_published"
                />
                <span class="toggle-track" aria-hidden="true"></span>
                <span class="toggle-text">{{ form.is_published ? 'Publik' : 'Draft' }}</span>
              </label>
            </div>

            <!-- Submit -->
            <button
              type="submit"
              class="btn btn--primary btn--full"
              :disabled="form.processing"
              :aria-busy="form.processing"
            >
              <svg v-if="form.processing" class="spinner" xmlns="http://www.w3.org/2000/svg"
                   fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ form.processing ? 'Menyimpan...' : 'Simpan Proyek' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import RichTextEditor from '@/components/admin/RichTextEditor.vue';
import ThumbnailUpload from '@/components/admin/ThumbnailUpload.vue';

const thumbnailError = ref('');

const form = useForm({
  title: '',
  description: '',
  tech_stack: [''],
  demo_url: '',
  repo_url: '',
  thumbnail: null,
  is_published: true,
});

function addTech() {
  form.tech_stack.push('');
}

function removeTech(idx) {
  form.tech_stack.splice(idx, 1);
}

function submit() {
  thumbnailError.value = '';
  form.post(route('admin.projects.store'), {
    forceFormData: true,
  });
}
</script>

<style scoped>
.project-form-page { max-width: 1000px; }

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

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text);
}

/* Layout */
.form-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

.form-main { display: flex; flex-direction: column; gap: 1.25rem; }

/* Form fields */
.form-field { display: flex; flex-direction: column; gap: 0.375rem; }

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text);
}

.required { color: #f87171; }
.optional { font-weight: 400; color: var(--muted); font-size: 0.75rem; }

.form-input {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0.65rem 0.875rem;
  font-size: 0.875rem;
  color: var(--text);
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  width: 100%;
  box-sizing: border-box;
}

.form-input::placeholder { color: var(--muted); }
.form-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15); }
.form-input--error { border-color: rgba(239, 68, 68, 0.5); }
.form-input--error:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15); }

.form-error { font-size: 0.8rem; color: #f87171; }

/* Tag list */
.tag-list { display: flex; flex-direction: column; gap: 0.5rem; }
.tag-item { display: flex; gap: 0.5rem; align-items: center; }
.tag-input { flex: 1; }

.tag-remove {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s;
}

.tag-remove:hover { background: rgba(239, 68, 68, 0.15); }
.tag-remove:focus-visible { outline: 2px solid #ef4444; outline-offset: 2px; }

.btn-add-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.875rem;
  border-radius: 7px;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--accent);
  background: rgba(124, 58, 237, 0.08);
  border: 1px dashed rgba(124, 58, 237, 0.3);
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s;
  align-self: flex-start;
}

.btn-add-tag:hover { background: rgba(124, 58, 237, 0.15); }

/* Sidebar */
.form-sidebar { display: flex; flex-direction: column; gap: 1rem; }

.sidebar-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.sidebar-card__title {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text);
}

/* Toggle */
.toggle-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.toggle-input { position: absolute; opacity: 0; width: 0; height: 0; }

.toggle-track {
  width: 40px;
  height: 22px;
  border-radius: 11px;
  background: var(--border);
  position: relative;
  transition: background 0.2s;
  flex-shrink: 0;
}

.toggle-track::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.2s;
}

.toggle-input:checked + .toggle-track {
  background: var(--accent);
}

.toggle-input:checked + .toggle-track::after {
  transform: translateX(18px);
}

.toggle-input:focus-visible + .toggle-track {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.toggle-text { font-size: 0.875rem; color: var(--text); font-weight: 500; }

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.7rem 1.25rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  border: none;
  transition: all 0.2s ease;
}

.btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }

.btn--primary {
  background: linear-gradient(135deg, var(--accent, #7c3aed), #4f46e5);
  color: #fff;
}

.btn--primary:hover:not(:disabled) { opacity: 0.9; }
.btn--full { width: 100%; }

.spinner {
  width: 16px;
  height: 16px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .form-layout { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
}
</style>
