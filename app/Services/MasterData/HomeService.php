<?php

namespace App\Services\MasterData;

use App\Models\Banner;

class HomeService
{
    /**
     * Get home dashboard statistics.
     *
     * @return array
     */
    public function getActiveBanners(): array
    {
        return Banner::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
