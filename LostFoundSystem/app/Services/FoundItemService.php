<?php

namespace App\Services;

use App\Models\FoundItem;
use App\Models\User;
use App\Repositories\FoundItemRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FoundItemService
{
    public function __construct(
        protected FoundItemRepository $repository,
        protected NlpMatchingService $nlp,
        protected AuditLogService $audit,
    ) {}

    public function create(User $user, array $data, ?UploadedFile $image = null): FoundItem
    {
        if ($image) {
            $data['image_path'] = $image->store('items/found', 'public');
        }

        $data['user_id'] = $user->id;
        $data['status'] = 'found';

        /** @var FoundItem $item */
        $item = $this->repository->create($data);

        $this->nlp->matchFoundItem($item);
        $this->audit->log($user->id, 'report_found_item', FoundItem::class, $item->id, $item->item_name, request());

        return $item;
    }

    public function update(FoundItem $item, array $data, ?UploadedFile $image = null): FoundItem
    {
        if ($image) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $image->store('items/found', 'public');
        }

        return $this->repository->update($item, $data);
    }

    public function delete(FoundItem $item): void
    {
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $this->repository->delete($item);
    }
}
