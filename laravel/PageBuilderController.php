<?php

namespace App\Http\Controllers;

use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageBuilderController extends Controller
{
    /**
     * Tüm sayfaları listele
     * GET /api/page-builder/pages
     */
    public function index()
    {
        $pages = PageContent::getAllPages();
        
        $pageList = collect($pages)->map(function ($slug) {
            $sections = PageContent::where('page_slug', $slug)->count();
            return [
                'slug' => $slug,
                'name' => $this->getPageName($slug),
                'sections_count' => $sections,
            ];
        });

        return response()->json([
            'success' => true,
            'pages' => $pageList,
        ]);
    }

    /**
     * Sayfa içeriğini getir (public)
     * GET /api/page-builder/page/{slug}
     */
    public function show(string $slug)
    {
        $sections = PageContent::where('page_slug', $slug)
                               ->where('is_active', true)
                               ->orderBy('order')
                               ->get();

        return response()->json([
            'success' => true,
            'page_slug' => $slug,
            'sections' => $sections,
        ]);
    }

    /**
     * Sayfa içeriğini getir (admin)
     * GET /api/page-builder/admin/page/{slug}
     */
    public function showAdmin(string $slug)
    {
        $sections = PageContent::where('page_slug', $slug)
                               ->orderBy('order')
                               ->get();

        return response()->json([
            'success' => true,
            'page_slug' => $slug,
            'sections' => $sections,
        ]);
    }

    /**
     * Tüm sayfayı kaydet
     * POST /api/page-builder/page/{slug}
     */
    public function store(Request $request, string $slug)
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.section_key' => 'required|string',
            'sections.*.section_type' => 'required|string',
            'sections.*.content' => 'required|array',
            'sections.*.is_active' => 'boolean',
            'sections.*.order' => 'integer',
        ]);

        // Mevcut bölümleri sil ve yenilerini ekle
        PageContent::where('page_slug', $slug)->delete();

        foreach ($request->sections as $index => $section) {
            PageContent::create([
                'page_slug' => $slug,
                'section_key' => $section['section_key'],
                'section_type' => $section['section_type'],
                'content' => $section['content'],
                'is_active' => $section['is_active'] ?? true,
                'order' => $section['order'] ?? $index,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sayfa başarıyla kaydedildi',
        ]);
    }

    /**
     * Tek bölüm güncelle
     * PUT /api/page-builder/section/{slug}/{key}
     */
    public function updateSection(Request $request, string $slug, string $key)
    {
        $request->validate([
            'content' => 'required|array',
            'section_type' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
        ]);

        $section = PageContent::updateOrCreate(
            ['page_slug' => $slug, 'section_key' => $key],
            [
                'content' => $request->content,
                'section_type' => $request->section_type ?? 'custom',
                'is_active' => $request->is_active ?? true,
                'order' => $request->order ?? 0,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Bölüm güncellendi',
            'section' => $section,
        ]);
    }

    /**
     * Bölüm sil
     * DELETE /api/page-builder/section/{slug}/{key}
     */
    public function deleteSection(string $slug, string $key)
    {
        $deleted = PageContent::where('page_slug', $slug)
                              ->where('section_key', $key)
                              ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? 'Bölüm silindi' : 'Bölüm bulunamadı',
        ]);
    }

    /**
     * Medya yükleme
     * POST /api/page-builder/upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,webm|max:20480',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('page-builder', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'path' => $path,
            'filename' => $filename,
        ]);
    }

    /**
     * Bölümleri yeniden sırala
     * POST /api/page-builder/reorder/{slug}
     */
    public function reorder(Request $request, string $slug)
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.section_key' => 'required|string',
            'sections.*.order' => 'required|integer',
        ]);

        foreach ($request->sections as $item) {
            PageContent::where('page_slug', $slug)
                       ->where('section_key', $item['section_key'])
                       ->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sıralama güncellendi',
        ]);
    }

    /**
     * Block şemalarını getir
     * GET /api/page-builder/blocks
     */
    public function blocks()
    {
        return response()->json([
            'success' => true,
            'blocks' => $this->getBlockSchemas(),
        ]);
    }

    /**
     * Sayfa slug'ına göre isim döndür
     */
    private function getPageName(string $slug): string
    {
        $names = [
            'home' => 'Ana Sayfa',
            'about' => 'Hakkımızda',
            'contact' => 'İletişim',
            'global-header' => 'Global Header',
            'global-footer' => 'Global Footer',
        ];

        return $names[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
    }

    /**
     * Block şemalarını döndür
     */
    private function getBlockSchemas(): array
    {
        return [
            [
                'type' => 'hero',
                'name' => 'Hero Bölümü',
                'icon' => 'Sparkles',
                'description' => 'Ana sayfa için dikkat çekici başlık bölümü',
                'defaultContent' => [
                    'badge' => 'Hoş Geldiniz',
                    'title' => 'Ana Başlık',
                    'subtitle' => 'Alt başlık metni buraya gelecek.',
                    'primaryButton' => ['text' => 'Başla', 'link' => '/'],
                    'secondaryButton' => ['text' => 'Daha Fazla', 'link' => '/about'],
                    'image' => '',
                    'showStats' => false,
                ],
                'schema' => [
                    ['key' => 'badge', 'type' => 'text', 'label' => 'Rozet Metni'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Ana Başlık'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Alt Başlık'],
                    ['key' => 'primaryButton', 'type' => 'button', 'label' => 'Ana Buton'],
                    ['key' => 'secondaryButton', 'type' => 'button', 'label' => 'İkincil Buton'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Görsel'],
                    ['key' => 'showStats', 'type' => 'boolean', 'label' => 'İstatistikleri Göster'],
                ],
            ],
            [
                'type' => 'cta',
                'name' => 'Call to Action',
                'icon' => 'Megaphone',
                'description' => 'Kullanıcıyı aksiyona yönlendiren bölüm',
                'defaultContent' => [
                    'title' => 'Harekete Geç',
                    'description' => 'Açıklama metni buraya gelecek.',
                    'buttonText' => 'Başla',
                    'buttonLink' => '/',
                    'backgroundColor' => 'primary',
                ],
                'schema' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'description', 'type' => 'textarea', 'label' => 'Açıklama'],
                    ['key' => 'buttonText', 'type' => 'text', 'label' => 'Buton Metni'],
                    ['key' => 'buttonLink', 'type' => 'text', 'label' => 'Buton Linki'],
                    ['key' => 'backgroundColor', 'type' => 'select', 'label' => 'Arka Plan', 'options' => ['primary', 'secondary', 'gradient']],
                ],
            ],
            [
                'type' => 'testimonials',
                'name' => 'Müşteri Yorumları',
                'icon' => 'MessageSquareQuote',
                'description' => 'Kullanıcı yorumları ve referanslar',
                'defaultContent' => [
                    'title' => 'Kullanıcılarımız Ne Diyor?',
                    'items' => [
                        ['name' => 'Kullanıcı 1', 'role' => 'Ünvan', 'comment' => 'Yorum metni...', 'avatar' => '', 'rating' => 5],
                    ],
                ],
                'schema' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'items', 'type' => 'array', 'label' => 'Yorumlar', 'itemSchema' => [
                        ['key' => 'name', 'type' => 'text', 'label' => 'İsim'],
                        ['key' => 'role', 'type' => 'text', 'label' => 'Ünvan'],
                        ['key' => 'comment', 'type' => 'textarea', 'label' => 'Yorum'],
                        ['key' => 'avatar', 'type' => 'image', 'label' => 'Avatar'],
                        ['key' => 'rating', 'type' => 'number', 'label' => 'Puan', 'min' => 1, 'max' => 5],
                    ]],
                ],
            ],
            [
                'type' => 'stats',
                'name' => 'İstatistikler',
                'icon' => 'BarChart3',
                'description' => 'Sayısal veriler ve metrikler',
                'defaultContent' => [
                    'title' => 'Rakamlarla Biz',
                    'items' => [
                        ['value' => '100+', 'label' => 'Kullanıcı', 'icon' => 'Users'],
                        ['value' => '50+', 'label' => 'Proje', 'icon' => 'Briefcase'],
                    ],
                ],
                'schema' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'items', 'type' => 'array', 'label' => 'İstatistikler', 'itemSchema' => [
                        ['key' => 'value', 'type' => 'text', 'label' => 'Değer'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Etiket'],
                        ['key' => 'icon', 'type' => 'text', 'label' => 'İkon'],
                    ]],
                ],
            ],
            [
                'type' => 'features',
                'name' => 'Özellikler',
                'icon' => 'LayoutGrid',
                'description' => 'Özellik listesi',
                'defaultContent' => [
                    'title' => 'Özellikler',
                    'subtitle' => 'Neler sunuyoruz?',
                    'items' => [
                        ['icon' => 'Check', 'title' => 'Özellik 1', 'description' => 'Açıklama...'],
                    ],
                ],
                'schema' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'subtitle', 'type' => 'text', 'label' => 'Alt Başlık'],
                    ['key' => 'items', 'type' => 'array', 'label' => 'Özellikler', 'itemSchema' => [
                        ['key' => 'icon', 'type' => 'text', 'label' => 'İkon'],
                        ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                        ['key' => 'description', 'type' => 'textarea', 'label' => 'Açıklama'],
                    ]],
                ],
            ],
            [
                'type' => 'text',
                'name' => 'Metin Bloğu',
                'icon' => 'Type',
                'description' => 'Zengin metin içeriği',
                'defaultContent' => [
                    'title' => 'Başlık',
                    'content' => 'İçerik buraya gelecek...',
                    'alignment' => 'left',
                ],
                'schema' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'content', 'type' => 'richtext', 'label' => 'İçerik'],
                    ['key' => 'alignment', 'type' => 'select', 'label' => 'Hizalama', 'options' => ['left', 'center', 'right']],
                ],
            ],
            [
                'type' => 'image',
                'name' => 'Görsel',
                'icon' => 'Image',
                'description' => 'Tekli görsel bloğu',
                'defaultContent' => [
                    'src' => '',
                    'alt' => '',
                    'caption' => '',
                    'fullWidth' => false,
                ],
                'schema' => [
                    ['key' => 'src', 'type' => 'image', 'label' => 'Görsel'],
                    ['key' => 'alt', 'type' => 'text', 'label' => 'Alt Metin'],
                    ['key' => 'caption', 'type' => 'text', 'label' => 'Açıklama'],
                    ['key' => 'fullWidth', 'type' => 'boolean', 'label' => 'Tam Genişlik'],
                ],
            ],
            [
                'type' => 'video',
                'name' => 'Video',
                'icon' => 'Video',
                'description' => 'YouTube veya video embed',
                'defaultContent' => [
                    'url' => '',
                    'title' => '',
                    'autoplay' => false,
                ],
                'schema' => [
                    ['key' => 'url', 'type' => 'text', 'label' => 'Video URL'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'autoplay', 'type' => 'boolean', 'label' => 'Otomatik Oynat'],
                ],
            ],
            [
                'type' => 'spacer',
                'name' => 'Boşluk',
                'icon' => 'MoveVertical',
                'description' => 'Bölümler arası boşluk',
                'defaultContent' => ['height' => 60],
                'schema' => [
                    ['key' => 'height', 'type' => 'number', 'label' => 'Yükseklik (px)', 'min' => 10, 'max' => 200],
                ],
            ],
            [
                'type' => 'divider',
                'name' => 'Ayırıcı',
                'icon' => 'Minus',
                'description' => 'Yatay çizgi ayırıcı',
                'defaultContent' => ['style' => 'solid', 'color' => 'border'],
                'schema' => [
                    ['key' => 'style', 'type' => 'select', 'label' => 'Stil', 'options' => ['solid', 'dashed', 'dotted']],
                    ['key' => 'color', 'type' => 'select', 'label' => 'Renk', 'options' => ['border', 'primary', 'muted']],
                ],
            ],
            [
                'type' => 'header',
                'name' => 'Header',
                'icon' => 'PanelTop',
                'description' => 'Sayfa üst bilgisi',
                'defaultContent' => [
                    'logo' => '',
                    'logoText' => 'Logo',
                    'menuItems' => [
                        ['label' => 'Anasayfa', 'url' => '/'],
                        ['label' => 'Hakkımızda', 'url' => '/about'],
                    ],
                    'showSearch' => true,
                    'loginButton' => ['text' => 'Giriş', 'link' => '/login'],
                    'registerButton' => ['text' => 'Kayıt', 'link' => '/register'],
                ],
                'schema' => [
                    ['key' => 'logo', 'type' => 'image', 'label' => 'Logo'],
                    ['key' => 'logoText', 'type' => 'text', 'label' => 'Logo Metni'],
                    ['key' => 'menuItems', 'type' => 'array', 'label' => 'Menü Öğeleri', 'itemSchema' => [
                        ['key' => 'label', 'type' => 'text', 'label' => 'Metin'],
                        ['key' => 'url', 'type' => 'text', 'label' => 'URL'],
                    ]],
                    ['key' => 'showSearch', 'type' => 'boolean', 'label' => 'Arama Göster'],
                    ['key' => 'loginButton', 'type' => 'button', 'label' => 'Giriş Butonu'],
                    ['key' => 'registerButton', 'type' => 'button', 'label' => 'Kayıt Butonu'],
                ],
            ],
            [
                'type' => 'footer',
                'name' => 'Footer',
                'icon' => 'PanelBottom',
                'description' => 'Sayfa alt bilgisi',
                'defaultContent' => [
                    'logo' => '',
                    'description' => 'Site açıklaması...',
                    'socialLinks' => [
                        ['platform' => 'facebook', 'url' => '#'],
                        ['platform' => 'twitter', 'url' => '#'],
                    ],
                    'columns' => [
                        ['title' => 'Linkler', 'links' => [['label' => 'Anasayfa', 'url' => '/']]],
                    ],
                    'copyright' => '© 2024 Tüm hakları saklıdır.',
                ],
                'schema' => [
                    ['key' => 'logo', 'type' => 'image', 'label' => 'Logo'],
                    ['key' => 'description', 'type' => 'textarea', 'label' => 'Açıklama'],
                    ['key' => 'socialLinks', 'type' => 'array', 'label' => 'Sosyal Medya', 'itemSchema' => [
                        ['key' => 'platform', 'type' => 'select', 'label' => 'Platform', 'options' => ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube']],
                        ['key' => 'url', 'type' => 'text', 'label' => 'URL'],
                    ]],
                    ['key' => 'columns', 'type' => 'array', 'label' => 'Menü Sütunları', 'itemSchema' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                        ['key' => 'links', 'type' => 'array', 'label' => 'Linkler', 'itemSchema' => [
                            ['key' => 'label', 'type' => 'text', 'label' => 'Metin'],
                            ['key' => 'url', 'type' => 'text', 'label' => 'URL'],
                        ]],
                    ]],
                    ['key' => 'copyright', 'type' => 'text', 'label' => 'Telif Hakkı'],
                ],
            ],
            [
                'type' => 'contactInfo',
                'name' => 'İletişim Bilgileri',
                'icon' => 'MapPin',
                'description' => 'E-posta, telefon, adres',
                'defaultContent' => [
                    'email' => 'info@example.com',
                    'phone' => '+90 123 456 7890',
                    'address' => 'Adres bilgisi',
                    'workingHours' => 'Pzt-Cum: 09:00-18:00',
                ],
                'schema' => [
                    ['key' => 'email', 'type' => 'text', 'label' => 'E-posta'],
                    ['key' => 'phone', 'type' => 'text', 'label' => 'Telefon'],
                    ['key' => 'address', 'type' => 'textarea', 'label' => 'Adres'],
                    ['key' => 'workingHours', 'type' => 'text', 'label' => 'Çalışma Saatleri'],
                ],
            ],
            [
                'type' => 'contactForm',
                'name' => 'İletişim Formu',
                'icon' => 'Mail',
                'description' => 'İletişim formu',
                'defaultContent' => [
                    'title' => 'Bize Ulaşın',
                    'subtitle' => 'Sorularınız için formu doldurun.',
                    'fields' => [
                        ['name' => 'name', 'label' => 'Ad Soyad', 'type' => 'text', 'required' => true],
                        ['name' => 'email', 'label' => 'E-posta', 'type' => 'email', 'required' => true],
                        ['name' => 'message', 'label' => 'Mesaj', 'type' => 'textarea', 'required' => true],
                    ],
                    'submitButtonText' => 'Gönder',
                    'successMessage' => 'Mesajınız gönderildi!',
                ],
                'schema' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Alt Başlık'],
                    ['key' => 'fields', 'type' => 'array', 'label' => 'Form Alanları', 'itemSchema' => [
                        ['key' => 'name', 'type' => 'text', 'label' => 'Alan Adı'],
                        ['key' => 'label', 'type' => 'text', 'label' => 'Etiket'],
                        ['key' => 'type', 'type' => 'select', 'label' => 'Tip', 'options' => ['text', 'email', 'tel', 'textarea']],
                        ['key' => 'required', 'type' => 'boolean', 'label' => 'Zorunlu'],
                    ]],
                    ['key' => 'submitButtonText', 'type' => 'text', 'label' => 'Buton Metni'],
                    ['key' => 'successMessage', 'type' => 'text', 'label' => 'Başarı Mesajı'],
                ],
            ],
            [
                'type' => 'faq',
                'name' => 'SSS',
                'icon' => 'HelpCircle',
                'description' => 'Sık sorulan sorular',
                'defaultContent' => [
                    'title' => 'Sık Sorulan Sorular',
                    'items' => [
                        ['question' => 'Soru 1?', 'answer' => 'Cevap 1...'],
                    ],
                ],
                'schema' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'items', 'type' => 'array', 'label' => 'Sorular', 'itemSchema' => [
                        ['key' => 'question', 'type' => 'text', 'label' => 'Soru'],
                        ['key' => 'answer', 'type' => 'textarea', 'label' => 'Cevap'],
                    ]],
                ],
            ],
        ];
    }
}
