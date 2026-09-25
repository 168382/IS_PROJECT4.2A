<nav class="admin-top-navigation">
    <div class="container-fluid px-3 px-lg-4">
        <div class="d-flex align-items-center justify-content-between py-2">
            <a href="{{ url('/admin') }}" class="admin-top-brand">
                <span class="admin-top-brand-icon"><i class="fas fa-user-shield"></i></span>
                <span>
                    <strong>{{ ($userData['role'] ?? '') === 'admin' ? 'Admin Panel' : 'Staff Portal' }}</strong>
                    <small>{{ $userData['name'] ?? 'Administrator' }}</small>
                </span>
            </a>
            <button class="btn admin-top-menu-button d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminTopNavigation" aria-controls="adminTopNavigation" aria-expanded="false" aria-label="Toggle admin navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse d-lg-flex justify-content-lg-end" id="adminTopNavigation">
                <div class="admin-top-links pt-3 pt-lg-0">
                    @if(($userData['role'] ?? '') === 'admin')
                        <a href="{{ url('/admin') }}" class="{{ request()->is('admin') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Overview</a>
                    @endif
                    <a href="{{ url('/admin/claims') }}" class="{{ request()->is('admin/claims') ? 'active' : '' }}"><i class="fas fa-tasks"></i> Claims @if(($pendingClaims ?? 0) > 0)<span class="admin-top-badge">{{ $pendingClaims }}</span>@endif</a>
                    @if(($userData['role'] ?? '') === 'admin')
                        <a href="{{ url('/admin/items') }}" class="{{ request()->is('admin/items') ? 'active' : '' }}"><i class="fas fa-boxes"></i> Items</a>
                        <a href="{{ url('/admin/users') }}" class="{{ request()->is('admin/users') ? 'active' : '' }}"><i class="fas fa-users-cog"></i> Users</a>
                        <a href="{{ url('/admin/machine-learning') }}" class="{{ request()->is('admin/machine-learning') ? 'active' : '' }}"><i class="fas fa-brain"></i> ML Matching</a>
                    @endif
                    <a href="{{ url('/dashboard') }}" class="admin-top-exit"><i class="fas fa-arrow-left"></i> Exit Admin</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.admin-shell { display: block !important; }
.admin-top-navigation { background: linear-gradient(135deg, #0f172a, #1e3a5f); box-shadow: 0 2px 14px rgba(15, 23, 42, 0.22); position: sticky; top: 0; z-index: 1040; }
.admin-top-brand { color: #fff; display: inline-flex; align-items: center; gap: 0.7rem; text-decoration: none; }
.admin-top-brand:hover { color: #fff; }
.admin-top-brand-icon { background: linear-gradient(135deg, #3b82f6, #06b6d4); width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; }
.admin-top-brand strong, .admin-top-brand small { display: block; line-height: 1.2; }
.admin-top-brand strong { font-size: 0.95rem; }
.admin-top-brand small { font-size: 0.7rem; color: rgba(255,255,255,0.65); max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.admin-top-menu-button { color: #fff; border-color: rgba(255,255,255,0.3); }
.admin-top-menu-button:hover { color: #fff; border-color: rgba(255,255,255,0.7); }
.admin-top-links { display: flex; align-items: center; gap: 0.35rem; }
.admin-top-links a { color: rgba(255,255,255,0.72); text-decoration: none; font-size: 0.88rem; font-weight: 600; padding: 0.55rem 0.8rem; border-radius: 8px; transition: background-color 0.2s, color 0.2s; }
.admin-top-links a:hover, .admin-top-links a.active { color: #fff; background: rgba(255,255,255,0.14); }
.admin-top-links .admin-top-exit { margin-left: 0.5rem; border: 1px solid rgba(255,255,255,0.28); }
.admin-top-badge { background: #ef4444; border-radius: 999px; color: #fff; font-size: 0.68rem; margin-left: 0.25rem; padding: 1px 6px; }
@media (max-width: 991.98px) {
    .admin-top-links { align-items: stretch; flex-direction: column; padding-bottom: 0.65rem; }
    .admin-top-links a { padding: 0.65rem 0.8rem; }
    .admin-top-links .admin-top-exit { margin: 0.35rem 0 0; }
}
</style>
