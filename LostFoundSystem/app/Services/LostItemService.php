<?php

namespace App\Services;

use App\Models\LostItem;
use App\Models\User;
use App\Repositories\LostItemRepository;
use Illuminate\Http\UploadedFile;

class LostItemService
{
    public function __construct(
        protected LostItemRepository $repository,
        protected NlpMatchingService $nlp,
        protected AuditLogService $audit,
        protected ItemImage $images,
    ) {}

    public function create(User $user, array $data, ?UploadedFile $image = null): LostItem
    {
        unset($data['image']);
        if ($image) {
            $data = array_merge($data, $this->images->fromUpload($image), ['image_path' => 'database']);
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
        unset($data['image']);
        if ($image) {
            $data = array_merge($data, $this->images->fromUpload($image), ['image_path' => 'database']);
        }

        return $this->repository->update($item, $data);
    }

    public function delete(LostItem $item): void
    {
        $this->repository->delete($item);
    }
}
