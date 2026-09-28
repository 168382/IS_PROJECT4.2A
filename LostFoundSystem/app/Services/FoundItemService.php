<?php

namespace App\Services;

use App\Models\FoundItem;
use App\Models\User;
use App\Repositories\FoundItemRepository;
use Illuminate\Http\UploadedFile;

class FoundItemService
{
    public function __construct(
        protected FoundItemRepository $repository,
        protected NlpMatchingService $nlp,
        protected AuditLogService $audit,
        protected ItemImage $images,
    ) {}

    public function create(User $user, array $data, ?UploadedFile $image = null): FoundItem
    {
        unset($data['image']);
        if ($image) {
            $data = array_merge($data, $this->images->fromUpload($image), ['image_path' => 'database']);
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
        unset($data['image']);
        if ($image) {
            $data = array_merge($data, $this->images->fromUpload($image), ['image_path' => 'database']);
        }

        return $this->repository->update($item, $data);
    }

    public function delete(FoundItem $item): void
    {
        $this->repository->delete($item);
    }
}
