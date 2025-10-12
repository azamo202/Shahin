<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BaseLandController extends Controller
{
    /**
     * إرجاع response ناجح
     */
    protected function successResponse($data = null, string $message = '', int $code = 200): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * إرجاع response فاشل
     */
    protected function errorResponse(string $message, int $code = 500, $errors = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * تسجيل الأخطاء
     */
    protected function logError(\Exception $e, string $context): void
    {
        Log::error("{$context}: {$e->getMessage()}", [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * تنسيق بيانات قائمة الأرض
     */
    protected function formatLandListingData($landListing): array
    {
        return [
            'id' => $landListing->id,
            'title' => $landListing->title,
            'land_type' => $landListing->land_type,
            'location' => $landListing->location,
            'area' => $landListing->area,
            'description' => $landListing->description,
            'deed_image' => $landListing->deed_image,
            'purpose' => $landListing->purpose,
            'price_per_meter' => $landListing->price_per_meter,
            'investment_start' => $landListing->investment_start ? $landListing->investment_start->format('Y-m-d') : null,
            'investment_end' => $landListing->investment_end ? $landListing->investment_end->format('Y-m-d') : null,
            'investment_estimated_value' => $landListing->investment_estimated_value,
            'real_estate_announcement_no' => $landListing->real_estate_announcement_no,
            'no_dispute_confirmed' => $landListing->no_dispute_confirmed,
            'user' => [
                'id' => $landListing->user->id,
                'full_name' => $landListing->user->full_name,
                'email' => $landListing->user->email,
            ],
            'created_at' => $landListing->created_at ? $landListing->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $landListing->updated_at ? $landListing->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }


    /**
     * تنسيق بيانات قائمة الأرض المختصرة
     */
    protected function formatLandListingSummary($landListing): array
    {
        return [
            'id' => $landListing->id,
            'title' => $landListing->title,
            'land_type' => $landListing->land_type,
            'location' => $landListing->location,
            'area' => $landListing->area,
            'purpose' => $landListing->purpose,
            'price_per_meter' => $landListing->price_per_meter,
            'investment_estimated_value' => $landListing->investment_estimated_value,
            'created_at' => $landListing->created_at,
        ];
    }
}
