<?php

namespace App\Livewire;

use App\Models\ShopItem;
use App\Services\ShopService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tienda')]
class Shop extends Component
{
    public function purchase(int $itemId, ShopService $shop): void
    {
        $item = ShopItem::findOrFail($itemId);

        if ($shop->purchase(auth()->user(), $item)) {
            session()->flash('status', 'Compraste: '.$item->name);
        } else {
            session()->flash('error', 'Gemas insuficientes.');
        }
    }

    public function apply(int $userItemId, ShopService $shop): void
    {
        $userItem = auth()->user()->userItems()->findOrFail($userItemId);
        $shop->apply(auth()->user(), $userItem);
        session()->flash('status', 'Item aplicado.');
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.shop', [
            'items' => ShopItem::where('is_active', true)->get(),
            'owned' => $user->userItems()->with('item')->where('quantity', '>', 0)->get(),
            'wallet' => $user->gemsWallet,
        ]);
    }
}
