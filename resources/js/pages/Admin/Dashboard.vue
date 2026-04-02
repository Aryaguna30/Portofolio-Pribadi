<template>
  <AdminLayout>
    <div class="dashboard">
      <h1 class="dashboard__title">Dashboard</h1>
      <p class="dashboard__subtitle">Selamat datang kembali, {{ $page.props.auth?.user?.name ?? 'Admin' }}</p>

      <!-- Stat cards -->
      <div class="dashboard__grid">
        <!-- Total Projects -->
        <div class="stat-card">
          <div class="stat-card__icon stat-card__icon--purple" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="12 2 2 7 12 12 22 7 12 2"/>
              <polyline points="2 17 12 22 22 17"/>
              <polyline points="2 12 12 17 22 12"/>
            </svg>
          </div>
          <div class="stat-card__body">
            <span class="stat-card__value">{{ stats.totalProjects }}</span>
            <span class="stat-card__label">Total Proyek</span>
          </div>
        </div>

        <!-- Total Messages -->
        <div class="stat-card">
          <div class="stat-card__icon stat-card__icon--cyan" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </div>
          <div class="stat-card__body">
            <span class="stat-card__value">{{ stats.totalMessages }}</span>
            <span class="stat-card__label">Total Pesan</span>
          </div>
        </div>

        <!-- Unread Messages — clickable -->
        <button
          class="stat-card stat-card--clickable"
          :aria-label="`${stats.unreadMessages} pesan belum dibaca, klik untuk melihat`"
          @click="goToMessages"
        >
          <div class="stat-card__icon stat-card__icon--amber" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
          </div>
          <div class="stat-card__body">
            <span class="stat-card__value">
              {{ stats.unreadMessages }}
              <span v-if="stats.unreadMessages > 0" class="stat-card__badge" aria-hidden="true">Baru</span>
            </span>
            <span class="stat-card__label">Pesan Belum Dibaca</span>
          </div>
          <svg class="stat-card__arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </button>
      </div>

      <!-- Quick links -->
      <div class="dashboard__quick">
        <h2 class="dashboard__section-title">Aksi Cepat</h2>
        <div class="quick-links">
          <Link :href="route('admin.projects.create')" class="quick-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Proyek
          </Link>
          <Link :href="route('admin.landing.index')" class="quick-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit Landing Page
          </Link>
          <Link :href="route('admin.messages.index')" class="quick-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            Lihat Pesan
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({ totalProjects: 0, totalMessages: 0, unreadMessages: 0 }),
  },
});

function goToMessages() {
  router.visit(route('admin.messages.index'));
}
</script>

<style scoped>
.dashboard {
  max-width: 900px;
}

.dashboard__title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.25rem;
}

.dashboard__subtitle {
  font-size: 0.9rem;
  color: var(--muted);
  margin-bottom: 2rem;
}

.dashboard__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2.5rem;
}

/* Stat card */
.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  text-align: left;
  position: relative;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.stat-card--clickable {
  cursor: pointer;
  width: 100%;
  font-family: inherit;
  color: inherit;
}

.stat-card--clickable:hover {
  border-color: rgba(245, 158, 11, 0.4);
  box-shadow: 0 0 20px rgba(245, 158, 11, 0.1);
}

.stat-card--clickable:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.stat-card__icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-card__icon--purple {
  background: rgba(124, 58, 237, 0.15);
  color: #a78bfa;
}

.stat-card__icon--cyan {
  background: rgba(6, 182, 212, 0.15);
  color: #22d3ee;
}

.stat-card__icon--amber {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
}

.stat-card__body {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  flex: 1;
}

.stat-card__value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text);
  font-family: 'Fira Code', monospace;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stat-card__badge {
  font-size: 0.65rem;
  font-weight: 700;
  font-family: 'Inter', sans-serif;
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
  padding: 0.1rem 0.45rem;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-card__label {
  font-size: 0.8125rem;
  color: var(--muted);
  font-weight: 500;
}

.stat-card__arrow {
  color: var(--muted);
  flex-shrink: 0;
}

/* Quick links */
.dashboard__section-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 1rem;
}

.quick-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.quick-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.125rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--muted);
  background: var(--bg-card);
  border: 1px solid var(--border);
  text-decoration: none;
  transition: all 0.2s ease;
}

.quick-link:hover {
  color: var(--accent);
  border-color: rgba(124, 58, 237, 0.3);
  background: rgba(124, 58, 237, 0.05);
}

.quick-link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}
</style>
