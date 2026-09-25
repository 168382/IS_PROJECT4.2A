@extends('layouts.app')
@section('title', 'Item Management — Admin Panel')

@section('content')
<div class="admin-shell">
    @include('components.admin-top-nav', ['userData' => $userData])

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 class="admin-page-title">Item Management</h1>
                <p class="admin-page-sub">Review and remove all reported lost and found items in the system.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <ul class="nav nav-pills mb-3 gap-2">
            <li class="nav-item"><button class="nav-link active rounded-3 px-3 fw-semibold" data-bs-toggle="tab" data-bs-target="#lost-tab" type="button">
                <i class="fas fa-exclamation-triangle text-warning me-1"></i> Lost Items ({{ count($lostItems) }})
            </button></li>
            <li class="nav-item"><button class="nav-link rounded-3 px-3 fw-semibold" data-bs-toggle="tab" data-bs-target="#found-tab" type="button">
                <i class="fas fa-hand-holding-heart text-success me-1"></i> Found Items ({{ count($foundItems) }})
            </button></li>
        </ul>

        <div class="tab-content">
            {{-- Lost Items --}}
            <div class="tab-pane fade show active" id="lost-tab">
                <div class="admin-card">
                    @if($lostItems->isEmpty())
                        <div class="empty-state py-5"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No lost items reported.</p></div>
                    @else
                        <div class="table-responsive p-2">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr><th>Photo</th><th>Item</th><th>Location</th><th>Date</th><th>Status</th><th>Reported By</th><th class="text-end">Remove</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($lostItems as $item)
                                        <tr>
                                            <td>
                                                @if($item->image_path)
                                                    <img src="{{ asset('storage/'.$item->image_path) }}" class="rounded-2 border" style="width:48px;height:48px;object-fit:cover;cursor:pointer;" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}')">
                                                @else
                                                    <div class="rounded-2 bg-light border text-muted d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-image"></i></div>
                                                @endif
                                            </td>
                                            <td><strong>{{ $item->item_name }}</strong><br><small class="text-muted">{{ Str::limit($item->description, 40) }}</small></td>
                                            <td><small><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $item->location_lost }}</small></td>
                                            <td><small class="text-muted">{{ $item->date_lost }}</small></td>
                                            <td><span class="badge {{ $item->status === 'retrieved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($item->status) }}</span></td>
                                            <td><small class="text-muted">User #{{ $item->user_id }}</small></td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.items.lost.delete', $item->id) }}" onsubmit="return confirm('Permanently delete this item?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Found Items --}}
            <div class="tab-pane fade" id="found-tab">
                <div class="admin-card">
                    @if($foundItems->isEmpty())
                        <div class="empty-state py-5"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No found items reported.</p></div>
                    @else
                        <div class="table-responsive p-2">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr><th>Photo</th><th>Item</th><th>Location</th><th>Date</th><th>Status</th><th>Reported By</th><th class="text-end">Remove</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($foundItems as $item)
                                        <tr>
                                            <td>
                                                @if($item->image_path)
                                                    <img src="{{ asset('storage/'.$item->image_path) }}" class="rounded-2 border" style="width:48px;height:48px;object-fit:cover;cursor:pointer;" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}')">
                                                @else
                                                    <div class="rounded-2 bg-light border text-muted d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-image"></i></div>
                                                @endif
                                            </td>
                                            <td><strong>{{ $item->item_name }}</strong><br><small class="text-muted">{{ Str::limit($item->description, 40) }}</small></td>
                                            <td><small><i class="fas fa-map-marker-alt text-success me-1"></i> {{ $item->location_found }}</small></td>
                                            <td><small class="text-muted">{{ $item->date_found }}</small></td>
                                            <td><span class="badge {{ $item->status === 'claimed' ? 'bg-success' : 'bg-info text-dark' }}">{{ ucfirst($item->status) }}</span></td>
                                            <td><small class="text-muted">User #{{ $item->user_id }}</small></td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.items.found.delete', $item->id) }}" onsubmit="return confirm('Permanently delete this item?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="modalImageTitle">Item Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center p-4">
                <img id="modalImage" src="" alt="Item" class="img-fluid rounded-3 shadow border" style="max-height:500px;width:100%;object-fit:contain;">
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
.empty-state{text-align:center;padding:3rem}
@media(max-width:768px){.admin-sidebar{display:none}.admin-main{padding:1rem}}
</style>
@endsection
