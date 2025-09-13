<?php

namespace App\Http\Controllers\Backend;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function __construct()
    {
        // Writes are allowed for guests too (user_id is nullable).
        // If you want to restrict updates/deletes, add middleware/policies here.
        // $this->middleware('auth')->only(['update','destroy']);
    }

    /**
     * GET /reviews  or  GET /products/{product}/reviews
     */
    public function index(Request $request, Product $product = null)
    {
        $perPage   = (int) $request->input('per_page', 20);
        $perPage   = max(1, min($perPage, 100));

        $productId = $product?->id ?? (int) $request->input('product_id');
        $userId    = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $rating    = $request->filled('rating') ? (int) $request->input('rating') : null;
        $status    = $request->has('status') ? (int) $request->input('status') : 1; // default visible
        $q         = trim((string) $request->input('q', ''));

        $sort      = $request->input('sort', 'recent'); // recent|oldest|rating_desc|rating_asc|most_viewed
        $direction = $request->input('direction', 'desc');

        $query = ProductReview::query()
            ->select(['id','product_id','user_id','guest_name','rating','comment','status','viewed','created_at'])
            ->with(['user:id,name','product:id,name,slug']);

        if ($productId) $query->where('product_id', $productId);
        if (!is_null($userId)) $query->where('user_id', $userId);
        if (!is_null($rating)) $query->where('rating', $rating);
        if (!is_null($status)) $query->where('status', $status);
        if ($q !== '') $query->where('comment', 'like', "%{$q}%");

        $useCursor = true;
        switch ($sort) {
            case 'oldest':
                $query->orderBy('id', 'asc');
                break;
            case 'rating_desc':
                $query->orderBy('rating', 'desc')->orderBy('id', 'desc');
                $useCursor = false;
                break;
            case 'rating_asc':
                $query->orderBy('rating', 'asc')->orderBy('id', 'desc');
                $useCursor = false;
                break;
            case 'most_viewed':
                $query->orderBy('viewed', 'desc')->orderBy('id', 'desc');
                $useCursor = false;
                break;
            case 'recent':
            default:
                $query->orderBy('id', $direction === 'asc' ? 'asc' : 'desc');
                break;
        }

        $reviews = $useCursor
            ? $query->cursorPaginate($perPage, ['*'], 'cursor')->appends($request->query())
            : $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($reviews);
        }

        return view('backend.reviews.index', [
            'reviews' => $reviews,
            'product' => $product,
            'filters' => compact('productId','userId','rating','status','q','sort','direction'),
        ]);
    }



    /**
     * GET /reviews/{review}/edit
     */
    public function edit(ProductReview $review)
    {
        return view('reviews.edit', compact('review'));
    }

    /**
     * PUT/PATCH /reviews/{review}
     */
    public function update(Request $request, ProductReview $review)
    {
        $rules = [
            'rating'     => ['sometimes','integer','min:1','max:5'],
            'comment'    => ['sometimes','string','min:3','max:5000'],
            'guest_name' => ['sometimes','string','min:2','max:100'],
            'status'     => ['sometimes','integer'], // adjust to your workflow (e.g., 0/1)
        ];

        $validated = $request->validate($rules);

        $review->fill($validated)->save();

        return $request->wantsJson()
            ? response()->json(['message' => 'Review updated.', 'review' => $review->fresh()->loadMissing('user:id,name')])
            : back()->with('success', 'Review updated.');
    }

    /**
     * DELETE /reviews/{review}
     */
    public function destroy(Request $request, ProductReview $review)
    {
        $review->delete();

        return $request->wantsJson()
            ? response()->json(['message' => 'Review deleted.'])
            : back()->with('success', 'Review deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), fn($v) => is_numeric($v));
        if (empty($ids)) {
            return back()->with('warning', 'No reviews selected.');
        }

        // Optional: authorize user here (e.g., Gate::authorize('reviews_delete'))
        \App\Models\ProductReview::whereIn('id', $ids)->delete();

        return back()->with('success', 'Selected reviews deleted.');
    }


    /**
     * GET /products/{product}/reviews/summary
     * Aggregates visible reviews (status filter, default 1).
     */
    public function summary(Product $product, Request $request)
    {
        $statusFilter = $request->has('status') ? (int) $request->input('status') : 1;

        $base = ProductReview::query()
            ->where('product_id', $product->id)
            ->when(!is_null($statusFilter), fn($q) => $q->where('status', $statusFilter));

        $total = (clone $base)->count();
        $avg   = (clone $base)->avg('rating');

        $breakdown = (clone $base)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating');

        return response()->json([
            'total'     => (int) $total,
            'average'   => round((float) $avg, 2),
            'breakdown' => $breakdown,
        ]);
    }
}

