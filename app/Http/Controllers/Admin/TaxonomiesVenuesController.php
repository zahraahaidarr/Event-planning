<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Models\RoleType;
use App\Models\EventCategory;
use App\Models\Venue;


class TaxonomiesVenuesController extends Controller
{
    public function index()
{
    // Worker types
    $workerTypes = RoleType::orderBy('name')
        ->get(['role_type_id', 'name', 'description']);

    // Event categories
    $eventCategories = EventCategory::orderBy('name')
        ->get(['category_id', 'name', 'description']);

    // Venues
    $venues = Venue::orderBy('name')
        ->get(['id', 'name', 'city', 'area_m2']);

    return view('Admin.admin-taxonomies-venues', [
        'workerTypes'      => $workerTypes,
        'eventCategories'  => $eventCategories,
        'venues'           => $venues,
    ]);
}

    // GET /admin/taxonomies-venues/worker-types
    public function workerTypesIndex()
    {
        $items = RoleType::orderBy('name')->get(['role_type_id','name','description']);
        return response()->json(['ok' => true, 'data' => $items]);
    }

    // POST /admin/taxonomies-venues/worker-types
    public function workerTypesStore(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('role_types','name')],
            'description' => ['nullable','string','max:1000'],
        ]);

        $item = RoleType::create($validated);

        return response()->json(['ok' => true, 'data' => $item, 'msg' => 'Worker type created.'], 201);
    }

    // DELETE /admin/taxonomies-venues/worker-types/{roleType}
    public function workerTypesDestroy(RoleType $roleType)
    {
        try {
            $roleType->delete();
            return response()->json(['ok' => true, 'msg' => 'Worker type deleted.']);
        } catch (QueryException $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Cannot delete: this worker type is referenced by other records.',
            ], 409);
        }
    }

    public function eventCategoriesIndex() {
    $items = EventCategory::orderBy('name')->get(['category_id','name','description']);
    return response()->json(['ok'=>true,'data'=>$items]);
}

public function eventCategoriesStore(Request $request) {
    $data = $request->validate([
        'name' => ['required','string','max:255', Rule::unique('event_categories','name')],
        'description' => ['nullable','string','max:1000'],
    ]);
    $item = EventCategory::create($data);
    return response()->json(['ok'=>true,'data'=>$item], 201);
}

public function eventCategoriesDestroy(EventCategory $eventCategory) {
    $eventCategory->delete();
    return response()->json(['ok'=>true]);
}

 public function venuesIndex()
    {
        $items = Venue::orderBy('name')
            ->get(['id','name','city','area_m2']);

        return response()->json(['ok'=>true,'data'=>$items]);
    }

    public function venuesStore(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'city'    => ['nullable','string','max:255'],
            'area_m2' => ['required','numeric','min:0'],
        ]);

        $venue = Venue::create($data);

        return response()->json(['ok'=>true,'data'=>$venue], 201);
    }

    public function venuesDestroy(Venue $venue)
    {
        $venue->delete();
        return response()->json(['ok'=>true]);
    }
}
