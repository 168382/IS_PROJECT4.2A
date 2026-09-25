@extends('layouts.app')
@section('title', 'Admin Control Panel — Lost and Found System')

@section('content')
<div class="admin-shell">

    @include('components.admin-top-nav', ['userData' => $userData, 'pendingClaims' => $pendingClaims])

    {{-- ── Main Content ─────────────────────────────────── --}}
    <main class="admin-main">

        {{-- Header --}}
        <div class="admin-topbar">
            <div>
                <h1 class="admin-page-title">System Overview</h1>
                <p class="admin-page-sub">Lost & Found Tracking — Real-time platform dashboard</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <div class="btn-group" role="group" aria-label="Download administrative reports">
                    <a href="{{ route('admin.reports.pdf') }}" class="btn btn-sm btn-outline-primary rounded-start-3"><i class="fas fa-file-pdf me-1"></i> PDF</a>
                    <a href="{{ route('admin.reports.excel') }}" class="btn btn-sm btn-outline-success rounded-end-3"><i class="fas fa-file-excel me-1"></i> Excel</a>
                </div>
                <span class="badge bg-success rounded-pill px-3 py-2 fs-7">
                    <i class="fas fa-circle me-1" style="font-size:0.55rem;"></i> System Online
                </span>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="admin-kpi-card kpi-blue">
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-value">{{ count($allUsers) }}</div>
                        <div class="kpi-label">Registered Users</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="admin-kpi-card kpi-amber">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-value">{{ count($lostItems) }}</div>
                        <div class="kpi-label">Lost Items Reported</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="admin-kpi-card kpi-green">
                    <div class="kpi-icon"><i class="fas fa-hand-holding-heart"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-value">{{ count($foundItems) }}</div>
                        <div class="kpi-label">Found Items Reported</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="admin-kpi-card kpi-red">
                    <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                    <div class="kpi-body">
                        <div class="kpi-value">{{ $pendingClaims }}</div>
                        <div class="kpi-label">Pending Claims</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="admin-stat-card text-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success mb-2"><i class="fas fa-check-double"></i></div>
                    <div class="stat-value text-success">{{ $approvedClaims }}</div>
                    <div class="stat-label">Approved Claims</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-stat-card text-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger mb-2"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-value text-danger">{{ $rejectedClaims }}</div>
                    <div class="stat-label">Rejected Claims</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-stat-card text-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="fas fa-box-open"></i></div>
                    <div class="stat-value text-primary">{{ $resolvedItems }}</div>
                    <div class="stat-label">Items Retrieved</div>
                </div>
            </div>
        </div>

        {{-- Pending Claims Quick Table --}}
        @php $pendingList = $claims->where('status','pending')->take(5)->values(); @endphp
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="admin-card h-100">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title"><i class="fas fa-exclamation-circle text-warning me-2"></i> Pending Claims Awaiting Review</h5>
                        <a href="{{ url('/admin/claims') }}" class="btn btn-sm btn-outline-primary rounded-3">View All</a>
                    </div>
                    @if($pendingList->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check fa-2x text-success mb-2"></i>
                            <p class="text-success fw-semibold mb-0">All claims are resolved!</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Claimant</th>
                                        <th>Item</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingList as $claim)
                                        <tr>
                                            <td><span class="fw-bold text-primary">#CLM-{{ $claim->id }}</span></td>
                                            <td>{{ $claim->user ? $claim->user->name : 'User #'.$claim->user_id }}</td>
                                            <td>{{ $claim->foundItem ? $claim->foundItem->item_name : 'N/A' }}</td>
                                            <td><small class="text-muted">{{ $claim->created_at }}</small></td>
                                            <td>
                                                <a href="{{ url('/admin/claims') }}" class="btn btn-xs btn-warning text-dark px-2 py-1 rounded-3" style="font-size:0.75rem;">
                                                    <i class="fas fa-eye me-1"></i> Review
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Audit Log --}}
            <div class="col-lg-5">
                <div class="admin-card h-100">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title"><i class="fas fa-history text-secondary me-2"></i> Audit Log</h5>
                    </div>
                    @if(count($recentAudits) === 0)
                        <div class="empty-state">
                            <i class="fas fa-scroll fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No activity recorded yet.</p>
                        </div>
                    @else
                        <div class="audit-timeline">
                            @foreach($recentAudits->take(12) as $log)
                                <div class="audit-item">
                                    <div class="audit-dot"></div>
                                    <div class="audit-body">
                                        <div class="audit-action">{{ str_replace('_', ' ', $log->action) }}</div>
                                        <div class="audit-meta">
                                            User #{{ $log->user_id }}
                                            @if($log->details) — {{ Str::limit($log->details, 40) }}@endif
                                        </div>
                                        <div class="audit-time">{{ $log->created_at }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- User Distribution --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-users text-primary me-2"></i> User Directory</h5>
                <a href="{{ url('/admin/users') }}" class="btn btn-sm btn-outline-primary rounded-3">Manage Users</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allUsers as $u)
                            <tr>
                                <td><small class="text-muted">#{{ $u['id'] }}</small></td>
                                <td><strong>{{ $u['name'] }}</strong></td>
                                <td><small class="text-secondary">{{ $u['email'] }}</small></td>
                                <td>
                                    @if($u['role'] === 'admin')
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($u['role'] === 'staff')
                                        <span class="badge bg-info text-dark">Staff</span>
                                    @else
                                        <span class="badge bg-secondary">Student</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $u['created_at'] }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
