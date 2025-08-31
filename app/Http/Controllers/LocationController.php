<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Resources\LocationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Location::withCount(['employees', 'packs', 'kemas']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $locations = $query->orderBy('name')->paginate(10)->withQueryString();

        $data = [
            'locations' => LocationResource::collection($locations),
            'filters' => $request->only(['search', 'is_active']),
        ];

        // Return JSON only for explicit fetch API requests (not for Inertia navigation)
        if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => $locations,
                'props' => $data
            ]);
        }

        return Inertia::render('Locations/Index', $data);
    }

    /**
     * Search for autocomplete - returns all matching records
     */
    public function search(Request $request)
    {
        $query = Location::query();

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $locations = $query->where('is_active', true)->limit(20)->get();

        return LocationResource::collection($locations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Locations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:locations',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:0',
                'address' => 'nullable|string',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $location = Location::create($validated);

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'data' => new LocationResource($location)
                ], 201);
            }

            return redirect()->route('locations.index')
                ->with('success', 'Location created successfully.');
                
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
                    'message' => 'An error occurred while creating location'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Location $location)
    {
        $location->loadCount(['employees', 'packs', 'kemas']);

        // Return JSON for fetch API requests
        if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => new LocationResource($location)
            ]);
        }

        return Inertia::render('Locations/Show', [
            'location' => new LocationResource($location),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        $location->loadCount(['employees', 'packs', 'kemas']);
        
        return Inertia::render('Locations/Edit', [
            'location' => new LocationResource($location),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:locations,code,' . $location->getKey(),
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:0',
                'address' => 'nullable|string',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $location->update($validated);

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'data' => new LocationResource($location)
                ]);
            }

            return redirect()->route('locations.index')
                ->with('success', 'Location updated successfully.');
                
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
                    'message' => 'An error occurred while updating location'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Location $location)
    {
        try {
            $location->delete();

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Location deleted successfully'
                ]);
            }

            return redirect()->route('locations.index')
                ->with('success', 'Location deleted successfully.');
                
        } catch (\Exception $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'An error occurred while deleting location'
                ], 500);
            }
            
            return redirect()->route('locations.index')
                ->with('error', 'An error occurred while deleting location.');
        }
    }
}
