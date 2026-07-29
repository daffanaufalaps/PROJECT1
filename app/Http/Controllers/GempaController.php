<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateRequest;
use App\Services\GempaCalculationService;
use App\Services\NarrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GempaController extends Controller
{
    public function __construct(
        protected GempaCalculationService $calculationService,
        protected NarrationService $narrationService
    ) {}

    /**
     * Show the main calculation page (Page 1 - Input)
     */
    public function index(Request $request): View
    {
        $latitude = $request->get('lat') ? (float) $request->get('lat') : null;
        $longitude = $request->get('lng') ? (float) $request->get('lng') : null;

        $initialNarrative = $this->narrationService->generateInitialNarrative($latitude, $longitude);

        return view('gempa.index', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'initialNarrative' => $initialNarrative,
        ]);
    }

    /**
     * Perform calculation and show results page (Page 2 - Results)
     */
    public function calculate(CalculateRequest $request): View|JsonResponse
    {
        $latitude = (float) $request->latitude;
        $longitude = (float) $request->longitude;
        $siteClass = $request->site_class;

        // Perform calculation
        $result = $this->calculationService->calculate($latitude, $longitude, $siteClass);

        // Generate narrative
        $narrative = $this->narrationService->generateNarrative($result);
        $mmiDescription = $this->narrationService->getMmiDescription($result['mmi']);
        $riskDescription = $this->narrationService->getRiskCategoryDescription($result['risk_category']);

        $responseData = [
            'success' => true,
            'data' => $result,
            'narrative' => $narrative,
            'mmi_description' => $mmiDescription,
            'risk_description' => $riskDescription,
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($responseData);
        }

        // Return view for web requests
        return view('gempa.result', [
            'result' => $result,
            'narrative' => $narrative,
            'mmiDescription' => $mmiDescription,
            'riskDescription' => $riskDescription,
        ]);
    }

    /**
     * API endpoint for calculation (POST /api/hitung)
     */
    public function apiCalculate(CalculateRequest $request): JsonResponse
    {
        $latitude = (float) $request->latitude;
        $longitude = (float) $request->longitude;
        $siteClass = $request->site_class;

        try {
            $result = $this->calculationService->calculate($latitude, $longitude, $siteClass);
            $narrative = $this->narrationService->generateNarrative($result);

            return response()->json([
                'success' => true,
                'data' => $result,
                'narrative' => $narrative,
                'mmi_description' => $this->narrationService->getMmiDescription($result['mmi']),
                'risk_description' => $this->narrationService->getRiskCategoryDescription($result['risk_category']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan dalam perhitungan.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show calculation history (Admin only)
     */
    public function history(Request $request): View
    {
        $histories = \App\Models\CalculationHistory::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.history', compact('histories'));
    }
}
