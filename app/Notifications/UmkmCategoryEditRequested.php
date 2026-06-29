<?php

namespace App\Notifications;

use App\Models\UmkmProductCategory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UmkmCategoryEditRequested extends Notification
{
    use Queueable;

    public function __construct(
        public UmkmProductCategory $category,
        public User $owner,
        public string $note,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'umkm_category_edit_request',
            'title' => __('Permintaan edit kategori'),
            'body' => __(':owner meminta edit kategori ":name": :note', [
                'owner' => $this->owner->name,
                'name' => translateValue($this->category->name),
                'note' => mb_strimwidth($this->note, 0, 120, '…'),
            ]),
            'category_id' => $this->category->id,
            'owner_user_id' => $this->owner->id,
            'note' => $this->note,
            'action_url' => route('admin.umkm.categories').'#category-'.$this->category->id,
        ];
    }
}
