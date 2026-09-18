@extends('layouts.app')
@section('title', 'Claim Verification — Admin Panel')

@section('content')
<div class="admin-shell">

    {{-- Sidebar --}}
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <div class="admin-brand-icon"><i class="fas fa-user-shield"></i></div>
            <div>
                <div class="admin-brand-title">{{ ($userData['role'] ?? '') === 'admin' ? 'Admin Panel' : 'Staff Portal' }}</div>
                <div class="admin-brand-sub">{{ $userData['name'] ?? 'Administrator' }}</div>
            </div>
        </div>
        <nav class="admin-nav">
            @if(($userData['role'] ?? '') === 'admin')
                <a href="{{ url('/admin') }}" class="admin-nav-item">
                    <i class="fas fa-tachometer-alt"></i> <span>Overview</span>
                </a>
            @endif
            <a href="{{ url('/admin/claims') }}" class="admin-nav-item active">
                <i class="fas fa-tasks"></i> <span>Claims</span>
            </a>
            @if(($userData['role'] ?? '') === 'admin')
                <a href="{{ url('/admin/items') }}" class="admin-nav-item">
                    <i class="fas fa-boxes"></i> <span>All Items</span>
                </a>
                <a href="{{ url('/admin/users') }}" class="admin-nav-item">
                    <i class="fas fa-users-cog"></i> <span>Users</span>
                </a>
            @endif
            <div class="admin-nav-divider"></div>
            <a href="{{ url('/dashboard') }}" class="admin-nav-item">
                <i class="fas fa-arrow-left"></i> <span>Exit Admin</span>
            </a>
        </nav>
    </aside>

    {{-- Main --}}
    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 class="admin-page-title">Claim Verification</h1>
                <p class="admin-page-sub">Review proof of ownership before approving or rejecting every claim.</p>
            </div>
            <div class="d-flex gap-2">
                @php $pending = $claims->where('status','pending')->count(); @endphp
                @if($pending > 0)
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-7">
                        <i class="fas fa-clock me-1"></i> {{ $pending }} Pending
                    </span>
                @else
                    <span class="badge bg-success rounded-pill px-3 py-2 fs-7">
                        <i class="fas fa-check me-1"></i> All Resolved
                    </span>
                @endif
            </div>
        </div>

        {{-- Flash --}}
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

        {{-- Filter Tabs --}}
        <ul class="nav nav-pills mb-3 gap-2" id="claimTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active rounded-3 px-3 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-pending" type="button">Pending ({{ $claims->where('status','pending')->count() }})</button></li>
            <li class="nav-item"><button class="nav-link rounded-3 px-3 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-approved" type="button">Approved ({{ $claims->where('status','approved')->count() }})</button></li>
            <li class="nav-item"><button class="nav-link rounded-3 px-3 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-rejected" type="button">Rejected ({{ $claims->where('status','rejected')->count() }})</button></li>
        </ul>

        <div class="tab-content">
            @foreach(['pending' => 'tab-pending', 'approved' => 'tab-approved', 'rejected' => 'tab-rejected'] as $status => $tabId)
                <div class="tab-pane fade {{ $status === 'pending' ? 'show active' : '' }}" id="{{ $tabId }}">
                    @php $filtered = $claims->where('status', $status)->sortByDesc('created_at')->values(); @endphp
                    <div class="admin-card">
                        @if($filtered->isEmpty())
                            <div class="empty-state py-5">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No {{ $status }} claims.</p>
                            </div>
                        @else
                            <div class="row g-4 p-4">
                                @foreach($filtered as $claim)
                                    <div class="col-lg-6">
                                        <div class="claim-card {{ $status === 'pending' ? 'claim-pending' : ($status === 'approved' ? 'claim-approved' : 'claim-rejected') }}">
                                            {{-- Header --}}
                                            <div class="claim-header">
                                                <div>
                                                    <span class="claim-id">#CLM-{{ $claim->id }}</span>
                                                    @if($status === 'pending')
                                                        <span class="badge bg-warning text-dark ms-2">Awaiting Review</span>
                                                    @elseif($status === 'approved')
                                                        <span class="badge bg-success ms-2">Approved</span>
                                                    @else
                                                        <span class="badge bg-danger ms-2">Rejected</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $claim->created_at }}</small>
                                            </div>

                                            {{-- Body --}}
                                            <div class="claim-body">
                                                {{-- Item Photo --}}
                                                <div class="claim-photo-wrap">
                                                    @if($claim->foundItem && $claim->foundItem->image_path)
                                                        <img src="{{ asset('storage/'.$claim->foundItem->image_path) }}"
                                                             alt="{{ $claim->foundItem->item_name }}"
                                                             class="claim-photo"
                                                             onclick="openImageModal('{{ asset('storage/'.$claim->foundItem->image_path) }}', '{{ e($claim->foundItem->item_name) }}')"
                                                             title="Click to enlarge">
                                                        <span class="claim-photo-label">Click to enlarge</span>
                                                    @else
                                                        <div class="claim-photo-placeholder">
                                                            <i class="fas fa-image"></i>
                                                            <span>No photo</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Details --}}
                                                <div class="claim-details">
                                                    <div class="claim-section-label">Found Item</div>
                                                    <div class="claim-item-name">{{ $claim->foundItem ? $claim->foundItem->item_name : 'Item #'.$claim->found_item_id }}</div>
                                                    @if($claim->foundItem)
                                                        <div class="claim-meta"><i class="fas fa-map-marker-alt text-success me-1"></i> {{ $claim->foundItem->location_found ?? '—' }}</div>
                                                        <div class="claim-meta"><i class="fas fa-calendar me-1 text-muted"></i> Found {{ $claim->foundItem->date_found ?? '—' }}</div>
                                                    @endif

                                                    <div class="claim-divider"></div>

                                                    <div class="claim-section-label">Claimant</div>
                                                    <div class="claim-item-name">{{ $claim->user ? $claim->user->name : 'User #'.$claim->user_id }}</div>
                                                    <div class="claim-meta"><i class="fas fa-envelope me-1 text-muted"></i> {{ $claim->user ? $claim->user->email : '—' }}</div>

                                                    @if($claim->verification_info)
                                                        <div class="claim-divider"></div>
                                                        <div class="claim-section-label">Verification Info</div>
                                                        <p class="claim-meta">{{ $claim->verification_info }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Actions (only for pending) --}}
                                            @if($status === 'pending')
                                                <div class="claim-actions">
                                                    <form method="POST" action="{{ route('admin.claims.approve', $claim->id) }}" class="d-inline flex-grow-1">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success w-100 rounded-3 fw-semibold" onclick="return confirm('Approve this claim? User will be notified.')">
                                                            <i class="fas fa-check me-1"></i> Approve Claim
                                                        </button>
                                                    </form>

                                                    <button class="btn btn-outline-danger rounded-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $claim->id }}">
                                                        <i class="fas fa-times me-1"></i> Reject
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Reject Modal --}}
                                    @if($status === 'pending')
                                        <div class="modal fade" id="rejectModal{{ $claim->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 rounded-4">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title fw-bold text-danger"><i class="fas fa-times-circle me-2"></i> Reject Claim #CLM-{{ $claim->id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.claims.reject', $claim->id) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p class="text-secondary small">Provide a reason for rejection. This will be sent to the claimant as a notification.</p>
                                                            <div class="form-floating">
                                                                <textarea class="form-control" name="reason" id="reason{{ $claim->id }}" placeholder="Reason" style="height:100px;"></textarea>
                                                                <label>Rejection Reason (optional)</label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger rounded-3 fw-semibold">
                                                                <i class="fas fa-times me-1"></i> Confirm Rejection
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </main>
</div>

