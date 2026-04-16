<template>
  <AdminLayout>
    <div class="landing-admin">
      <h1 class="page-title">Landing Page</h1>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="alert alert--success" role="status">
        {{ $page.props.flash.success }}
      </div>

      <!-- ── Hero Section Form ─────────────────────────────────── -->
      <section class="admin-section">
        <h2 class="admin-section__title">Hero Section</h2>
        <form @submit.prevent="submitHero">
          <div class="form-grid">
            <div class="form-field">
              <label for="hero-name" class="form-label">Nama Developer</label>
              <input
                id="hero-name"
                v-model="heroForm.name"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': heroForm.errors.name }"
                placeholder="Nama lengkap"
              />
              <p v-if="heroForm.errors.name" class="form-error">{{ heroForm.errors.name }}</p>
            </div>

            <div class="form-field">
              <label for="hero-description" class="form-label">Deskripsi Singkat</label>
              <textarea
                id="hero-description"
                v-model="heroForm.description"
                class="form-input form-textarea"
                :class="{ 'form-input--error': heroForm.errors.description }"
                placeholder="Deskripsi singkat tentang diri Anda"
                rows="3"
              ></textarea>
              <p v-if="heroForm.errors.description" class="form-error">{{ heroForm.errors.description }}</p>
            </div>

            <!-- Professions array -->
            <div class="form-field form-field--full">
              <label class="form-label">Profesi (Typing Animation)</label>
              <div class="tag-list">
                <div
                  v-for="(prof, idx) in heroForm.professions"
                  :key="idx"
                  class="tag-item"
                >
                  <input
                    v-model="heroForm.professions[idx]"
                    type="text"
                    class="form-input tag-input"
                    :aria-label="`Profesi ${idx + 1}`"
                    placeholder="Contoh: Full-Stack Developer"
                  />
                  <button
                    type="button"
                    class="tag-remove"
                    :aria-label="`Hapus profesi ${idx + 1}`"
                    @click="removeProfession(idx)"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                  </button>
                </div>
                <button type="button" class="btn-add-tag" @click="addProfession">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                       fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
                  Tambah Profesi
                </button>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn--primary" :disabled="heroForm.processing">
            {{ heroForm.processing ? 'Menyimpan...' : 'Simpan Hero' }}
          </button>
        </form>
      </section>

      <!-- ── About Section ─────────────────────────────────────── -->
      <section class="admin-section">
        <h2 class="admin-section__title">Tentang Saya</h2>
        <form @submit.prevent="submitAbout">
          <div class="form-grid">
            <div class="form-field form-field--full">
              <label for="about-bio1" class="form-label">Bio Paragraf 1</label>
              <textarea
                id="about-bio1"
                v-model="aboutForm.bio1"
                class="form-input form-textarea"
                :class="{ 'form-input--error': aboutForm.errors.bio1 }"
                placeholder="Paragraf pertama tentang diri Anda..."
                rows="3"
              ></textarea>
              <p v-if="aboutForm.errors.bio1" class="form-error">{{ aboutForm.errors.bio1 }}</p>
            </div>
            <div class="form-field form-field--full">
              <label for="about-bio2" class="form-label">Bio Paragraf 2</label>
              <textarea
                id="about-bio2"
                v-model="aboutForm.bio2"
                class="form-input form-textarea"
                :class="{ 'form-input--error': aboutForm.errors.bio2 }"
                placeholder="Paragraf kedua tentang diri Anda..."
                rows="3"
              ></textarea>
              <p v-if="aboutForm.errors.bio2" class="form-error">{{ aboutForm.errors.bio2 }}</p>
            </div>
            <div class="form-field">
              <label for="about-stat-years" class="form-label">Statistik — Tahun Pengalaman</label>
              <input
                id="about-stat-years"
                v-model="aboutForm.stat_years"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': aboutForm.errors.stat_years }"
                placeholder="Contoh: 3+"
              />
              <p v-if="aboutForm.errors.stat_years" class="form-error">{{ aboutForm.errors.stat_years }}</p>
            </div>
            <div class="form-field">
              <label for="about-stat-projects" class="form-label">Statistik — Proyek Selesai</label>
              <input
                id="about-stat-projects"
                v-model="aboutForm.stat_projects"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': aboutForm.errors.stat_projects }"
                placeholder="Contoh: 12"
              />
              <p v-if="aboutForm.errors.stat_projects" class="form-error">{{ aboutForm.errors.stat_projects }}</p>
            </div>
            <div class="form-field">
              <label for="about-stat-commits" class="form-label">Statistik — Total Commits</label>
              <input
                id="about-stat-commits"
                v-model="aboutForm.stat_commits"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': aboutForm.errors.stat_commits }"
                placeholder="Contoh: 1.2K+"
              />
              <p v-if="aboutForm.errors.stat_commits" class="form-error">{{ aboutForm.errors.stat_commits }}</p>
            </div>
          </div>
          <button type="submit" class="btn btn--primary" :disabled="aboutForm.processing">
            {{ aboutForm.processing ? 'Menyimpan...' : 'Simpan Tentang Saya' }}
          </button>
        </form>
      </section>

      <!-- ── CV Upload (hidden) ────────────────────────────────── -->
      <!--
      <section class="admin-section">
        <h2 class="admin-section__title">File CV</h2>
        <div class="cv-status">
          <div v-if="currentCvPath" class="cv-current">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span>CV tersedia</span>
            <button type="button" class="btn-danger-sm" :disabled="cvDeleteForm.processing" @click="deleteCv">
              {{ cvDeleteForm.processing ? 'Menghapus...' : 'Hapus CV' }}
            </button>
          </div>
          <p v-else class="cv-empty">Belum ada CV yang diunggah.</p>
        </div>

        <form @submit.prevent="uploadCv" class="cv-upload-form">
          <div class="form-field">
            <label for="cv-file" class="form-label">Upload CV Baru (PDF, maks. 5MB)</label>
            <input
              id="cv-file"
              ref="cvInputRef"
              type="file"
              accept="application/pdf"
              class="form-input-file"
              :class="{ 'form-input--error': cvForm.errors.cv_file }"
              aria-describedby="cv-error"
              @change="onCvChange"
            />
            <p v-if="cvForm.errors.cv_file" id="cv-error" class="form-error">{{ cvForm.errors.cv_file }}</p>
          </div>
          <button type="submit" class="btn btn--primary" :disabled="cvForm.processing || !cvForm.cv_file">
            {{ cvForm.processing ? 'Mengunggah...' : 'Upload CV' }}
          </button>
        </form>
      </section>
      -->

      <!-- ── Social Links ─────────────────────────────────────── -->
      <section class="admin-section">
        <h2 class="admin-section__title">Social Links</h2>
        <form @submit.prevent="submitSocial">
          <div class="form-grid">
            <div class="form-field">
              <label for="social-linkedin" class="form-label">LinkedIn URL</label>
              <input
                id="social-linkedin"
                v-model="socialForm.linkedin"
                type="url"
                class="form-input"
                :class="{ 'form-input--error': socialForm.errors.linkedin }"
                placeholder="https://linkedin.com/in/username"
              />
              <p v-if="socialForm.errors.linkedin" class="form-error">{{ socialForm.errors.linkedin }}</p>
            </div>
            <div class="form-field">
              <label for="social-github" class="form-label">GitHub URL</label>
              <input
                id="social-github"
                v-model="socialForm.github"
                type="url"
                class="form-input"
                :class="{ 'form-input--error': socialForm.errors.github }"
                placeholder="https://github.com/username"
              />
              <p v-if="socialForm.errors.github" class="form-error">{{ socialForm.errors.github }}</p>
            </div>
            <div class="form-field">
              <label for="social-email" class="form-label">Email</label>
              <input
                id="social-email"
                v-model="socialForm.email"
                type="email"
                class="form-input"
                :class="{ 'form-input--error': socialForm.errors.email }"
                placeholder="email@example.com"
              />
              <p v-if="socialForm.errors.email" class="form-error">{{ socialForm.errors.email }}</p>
            </div>
          </div>
          <button type="submit" class="btn btn--primary" :disabled="socialForm.processing">
            {{ socialForm.processing ? 'Menyimpan...' : 'Simpan Social Links' }}
          </button>
        </form>
      </section>

      <!-- ── Timeline Entries ──────────────────────────────────── -->
      <section class="admin-section">
        <div class="admin-section__header">
          <h2 class="admin-section__title">Timeline</h2>
          <button type="button" class="btn btn--primary btn--sm" @click="openTimelineForm(null)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah
          </button>
        </div>

        <div class="data-table-wrap">
          <table class="data-table" aria-label="Daftar timeline">
            <thead>
              <tr>
                <th>Tipe</th>
                <th>Institusi</th>
                <th>Peran</th>
                <th>Tahun</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!timelineEntries.length">
                <td colspan="5" class="data-table__empty">Belum ada entri timeline.</td>
              </tr>
              <tr v-for="entry in timelineEntries" :key="entry.id">
                <td>
                  <span class="badge" :class="entry.type === 'education' ? 'badge--blue' : 'badge--green'">
                    {{ entry.type === 'education' ? 'Pendidikan' : 'Pengalaman' }}
                  </span>
                </td>
                <td>{{ trans(entry.institution) }}</td>
                <td>{{ trans(entry.role) }}</td>
                <td class="font-mono">{{ entry.start_year }}–{{ entry.end_year ?? 'Sekarang' }}</td>
                <td>
                  <div class="action-btns">
                    <button type="button" class="btn-icon" aria-label="Edit entri" @click="openTimelineForm(entry)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                    <button type="button" class="btn-icon btn-icon--danger" aria-label="Hapus entri"
                            @click="confirmDeleteTimeline(entry)">
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
      </section>

      <!-- ── Skills ────────────────────────────────────────────── -->
      <section class="admin-section">
        <div class="admin-section__header">
          <h2 class="admin-section__title">Keahlian</h2>
          <button type="button" class="btn btn--primary btn--sm" @click="openSkillForm(null)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah
          </button>
        </div>

        <div class="data-table-wrap">
          <table class="data-table" aria-label="Daftar keahlian">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Icon Class</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!skills.length">
                <td colspan="4" class="data-table__empty">Belum ada keahlian.</td>
              </tr>
              <tr v-for="skill in skills" :key="skill.id">
                <td>{{ skill.name }}</td>
                <td>{{ skill.category ?? '—' }}</td>
                <td class="font-mono text-sm">{{ skill.icon_class ?? '—' }}</td>
                <td>
                  <div class="action-btns">
                    <button type="button" class="btn-icon" aria-label="Edit keahlian" @click="openSkillForm(skill)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                    <button type="button" class="btn-icon btn-icon--danger" aria-label="Hapus keahlian"
                            @click="confirmDeleteSkill(skill)">
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
      </section>
    </div>

    <!-- ── Timeline Form Modal ───────────────────────────────── -->
    <Transition name="modal-fade">
      <div v-if="timelineModal.open" class="modal-backdrop" role="dialog" aria-modal="true"
           aria-labelledby="timeline-modal-title" @click.self="closeTimelineForm">
        <div ref="timelineTrapRef" class="modal-panel" @keydown.esc="closeTimelineForm">
          <h3 id="timeline-modal-title" class="modal-title">
            {{ timelineModal.editing ? 'Edit Entri Timeline' : 'Tambah Entri Timeline' }}
          </h3>
          <form @submit.prevent="submitTimeline">
            <div class="form-grid">
              <div class="form-field">
                <label for="tl-type" class="form-label">Tipe</label>
                <select id="tl-type" v-model="timelineForm.type" class="form-input form-select">
                  <option value="education">Pendidikan</option>
                  <option value="experience">Pengalaman</option>
                </select>
              </div>
              <div class="form-field">
                <label for="tl-institution" class="form-label">Institusi</label>
                <input id="tl-institution" v-model="timelineForm.institution" type="text"
                       class="form-input" :class="{ 'form-input--error': timelineForm.errors.institution }"
                       placeholder="Nama institusi/perusahaan" />
                <p v-if="timelineForm.errors.institution" class="form-error">{{ timelineForm.errors.institution }}</p>
              </div>
              <div class="form-field">
                <label for="tl-role" class="form-label">Peran / Jabatan</label>
                <input id="tl-role" v-model="timelineForm.role" type="text"
                       class="form-input" :class="{ 'form-input--error': timelineForm.errors.role }"
                       placeholder="Jurusan / Posisi" />
                <p v-if="timelineForm.errors.role" class="form-error">{{ timelineForm.errors.role }}</p>
              </div>
              <div class="form-field">
                <label for="tl-start" class="form-label">Tahun Mulai</label>
                <input id="tl-start" v-model="timelineForm.start_year" type="number"
                       class="form-input" min="1990" :max="new Date().getFullYear()"
                       :class="{ 'form-input--error': timelineForm.errors.start_year }" />
                <p v-if="timelineForm.errors.start_year" class="form-error">{{ timelineForm.errors.start_year }}</p>
              </div>
              <div class="form-field">
                <label for="tl-end" class="form-label">Tahun Selesai <span class="form-label--optional">(kosongkan jika masih berlangsung)</span></label>
                <input id="tl-end" v-model="timelineForm.end_year" type="number"
                       class="form-input" min="1990" :max="new Date().getFullYear() + 1"
                       placeholder="Sekarang" />
              </div>
              <div class="form-field form-field--full">
                <label for="tl-desc" class="form-label">Deskripsi <span class="form-label--optional">(opsional)</span></label>
                <textarea id="tl-desc" v-model="timelineForm.description" class="form-input form-textarea"
                          rows="3" placeholder="Deskripsi singkat"></textarea>
              </div>
            </div>
            <div class="modal-actions">
              <button type="button" class="btn btn--ghost" @click="closeTimelineForm">Batal</button>
              <button type="submit" class="btn btn--primary" :disabled="timelineForm.processing">
                {{ timelineForm.processing ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ── Skill Form Modal ──────────────────────────────────── -->
    <Transition name="modal-fade">
      <div v-if="skillModal.open" class="modal-backdrop" role="dialog" aria-modal="true"
           aria-labelledby="skill-modal-title" @click.self="closeSkillForm">
        <div ref="skillTrapRef" class="modal-panel" @keydown.esc="closeSkillForm">
          <h3 id="skill-modal-title" class="modal-title">
            {{ skillModal.editing ? 'Edit Keahlian' : 'Tambah Keahlian' }}
          </h3>
          <form @submit.prevent="submitSkill">
            <div class="form-grid">
              <div class="form-field">
                <label for="sk-name" class="form-label">Nama</label>
                <input id="sk-name" v-model="skillForm.name" type="text"
                       class="form-input" :class="{ 'form-input--error': skillForm.errors.name }"
                       placeholder="Contoh: Laravel" />
                <p v-if="skillForm.errors.name" class="form-error">{{ skillForm.errors.name }}</p>
              </div>
              <div class="form-field">
                <label for="sk-category" class="form-label">Kategori <span class="form-label--optional">(opsional)</span></label>
                <input id="sk-category" v-model="skillForm.category" type="text"
                       class="form-input" placeholder="Backend / Frontend / DevOps" />
              </div>
              <div class="form-field form-field--full">
                <label for="sk-icon" class="form-label">Icon Class <span class="form-label--optional">(opsional)</span></label>
                <input id="sk-icon" v-model="skillForm.icon_class" type="text"
                       class="form-input font-mono" placeholder="devicon-laravel-plain" />
              </div>
            </div>
            <div class="modal-actions">
              <button type="button" class="btn btn--ghost" @click="closeSkillForm">Batal</button>
              <button type="submit" class="btn btn--primary" :disabled="skillForm.processing">
                {{ skillForm.processing ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ── Confirm Delete Dialogs ────────────────────────────── -->
    <ConfirmDialog
      :is-open="deleteDialog.open"
      :title="deleteDialog.title"
      :message="deleteDialog.message"
      :loading="deleteDialog.loading"
      @confirm="executeDelete"
      @cancel="deleteDialog.open = false"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue';
import { useFocusTrap } from '@/composables/useFocusTrap.js';

const props = defineProps({
  hero: { type: Object, default: () => ({}) },
  about: { type: Object, default: () => ({}) },
  socialLinks: { type: Object, default: () => ({}) },
  timelineEntries: { type: Array, default: () => [] },
  skills: { type: Array, default: () => [] },
  currentCvPath: { type: String, default: null },
});

// Extract string from translatable field (e.g. {"en": "value"} → "value")
function trans(val) {
  if (!val) return '';
  if (typeof val === 'string') return val;
  if (typeof val === 'object') return val['id'] ?? val['en'] ?? Object.values(val)[0] ?? '';
  return String(val);
}

// ── Hero Form ──────────────────────────────────────────────────
const heroForm = useForm({
  name: props.hero?.name ?? '',
  description: props.hero?.description ?? '',
  professions: props.hero?.professions ?? [''],
});

function addProfession() {
  heroForm.professions.push('');
}

function removeProfession(idx) {
  heroForm.professions.splice(idx, 1);
}

function submitHero() {
  heroForm.put(route('admin.landing.hero.update'), { preserveScroll: true });
}

// ── About Form ─────────────────────────────────────────────────
const aboutForm = useForm({
  bio1:          props.about?.bio1 ?? '',
  bio2:          props.about?.bio2 ?? '',
  stat_years:    props.about?.stat_years ?? '3+',
  stat_projects: props.about?.stat_projects ?? '12',
  stat_commits:  props.about?.stat_commits ?? '1.2K+',
});

function submitAbout() {
  aboutForm.put(route('admin.landing.about.update'), { preserveScroll: true });
}

// ── Social Links Form ──────────────────────────────────────────
const socialForm = useForm({
  linkedin: props.socialLinks?.linkedin ?? '',
  github:   props.socialLinks?.github ?? '',
  email:    props.socialLinks?.email ?? '',
});

function submitSocial() {
  socialForm.put(route('admin.landing.social.update'), { preserveScroll: true });
}

// ── CV Form ────────────────────────────────────────────────────
const cvInputRef = ref(null);
const cvForm = useForm({ cv_file: null });
const cvDeleteForm = useForm({});

function onCvChange(e) {
  cvForm.cv_file = e.target.files?.[0] ?? null;
}

function uploadCv() {
  cvForm.post(route('admin.landing.cv.upload'), {
    preserveScroll: true,
    onSuccess: () => {
      cvForm.reset();
      if (cvInputRef.value) cvInputRef.value.value = '';
    },
  });
}

function deleteCv() {
  cvDeleteForm.delete(route('admin.landing.cv.delete'), { preserveScroll: true });
}

// ── Timeline Modal ─────────────────────────────────────────────
const timelineModal = reactive({ open: false, editing: null });
const timelineForm = useForm({
  type: 'education',
  institution: '',
  role: '',
  start_year: new Date().getFullYear(),
  end_year: '',
  description: '',
});

const { trapRef: timelineTrapRef, activateTrap: activateTimelineTrap, deactivateTrap: deactivateTimelineTrap } = useFocusTrap();

watch(() => timelineModal.open, (open) => {
  if (open) setTimeout(activateTimelineTrap, 50);
  else deactivateTimelineTrap();
});

function openTimelineForm(entry) {
  if (entry) {
    timelineModal.editing = entry;
    timelineForm.type = entry.type;
    timelineForm.institution = trans(entry.institution);
    timelineForm.role = trans(entry.role);
    timelineForm.start_year = entry.start_year;
    timelineForm.end_year = entry.end_year ?? '';
    timelineForm.description = trans(entry.description) ?? '';
  } else {
    timelineModal.editing = null;
    timelineForm.reset();
    timelineForm.type = 'education';
    timelineForm.start_year = new Date().getFullYear();
  }
  timelineModal.open = true;
}

function closeTimelineForm() {
  timelineModal.open = false;
}

function submitTimeline() {
  const editing = timelineModal.editing;
  if (editing) {
    timelineForm.put(route('admin.timeline.update', editing.id), {
      preserveScroll: true,
      onSuccess: closeTimelineForm,
    });
  } else {
    timelineForm.post(route('admin.timeline.store'), {
      preserveScroll: true,
      onSuccess: closeTimelineForm,
    });
  }
}

// ── Skill Modal ────────────────────────────────────────────────
const skillModal = reactive({ open: false, editing: null });
const skillForm = useForm({ name: '', category: '', icon_class: '' });

const { trapRef: skillTrapRef, activateTrap: activateSkillTrap, deactivateTrap: deactivateSkillTrap } = useFocusTrap();

watch(() => skillModal.open, (open) => {
  if (open) setTimeout(activateSkillTrap, 50);
  else deactivateSkillTrap();
});

function openSkillForm(skill) {
  if (skill) {
    skillModal.editing = skill;
    skillForm.name = skill.name;
    skillForm.category = skill.category ?? '';
    skillForm.icon_class = skill.icon_class ?? '';
  } else {
    skillModal.editing = null;
    skillForm.reset();
  }
  skillModal.open = true;
}

function closeSkillForm() {
  skillModal.open = false;
}

function submitSkill() {
  const editing = skillModal.editing;
  if (editing) {
    skillForm.put(route('admin.skills.update', editing.id), {
      preserveScroll: true,
      onSuccess: closeSkillForm,
    });
  } else {
    skillForm.post(route('admin.skills.store'), {
      preserveScroll: true,
      onSuccess: closeSkillForm,
    });
  }
}

// ── Delete Confirm ─────────────────────────────────────────────
const deleteDialog = reactive({
  open: false,
  title: '',
  message: '',
  loading: false,
  type: null,
  target: null,
});

function confirmDeleteTimeline(entry) {
  deleteDialog.type = 'timeline';
  deleteDialog.target = entry;
  deleteDialog.title = 'Hapus Entri Timeline';
  deleteDialog.message = `Hapus entri "${entry.institution}"? Tindakan ini tidak dapat dibatalkan.`;
  deleteDialog.open = true;
}

function confirmDeleteSkill(skill) {
  deleteDialog.type = 'skill';
  deleteDialog.target = skill;
  deleteDialog.title = 'Hapus Keahlian';
  deleteDialog.message = `Hapus keahlian "${skill.name}"? Tindakan ini tidak dapat dibatalkan.`;
  deleteDialog.open = true;
}

function executeDelete() {
  deleteDialog.loading = true;
  const routeName = deleteDialog.type === 'timeline'
    ? route('admin.timeline.destroy', deleteDialog.target.id)
    : route('admin.skills.destroy', deleteDialog.target.id);

  router.delete(routeName, {
    preserveScroll: true,
    onFinish: () => {
      deleteDialog.loading = false;
      deleteDialog.open = false;
    },
  });
}
</script>

<style scoped>
.landing-admin { max-width: 900px; }

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 2rem;
}

.alert {
  padding: 0.875rem 1.125rem;
  border-radius: 8px;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.alert--success {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #34d399;
}

/* Sections */
.admin-section {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.admin-section__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.admin-section__title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 1.25rem;
}

.admin-section__header .admin-section__title { margin-bottom: 0; }

/* Form */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.form-field { display: flex; flex-direction: column; gap: 0.375rem; }
.form-field--full { grid-column: 1 / -1; }

.form-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text);
}

.form-label--optional {
  font-weight: 400;
  color: var(--muted);
  font-size: 0.75rem;
}

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

.form-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}

.form-input--error { border-color: rgba(239, 68, 68, 0.5); }
.form-input--error:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15); }