@endsection

@section('scripts')
<style>
.admin-shell { display: flex; min-height: 100vh; background: #f0f4ff; }
.admin-sidebar {
    width: 240px; min-height: 100vh; background: linear-gradient(180deg, #0f172a 0%, #1e3a5f 100%);
    color: #fff; display: flex; flex-direction: column; padding: 1.5rem 1rem; position: sticky; top: 0;
    flex-shrink: 0; box-shadow: 4px 0 20px rgba(0,0,0,0.18);
}
.admin-brand { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; }
.admin-brand-icon { width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #06b6d4);
    border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.admin-brand-title { font-weight: 700; font-size: 0.95rem; line-height: 1.1; }
.admin-brand-sub { font-size: 0.7rem; opacity: 0.6; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
.admin-nav { display: flex; flex-direction: column; gap: 0.2rem; }
.admin-nav-item {
    display: flex; align-items: center; gap: 0.7rem; padding: 0.6rem 0.8rem; border-radius: 8px;
    color: rgba(255,255,255,0.65); text-decoration: none; font-size: 0.88rem; font-weight: 500;
    transition: all 0.2s; position: relative;
}
.admin-nav-item:hover, .admin-nav-item.active {
    background: rgba(255,255,255,0.12); color: #fff;
}
.admin-nav-item.active { background: linear-gradient(135deg, rgba(59,130,246,0.35), rgba(6,182,212,0.25)); }
.admin-nav-item i { width: 18px; text-align: center; }
.sidebar-badge {
    margin-left: auto; background: #ef4444; color: #fff;
    font-size: 0.7rem; border-radius: 999px; padding: 1px 7px; font-weight: 700;
}
.admin-nav-divider { border-top: 1px solid rgba(255,255,255,0.1); margin: 0.5rem 0; }
.admin-main { flex: 1; padding: 2rem; overflow: auto; }
.admin-topbar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; }
.admin-page-title { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; }
.admin-page-sub { color: #64748b; font-size: 0.88rem; margin: 0; }
.admin-kpi-card {
    background: #fff; border-radius: 16px; padding: 1.4rem; display: flex; align-items: center;
    gap: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.07); border: 1px solid rgba(0,0,0,0.04);
}
.kpi-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
.kpi-blue .kpi-icon { background: rgba(59,130,246,0.12); color: #3b82f6; }
.kpi-amber .kpi-icon { background: rgba(245,158,11,0.12); color: #f59e0b; }
.kpi-green .kpi-icon { background: rgba(16,185,129,0.12); color: #10b981; }
.kpi-red .kpi-icon { background: rgba(239,68,68,0.12); color: #ef4444; }
.kpi-value { font-size: 1.9rem; font-weight: 800; line-height: 1; color: #0f172a; }
.kpi-label { font-size: 0.78rem; color: #64748b; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }
.admin-stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin: 0 auto; }
.stat-value { font-size: 1.7rem; font-weight: 800; margin-top: 0.5rem; }
.stat-label { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.admin-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); overflow: hidden; }
.admin-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; }
.admin-card-title { font-weight: 700; font-size: 0.95rem; margin: 0; }
.admin-card .table-responsive { padding: 0 0.5rem 0.5rem; }
.audit-timeline { padding: 1rem 1.2rem; max-height: 320px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.75rem; }
.audit-item { display: flex; gap: 0.75rem; }
.audit-dot { width: 10px; height: 10px; border-radius: 50%; background: linear-gradient(135deg,#3b82f6,#06b6d4); flex-shrink: 0; margin-top: 5px; }
.audit-action { font-weight: 600; font-size: 0.82rem; color: #1e293b; text-transform: capitalize; }
.audit-meta { font-size: 0.75rem; color: #64748b; }
.audit-time { font-size: 0.7rem; color: #94a3b8; }
.empty-state { text-align: center; padding: 2rem 1rem; }
.btn-xs { font-size: 0.75rem !important; }
@media (max-width: 768px) {
    .admin-sidebar { display: none; }
    .admin-main { padding: 1rem; }
}
</style>
@endsection
