<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;

class SchemaHelper
{
    /**
     * Generate Product Schema
     */
    public static function product($product, $brand = null, $reviews = [])
    {
        // Product Images (thumbnail + photos)
        $images = [];
        if ($product->thumbnail_img) {
            $images[] = asset('storage/'.$product->thumbnail_img);
        }
        if (!empty($product->photos)) {
            $photos = is_array($product->photos) ? $product->photos : json_decode($product->photos, true);
            foreach ($photos as $p) {
                $images[] = asset('storage/'.$p);
            }
        }

        // Review Data
        $reviewCount = count($reviews);
        $avgRating   = $reviewCount > 0 ? round(collect($reviews)->avg('rating'), 1) : ($product->rating ?? 0);

        $schema = [
            "@context"    => "https://schema.org/",
            "@type"       => "Product",
            "name"        => $product->name,
            "image"       => $images,
            "description" => strip_tags($product->short_description ?? $product->description),
            "sku"         => $product->barcode ?: null,
            "brand"       => [
                "@type" => "Brand",
                "name"  => $brand->name ?? "Generic"
            ],
            "offers" => [
                "@type"         => "Offer",
                "url"           => url('/product/'.$product->slug),
                "priceCurrency" => "BDT",
                "price"         => $product->unit_price,
                "availability"  => "https://schema.org/".($product->current_stock > 0 ? "InStock" : "OutOfStock"),
                "itemCondition" => "https://schema.org/NewCondition"
            ],
        ];

        // Add AggregateRating if available
        if ($avgRating > 0) {
            $schema["aggregateRating"] = [
                "@type"       => "AggregateRating",
                "ratingValue" => $avgRating,
                "reviewCount" => $reviewCount > 0 ? $reviewCount : ($product->num_of_sale ?? 1)
            ];
        }

        // Add Reviews
        if ($reviewCount > 0) {
            $schema["review"] = collect($reviews)->map(function ($r) {
                return [
                    "@type" => "Review",
                    "reviewRating" => [
                        "@type"       => "Rating",
                        "ratingValue" => $r->rating,
                        "bestRating"  => 5
                    ],
                    "author" => [
                        "@type" => "Person",
                        "name"  => $r->user->name ?? "Anonymous"
                    ],
                    "reviewBody" => strip_tags($r->comment),
                    "datePublished" => $r->created_at->toDateString()
                ];
            })->toArray();
        }

        return $schema;
    }

    /**
     * Generate Category Schema
     */
    public static function category($category)
    {
        return [
            "@context"    => "https://schema.org",
            "@type"       => "CollectionPage",
            "name"        => $category->name,
            "description" => strip_tags($category->description ?? $category->short_description),
            "url"         => url('/category/'.$category->slug)
        ];
    }

    /**
     * Generate Brand Schema
     */
    public static function brand($brand)
    {
        return [
            "@context"    => "https://schema.org",
            "@type"       => "Brand",
            "name"        => $brand->name,
            "description" => strip_tags($brand->description ?? ''),
            "logo"        => $brand->logo ? asset('storage/'.$brand->logo) : null,
            "url"         => url('/brand/'.$brand->slug)
        ];
    }

    /**
     * Generate FAQ Schema
     */
    public static function faq($faqs)
    {
        $faqList = [];
        foreach ($faqs as $faq) {
            $faqList[] = [
                "@type" => "Question",
                "name"  => $faq->question,
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text"  => $faq->answer
                ]
            ];
        }

        return [
            "@context"    => "https://schema.org",
            "@type"       => "FAQPage",
            "mainEntity"  => $faqList
        ];
    }

    /**
     * Generate Blog/Article Schema
     */
    public static function article($post)
    {
        return [
            "@context"      => "https://schema.org",
            "@type"         => "Article",
            "headline"      => $post->title,
            "image"         => [asset('storage/'.$post->thumbnail)],
            "author"        => [
                "@type" => "Person",
                "name"  => $post->author->name ?? "Editorial Team"
            ],
            "publisher"     => [
                "@type" => "Organization",
                "name"  => config('app.name'),
                "logo"  => [
                    "@type" => "ImageObject",
                    "url"   => asset('images/logo.png')
                ]
            ],
            "datePublished" => $post->created_at->toDateString(),
            "dateModified"  => $post->updated_at->toDateString()
        ];
    }
}
