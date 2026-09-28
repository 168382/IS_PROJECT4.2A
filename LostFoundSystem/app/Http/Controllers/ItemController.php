<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\CategoryRepository;
use App\Repositories\FoundItemRepository;
use App\Repositories\LostItemRepository;
use App\Services\AuthService;
use App\Services\FoundItemService;
use App\Services\LostItemService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(
        protected LostItemService $lostItemService,
        protected FoundItemService $foundItemService,
        protected LostItemRepository $lostItemRepo,
        protected FoundItemRepository $foundItemRepo,
        protected CategoryRepository $categoryRepo,
        protected AuthService $auth,
    ) {}

    public function image(string $type, int $id)
    {
        $repository = $type === 'lost' ? $this->lostItemRepo : $this->foundItemRepo;
        $image = $repository->image($id);
        abort_unless($image && $image->image_data && $image->image_mime, 404);

        return response($image->image_data)
            ->header('Content-Type', $image->image_mime)
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Show the Report Lost Item form.
     */
    public function showReportLostForm()
    {
        return view('items.report_lost', ['categories' => $this->categoryRepo->allOrdered()]);
    }

    /**
     * Handle the lost item submission.
     */
    public function storeLostItem(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'description' => 'required|string',
            'location_lost' => 'required|string|max:255',
            'date_lost' => 'required|date|before_or_equal:today',
            'color' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:5120|dimensions:max_width=4096,max_height=4096',
        ]);

        if (! $this->categoryRepo->find((int) $validated['category_id'])) {
            return back()->withInput()->withErrors(['category_id' => 'Please choose a valid category.']);
        }

        $userData = $this->auth->user($request);
        if (! $userData) {
            return redirect('/login')->with('error', 'Please log in to report a lost item.');
        }

        $user = new User($userData);
        $user->id = (int) $userData['id'];
        $user->exists = true;

        $image = $request->file('image');

        $this->lostItemService->create($user, $validated, $image);

        return redirect('/dashboard')->with('success', 'Your lost item report has been submitted successfully! Potential matches will be displayed on your dashboard.');
    }

    /**
     * Show the Report Found Item form.
     */
    public function showReportFoundForm()
    {
        return view('items.report_found', ['categories' => $this->categoryRepo->allOrdered()]);
    }

    /**
     * Handle the found item submission.
     */
    public function storeFoundItem(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'description' => 'required|string',
            'location_found' => 'required|string|max:255',
            'date_found' => 'required|date|before_or_equal:today',
            'color' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:5120|dimensions:max_width=4096,max_height=4096',
        ]);

        if (! $this->categoryRepo->find((int) $validated['category_id'])) {
            return back()->withInput()->withErrors(['category_id' => 'Please choose a valid category.']);
        }

        $userData = $this->auth->user($request);
        if (! $userData) {
            return redirect('/login')->with('error', 'Please log in to report a found item.');
        }

        $user = new User($userData);
        $user->id = (int) $userData['id'];
        $user->exists = true;

        $image = $request->file('image');

        $this->foundItemService->create($user, $validated, $image);

        return redirect('/dashboard')->with('success', 'Your found item report has been submitted successfully! Thank you for your contribution.');
    }

    /**
     * Show the search page.
     */
    public function search(Request $request)
    {
        $filters = $request->only(['keyword', 'category_id', 'color', 'location', 'date', 'status']);

        $lostItems = $this->lostItemRepo->search($filters);
        $foundItems = $this->foundItemRepo->search($filters);

        $categories = $this->categoryRepo->allOrdered();

        return view('items.search', compact('lostItems', 'foundItems', 'filters', 'categories'));
    }
}
