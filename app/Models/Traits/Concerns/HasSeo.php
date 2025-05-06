<?php

namespace App\Models\Traits\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait HasSeo
{
    public static function generateSlug(string $value): string
    {
        return str($value)->slug().'-'.str(str()->random(6))->lower();
    }

    /**
     * @return array{
     *     max_size: int,
     *     accepted_types: string[],
     *     disk: string,
     *     directory: string
     * }
     */
    public static function metaImageConstraints(): array
    {
        return [
            'max_size' => 5120,
            'accepted_types' => ['image/*'],
            'disk' => 'public',
            'directory' => 'meta/images',
        ];
    }

    public static function generateSeoBlock(string $title, string $description): array
    {
        return [
            'meta_title' => self::makeMetaTitle($title),
            'meta_description' => self::makeMetaDescription($description),
            'meta_keywords' => self::makeMetaKeywords($title),
        ];
    }

    public static function makeMetaTitle(string $title): string
    {
        return $title.' | '.config('app.name');
    }

    public static function makeMetaDescription(string $description): string
    {
        return Str::length($description) > 37 ? Str::substr($description, 0, 373).'...' : $description;
    }

    public static function makeMetaKeywords(string $title): string
    {
        return implode(', ', array_filter(explode(' ', Str::lower($title))));
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function metaImage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset("storage/$value") : null
        );
    }
}