{{-- Lightbox Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalImageTitle">Item Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modalImage" src="" alt="Item Image" class="img-fluid rounded-3 shadow border" style="max-height:500px; width:100%; object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openImageModal(url, title) {
    document.getElementById('modalImage').src = url;
    document.getElementById('modalImageTitle').textContent = title || 'Item Photo';
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
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
.admin-brand-title { font-weight: 700; font-size: 0.95rem; }
.admin-brand-sub { font-size: 0.7rem; opacity: 0.6; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
.admin-nav { display: flex; flex-direction: column; gap: 0.2rem; }
.admin-nav-item {
    display: flex; align-items: center; gap: 0.7rem; padding: 0.6rem 0.8rem; border-radius: 8px;
    color: rgba(255,255,255,0.65); text-decoration: none; font-size: 0.88rem; font-weight: 500; transition: all 0.2s;
}
.admin-nav-item:hover, .admin-nav-item.active { background: rgba(255,255,255,0.12); color: #fff; }
.admin-nav-item.active { background: linear-gradient(135deg, rgba(59,130,246,0.35), rgba(6,182,212,0.25)); }
.admin-nav-item i { width: 18px; text-align: center; }
.admin-nav-divider { border-top: 1px solid rgba(255,255,255,0.1); margin: 0.5rem 0; }
.admin-main { flex: 1; padding: 2rem; overflow: auto; }
.admin-topbar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; }
.admin-page-title { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; }
.admin-page-sub { color: #64748b; font-size: 0.88rem; margin: 0; }
.admin-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); overflow: hidden; }
.empty-state { text-align: center; padding: 3rem; }

.claim-card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border-top: 4px solid #e2e8f0; }
.claim-pending { border-top-color: #f59e0b; }
.claim-approved { border-top-color: #10b981; }
.claim-rejected { border-top-color: #ef4444; }
.claim-header { display: flex; justify-content: space-between; align-items: center; padding: 0.9rem 1rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.claim-id { font-weight: 800; font-size: 0.9rem; color: #1e293b; }
.claim-body { display: flex; gap: 1rem; padding: 1rem; }
.claim-photo-wrap { flex-shrink: 0; width: 110px; }
.claim-photo { width: 110px; height: 110px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; cursor: pointer; display: block; transition: transform 0.2s; }
.claim-photo:hover { transform: scale(1.04); }
.claim-photo-label { display: block; text-align: center; font-size: 0.65rem; color: #94a3b8; margin-top: 3px; }
.claim-photo-placeholder {
    width: 110px; height: 110px; border-radius: 10px; border: 2px dashed #e2e8f0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: #cbd5e1; font-size: 1.5rem; gap: 4px;
}
.claim-photo-placeholder span { font-size: 0.7rem; }
.claim-details { flex: 1; overflow: hidden; }
.claim-section-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 2px; }
.claim-item-name { font-weight: 700; font-size: 0.95rem; color: #1e293b; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.claim-meta { font-size: 0.8rem; color: #64748b; margin-bottom: 2px; }
.claim-divider { border-top: 1px solid #f1f5f9; margin: 0.5rem 0; }
.claim-actions { display: flex; gap: 0.5rem; padding: 0.75rem 1rem; border-top: 1px solid #f1f5f9; background: #fafafa; }
@media (max-width: 768px) { .admin-sidebar { display: none; } .admin-main { padding: 1rem; } }
</style>
@endsection
