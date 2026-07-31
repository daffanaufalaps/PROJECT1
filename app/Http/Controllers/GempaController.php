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

    protected function buildNarrativeData(array $result): array
    {
        if ($result['requires_detailed_study'] ?? false) {
            return [
                'narrative' => $result['warning_message'],
                'sig_bmkg_description' => null,
                'risk_description' => null,
            ];
        }

        return [
            'narrative' => $this->narrationService->generateNarrative($result),
            'sig_bmkg_description' => $this->narrationService->getSigBmkgDescription($result['sig_bmkg_scale']),
            'risk_description' => $this->narrationService->getRiskCategoryDescription($result['risk_category']),
        ];
    }

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
            'sig_bmkg_description' => $narrativeData['sig_bmkg_description'],
            'risk_description' => $narrativeData['risk_description'],
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($responseData);
        }

        return view('gempa.result', [
            'result' => $result,
            'narrative' => $narrativeData['narrative'],
            'sigBmkgDescription' => $narrativeData['sig_bmkg_description'],
            'riskDescription' => $narrativeData['risk_description'],
        ]);
    }

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
                'sig_bmkg_description' => $narrativeData['sig_bmkg_description'],
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
     * Generate dan unduh laporan hasil analisis dalam bentuk PDF.
     * Menerima data hasil (result_data) yang sudah dihitung sebelumnya,
     * tidak menghitung ulang -- supaya tidak membuat entri histori duplikat.
     */
    public function downloadReport(Request $request)
    {
        $result = json_decode($request->input('result_data'), true);

        if (!$result) {
            abort(400, 'Data hasil analisis tidak valid atau tidak ditemukan.');
        }

        $narrativeData = $this->buildNarrativeData($result);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gempa.report-pdf', [
            'result' => $result,
            'narrative' => $narrativeData['narrative'],
            'riskDescription' => $narrativeData['risk_description'],
        ])->setPaper('a4');

        $filename = 'Laporan-Risiko-Gempa-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    public function history(Request $request): View
    
    {
        $histories = \App\Models\CalculationHistory::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.history', compact('histories'));
    }
}