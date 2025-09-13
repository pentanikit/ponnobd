{{-- resources/views/backend/reviews/index.blade.php --}}
@extends('backend.layouts.app')

@section('title', 'Reviews')

@section('content')
{{-- Page header (optional product context) --}}
<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        Reviews
        @if(!empty($product))
            <small class="text-muted">for: {{ $product->name }}</small>
        @endif
    </h5>
    <div>
        <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="card-title">Filter & Search</div>
        <form method="GET" action="{{ url()->current() }}">
            <div class="row g-2">
                {{-- Search in comment --}}
                <div class="col-md-4">
                    <input type="text"
                           name="q"
                           class="form-control"
                           value="{{ request('q', $filters['q'] ?? '') }}"
                           placeholder="Search in comment...">
                </div>

                {{-- Product ID (when not using nested route) --}}
                <div class="col-md-2">
                    <input type="number"
                           name="product_id"
                           class="form-control"
                           value="{{ request('product_id', $filters['productId'] ?? '') }}"
                           placeholder="Product ID">
                </div>

                {{-- User ID --}}
                <div class="col-md-2">
                    <input type="number"
                           name="user_id"
                           class="form-control"
                           value="{{ request('user_id', $filters['userId'] ?? '') }}"
                           placeholder="User ID">
                </div>

                {{-- Rating --}}
                <div class="col-md-2">
                    <select class="form-select" name="rating">
                        @php $ratingSel = request('rating', $filters['rating'] ?? ''); @endphp
                        <option value="">All ratings</option>
                        @for($r=5; $r>=1; $r--)
                            <option value="{{ $r }}" @selected((string)$ratingSel === (string)$r)>{{ $r }} star{{ $r>1?'s':'' }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    @php $statusSel = request('status', $filters['status'] ?? 1); @endphp
                    <select class="form-select" name="status">
                        <option value="">All status</option>
                        <option value="1" @selected((string)$statusSel === '1')>Approved/Active (1)</option>
                        <option value="0" @selected((string)$statusSel === '0')>Pending/Inactive (0)</option>
                    </select>
                </div>

                {{-- Sort / Direction --}}
                <div class="col-md-3">
                    @php $sortSel = request('sort', $filters['sort'] ?? 'recent'); @endphp
                    <select class="form-select" name="sort">
                        <option value="recent" @selected($sortSel==='recent')>Recent (id desc)</option>
                        <option value="oldest" @selected($sortSel==='oldest')>Oldest (id asc)</option>
                        <option value="rating_desc" @selected($sortSel==='rating_desc')>Rating high → low</option>
                        <option value="rating_asc" @selected($sortSel==='rating_asc')>Rating low → high</option>
                        <option value="most_viewed" @selected($sortSel==='most_viewed')>Most viewed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    @php $dirSel = request('direction', $filters['direction'] ?? 'desc'); @endphp
                    <select class="form-select" name="direction">
                        <option value="desc" @selected($dirSel==='desc')>DESC</option>
                        <option value="asc"  @selected($dirSel==='asc')>ASC</option>
                    </select>
                </div>

                {{-- Per page --}}
                <div class="col-md-2">
                    @php $ppSel = (int)request('per_page', $filters['perPage'] ?? 20); @endphp
                    <select class="form-select" name="per_page">
                        @foreach([10,20,30,50,100] as $pp)
                            <option value="{{ $pp }}" @selected($ppSel===$pp)>{{ $pp }} / page</option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit --}}
                <div class="col-md-3">
                    <button class="btn btn-success w-100">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table align-items-center mb-0">
            <thead>
            <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Product</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Reviewer</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Rating</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="min-width:320px">Comment</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Viewed</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                use Illuminate\Support\Str;
            @endphp

            @forelse ($reviews as $review)
                <tr>
                    <td class="align-middle">
                        {{-- Supports both CursorPaginator & LengthAwarePaginator --}}
                        @if($reviews instanceof \Illuminate\Pagination\CursorPaginator)
                            {{ $loop->iteration }}
                        @elseif(method_exists($reviews, 'firstItem') && $reviews->firstItem())
                            {{ $reviews->firstItem() + $loop->index }}
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </td>

                    <td class="align-middle">
                        @if($review->product)
                            <a href="{{ route('product.details', $review->product->slug) }}" target="_blank">
                                {{ $review->product->name }}
                            </a>
                        @else
                            <span class="text-muted">#{{ $review->product_id }}</span>
                        @endif
                    </td>

                    <td class="align-middle">
                        {{ $review->user?->name ?? $review->guest_name }}
                    </td>

                    <td class="align-middle">
                        @for ($i = 1; $i <= 5; $i++)
                            {!! $i <= (int)$review->rating ? '★' : '☆' !!}
                        @endfor
                        <small class="text-muted">({{ $review->rating }})</small>
                    </td>

                    <td class="align-middle">
                        {{ Str::limit(strip_tags($review->comment), 140) }}
                    </td>

                    <td class="align-middle">
                        @if((int)$review->status === 1)
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>

                    <td class="align-middle">
                        {{ $review->viewed }}
                    </td>

                    <td class="align-middle">
                        {{ optional($review->created_at)->format('Y-m-d H:i') }}
                    </td>

                    <td class="align-middle">
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary mb-0" data-bs-toggle="dropdown" id="actionLink{{ $review->id }}">
                                <i class="fa fa-ellipsis-v text-xs" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="actionLink{{ $review->id }}">
                                
                               
                                <li>
                                    <form action="{{ route('reviews.destroy', $review->id) }}" method="POST"
                                          onsubmit="return confirm('Delete this review?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">No reviews found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination (works for both cursor & length-aware) --}}
    <div class="p-3">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
