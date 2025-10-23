<?php

namespace App\Http\Controllers\Admin\AdminUserController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;



class AdminUserReportController extends Controller
{
    /**
     * Generate comprehensive user report for administrators
     * 
     * GET /api/admin/users/report
     */
    public function report(Request $request): JsonResponse
    {
        try {
            // Validate request parameters
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:active,inactive,suspended,pending',
                'user_type_id' => 'nullable|integer|exists:user_types,id',
                'sort_field' => 'nullable|string|in:created_at,full_name,email,status,updated_at',
                'sort_direction' => 'nullable|string|in:asc,desc',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            // Generate cache key based on request parameters
            $cacheKey = $this->generateCacheKey($request);
            
            // Use caching for better performance (5 minutes TTL)
            $result = Cache::remember($cacheKey, 300, function () use ($validated) {
                return $this->generateUserReport($validated);
            });

            return response()->json([
                'success' => true,
                'data' => $result['users'],
                'meta' => $result['meta'],
                'analytics' => $result['analytics'],
                'message' => 'User report generated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('User report generation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_params' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate user report',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate comprehensive user report with analytics
     */
    private function generateUserReport(array $filters): array
    {
        $query = User::with([
            'userType',
            'landOwner' => function($query) {
                $query->select(['id', 'user_id', 'company_name', 'license_number', 'created_at']);
            },
            'legalAgent' => function($query) {
                $query->select(['id', 'user_id', 'bar_association_number', 'practice_area', 'created_at']);
            },
            'businessEntity' => function($query) {
                $query->select(['id', 'user_id', 'entity_name', 'commercial_registration', 'created_at']);
            },
            'realEstateBroker' => function($query) {
                $query->select(['id', 'user_id', 'broker_license', 'experience_years', 'created_at']);
            },
            'auctionCompany' => function($query) {
                $query->select(['id', 'user_id', 'company_name', 'auction_license', 'created_at']);
            }
        ])->withCount(['properties', 'bids', 'auctions']);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $filters['per_page'] ?? 20;
        $page = $filters['page'] ?? 1;
        
        $users = $query->paginate($perPage, ['*'], 'page', $page);

        // Format users data
        $formattedUsers = $users->getCollection()->map(function ($user) {
            return $this->formatUserData($user);
        });

        return [
            'users' => $formattedUsers,
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'analytics' => $this->getAnalytics($filters)
        ];
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, array $filters): void
    {
        // Search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // User type filter
        if (!empty($filters['user_type_id'])) {
            $query->where('user_type_id', $filters['user_type_id']);
        }
    }

    /**
     * تنسيق بيانات المستخدم للاستخدام في التقارير
     */
    private function formatUserData(User $user): array
    {
        $data = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
            'user_type' => [
                'id' => $user->userType->id ?? null,
                'name' => $user->userType->type_name ?? 'غير محدد',
            ],
            'profile_details' => $this->getUserProfileDetails($user),
            'statistics' => [
                'properties_count' => $user->properties_count ?? 0,
                'bids_count' => $user->bids_count ?? 0,
                'auctions_count' => $user->auctions_count ?? 0,
            ],
            'dates' => [
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
            ]
        ];

        return $data;
    }

    /**
     * Get user-specific profile details based on user type
     */
    private function getUserProfileDetails(User $user): ?array
    {
        if ($user->landOwner) {
            return [
                'type' => 'land_owner',
                'company_name' => $user->landOwner->company_name,
                'license_number' => $user->landOwner->license_number,
            ];
        }

        if ($user->legalAgent) {
            return [
                'type' => 'legal_agent',
                'bar_association_number' => $user->legalAgent->bar_association_number,
                'practice_area' => $user->legalAgent->practice_area,
            ];
        }

        if ($user->businessEntity) {
            return [
                'type' => 'business_entity',
                'entity_name' => $user->businessEntity->entity_name,
                'commercial_registration' => $user->businessEntity->commercial_registration,
            ];
        }

        if ($user->realEstateBroker) {
            return [
                'type' => 'real_estate_broker',
                'broker_license' => $user->realEstateBroker->broker_license,
                'experience_years' => $user->realEstateBroker->experience_years,
            ];
        }

        if ($user->auctionCompany) {
            return [
                'type' => 'auction_company',
                'company_name' => $user->auctionCompany->company_name,
                'auction_license' => $user->auctionCompany->auction_license,
            ];
        }

        return null;
    }

    /**
     * Get comprehensive analytics for users
     */
    private function getAnalytics(array $filters): array
    {
        $analyticsQuery = User::query();

        // Apply same filters to analytics
        $this->applyFilters($analyticsQuery, $filters);

        $statusDistribution = $analyticsQuery->clone()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $userTypeDistribution = $analyticsQuery->clone()
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('user_types.type_name', DB::raw('count(users.id) as count'))
            ->groupBy('user_types.id', 'user_types.type_name')
            ->pluck('count', 'user_types.type_name')
            ->toArray();

        $startDate = now()->subDays(30)->startOfDay();
        $registrationTrend = $analyticsQuery->clone()
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return [
            'total_users' => $analyticsQuery->count(),
            'status_distribution' => $statusDistribution,
            'user_type_distribution' => $userTypeDistribution,
            'registration_trend' => $registrationTrend,
            'active_users_count' => $analyticsQuery->clone()->where('status', 'active')->count(),
            'new_users_today' => $analyticsQuery->clone()->whereDate('created_at', today())->count(),
            'new_users_this_week' => $analyticsQuery->clone()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    /**
     * Generate comprehensive cache key based on request parameters
     */
    private function generateCacheKey(Request $request): string
    {
        $params = $request->only([
            'search', 'status', 'user_type_id', 'sort_field', 'sort_direction', 'page', 'per_page'
        ]);

        ksort($params); // Sort parameters for consistent cache keys

        return 'user_report:' . md5(serialize($params) . '_' . $request->user()?->id);
    }

    /**
     * Export user report to Excel
     * 
     * POST /api/admin/users/report/export
     */
    public function exportReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filters' => 'nullable|array',
                'filters.search' => 'nullable|string',
                'filters.status' => 'nullable|string',
                'filters.user_type_id' => 'nullable|integer',
            ]);

            // Generate export file and return download URL
            $exportUrl = $this->generateExport($validated['filters'] ?? []);

            return response()->json([
                'success' => true,
                'download_url' => $exportUrl,
                'message' => 'User report export generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('User report export failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export user report'
            ], 500);
        }
    }

    /**
     * Generate export file
     */
    private function generateExport(array $filters): string
    {
        // This would typically generate an Excel file and return the download URL
        // For now, return a placeholder URL
        return route('admin.users.export.download', [
            'filters' => base64_encode(json_encode($filters)),
            'token' => Str::random(32)
        ]);
    }
}