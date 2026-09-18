@extends('layouts.app')
@section('title', 'Dashboard — Lost and Found Tracking System')

@section('content')
<div class="container py-4">
    <!-- Welcome Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1a56db 0%, #06b6d4 100%);">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-semibold mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                        <i class="fas fa-user-circle me-1"></i> {{ ucfirst($user['role'] ?? 'User') }} Dashboard
                    </span>
                    <h2 class="fw-bold mb-1">Welcome back, {{ $user['name'] ?? 'User' }}!</h2>
                    <p class="mb-0 text-white-50">{{ $user['email'] ?? '' }} — Manage your reported items, track claims, and review NLP matches.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ url('/report/lost') }}" class="btn btn-light me-2 fw-semibold px-3 py-2 rounded-3 shadow-sm">
                        <i class="fas fa-plus-circle me-1 text-danger"></i> Report Lost
                    </a>
                    <a href="{{ url('/report/found') }}" class="btn btn-outline-light fw-semibold px-3 py-2 rounded-3">
                        <i class="fas fa-plus-circle me-1 text-success"></i> Report Found
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-3 me-3 fs-4">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary mb-1 text-uppercase fw-semibold" style="font-size:0.75rem;">Lost Items</h6>
                        <h3 class="fw-bold mb-0">{{ count($lostItems) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 me-3 fs-4">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary mb-1 text-uppercase fw-semibold" style="font-size:0.75rem;">Found Items</h6>
                        <h3 class="fw-bold mb-0">{{ count($foundItems) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 me-3 fs-4">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary mb-1 text-uppercase fw-semibold" style="font-size:0.75rem;">My Claims</h6>
                        <h3 class="fw-bold mb-0">{{ count($claims) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 me-3 fs-4">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary mb-1 text-uppercase fw-semibold" style="font-size:0.75rem;">Notifications</h6>
                        <h3 class="fw-bold mb-0">{{ $unreadNotificationCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="dashboardTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active rounded-3 px-4 fw-semibold" id="lost-tab" data-bs-toggle="tab" data-bs-target="#lost-pane" type="button">
                <i class="fas fa-exclamation-circle me-1"></i> My Lost Items ({{ count($lostItems) }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-3 px-4 fw-semibold" id="found-tab" data-bs-toggle="tab" data-bs-target="#found-pane" type="button">
                <i class="fas fa-hand-holding me-1"></i> My Found Items ({{ count($foundItems) }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-3 px-4 fw-semibold" id="matches-tab" data-bs-toggle="tab" data-bs-target="#matches-pane" type="button">
                <i class="fas fa-magic me-1"></i> Potential Matches ({{ count($matches) }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-3 px-4 fw-semibold" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims-pane" type="button">
                <i class="fas fa-file-signature me-1"></i> Claims ({{ count($claims) }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-3 px-4 fw-semibold" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications-pane" type="button">
                <i class="fas fa-bell me-1"></i> Notifications @if($unreadNotificationCount > 0)<span class="badge bg-danger rounded-pill ms-1">{{ $unreadNotificationCount }}</span>@endif
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="dashboardTabsContent">
        
        <!-- My Lost Items -->
        <div class="tab-pane fade show active" id="lost-pane">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Items You Reported Lost</h5>
                    <a href="{{ url('/report/lost') }}" class="btn btn-sm btn-primary-gradient"><i class="fas fa-plus me-1"></i> Report New</a>
                </div>
                @if(count($lostItems) === 0)
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-secondary">You haven't reported any lost items yet.</p>
                        <a href="{{ url('/report/lost') }}" class="btn btn-outline-primary rounded-3">Report Lost Item</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Photo</th>
                                    <th>Item Details</th>
                                    <th>Category</th>
                                    <th>Location Lost</th>
                                    <th>Date Lost</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lostItems as $item)
                                    <tr>
                                        <td style="width: 80px;">
                                            @if($item->image_path)
                                                <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->item_name }}" class="rounded-3 shadow-sm border cursor-pointer" style="width:60px;height:60px;object-fit:cover;" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}');" title="Click to enlarge photo">
                                            @else
                                                <div class="rounded-3 bg-light text-muted border d-flex flex-column align-items-center justify-content-center" style="width:60px;height:60px;">
                                                    <i class="fas fa-image"></i>
                                                    <span style="font-size:0.65rem;">No photo</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="d-block text-dark fs-6">{{ $item->item_name }}</strong>
                                            <small class="text-muted d-block">{{ Str::limit($item->description, 50) }}</small>
                                            @if($item->color || $item->brand)
                                                <div class="mt-1">
                                                    @if($item->color)<span class="badge bg-light text-dark border me-1"><i class="fas fa-palette me-1"></i>{{ $item->color }}</span>@endif
                                                    @if($item->brand)<span class="badge bg-light text-dark border"><i class="fas fa-tag me-1"></i>{{ $item->brand }}</span>@endif
                                                </div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $item->category ? $item->category->name : 'N/A' }}</span></td>
                                        <td><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $item->location_lost }}</td>
                                        <td>{{ $item->date_lost }}</td>
                                        <td>
                                            @if($item->status == 'lost')
                                                <span class="badge bg-warning text-dark">Lost</span>
                                            @elseif($item->status == 'retrieved')
                                                <span class="badge bg-success">Retrieved</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- My Found Items -->
        <div class="tab-pane fade" id="found-pane">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-hand-holding-heart text-success me-2"></i> Items You Reported Found</h5>
                    <a href="{{ url('/report/found') }}" class="btn btn-sm btn-success"><i class="fas fa-plus me-1"></i> Report New</a>
                </div>
                @if(count($foundItems) === 0)
                    <div class="text-center py-5">
                        <i class="fas fa-hand-holding-heart fa-3x text-muted mb-3"></i>
                        <p class="text-secondary">You haven't reported any found items yet.</p>
                        <a href="{{ url('/report/found') }}" class="btn btn-outline-success rounded-3">Report Found Item</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Photo</th>
                                    <th>Item Details</th>
                                    <th>Category</th>
                                    <th>Location Found</th>
                                    <th>Date Found</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($foundItems as $item)
                                    <tr>
                                        <td style="width: 80px;">
                                            @if($item->image_path)
                                                <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->item_name }}" class="rounded-3 shadow-sm border cursor-pointer" style="width:60px;height:60px;object-fit:cover;" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}');" title="Click to enlarge photo">
                                            @else
                                                <div class="rounded-3 bg-light text-muted border d-flex flex-column align-items-center justify-content-center" style="width:60px;height:60px;">
                                                    <i class="fas fa-image"></i>
                                                    <span style="font-size:0.65rem;">No photo</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="d-block text-dark fs-6">{{ $item->item_name }}</strong>
                                            <small class="text-muted d-block">{{ Str::limit($item->description, 50) }}</small>
                                            @if($item->color || $item->brand)
                                                <div class="mt-1">
                                                    @if($item->color)<span class="badge bg-light text-dark border me-1"><i class="fas fa-palette me-1"></i>{{ $item->color }}</span>@endif
                                                    @if($item->brand)<span class="badge bg-light text-dark border"><i class="fas fa-tag me-1"></i>{{ $item->brand }}</span>@endif
                                                </div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $item->category ? $item->category->name : 'N/A' }}</span></td>
                                        <td><i class="fas fa-map-marker-alt text-success me-1"></i> {{ $item->location_found }}</td>
                                        <td>{{ $item->date_found }}</td>
                                        <td>
                                            @if($item->status == 'found')
                                                <span class="badge bg-info text-dark">Available</span>
                                            @elseif($item->status == 'claimed')
                                                <span class="badge bg-success">Claimed</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Potential Matches -->
        <div class="tab-pane fade" id="matches-pane">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-magic text-primary me-2"></i> NLP Matching Results</h5>
                @if(count($matches) === 0)
                    <div class="text-center py-5">
                        <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                        <p class="text-secondary">No automated matches generated yet. Report items to see smart AI matching!</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($matches as $match)
                            <div class="col-md-6 col-lg-4">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 feature-card text-start p-0">
                                    <!-- Matched Found Item Photo -->
                                    <div class="position-relative" style="height: 180px; background: #e2e8f0;">
                                        @if($match->foundItem && $match->foundItem->image_path)
                                            <img src="{{ asset('storage/'.$match->foundItem->image_path) }}" alt="{{ $match->foundItem->item_name }}" class="w-100 h-100 object-fit-cover cursor-pointer" onclick="openImageModal('{{ asset('storage/'.$match->foundItem->image_path) }}', '{{ e($match->foundItem->item_name) }}');">
                                            <button class="btn btn-sm btn-dark bg-opacity-75 position-absolute bottom-0 end-0 m-2 rounded-pill px-2 py-1 fs-7" onclick="openImageModal('{{ asset('storage/'.$match->foundItem->image_path) }}', '{{ e($match->foundItem->item_name) }}');">
                                                <i class="fas fa-search-plus me-1"></i> View Photo
                                            </button>
                                        @else
                                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted bg-light">
                                                <i class="fas fa-hand-holding-heart fa-3x mb-2 opacity-50"></i>
                                                <span class="small">No photo uploaded</span>
                                            </div>
                                        @endif
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-3 shadow-sm fs-7">
                                            <i class="fas fa-brain me-1"></i> {{ $match->similarity_score }}% Match Score
                                        </span>
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column">
                                        <h6 class="fw-bold mb-1">Found Item: {{ $match->foundItem ? $match->foundItem->item_name : 'Item' }}</h6>
                                        <p class="text-secondary small mb-2">Matched for your lost: <strong>{{ $match->lostItem ? $match->lostItem->item_name : 'Item' }}</strong></p>

                                        @if($match->foundItem && $match->foundItem->status === 'found' && (int) $match->foundItem->user_id !== (int) $user['id'])
                                            <button type="button" class="btn btn-sm btn-primary-gradient w-100 rounded-3 mt-auto pt-2" onclick="openClaimModal({{ $match->foundItem->id }}, @js($match->foundItem->item_name))">
                                                <i class="fas fa-file-signature me-1"></i> Submit Claim
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Claims -->
        <div class="tab-pane fade" id="claims-pane">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-file-signature text-info me-2"></i> Your Item Claims</h5>
                @if(count($claims) === 0)
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-secondary">You haven't submitted any item claims yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Found Item Photo</th>
                                    <th>Found Item</th>
                                    <th>Status</th>
                                    <th>Submitted On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($claims as $claim)
                                    <tr>
                                        <td>#CLM-{{ $claim->id }}</td>
                                        <td style="width: 70px;">
                                            @if($claim->foundItem && $claim->foundItem->image_path)
                                                <img src="{{ asset('storage/'.$claim->foundItem->image_path) }}" alt="{{ $claim->foundItem->item_name }}" class="rounded-3 shadow-sm border cursor-pointer" style="width:50px;height:50px;object-fit:cover;" onclick="openImageModal('{{ asset('storage/'.$claim->foundItem->image_path) }}', '{{ e($claim->foundItem->item_name) }}');">
                                            @else
                                                <div class="rounded-3 bg-light text-muted border d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td><strong>{{ $claim->foundItem ? $claim->foundItem->item_name : 'N/A' }}</strong></td>
                                        <td>
                                            @if($claim->status == 'pending')
                                                <span class="badge bg-warning text-dark">Pending Review</span>
                                            @elseif($claim->status == 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($claim->status == 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($claim->status) }}</span>
                                            @endif
                                        </td>
                                        <td><small class="text-muted">{{ $claim->created_at }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notifications -->
        <div class="tab-pane fade" id="notifications-pane">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-bell text-primary me-2"></i> Notifications</h5>
                    @if($unreadNotificationCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary rounded-3"><i class="fas fa-check-double me-1"></i> Mark all read</button>
                        </form>
                    @endif
                </div>
                @if(count($notifications) === 0)
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <p class="text-secondary">No notifications found.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notif)
                            <div class="list-group-item px-0 py-3 border-bottom {{ empty($notif->is_read) ? 'bg-primary bg-opacity-10' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-1 {{ empty($notif->is_read) ? 'text-primary' : 'text-dark' }}">
                                        {{ $notif->title }}
                                    </h6>
                                    <small class="text-muted">{{ $notif->created_at }}</small>
                                </div>
                                <p class="text-secondary mb-2 small">{{ $notif->message }}</p>
                                <div class="d-flex gap-2">
                                    @if($notif->related_link)
                                        <a href="{{ route('notifications.open', $notif->id) }}" class="btn btn-sm btn-outline-primary rounded-3">View details</a>
                                    @endif
                                    @if(empty($notif->is_read))
                                        <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-light border rounded-3">Mark read</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@include('components.claim-modal')

<!-- Image Lightbox Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalImageTitle">Item Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modalImage" src="" alt="Full Item Image" class="img-fluid rounded-3 shadow border" style="max-height: 500px; width: 100%; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function openImageModal(imageUrl, itemTitle) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalImageTitle').textContent = itemTitle || 'Item Photo';
    const myModal = new bootstrap.Modal(document.getElementById('imageModal'));
    myModal.show();
}

function openClaimModal(itemId, itemName) {
    document.getElementById('claim-found-item-id').value = itemId;
    document.getElementById('claim-item-name').textContent = itemName || 'this item';
    new bootstrap.Modal(document.getElementById('claimModal')).show();
}

@if($errors->has('verification_info') || $errors->has('proof_image'))
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('claimModal')).show();
});
@endif
</script>
@endsection
@endsection
