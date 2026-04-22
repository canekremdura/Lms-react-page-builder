/**
 * Page Builder API Yardımcı Fonksiyonları
 */

// process.env için global tip tanımı veya güvenli kontrol
const getApiUrl = () => {
    if (typeof process !== "undefined" && process.env?.NEXT_PUBLIC_API_URL) {
        return process.env.NEXT_PUBLIC_API_URL;
    }
    return "http://localhost:8000/api";
};

const getHeaders = () => {
    const token = typeof window !== "undefined"
        ? (localStorage.getItem("token") || localStorage.getItem("auth_token"))
        : null;

    return {
        "Content-Type": "application/json",
        "Authorization": token ? `Bearer ${token}` : "",
    };
};

/**
 * Sayfa içeriğini (bölümlerini) getir
 */
export async function getPageSections(slug: string) {
    try {
        const res = await fetch(`${getApiUrl()}/page-builder/page/${slug}`, {
            cache: 'no-store'
        });
        if (!res.ok) return [];
        const data = await res.json();
        return data.sections || [];
    } catch (err) {
        console.error("getPageSections error:", err);
        return [];
    }
}

/**
 * Sayfa içeriğini key-value objesi olarak getir
 */
export async function getPageContent(slug: string) {
    const sections = await getPageSections(slug);
    const content: Record<string, any> = {};

    sections.forEach((section: any) => {
        // Section type'ı key olarak kullanırız
        content[section.section_type] = section.content;
    });

    return content;
}

/**
 * Tüm sayfaları listele (Admin)
 */
export async function getPages() {
    const res = await fetch(`${getApiUrl()}/page-builder/pages`, {
        headers: getHeaders()
    });
    return await res.json();
}

/**
 * Sayfayı kaydet (Admin)
 */
export async function savePage(slug: string, sections: any[]) {
    const res = await fetch(`${getApiUrl()}/page-builder/page/${slug}`, {
        method: "POST",
        headers: getHeaders(),
        body: JSON.stringify({ sections }),
    });
    return await res.json();
}

/**
 * Medya yükle
 */
export async function uploadMedia(file: File) {
    const formData = new FormData();
    formData.append("file", file);

    const token = typeof window !== "undefined"
        ? (localStorage.getItem("token") || localStorage.getItem("auth_token"))
        : null;

    const res = await fetch(`${getApiUrl()}/page-builder/upload`, {
        method: "POST",
        headers: {
            "Authorization": token ? `Bearer ${token}` : "",
        },
        body: formData,
    });
    return await res.json();
}
