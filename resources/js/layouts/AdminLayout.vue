<template>
  <div class="admin-layout min-h-screen flex">
    <!-- Sidebar -->
    <aside
      class="admin-sidebar"
      :class="{ 'admin-sidebar--open': sidebarOpen }"
      aria-label="Admin navigation"
    >
      <!-- Logo -->
      <div class="admin-sidebar__logo">
        <span class="logo-bracket">&lt;</span>
        <span class="logo-text">NA</span>
        <span class="logo-bracket">/&gt;</span>
        <span class="admin-sidebar__logo-label">Admin</span>
      </div>

      <!-- Nav links -->
      <nav class="admin-sidebar__nav">
        <Link
          :href="route('admin.dashboard')"
          class="admin-nav-link"
          :class="{ active: isActive('admin.dashboard') }"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
          </svg>
          Dashboard
        </Link>

        <Link
          :href="route('admin.landing.index')"
          class="admin-nav-link"
          :class="{ active: isActive('admin.landing.index') }"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Landing Page
        </Link>

        <Link
          :href="route('admin.projects.index')"
          class="admin-nav-link"
          :class="{ active: isActive('admin.projects.index') }"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
            <polyline points="2 17 12 22 22 17"/>
            <polyline points="2 12 12 17 22 12"/>
          </svg>
          Proyek
        </Link>

        <Link
          :href="route('admin.messages.index')"
          class="admin-nav-link"
          :class="{ active: isActive('admin.messages.index') }"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          Pesan
          <span v-if="unreadCount > 0" class="admin-nav-link__badge">{{ unreadCount }}</span>
        </Link>
      </nav>

      <!-- Sidebar footer: logout -->
      <div class="admin-sidebar__footer">
        <div class="admin-sidebar__user">
          <span class="admin-sidebar__user-name">{{ $page.props.auth?.user?.name ?? 'Admin' }}</span>
          <span class="admin-sidebar__user-email">{{ $page.props.auth?.user?.email ?? '' }}</span>
        </div>
        <Link
          :href="route('admin.logout')"
          method="post"
          as="button"
          class="admin-logout-btn"
          aria-label="Logout"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          Logout
        </Link>
      </div>
    </aside>

    <!-- Main content area -->
    <div class="admin-main flex-1 flex flex-col min-w-0">
      <!-- Top bar (mobile) -->
      <header class="admin-topbar">
        <button
          class="admin-topbar__hamburger"
          :aria-expanded="sidebarOpen"
          aria-label="Toggle sidebar"
          @click="sidebarOpen = !sidebarOpen"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
        <span class="admin-topbar__title">Admin Panel</span>
      </header>

      <!-- Page content slot -->
      <main class="admin-content flex-1 p-6">
        <slot />
      </main>
    </div>

    <!-- Sidebar overlay (mobile) -->
    <div
      v-if="sidebarOpen"
      class="admin-sidebar-overlay"
      aria-hidden="true"
      @click="sidebarOpen = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

const { theme } = useTheme();
const page = usePage();
const sidebarOpen = ref(false);

const unreadCount = computed(() => page.props.unreadMessages ?? 0);

function isActive(routeName) {
  try {
    return route().current(routeName);
  } catch {
    return false;
  }
}
</script>

<style scoped>
.admin-layout {
  background: var(--bg);
  color: var(--text);
  font-family: 'Inter', sans-serif;
}

.admin-sidebar {
  width: 240px;
  min-height: 100vh;
  background: var(--bg-card);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  flex-shrink: 0;
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 50;
}

.admin-sidebar__logo {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 1.5rem 1.25rem 1rem;
  font-family: 'Fira Code', monospace;
  font-size: 1rem;
  font-weight: 700;
  border-bottom: 1px solid var(--border);
}

.logo-bracket { color: var(--accent); }
.logo-text {
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.admin-sidebar__logo-label {
  font-family: 'Inter', sans-serif;
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-left: 0.25rem;
}

.admin-sidebar__nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 1rem 0.75rem;
  overflow-y: auto;
}

.admin-nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.875rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--muted);
  transition: all 0.2s ease;
  position: relative;
  text-decoration: none;
}

.admin-nav-link:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.05);
}

.admin-nav-link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.admin-nav-link.active {
  color: var(--accent);
  background: rgba(124, 58, 237, 0.1);
  border: 1px solid rgba(124, 58, 237, 0.2);
}

.admin-nav-link__badge {
  margin-left: auto;
  background: var(--accent);
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.1rem 0.45rem;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.admin-sidebar__footer {
  padding: 1rem 0.75rem;
  border-top: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.admin-sidebar__user {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0 0.25rem;
}

.admin-sidebar__user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text);
}

.admin-sidebar__user-email {
  font-size: 0.75rem;
  color: var(--muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.admin-logout-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.875rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--muted);
  transition: all 0.2s ease;
  width: 100%;
  cursor: pointer;
  background: none;
  border: none;
  font-family: inherit;
  text-align: left;
}

.admin-logout-btn:hover {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.08);
}

.admin-logout-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.admin-topbar {
  display: none;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1.25rem;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 40;
}

.admin-topbar__hamburger {
  color: var(--muted);
  padding: 0.25rem;
  border-radius: 6px;
  transition: color 0.2s;
  background: none;
  border: none;
  cursor: pointer;
}

.admin-topbar__hamburger:hover { color: var(--text); }
.admin-topbar__hamburger:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.admin-topbar__title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text);
}

.admin-content {
  background: var(--bg);
}

.admin-sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 49;
}

@media (max-width: 767px) {
  .admin-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    transform: translateX(-100%);
  }

  .admin-sidebar--open {
    transform: translateX(0);
  }

  .admin-topbar {
    display: flex;
  }

  .admin-sidebar-overlay {
    display: block;
  }
}
</style>
