@section('meta')
<meta property="title" content="{{ settings('site_meta_title') }}" />
<meta name="keywords" content="{{ settings('site_meta_keywords') }}" />
<meta property="og:title" content="{{ settings('site_meta_title') }}" />
<meta property="og:description" content="{{ settings('site_meta_description') }}" />
<meta name="description" content="{{ settings('site_meta_description') }}" />
<meta property="og:image" content="{{ uploadedFIle(settings('site_meta_image')) }}" />
<meta property="og:image:secure_url" content="{{ uploadedFIle(settings('site_meta_image')) }}" />
<meta name="twitter:title" content="{{ settings('site_meta_title') }}" />
<meta name="twitter:description" content="{{ settings('site_meta_description') }}" />
<meta name="twitter:image" content="{{ uploadedFIle(settings('site_meta_image')) }}" />
@endsection
<div>
  
    <!--  Hero Section />-->
  <!-- < Hero Section -->
    <section class="hero bg--white">

        <div class="container position-relative px-0">
            @php
                $galleries = is_array(json_decode(settings('home_galleries_image'))) ? json_decode(settings('home_galleries_image')) : [];
                $links = @json_decode(settings('home_galleries_link'));
            @endphp
            <div class="hero--content w--100">
                <div class="row gy-2">
                    @isset($galleries[0])
                    <div class="col-md-6">
                        <div class="hero-right--thumb radius--8 overflow--hidden">
                            <img class="fit--img"  src="{{ uploadedFile($galleries[0]) }}" alt="Gallery">    
                        </div>
                    </div>
                    @endisset
                    <div class="col-md-6">
                        @isset($galleries[1])
                        <div class="hero-left--thumb mb-3 radius--8 overflow--hidden">
                            <img  style="width: 100%; height:280px" src="{{ uploadedFile($galleries[1]) }}" alt="Gallery">
                        </div>
                        @endisset
                        <div class="row">
                            @isset($galleries[2])
                            <div class="col-md-6 col-6">
                                <div class="hero-img3 radius--8 overflow--hidden">
                                    <img class="fit--img" src="{{ uploadedFile($galleries[2]) }}" alt="Gallery">
                                </div>
                            </div>
                            @endisset
                            @isset($galleries[3])
                            <div class="col-md-6 col-6">
                                <div class="hero-img3 radius--8 overflow--hidden">
                                    <img class="fit--img" src="{{ uploadedFile($galleries[3]) }}" alt="Gallery">
                                </div>
                            </div>
                            @endisset
                        </div>
                    </div>
                </div>
                @php
                    $category_section = is_array(json_decode(settings('cat_title'))) ?
                    json_decode(settings('cat_title')) : [];
                    $links = @json_decode(settings('cat_link'));
                    $icons = @json_decode(settings('cat_icon'));
                @endphp
                <div class="row">
                    @foreach ($category_section as $key => $title)
                    <div class="category-listing" bis_skin_checked="1">
                        <a href="{{ $links[$key] }}">
                            <img src="{{ uploadedFile($icons[$key]) }}" alt="{{ $title }}">
                            <span class="ciname">{{ $title }}</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!-- Categories product section -->
    @foreach ($this->sections as $section)
    <section class="browse--section py-10">
        <div class="container">
            {{-- <p class="text-end">
                @if($section->button_url && $section->button_text)
                <a href="{{ $section->button_url }}" class="viewall">{{ $section->button_text }}</a>
                @endif
            </p> --}}
            <div class="row gy-5 justify-content-center">
                <div class="col-xl-7 col-lg-12 col-md-8">
                    <div class="section-content-4 d-flex justify-content-center flex-column align-items-center mb-3">
                        <h6 class="title wow animate__animated animate__fadeInUp text-center fs--32 fw--600 splite-text mb-2" data-splitting data-wow-delay="0.2s">{{ $section->name }}</h6>
                        @if($section->short_description)
                        <p class="text--black text-center fw--600">{{ $section->short_description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="row gy-4 justify-content-center">
                @php
                $products = Cache::rememberForever('products-section-'.$section->id, function () use($section){
                    return App\Models\Product::whereIn('id',$section->products->pluck('product_id'))
                                        ->publish()
                                        ->orderByPrice()
                                        ->get();
                });
                @endphp

                @foreach ($products->take(12) as $product)
                    <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                        <livewire:frontend.product-box :$product wire:key='{{ $product->id }}' lazy>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endforeach
    <!-- Categories product section -->


    @php
    $siteUrl    = url('/');
    $currentUrl = url()->current();
    $siteName   = config('app.name');
    $metaTitle  = settings('site_meta_title') ?: $siteName;
    $metaDesc   = strip_tags(settings('site_meta_description'));
    $metaImage  = uploadedFile(settings('site_meta_image'));
    $phone      = settings('header_phone') ?? null;

    // Home page categories (titles, links, icons)
    $catTitles = is_array(json_decode(settings('cat_title'))) ? json_decode(settings('cat_title')) : [];
    $catLinks  = @json_decode(settings('cat_link')) ?: [];
    $catIcons  = @json_decode(settings('cat_icon')) ?: [];

    // Hero gallery images
    $galleries = is_array(json_decode(settings('home_galleries_image'))) ? json_decode(settings('home_galleries_image')) : [];

    $graph = [];

    // WebSite
    $graph[] = [
        '@type'  => 'WebSite',
        '@id'    => "{$siteUrl}#website",
        'url'    => $siteUrl,
        'name'   => $siteName,
        'inLanguage' => 'bn-BD',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => "{$siteUrl}/search?q={search_term_string}",
            'query-input' => 'required name=search_term_string',
        ],
    ];

    // Organization
    $org = [
        '@type' => 'Organization',
        '@id'   => "{$siteUrl}#organization",
        'name'  => $siteName,
        'url'   => $siteUrl,
        'logo'  => [
            '@type' => 'ImageObject',
            'url'   => $metaImage,
        ],
    ];
    if (!empty($phone)) {
        $org['contactPoint'] = [[
            '@type'         => 'ContactPoint',
            'contactType'   => 'customer support',
            'telephone'     => $phone,
            'areaServed'    => 'BD',
            'availableLanguage' => ['bn', 'en'],
        ]];
    }
    $graph[] = $org;

    // Home WebPage
    $graph[] = [
        '@type'  => 'WebPage',
        '@id'    => "{$currentUrl}#webpage",
        'url'    => $currentUrl,
        'name'   => $metaTitle,
        'isPartOf' => ['@id' => "{$siteUrl}#website"],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url'   => $metaImage,
        ],
        'description' => $metaDesc,
        'about' => array_values(array_map(fn($t) => ['@type' => 'Thing', 'name' => $t], $catTitles)),
    ];

    // SiteNavigationElement (top category tiles)
    if (!empty($catTitles)) {
        $navItems = [];
        foreach ($catTitles as $i => $title) {
            $navItems[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $title,
                'item'     => $catLinks[$i] ?? $siteUrl,
                'image'    => isset($catIcons[$i]) ? uploadedFile($catIcons[$i]) : null,
            ];
        }
        // Remove null image keys
        foreach ($navItems as &$it) { if ($it['image'] === null) unset($it['image']); }

        $graph[] = [
            '@type'           => 'SiteNavigationElement',
            '@id'             => "{$currentUrl}#site-navigation",
            'name'            => 'Top categories',
            'url'             => $currentUrl,
            'itemListElement' => $navItems,
        ];
    }

    // Hero image gallery
    if (!empty($galleries)) {
        $galleryMedia = [];
        foreach ($galleries as $imgId) {
            $galleryMedia[] = [
                '@type' => 'ImageObject',
                'url'   => uploadedFile($imgId),
            ];
        }
        $graph[] = [
            '@type'            => 'ImageGallery',
            '@id'              => "{$currentUrl}#hero-gallery",
            'name'             => 'Home hero gallery',
            'associatedMedia'  => $galleryMedia,
        ];
    }

    // Product ItemLists for the visible homepage sections (top 12 per section)
    if (!empty($this->sections)) {
        foreach ($this->sections as $section) {
            $products = Cache::rememberForever('products-section-'.$section->id, function () use ($section) {
                return App\Models\Product::whereIn('id', $section->products->pluck('product_id'))
                    ->publish()
                    ->orderByPrice()
                    ->get();
            });

            if ($products->isEmpty()) continue;

            $items = [];
            foreach ($products->take(12) as $idx => $product) {
                $productUrl = url('/product/' . ($product->slug ?? $product->id));
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $idx + 1,
                    'item'     => [
                        '@type'  => 'Product',
                        '@id'    => "{$productUrl}#product",
                        'url'    => $productUrl,
                        'name'   => $product->name,
                        'image'  => [ uploadedFile($product->thumbnail_img) ],
                        // Keep homepage Product minimal; full Offer/AggregateRating belongs on the product detail page.
                    ],
                ];
            }

            $graph[] = [
                '@type'           => 'ItemList',
                '@id'             => "{$currentUrl}#section-{$section->id}",
                'name'            => $section->name,
                'numberOfItems'   => count($items),
                'itemListOrder'   => 'http://schema.org/ItemListOrderAscending',
                'itemListElement' => $items,
            ];
        }
    }

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>


    <!-- footer top section -->
    <section class="footertop">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="contentwrp">
                        {!! settings('footer_content') !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- footer top section --> 
</div>
