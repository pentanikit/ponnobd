@section('meta')
<meta property="title" content="{{ $product->name }} | {{ config('app.name') }}" />
<meta name="keywords" content="{{ $product->tags }}" />
<meta property="og:title" content="{{ $product->name }} | {{ config('app.name') }}" />
<meta property="og:description" content="{{ strip_tags($product->meta_description) }}" />
<meta name="description" content="{{ strip_tags($product->meta_description) }}" />
<meta property="og:image" content="{{ uploadedFile($product->thumbnail_img) }}" />
<meta property="og:image:secure_url" content="{{ uploadedFile($product->thumbnail_img) }}" />
<meta property="og:image:alt" content="{{ $product->name }} | {{ config('app.name') }}" />
<meta name="twitter:title" content="{{ $product->name }} | {{ config('app.name') }}" />
<meta name="twitter:description" content="{{ strip_tags($product->meta_description) }}" />
<meta name="twitter:image" content="{{ uploadedFile($product->thumbnail_img) }}" />
@endsection

<div>
  <section class="product-details bg--white py-50">
    <div class="container">
      @php
        $photos = json_decode($product->photos);
        $galleries_alt = $product->galleries_alt !== null ? explode(',',$product->galleries_alt) : [];
      @endphp

      <div class="row gy-4 mb-2">
        <div class="col-xxl-6 col-xl-7 col-lg-7">
          <div class="row gy-4 flex-wrap-reverse">
            <div class="col-xxl-2 col-xl-2 col-lg-3">
              <div class="product-all--img">
                @foreach ($photos as $key => $photo)
                  <a data-zoom-id="productImg"
                     href="{{ uploadedFile($photo->id) }}"
                     data-image="{{ uploadedFile($photo->id) }}">
                    <img src="{{ uploadedFile($photo->id) }}" alt="{{ isset($galleries_alt[$key]) ? trim($galleries_alt[$key]) : $product->alt }}">
                  </a>
                @endforeach
              </div>
            </div>

            <div class="col-xx-10 col-xl-10 col-lg-9">
              <div class="product--thumb">
                @if($variationImg != null)
                  <a href="{{ uploadedFile($variationImg) }}"
                     class="MagicZoom" id="productImg"
                     data-options="zoomWidth:500;zoomHeight:500;zoomPosition:inner;zoomDistance:3;selectorTrigger:hover;lazyZoom:true;rightClick:true;variableZoom:true;"
                     data-mobile-options="zoomMode:zoom;textClickZoomHint:Double tap to zoom;">
                    <img class="fit--img" src="{{ uploadedFile($variationImg) }}" alt="{{ $product->alt }}">
                  </a>
                @else
                  <a href="{{ uploadedFile($product->thumbnail_img) }}"
                     class="MagicZoom" id="productImg"
                     data-options="zoomWidth:500;zoomHeight:500;zoomPosition:inner;zoomDistance:3;selectorTrigger:hover;lazyZoom:true;rightClick:true;variableZoom:true;"
                     data-mobile-options="zoomMode:zoom;textClickZoomHint:Double tap to zoom;">
                    <img class="fit--img" src="{{ uploadedFile($product->thumbnail_img) }}" alt="{{ $product->alt }}">
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>

        <div class="col-xxl-6 col-xl-5 col-lg-5">
          <div class="details-wrap">
            <div class="title--wrap mb-3">
              <h4 class="title fs--26 fw--500">{{ $product->name }}</h4>
            </div>

            <ul class="details--list d-flex flex-column gap--8 mb-3">
              {!! $product->short_description !!}
            </ul>

            <div class="support--number mb-3">
              <p class="text--base fw--600 fs--20">Call for details :
                <a href="tel:{{ settings('header_phone') }}" class="text--base">{{ settings('header_phone') }}</a>
              </p>
            </div>

            <ul class="product--status d-flex flex-wrap justify-content-start align-items-center gap--12">
              <li class="price">
                <p class="fs--14">Price:
                  @if(($homePrice = $product->regularPrice(false)) != ($discountPrice = $product->discountPrice(false)))
                    <span class="fw--900">{{ formatPrice($discountPrice) }} {!! $product->unit != null ? '/ <sup><small>PC</small></sup>' : '' !!}</span>
                    <span class="del--price text--sm">{{ formatPrice($homePrice) }}</span>
                  @else
                    <span class="fw--900">{{ formatPrice($discountPrice) }} {!! $product->unit != null ? '/ <sup><small>PC</small></sup>' : '' !!}</span>
                  @endif
                </p>
              </li>

              @php
                $quantity = $product->variant_product ? $product->stocks->sum('qty') : $product->current_stock;
              @endphp

              <li class="price">
                <p class="fs--14">Status:
                  <span class="fw--900">
                    @if($quantity > 0)
                      In Stock
                    @else
                      <span class="text-danger">Out Of Stock</span>
                    @endif
                  </span>
                </p>
              </li>

              @if($totalPrice != 0)
                <li>
                  @if($quantity > 0)
                    {{-- Make sure this is NOT inside a <form> and is a plain button --}}
                    <button class="btn btn--base pill" type="button" wire:click="addToCart">Add To Cart</button>
                  @endif
                </li>

                <li>
                  {{-- Prevent hard submit: use .prevent --}}
                  <form wire:submit.prevent="addToCart(true)">
                    @if($quantity > 0)
                      <button type="submit" class="btn btn-success">Order Now</button>
                    @endif
                  </form>
                </li>
              @endif
            </ul>
          </div>
        </div>
      </div>

      <div class="row gy-5">
        <div class="col-lg-12">
          <div class="ticket--description radius--8 card--bg">
            <div class="tab-pane fade active show" id="buy" role="tabpanel" aria-labelledby="buy-tab">
              <ul class="nav nav-tabs custom--tabs mb-2" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="Win-tab" data-bs-toggle="tab"
                          data-bs-target="#Win" type="button" role="tab" aria-selected="false" tabindex="-1">Specifications</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="specifications-tab" data-bs-toggle="tab"
                          data-bs-target="#specifications" type="button" role="tab" aria-selected="true">Descriptions</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="support-tab" data-bs-toggle="tab"
                          data-bs-target="#support" type="button" role="tab" aria-selected="true">Support</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="galleries-tab" data-bs-toggle="tab"
                          data-bs-target="#galleries" type="button" role="tab" aria-selected="false" tabindex="-1">Galleries</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="video-tab" data-bs-toggle="tab"
                          data-bs-target="#video" type="button" role="tab" aria-selected="false" tabindex="-1">Video</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="review-tab" data-bs-toggle="tab"
                          data-bs-target="#review" type="button" role="tab" aria-selected="false" tabindex="-1">Reviews</button>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane fade active show" id="Win" role="tabpanel" aria-labelledby="Win-tab">
                  <div class="row justify-content-start">
                    <div class="col-lg-8 product-description">
                      {!! $product->description !!}
                    </div>
                  </div>
                </div>

                <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                  <div class="details-wrap mb-4">
                    <style>
                      .wrap{ width: 1200px; margin: 0 auto; }
                      p{ margin-bottom:10px; font-size:16px; color:#01132d; font-family:"Trebuchet MS",sans-serif; }
                      h1{ font-size:26px; line-height:35px; margin-bottom:5px; font-family:"Trebuchet MS",sans-serif; }
                      h2{ font-size:24px; line-height:30px; margin-bottom:5px; font-family:"Trebuchet MS",sans-serif; }
                      h3{ font-size:20px; line-height:26px; margin-bottom:5px; font-family:"Trebuchet MS",sans-serif; }
                      .wrap>img{ width:1200px; height:630px; }
                      table{ width:100%; text-align:center; border:1px solid black; }
                      table td,table th{ border:1px solid black; }
                      li{ margin-bottom:0; font-size:16px; color:#01132d; line-height:26px; font-family:"Trebuchet MS",sans-serif; margin-left:15px; }
                    </style>
                    {!! $product->support_description !!}
                  </div>
                </div>

                <div class="tab-pane fade" id="support" role="tabpanel" aria-labelledby="support-tab">
                  <div class="details-wrap mb-4">
                    {!! $product->short_description !!}
                  </div>
                </div>

                <div class="tab-pane fade" id="galleries" role="tabpanel" aria-labelledby="galleries-tab">
                  @foreach ($photos as $key => $photo)
                    <div class="thumb--wrap">
                      <img src="{{ uploadedFile($photo->id) }}" alt="{{ isset($galleries_alt[$key]) ? trim($galleries_alt[$key]) : $product->alt }}">
                    </div>
                  @endforeach
                </div>

                <div class="tab-pane fade" id="video" role="tabpanel" aria-labelledby="video-tab">
                  <div class="details-wrap mb-4">
                    {!! $product->product_video !!}
                  </div>
                </div>

                <div class="tab-pane fade" id="review" role="tabpanel" aria-labelledby="review-tab">
                  <div class="details-wrap mb-4">
                    <div class="reviews-container">
                      <h2 class="section-title">Customer Reviews</h2>

                      <div class="rating-summary">
                        <div class="average-rating">
                          {{ number_format($product->reviews->avg('rating')) }}
                          <span style="font-size:1.5rem;">/5</span>
                          <span class="mx-3 review-rating">
                            {!! str_repeat('★', (int) $product->reviews->avg('rating')) . str_repeat('☆', 5 - (int) $product->reviews->avg('rating')) !!}
                          </span>
                        </div>
                      </div>

                      <div class="reviews-list">
                        @php
                          $uniqueReviews = $product->reviews
                            ->sortByDesc('created_at')   // latest first
                            ->unique('user_id')          // one review per user
                            ->values()
                            ->take(2);
                        @endphp

                        @foreach ($uniqueReviews as $review)
                          <div class="review-item">
                            <div class="review-header">
                              <span class="reviewer-name">{{ optional($review->user)->name ?? ($review->guest_name ?? 'Anonymous') }}</span>
                              <span class="review-date">{{ $review->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="review-rating" aria-label="{{ (int) $review->rating }} out of 5">
                              {!! str_repeat('★', (int) $review->rating) . str_repeat('☆', 5 - (int) $review->rating) !!}
                            </div>
                            <p class="review-content">{{ $review->comment }}</p>
                          </div>
                        @endforeach
                      </div>

                      {{-- Toggle button is NOT a submit --}}
                      <button class="add-review-btn" id="show-form-btn" type="button">Write a Review</button>

                      <div class="review-form" id="review-form">
                        <h3 class="form-title">Add Your Review</h3>

<form id="reviewForm" onsubmit="return false;">  {{-- kill native submit --}}
    @csrf

    @guest
    <div class="form-group">
        <label class="form-label" for="name">Your Name</label>
        <input type="text"
               class="form-input"
               id="name"
               wire:model.lazy="guest_name"
               required
               onkeydown="if(event.key==='Enter'){event.preventDefault();}">
        @error('guest_name') <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
    </div>
    @endguest

    <div class="form-group">
        <label class="form-label">Your Rating</label>
        <div class="rating-input" style="display:flex; gap:.25rem; flex-direction: row-reverse;">
            <input type="radio" id="star5" value="5" wire:model="rating"><label for="star5">★</label>
            <input type="radio" id="star4" value="4" wire:model="rating"><label for="star4">★</label>
            <input type="radio" id="star3" value="3" wire:model="rating"><label for="star3">★</label>
            <input type="radio" id="star2" value="2" wire:model="rating"><label for="star2">★</label>
            <input type="radio" id="star1" value="1" wire:model="rating" required><label for="star1">★</label>
        </div>
        @error('rating') <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="review">Your Review</label>
        <textarea class="form-input" id="review"
                  wire:model.lazy="comment"
                  required
                  onkeydown="if(event.key==='Enter'){event.preventDefault();}"></textarea>
        @error('comment') <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <!-- Button-only submit -->
    <button type="button" class="submit-btn" wire:loading.attr="disabled" wire:click="addReview">
        <span wire:loading.remove>Submit Review</span>
        <span wire:loading>Submitting…</span>
    </button>

    @if (session('review_saved'))
        <div class="mt-3 text-green-600">{{ session('review_saved') }}</div>
    @endif
</form>

<script>
  // Defensive: block any sneaky submit from other scripts
  document.addEventListener('DOMContentLoaded', () => {
    const f = document.getElementById('reviewForm');
    if (!f) return;
    f.addEventListener('submit', e => { e.preventDefault(); e.stopPropagation(); return false; }, true);
  });
</script>
                      </div>
                    </div>

                    <style>
                      .reviews-container{max-width:800px;background:#fff;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,.08);padding:2rem;}
                      .section-title{font-size:1.5rem;font-weight:600;margin-bottom:1.5rem;color:#2c3e50;border-bottom:1px solid #eaecef;padding-bottom:.75rem;}
                      .rating-summary{display:flex;align-items:center;margin-bottom:2rem;padding:1.5rem;background:#f8f9fa;border-radius:6px;}
                      .average-rating{font-size:3rem;font-weight:300;color:#2c3e50;margin-right:1.5rem;}
                      .reviews-list{margin-bottom:2rem;}
                      .review-item{padding:1.5rem 0;border-bottom:1px solid #eaecef;}
                      .review-item:last-child{border-bottom:none;}
                      .review-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;}
                      .reviewer-name{font-weight:600;color:#2c3e50;}
                      .review-date{font-size:.85rem;color:#6c757d;}
                      .review-rating{color:#ffc107;margin-bottom:.5rem;font-size:1.1rem;letter-spacing:2px;}
                      .review-content{color:#495057;line-height:1.6;}
                      .add-review-btn{background:#2c3e50;color:#fff;border:none;padding:.75rem 1.5rem;border-radius:4px;font-weight:500;cursor:pointer;transition:background .2s;}
                      .add-review-btn:hover{background:#1a252f;}
                      .review-form{display:none;margin-top:2rem;padding:1.5rem;background:#f8f9fa;border-radius:6px;}
                      .form-title{font-size:1.2rem;margin-bottom:1rem;color:#2c3e50;}
                      .form-group{margin-bottom:1rem;}
                      .form-label{display:block;margin-bottom:.5rem;font-weight:500;color:#495057;}
                      .form-input{width:100%;padding:.75rem;border:1px solid #ced4da;border-radius:4px;font-size:1rem;}
                      textarea.form-input{min-height:120px;resize:vertical;}
                      .rating-input{display:flex;flex-direction:row-reverse;justify-content:flex-end;margin-bottom:1rem;}
                      .rating-input input{display:none;}
                      .rating-input label{cursor:pointer;width:30px;height:30px;background:#e9ecef;display:flex;justify-content:center;align-items:center;color:#6c757d;font-size:1.5rem;transition:all .2s;}
                      .rating-input input:checked ~ label,
                      .rating-input label:hover,
                      .rating-input label:hover ~ label{background:#ffc107;color:#fff;}
                      .submit-btn{background:#28a745;color:#fff;border:none;padding:.75rem 1.5rem;border-radius:4px;font-weight:500;cursor:pointer;transition:background .2s;}
                      .submit-btn:hover{background:#218838;}
                      @media (max-width:600px){
                        .rating-summary{flex-direction:column;align-items:flex-start;}
                        .average-rating{margin-bottom:1rem;}
                        .reviews-container{padding:1rem;}
                      }
                    </style>

                    <script>
                     
                        const showFormBtn = document.getElementById('show-form-btn');
                        const reviewForm = document.getElementById('review-form');

                        showFormBtn.addEventListener('click', function () {
                          const open = reviewForm.style.display === 'block';
                          reviewForm.style.display = open ? 'none' : 'block';
                          showFormBtn.textContent = open ? 'Write a Review' : 'Cancel';
                        });
                    

                      document.addEventListener('livewire:init', () => {
                        Livewire.on('review-saved', ({ message }) => {
                          alert(message || 'Thank you for your review!');
                        });
                      });
                    </script>
                  </div>
                </div> {{-- /#review --}}
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>
