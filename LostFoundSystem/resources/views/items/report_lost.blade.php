@extends('layouts.app')
@section('title', 'Report Lost Item — Lost and Found Tracking System')

@section('content')
<div class="report-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="report-card animate-in">
                    <div class="report-header">
                        <h3><i class="fas fa-exclamation-triangle text-warning me-2"></i> Report a Lost Item</h3>
                        <p>Provide as much detail as possible to help the NLP engine find a match.</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/report/lost') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <!-- Item Name -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="item_name" name="item_name" placeholder="Item Name" value="{{ old('item_name') }}" required>
                                    <label for="item_name"><i class="fas fa-tag me-1"></i> Item Name</label>
                                </div>
                            </div>
                            <!-- Category -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select category...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="category_id"><i class="fas fa-folder me-1"></i> Category</label>
                                </div>
                            </div>
                            <!-- Color -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="color" name="color" placeholder="Color" value="{{ old('color') }}">
                                    <label for="color"><i class="fas fa-palette me-1"></i> Color</label>
                                </div>
                            </div>
                            <!-- Brand -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" name="brand" placeholder="Brand" value="{{ old('brand') }}">
                                    <label for="brand"><i class="fas fa-copyright me-1"></i> Brand</label>
                                </div>
                            </div>
                            <!-- Location -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="location_lost" name="location_lost" placeholder="Location" value="{{ old('location_lost') }}" required>
                                    <label for="location_lost"><i class="fas fa-map-marker-alt me-1"></i> Where did you lose it?</label>
                                </div>
                            </div>
                            <!-- Date Lost -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="date_lost" name="date_lost" value="{{ old('date_lost', date('Y-m-d')) }}" required>
                                    <label for="date_lost"><i class="fas fa-calendar me-1"></i> Date Lost</label>
                                </div>
                            </div>
                            <!-- Description -->
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="description" name="description" placeholder="Description" style="height:120px;" required>{{ old('description') }}</textarea>
                                    <label for="description"><i class="fas fa-align-left me-1"></i> Detailed Description</label>
                                </div>
                                <small class="text-secondary">Include distinguishing features: scratches, stickers, engravings, content, etc.</small>
                            </div>
                            <!-- Image Upload & Preview -->
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary" style="font-size:0.88rem;"><i class="fas fa-camera me-1"></i> Upload Image (optional)</label>
                                <div class="image-upload-zone" onclick="document.getElementById('image').click();">
                                    <i class="fas fa-cloud-upload-alt d-block"></i>
                                    <p id="upload-label">Click to upload or drag & drop an image</p>
                                    <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="handleImagePreview(this);">
                                </div>
                                <div id="image-preview-container" class="mt-3 text-center d-none">
                                    <div class="position-relative d-inline-block">
                                        <img id="image-preview" src="#" alt="Lost Item Image Preview" class="img-fluid rounded-3 shadow-sm border" style="max-height:250px; width:100%; object-fit:cover;">
                                        <span class="badge bg-dark position-absolute top-0 end-0 m-2">Preview</span>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="clearImagePreview();">
                                            <i class="fas fa-trash me-1"></i> Remove Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Submit -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-gradient w-100 py-3">
                                    <i class="fas fa-paper-plane me-2"></i> Submit Lost Item Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function handleImagePreview(input) {
    const previewContainer = document.getElementById('image-preview-container');
    const previewImage = document.getElementById('image-preview');
    const label = document.getElementById('upload-label');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        label.textContent = file.name;

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}

function clearImagePreview() {
    const input = document.getElementById('image');
    const previewContainer = document.getElementById('image-preview-container');
    const label = document.getElementById('upload-label');

    input.value = '';
    label.textContent = 'Click to upload or drag & drop an image';
    previewContainer.classList.add('d-none');
}
</script>
@endsection
@endsection
