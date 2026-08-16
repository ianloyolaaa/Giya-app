<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\ChurchCategory;
use App\Models\ChurchImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ChurchController extends Controller
{
    public function index(Request $request): View
    {
        $churches = Church::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->when($request->category && $request->category !== 'All',
                fn ($q, $c) => $q->ofCategory($c))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.destinations', [
            'churches'   => $churches,
            'search'     => $request->search,
            'category'   => $request->category ?? 'All',
            'categories' => ChurchCategory::orderBy('name')->pluck('name')->prepend('All')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'location'    => ['required', 'string', 'max:200'],
            'category'    => ['required', 'in:Basilica,Cathedral,Shrine,Church,Chapel,Heritage'],
            'description' => ['nullable', 'string'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'opening_time'=> ['nullable', 'date_format:H:i'],
            'closing_time'=> ['nullable', 'date_format:H:i'],
            'photo'       => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'caption'     => ['nullable', 'string', 'max:255'],
        ], [
            'photo.image' => 'Choose an image file (JPG, PNG, or WEBP).',
            'photo.max'   => 'Keep the photo under 4 MB.',
        ]);

        // The ERD stores the category as a foreign key, not a string.
        $category = ChurchCategory::firstOrCreate(
            ['name' => $data['category']],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $photo   = $request->file('photo');
        $caption = $data['caption'] ?? null;
        unset($data['category'], $data['photo'], $data['caption']);

        $church = Church::create($data + [
            'category_id' => $category->id,
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        if ($photo) {
            ChurchImage::create([
                'church_id'   => $church->id,
                'image_url'   => 'storage/' . $photo->store('churches', 'public'),
                'caption'     => $caption ?: $church->name,
                'is_primary'  => true,
                'uploaded_at' => now(),
                'created_at'  => now(),
            ]);
        }

        return back()->with('success', 'Destination added.');
    }

    /** Replace the main photo of an existing destination. */
    public function updatePhoto(Request $request, Church $church): RedirectResponse
    {
        $request->validate([
            'photo'   => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($church->images()->where('is_primary', true)->get() as $old) {
            if (! str_starts_with($old->image_url, 'http')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $old->image_url));
            }
            $old->delete();
        }

        ChurchImage::create([
            'church_id'   => $church->id,
            'image_url'   => 'storage/' . $request->file('photo')->store('churches', 'public'),
            'caption'     => $request->caption ?: $church->name,
            'is_primary'  => true,
            'uploaded_at' => now(),
            'created_at'  => now(),
        ]);

        return back()->with('success', 'Photo updated for ' . $church->name . '.');
    }
    public function toggle(Church $church): RedirectResponse
    {
        $church->update([
            'is_active'  => ! $church->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('success',
            $church->name . ' is now ' . ($church->is_active ? 'active' : 'hidden') . '.');
    }
}
