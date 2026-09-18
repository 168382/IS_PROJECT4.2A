<?php

namespace App\Services;

use App\Models\LostItem;
use App\Models\User;
use App\Repositories\LostItemRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LostItemService
{
    public function __construct(
        protected LostItemRepository $repository,
        protected NlpMatchingService $nlp,
        protected AuditLogService $audit,
    ) {}

    public function create(User $user, array $data, ?UploadedFile $image = null): LostItem
    {
        if ($image) {
            $data['image_path'] = $image->store('items/lost', 'public');
        }

        $data['user_id'] = $user->id;
        $data['status'] = 'lost';

        /** @var LostItem $item */
        $item = $this->repository->create($data);

        $this->nlp->matchLostItem($item);
        $this->audit->log($user->id, 'report_lost_item', LostItem::class, $item->id, $item->item_name, request());

        return $item;
    }

    public function update(LostItem $item, array $data, ?UploadedFile $image = null): LostItem
    {
        if ($image) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $image->store('items/lost', 'public');
        }

        return $this->repository->update($item, $data);
    }

    public function delete(LostItem $item): void
    {
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $this->repository->delete($item);
    }
}