.form-textarea { resize: vertical; min-height: 80px; }

.form-select { cursor: pointer; }

.form-error { font-size: 0.8rem; color: #f87171; }

.form-input-file {
  font-size: 0.875rem;
  color: var(--muted);
  padding: 0.5rem 0;
}

/* Tag list (professions) */
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

/* CV */
.cv-status { margin-bottom: 1.25rem; }

.cv-current {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 8px;
  color: #34d399;
  font-size: 0.875rem;
}

.cv-empty { font-size: 0.875rem; color: var(--muted); }

.cv-upload-form { display: flex; flex-direction: column; gap: 1rem; }

.btn-danger-sm {
  margin-left: auto;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  color: #f87171;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.25);
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s;
}

.btn-danger-sm:hover { background: rgba(239, 68, 68, 0.18); }
.btn-danger-sm:disabled { opacity: 0.6; cursor: not-allowed; }

/* Table */
.data-table-wrap { overflow-x: auto; }

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.data-table th {
  text-align: left;
  padding: 0.625rem 0.875rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--muted);
  border-bottom: 1px solid var(--border);
}

.data-table td {
  padding: 0.75rem 0.875rem;
  color: var(--text);
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  vertical-align: middle;
}

.data-table__empty {
  text-align: center;
  color: var(--muted);
  padding: 2rem !important;
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.badge--blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.badge--green { background: rgba(16, 185, 129, 0.15); color: #34d399; }

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
  transition: all 0.2s ease;
}

.btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }

.btn--primary {
  background: linear-gradient(135deg, var(--accent, #7c3aed), #4f46e5);
  color: #fff;
}

.btn--primary:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }

.btn--ghost {
  background: transparent;
  color: var(--muted);
  border: 1px solid var(--border);
}

.btn--ghost:hover { color: var(--text); border-color: rgba(255, 255, 255, 0.2); }

.btn--sm { padding: 0.45rem 0.875rem; font-size: 0.8125rem; }

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
}

.modal-panel {
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 1.75rem;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
}

.modal-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 1.5rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.font-mono { font-family: 'Fira Code', monospace; }
.text-sm { font-size: 0.8125rem; }

@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
}
</style>
