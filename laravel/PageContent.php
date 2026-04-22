<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = [
        'page_slug',
        'section_key',
        'section_type',
        'content',
        'is_active',
        'order',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Bir sayfanın tüm içeriğini getir
     */
    public static function getPageContent(string $slug): array
    {
        $sections = self::where('page_slug', $slug)
                        ->where('is_active', true)
                        ->orderBy('order')
                        ->get();

        $content = [];
        foreach ($sections as $section) {
            $content[$section->section_type] = $section->content;
        }
        
        return $content;
    }

    /**
     * Belirli bir bölümün içeriğini getir
     */
    public static function getSectionContent(string $slug, string $sectionKey): ?array
    {
        $section = self::where('page_slug', $slug)
                       ->where('section_key', $sectionKey)
                       ->where('is_active', true)
                       ->first();

        return $section?->content;
    }

    /**
     * Tüm sayfa slug'larını getir
     */
    public static function getAllPages(): array
    {
        return self::distinct()->pluck('page_slug')->toArray();
    }
}
