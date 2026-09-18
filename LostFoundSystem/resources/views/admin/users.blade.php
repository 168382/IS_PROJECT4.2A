@extends('layouts.app')
@section('title', 'User Management — Admin Panel')

@section('content')
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <div class="admin-brand-icon"><i class="fas fa-user-shield"></i></div>
            <div>
                <div class="admin-brand-title">Admin Panel</div>
                <div class="admin-brand-sub">{{ $userData['name'] ?? 'Administrator' }}</div>
            </div>
        </div>
        <nav class="admin-nav">
            <a href="{{ url('/admin') }}" class="admin-nav-item"><i class="fas fa-tachometer-alt"></i> <span>Overview</span></a>
            <a href="{{ url('/admin/claims') }}" class="admin-nav-item"><i class="fas fa-tasks"></i> <span>Claims</span></a>
            <a href="{{ url('/admin/items') }}" class="admin-nav-item"><i class="fas fa-boxes"></i> <span>All Items</span></a>
            <a href="{{ url('/admin/users') }}" class="admin-nav-item active"><i class="fas fa-users-cog"></i> <span>Users</span></a>
            <div class="admin-nav-divider"></div>
            <a href="{{ url('/dashboard') }}" class="admin-nav-item"><i class="fas fa-arrow-left"></i> <span>Exit Admin</span></a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 class="admin-page-title">User Management</h1>
                <p class="admin-page-sub">Assign roles and manage platform access for all registered users.</p>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2 fs-7">{{ count($allUsers) }} Total Users</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-users text-primary me-2"></i> All Registered Users</h5>
            </div>
            <div class="table-responsive p-2">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th class="text-end">Change Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allUsers as $u)
                            <tr>
                                <td><small class="text-muted">#{{ $u['id'] }}</small></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar">{{ strtoupper(substr($u['name'],0,1)) }}</div>
                                        <strong>{{ $u['name'] }}</strong>
                                    </div>
                                </td>
                                <td><small class="text-secondary">{{ $u['email'] }}</small></td>
                                <td>
                                    @if($u['role'] === 'admin')
                                        <span class="badge bg-danger px-3 py-2">Admin</span>
                                    @elseif($u['role'] === 'staff')
                                        <span class="badge bg-info text-dark px-3 py-2">Staff</span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">Student</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $u['created_at'] }}</small></td>
                                <td class="text-end">
                                    @if((int)$userData['id'] !== (int)$u['id'])
                                        <form method="POST" action="{{ route('admin.users.role', $u['id']) }}" class="d-inline-flex align-items-center gap-2">
                                            @csrf
                                            <select name="role" class="form-select form-select-sm rounded-3" style="width:130px;">
                                                <option value="student" {{ $u['role']==='student' ? 'selected':'' }}>Student</option>
                                                <option value="staff"   {{ $u['role']==='staff'   ? 'selected':'' }}>Staff</option>
                                                <option value="admin"   {{ $u['role']==='admin'   ? 'selected':'' }}>Admin</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary rounded-3" aria-label="Save role for {{ $u['name'] }}" title="Save role">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-dark px-2 py-1" title="You cannot change your own role">
                                            <i class="fas fa-lock me-1"></i> You
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="alert alert-info rounded-3 mt-4 d-flex align-items-start gap-3">
            <i class="fas fa-info-circle fs-5 text-info mt-1"></i>
            <div>
                <strong>Role Permissions</strong>
                <ul class="mb-0 mt-1 small text-secondary">
                    <li><strong>Admin</strong> — Full access: approve/reject claims, delete items, manage all users.</li>
                    <li><strong>Staff</strong> — Can review and process claims; cannot change user roles.</li>
                    <li><strong>Student</strong> — Can report lost/found items and submit claims only.</li>
                </ul>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<style>
.admin-shell{display:flex;min-height:100vh;background:#f0f4ff}
.admin-sidebar{width:240px;min-height:100vh;background:linear-gradient(180deg,#0f172a 0%,#1e3a5f 100%);color:#fff;display:flex;flex-direction:column;padding:1.5rem 1rem;position:sticky;top:0;flex-shrink:0;box-shadow:4px 0 20px rgba(0,0,0,.18)}
.admin-brand{display:flex;align-items:center;gap:.75rem;margin-bottom:2rem}
.admin-brand-icon{width:40px;height:40px;background:linear-gradient(135deg,#3b82f6,#06b6d4);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.admin-brand-title{font-weight:700;font-size:.95rem}.admin-brand-sub{font-size:.7rem;opacity:.6}
.admin-nav{display:flex;flex-direction:column;gap:.2rem}
.admin-nav-item{display:flex;align-items:center;gap:.7rem;padding:.6rem .8rem;border-radius:8px;color:rgba(255,255,255,.65);text-decoration:none;font-size:.88rem;font-weight:500;transition:all .2s}
.admin-nav-item:hover,.admin-nav-item.active{background:rgba(255,255,255,.12);color:#fff}
.admin-nav-item.active{background:linear-gradient(135deg,rgba(59,130,246,.35),rgba(6,182,212,.25))}
.admin-nav-item i{width:18px;text-align:center}
.admin-nav-divider{border-top:1px solid rgba(255,255,255,.1);margin:.5rem 0}
.admin-main{flex:1;padding:2rem;overflow:auto}
.admin-topbar{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem}
.admin-page-title{font-size:1.6rem;font-weight:800;color:#0f172a;margin:0}
.admin-page-sub{color:#64748b;font-size:.88rem;margin:0}
.admin-card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden}
.admin-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.1rem 1.4rem;border-bottom:1px solid #f1f5f9}
.admin-card-title{font-weight:700;font-size:.95rem;margin:0}
.user-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#06b6d4);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0}
@media(max-width:768px){.admin-sidebar{display:none}.admin-main{padding:1rem}}
</style>
@endsection
