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
        $latitude = $request->input('lat') ? (float) $request->input('lat') : null;
        $longitude = $request->input('lng') ? (float) $request->input('lng') : null;

        $initialNarrative = $this->narrationService->generateInitialNarrative($latitude, $longitude);

        return view('gempa.index', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'initialNarrative' => $initialNarrative,
        ]);
    }

    /**
     * Build narrative/description data, dengan penanganan khusus saat
     * lokasi membutuhkan kajian lebih mendetail (mis. Kelas Situs F).
     */
    protected function buildNarrativeData(array $result): array
    {
        if ($result['requires_detailed_study'] ?? false) {
            return [
                'narrative' => $result['warning_message'],
                'mmi_description' => null,
                'risk_description' => null,
            ];
        }

        return [
            'narrative' => $this->narrationService->generateNarrative($result),
            'mmi_description' => $this->narrationService->getMmiDescription($result['mmi']),
            'risk_description' => $this->narrationService->getRiskCategoryDescription($result['risk_category']),
        ];
    }

    /**
     * Perform calculation and show results page (Page 2 - Results)
     */
    public function calculate(CalculateRequest $request): View|JsonResponse
    {
        $latitude = (float) $request->latitude;
        $longitude = (float) $request->longitude;
        $siteClass = $request->site_class;

        $result = $this->calculationService->calculate($latitude, $longitude, $siteClass);
        $narrativeData = $this->buildNarrativeData($result);

        $responseData = [
            'success' => true,
            'data' => $result,
            'narrative' => $narrativeData['narrative'],
            'mmi_description' => $narrativeData['mmi_description'],
            'risk_description' => $narrativeData['risk_description'],
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($responseData);
        }

        return view('gempa.result', [
            'result' => $result,
            'narrative' => $narrativeData['narrative'],
            'mmiDescription' => $narrativeData['mmi_description'],
            'riskDescription' => $narrativeData['risk_description'],
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
            $narrativeData = $this->buildNarrativeData($result);

            return response()->json([
                'success' => true,
                'data' => $result,
                'narrative' => $narrativeData['narrative'],
                'mmi_description' => $narrativeData['mmi_description'],
                'risk_description' => $narrativeData['risk_description'],
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