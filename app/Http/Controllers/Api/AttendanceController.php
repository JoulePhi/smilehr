<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAttendanceRequest;
use App\Http\Requests\Api\VerifyFaceRequest;
use App\Http\Requests\Api\VerifyIdCardRequest;
use App\Http\Requests\Api\VerifyPinRequest;
use App\Http\Resources\UserResource;
use App\Models\Attendance;
use App\Models\User;
use App\Services\GeoService;
use App\Services\UploadService;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Psy\CodeCleaner\FunctionContextPass;

class AttendanceController extends Controller
{


    public function __construct(
        private GeoService $geoService,
        private UploadService $uploadService
    ) {}

    public function verifyIdCard(VerifyIdCardRequest $request)
    {
        $user = User::where('employee_id_code', $request->id_card_code)->first();
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'ID Card verified successfully',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'ID Card verification failed'
        ], 404);
    }


    public function verifyPin(VerifyPinRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !password_verify($request->pin, $user->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or PIN'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'PIN verified successfully'
        ]);
    }

    public function verifyFace(VerifyFaceRequest $request)
    {

        $user = User::where('email', $request->email)->first();
        if (!$user || !$user->face_descriptor) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or face descriptor not set'
            ], 404);
        }



        return response()->json([
            'success' => false,
            'message' => 'Face verification failed'
        ], 200);
    }



    public function checkIn(StoreAttendanceRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->first();
            if (! $user || ! Hash::check($request->pin, $user->pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.'],
                    ],
                ], 422);
            }

            // check if already checked in today
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date_in', now()->toDateString())
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already checked in today.'
                ], 409);
            }

            // check if user have schedule today
            if (!$user->hasScheduleToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have a schedule today.'
                ], 403);
            }

            $branch  = $user->branch;

            $distance = $this->geoService->getDistance(
                $request->latitude,
                $request->longitude,
                $branch->latitude,
                $branch->longitude
            );

            if ($distance > $branch->radius) {
                return response()->json([
                    'success' => false,
                    'message' => "Location invalid. You are {$distance}m away. Max allowed: {$branch->radius}m."
                ], 403);
            }

            $storedDescriptor = $user->face_descriptor;
            $liveDescriptor = $request->face_descriptor;
            $storedDescriptor = is_array($storedDescriptor) ? $storedDescriptor : json_decode($storedDescriptor, true);
            $distance = 0;
            foreach ($storedDescriptor as $i => $value) {
                $diff = $value - $liveDescriptor[$i];
                $distance += $diff * $diff;
            }
            $distance = sqrt($distance);

            if ($distance > 0.5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face verification failed'
                ]);
            }

            $image_path = $this->uploadService->uploadBase64(
                $request->image
            );

            Attendance::create([
                'user_id' => $user->id,
                'date_in' => now()->toDateString(),
                'time_in' => now(),
                'lat_in' => $request->latitude,
                'lng_in' => $request->longitude,
                'photo_in' => $image_path
            ]);
            DB::commit();

            $token = $user->createToken('api-token', [
                'user:' . $user->id,
                'roles:' . implode(',', $user->getRoleNames()->toArray()),
                'permissions:' . implode(',', $user->getAllPermissions()->pluck('name')->toArray()),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token->plainTextToken,
                    'expires_at' => null, // Never expires
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance.'
            ], 500);
        }
    }
}
