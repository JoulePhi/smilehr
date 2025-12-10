<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreBannerRequest;
use App\Http\Requests\Api\MasterData\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\HomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{

    public function __construct(
        private HomeService $homeService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $banners = $this->homeService->getActiveBanners();
        return response()->json([
            'success' => true,
            'message' => 'Active banners retrieved successfully.',
            'data' => $banners,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBannerRequest $request)
    {
        try {
            DB::beginTransaction();

            $imagePath = $request->file('image')->store('banners', 'public');
            $banner = Banner::create([
                'title' => $request->input('title'),
                'status' => $request->input('status'),
                'image_path' => $imagePath,
                'link_url' => $request->input('link_url'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Banner created successfully.',
                'data' => $banner,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('banners', 'public');
                $banner->image_path = $imagePath;
            }

            if ($request->has('title')) {
                $banner->title = $request->input('title');
            }

            if ($request->has('status')) {
                $banner->status = $request->input('status');
            }

            if ($request->has('link_url')) {
                $banner->link_url = $request->input('link_url');
            }

            $banner->save();

            DB::commit();
            Log::info('Banner updated: ' . $banner->id);
            return response()->json([
                'success' => true,
                'message' => 'Banner updated successfully.',
                'data' => $banner,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner): JsonResponse
    {
        try {
            $banner->delete();
            Log::info('Banner deleted: ' . $banner->id);
            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner: ' . $e->getMessage(),
            ], 500);
        }
    }
}
