# Laravel React Page Builder

**Elementor benzeri görsel sayfa düzenleyici.** Laravel backend + Next.js/React frontend için tasarlandı.
Bu paket `laravel-react-page-builder` olarak adlandırılmıştır.

![Page Builder](https://img.shields.io/badge/Laravel-10.x-red) ![React](https://img.shields.io/badge/Next.js-14-black) ![License](https://img.shields.io/badge/license-MIT-green)

## 🚀 Özellikler

- ✅ **Görsel Sayfa Düzenleyici** - Sürükle-bırak desteği
- ✅ **15+ Hazır Block Tipi** - Hero, CTA, Testimonials, SSS, İletişim Formu...
- ✅ **Global Header/Footer** - Tüm sayfalarda kullanılacak merkezi ayarlar
- ✅ **Yeni Sayfa Oluşturma** - Dinamik sayfa ekleme
- ✅ **Türkçe Karakter Desteği** - Otomatik slug oluşturma
- ✅ **Anlık Önizleme** - Değişiklikleri hemen görün
- ✅ **Medya Yükleme** - Görsel/video yükleme desteği

## 📦 Kurulum

### Laravel Backend

#### 1. Migration Ekle

```bash
php artisan make:migration create_page_contents_table
```

```php
// database/migrations/xxxx_create_page_contents_table.php
Schema::create('page_contents', function (Blueprint $table) {
    $table->id();
    $table->string('page_slug')->index();
    $table->string('section_key');
    $table->string('section_type');
    $table->json('content');
    $table->boolean('is_active')->default(true);
    $table->integer('order')->default(0);
    $table->timestamps();
    
    $table->unique(['page_slug', 'section_key']);
});
```

#### 2. Model Oluştur

```php
// app/Models/PageContent.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = [
        'page_slug', 'section_key', 'section_type', 
        'content', 'is_active', 'order'
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

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

    public static function getAllPages(): array
    {
        return self::distinct()->pluck('page_slug')->toArray();
    }
}
```

#### 3. Controller Ekle

```php
// app/Http/Controllers/PageBuilderController.php
// Tam controller kodu için: LMSbackend-main/app/Http/Controllers/PageBuilderController.php
```

#### 4. Route'ları Ekle

```php
// routes/api.php

// Public routes
Route::prefix('page-builder')->group(function () {
    Route::get('/page/{slug}', [PageBuilderController::class, 'show']);
    Route::get('/blocks', [PageBuilderController::class, 'blocks']);
});

// Admin routes (auth gerekli)
Route::prefix('page-builder')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/pages', [PageBuilderController::class, 'index']);
    Route::get('/admin/page/{slug}', [PageBuilderController::class, 'showAdmin']);
    Route::post('/page/{slug}', [PageBuilderController::class, 'store']);
    Route::put('/section/{slug}/{key}', [PageBuilderController::class, 'updateSection']);
    Route::delete('/section/{slug}/{key}', [PageBuilderController::class, 'deleteSection']);
    Route::post('/upload', [PageBuilderController::class, 'upload']);
    Route::delete('/media', [PageBuilderController::class, 'deleteMedia']);
    Route::post('/reorder/{slug}', [PageBuilderController::class, 'reorder']);
});
```

---

### React/Next.js Frontend

#### 1. Bağımlılıkları Yükle

```bash
npm install @dnd-kit/core @dnd-kit/sortable @dnd-kit/utilities
```

#### 2. API Helper Fonksiyonları

```typescript
// lib/page-builder.ts
const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export async function getPageSections(slug: string) {
    const res = await fetch(`${API_URL}/page-builder/page/${slug}`, {
        cache: 'no-store'
    });
    if (!res.ok) return [];
    const data = await res.json();
    return data.sections || [];
}

export async function getPageContent(slug: string) {
    const sections = await getPageSections(slug);
    const content: Record<string, any> = {};
    sections.forEach((section: any) => {
        content[section.section_type] = section.content;
    });
    return content;
}
```

#### 3. Admin Sayfa Düzenleyici

```
app/admin/page-builder/page.tsx
```

Tam kod için: `LMSfrontend-main/app/admin/page-builder/page.tsx`

#### 4. Dinamik Sayfa Route

```typescript
// app/[slug]/page.tsx
import { getPageContent } from "@/lib/page-builder";
import { notFound } from "next/navigation";

export default async function DynamicPage({ params }) {
    const { slug } = await params;
    const content = await getPageContent(slug);
    
    if (!content || Object.keys(content).length === 0) {
        notFound();
    }
    
    // Block'ları render et...
}
```

---

## 🧱 Block Tipleri

| Block | Açıklama |
|-------|----------|
| `hero` | Ana sayfa hero bölümü - başlık, alt başlık, butonlar, görsel |
| `cta` | Call to Action - aksiyon çağrısı |
| `testimonials` | Müşteri/öğrenci yorumları |
| `howItWorks` | Adım adım süreç açıklaması |
| `stats` | İstatistikler - sayısal veriler |
| `features` | Özellikler listesi |
| `text` | Zengin metin içeriği |
| `image` | Tekli görsel |
| `video` | Video embed (YouTube/Vimeo) |
| `spacer` | Boşluk |
| `divider` | Yatay çizgi ayırıcı |
| `footer` | Sayfa alt bilgisi |
| `header` | Sayfa üst bilgisi |
| `contactInfo` | İletişim bilgileri |
| `contactForm` | İletişim formu |
| `faq` | Sık sorulan sorular |

---

## 📖 Kullanım

### Admin Panelden Sayfa Düzenleme

1. `/admin/page-builder` adresine gidin
2. Dropdown'dan sayfa seçin
3. Sol panelden block ekleyin (tıklayarak)
4. Sağ panelden block ayarlarını düzenleyin
5. "Kaydet" butonuna tıklayın

### Yeni Sayfa Oluşturma

1. "Yeni Sayfa" butonuna tıklayın
2. Sayfa adını girin (slug otomatik oluşur)
3. "Oluştur" ile kaydedin
4. Block'lar ekleyin ve kaydedin

### Frontend'de Kullanım

```tsx
// Sayfa bileşeninde
import { getPageContent } from "@/lib/page-builder";
import { HeroSection } from "@/components/hero-section";

export default async function HomePage() {
    const content = await getPageContent("home");
    
    return (
        <>
            <HeroSection content={content.hero} />
            {/* Diğer bileşenler... */}
        </>
    );
}
```

---

## 🔧 Bileşenleri Dinamik Hale Getirme

Mevcut bileşenlerinizi page builder ile uyumlu hale getirmek için `content` prop'u ekleyin:

```tsx
// Önce (statik)
export function HeroSection() {
    return <h1>Sabit Başlık</h1>;
}

// Sonra (dinamik)
interface HeroContent {
    title?: string;
    subtitle?: string;
}

export function HeroSection({ content }: { content?: HeroContent }) {
    const title = content?.title || "Varsayılan Başlık";
    return <h1>{title}</h1>;
}
```

---

## 📁 Dosya Yapısı

```
Backend (Laravel)
├── app/
│   ├── Models/PageContent.php
│   └── Http/Controllers/PageBuilderController.php
├── database/
│   ├── migrations/xxxx_create_page_contents_table.php
│   └── seeders/PageContentSeeder.php
└── routes/api.php

Frontend (Next.js)
├── app/
│   ├── admin/page-builder/page.tsx
│   └── [slug]/page.tsx
├── lib/page-builder.ts
└── components/
    ├── hero-section.tsx
    ├── cta-section.tsx
    ├── testimonials.tsx
    └── ...
```

---

## 🌐 API Endpoints

| Method | Endpoint | Açıklama |
|--------|----------|----------|
| GET | `/api/page-builder/page/{slug}` | Sayfa içeriğini getir (public) |
| GET | `/api/page-builder/blocks` | Block şemalarını getir |
| GET | `/api/page-builder/pages` | Tüm sayfaları listele |
| POST | `/api/page-builder/page/{slug}` | Sayfayı kaydet |
| PUT | `/api/page-builder/section/{slug}/{key}` | Bölüm güncelle |
| DELETE | `/api/page-builder/section/{slug}/{key}` | Bölüm sil |
| POST | `/api/page-builder/upload` | Medya yükle |
| POST | `/api/page-builder/reorder/{slug}` | Bölümleri sırala |

---

## 📄 Lisans

MIT License

---

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

---

## 📞 İletişim

Sorularınız için issue açabilirsiniz.
#   L m s - r e a c t - p a g e - b u i l d e r 
 
 
