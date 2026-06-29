<?php

namespace App\Notifications;

use App\Models\UmkmProductCategory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUmkmCategoryCreated extends Notification
{
    use Queueable;

    public function __construct(public UmkmProductCategory $category, public User $owner) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'umkm_category_created',
            'title' => __('Kategori baru dibuat'),
            'body' => __(':owner menambahkan kategori ":name". Mohon lengkapi model 3D bila perlu.', [
                'owner' => $this->owner->name,
                'name' => translateValue($this->category->name),
            ]),
            'category_id' => $this->category->id,
            'owner_user_id' => $this->owner->id,
            'action_url' => route('admin.umkm.categories').'#category-'.$this->category->id,
        ];
    }
}
