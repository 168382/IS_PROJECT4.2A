@extends('layouts.app')
@section('title', 'Search Items — Lost and Found Tracking System')

@section('content')
<div class="report-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="report-card animate-in">
                    <div class="report-header text-center">
                        <h3><i class="fas fa-search text-primary me-2"></i> Search Lost & Found Items</h3>
                        <p>Search the database by keywords, category, color, location, or date.</p>
                    </div>

                    <form method="GET" action="{{ url('/search') }}">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Keyword" value="{{ request('keyword') }}">
                                    <label for="keyword"><i class="fas fa-search me-1"></i> Keyword</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="category_id"><i class="fas fa-folder me-1"></i> Category</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="color" name="color" placeholder="Color" value="{{ request('color') }}">
                                    <label for="color"><i class="fas fa-palette me-1"></i> Color</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="location" name="location" placeholder="Location" value="{{ request('location') }}">
                                    <label for="location"><i class="fas fa-map-marker-alt me-1"></i> Location</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                                    <label for="date"><i class="fas fa-calendar me-1"></i> Date</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary-gradient w-100 h-100" style="min-height:58px;">
                                    <i class="fas fa-search me-2"></i> Search Items
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Results Section -->
                    <div class="mt-4">
                        <ul class="nav nav-tabs mb-3" id="searchTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" id="lost-search-tab" data-bs-toggle="tab" data-bs-target="#lost-search-pane" type="button">
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i> Lost Items ({{ count($lostItems) }})
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" id="found-search-tab" data-bs-toggle="tab" data-bs-target="#found-search-pane" type="button">
                                    <i class="fas fa-hand-holding-heart text-success me-1"></i> Found Items ({{ count($foundItems) }})
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="searchTabsContent">
                            <!-- Lost Items Tab -->
                            <div class="tab-pane fade show active" id="lost-search-pane">
                                @if(count($lostItems) === 0)
                                    <div class="text-center py-4 text-secondary">
                                        <i class="fas fa-search-minus fa-2x mb-2 d-block opacity-50"></i>
                                        No lost items match your query.
                                    </div>
                                @else
                                    <div class="row g-4">
                                        @foreach($lostItems as $item)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 feature-card text-start p-0">
                                                    <!-- Item Image / Banner -->
                                                    <div class="position-relative" style="height: 200px; background: #e2e8f0;">
                                                        @if($item->image_path)
                                                            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->item_name }}" class="w-100 h-100 object-fit-cover" style="cursor:pointer;" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}');">
                                                            <button class="btn btn-sm btn-dark bg-opacity-75 position-absolute bottom-0 end-0 m-2 rounded-pill px-2 py-1 fs-7" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}');">
                                                                <i class="fas fa-search-plus me-1"></i> View Photo
                                                            </button>
                                                        @else
                                                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted bg-light">
                                                                <i class="fas fa-box-open fa-3x mb-2 opacity-50"></i>
                                                                <span class="small">No photo uploaded</span>
                                                            </div>
                                                        @endif
                                                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 shadow-sm">
                                                            {{ $item->category ? $item->category->name : 'Lost Item' }}
                                                        </span>
                                                    </div>

                                                    <div class="card-body p-3 d-flex flex-column">
                                                        <h5 class="fw-bold mb-1">{{ $item->item_name }}</h5>
                                                        <p class="text-secondary small mb-3 text-truncate-2">{{ $item->description }}</p>

                                                        <div class="mt-auto pt-2 border-top">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $item->location_lost }}</small>
                                                                <small class="text-muted"><i class="fas fa-calendar me-1"></i> {{ $item->date_lost }}</small>
                                                            </div>
                                                            @if($item->color || $item->brand)
                                                                <div class="d-flex gap-1 mt-2">
                                                                    @if($item->color)<span class="badge bg-light text-dark border"><i class="fas fa-palette me-1"></i>{{ $item->color }}</span>@endif
                                                                    @if($item->brand)<span class="badge bg-light text-dark border"><i class="fas fa-tag me-1"></i>{{ $item->brand }}</span>@endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Found Items Tab -->
                            <div class="tab-pane fade" id="found-search-pane">
                                @if(count($foundItems) === 0)
                                    <div class="text-center py-4 text-secondary">
                                        <i class="fas fa-search-minus fa-2x mb-2 d-block opacity-50"></i>
                                        No found items match your query.
                                    </div>
                                @else
                                    <div class="row g-4">
                                        @foreach($foundItems as $item)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 feature-card text-start p-0">
                                                    <!-- Item Image / Banner -->
                                                    <div class="position-relative" style="height: 200px; background: #e2e8f0;">
                                                        @if($item->image_path)
                                                            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->item_name }}" class="w-100 h-100 object-fit-cover" style="cursor:pointer;" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}');">
                                                            <button class="btn btn-sm btn-dark bg-opacity-75 position-absolute bottom-0 end-0 m-2 rounded-pill px-2 py-1 fs-7" onclick="openImageModal('{{ asset('storage/'.$item->image_path) }}', '{{ e($item->item_name) }}');">
                                                                <i class="fas fa-search-plus me-1"></i> View Photo
                                                            </button>
                                                        @else
                                                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted bg-light">
                                                                <i class="fas fa-hand-holding-heart fa-3x mb-2 opacity-50"></i>
                                                                <span class="small">No photo uploaded</span>
                                                            </div>
                                                        @endif
                                                        <span class="badge bg-success position-absolute top-0 start-0 m-3 shadow-sm">
                                                            {{ $item->category ? $item->category->name : 'Found Item' }}
                                                        </span>
                                                    </div>

                                                    <div class="card-body p-3 d-flex flex-column">
                                                        <h5 class="fw-bold mb-1">{{ $item->item_name }}</h5>
                                                        <p class="text-secondary small mb-3 text-truncate-2">{{ $item->description }}</p>

                                                        <div class="mt-auto pt-2 border-top">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <small class="text-muted"><i class="fas fa-map-marker-alt text-success me-1"></i> {{ $item->location_found }}</small>
                                                                <small class="text-muted"><i class="fas fa-calendar me-1"></i> {{ $item->date_found }}</small>
                                                            </div>
                                                            @if($item->status === 'found' && (int) $item->user_id !== (int) session('auth_user_id'))
                                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 w-100" onclick="openClaimModal({{ $item->id }}, @js($item->item_name))">
                                                                    <i class="fas fa-file-signature me-1"></i> Claim Item
                                                                </button>
                                                            @elseif($item->status !== 'found')
                                                                <span class="btn btn-sm btn-light border w-100 text-muted disabled"><i class="fas fa-check-circle me-1"></i> No longer available</span>
                                                            @else
                                                                <span class="btn btn-sm btn-light border w-100 text-muted disabled">Reported by you</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
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
