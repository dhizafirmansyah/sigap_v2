<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $brands = $query->orderBy('name')->paginate(10)->withQueryString();

        $data = [
            'brands' => BrandResource::collection($brands),
            'filters' => $request->only(['search', 'is_active']),
        ];

        // Return JSON only for explicit fetch API requests (not for Inertia navigation)
        if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => $brands,
                'props' => $data
            ]);
        }

        return Inertia::render('Brands/Index', $data);
    }

    /**
     * Search for autocomplete - returns all matching records
     */
    public function search(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $brands = $query->where('is_active', true)->limit(20)->get();

        return BrandResource::collection($brands);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Brands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:brands',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:255',
                'target_production_per_day' => 'nullable|numeric|min:0',
                'quality_standards' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            $brand = Brand::create($validated);

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'data' => new BrandResource($brand)
                ], 201);
            }

            return redirect()->route('brands.index')
                ->with('success', 'Brand created successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'An error occurred while creating brand'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Brand $brand)
    {
        $brand->load(['kemas.packs.packQualityRecords']);

        // Return JSON for fetch API requests
        if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => new BrandResource($brand)
            ]);
        }

        return Inertia::render('Brands/Show', [
            'brand' => new BrandResource($brand),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        return Inertia::render('Brands/Edit', [
            'brand' => new BrandResource($brand),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:brands,code,' . $brand->getKey(),
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:255',
                'target_production_per_day' => 'nullable|numeric|min:0',
                'quality_standards' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            $brand->update($validated);

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'data' => new BrandResource($brand)
                ]);
            }

            return redirect()->route('brands.index')
                ->with('success', 'Brand updated successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'An error occurred while updating brand'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Brand $brand)
    {
        try {
            $brand->delete();

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Brand deleted successfully'
                ]);
            }

            return redirect()->route('brands.index')
                ->with('success', 'Brand deleted successfully.');
                
        } catch (\Exception $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'An error occurred while deleting brand'
                ], 500);
            }
            
            return redirect()->route('brands.index')
                ->with('error', 'An error occurred while deleting brand.');
        }
    }
}
