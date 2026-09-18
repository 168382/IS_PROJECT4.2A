<div class="modal fade" id="claimModal" tabindex="-1" aria-labelledby="claimModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <span class="text-uppercase text-primary fw-bold" style="font-size: .72rem; letter-spacing: .08em;">Secure verification</span>
                    <h5 class="modal-title fw-bold mt-1" id="claimModalTitle">Claim an item</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('claims.submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="found_item_id" id="claim-found-item-id" value="{{ old('found_item_id') }}">
                <div class="modal-body px-4 py-3">
                    <p class="text-secondary small">Tell the administrator why <strong id="claim-item-name">this item</strong> belongs to you. Include details not visible in the listing, such as its contents, serial number, or identifying marks.</p>

                    <div class="mb-3">
                        <label for="verification_info" class="form-label fw-semibold">Proof of ownership <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('verification_info') is-invalid @enderror" id="verification_info" name="verification_info" rows="5" minlength="20" maxlength="2000" required placeholder="Describe the details that verify ownership...">{{ old('verification_info') }}</textarea>
                        <div class="form-text">At least 20 characters. Do not include passwords or financial details.</div>
                        @error('verification_info')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-1">
                        <label for="proof_image" class="form-label fw-semibold">Supporting photo <span class="text-muted fw-normal">(optional)</span></label>
                        <input class="form-control @error('proof_image') is-invalid @enderror" type="file" id="proof_image" name="proof_image" accept="image/*">
                        @error('proof_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient rounded-3"><i class="fas fa-shield-alt me-1"></i> Submit for review</button>
                </div>
            </form>
        </div>
    </div>
</div>
