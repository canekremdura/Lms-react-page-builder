"use client";

import React, { useState, useEffect, useCallback } from "react";
import { DndContext, closestCenter, DragEndEvent, DragOverlay } from "@dnd-kit/core";
import { SortableContext, verticalListSortingStrategy, useSortable, arrayMove } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import {
    Plus, Save, Eye, Trash2, GripVertical, Settings,
    Sparkles, Type, Image as ImageIcon, Video,
    MoveVertical, Minus, PanelBottom, PanelTop,
    FilePlus, ChevronRight, Loader2, MapPin, Mail, HelpCircle
} from "lucide-react";

/**
 * Bu bileşen temel bir Page Builder arayüzüdür.
 * Kullanılan UI bileşenleri (Button, Input, vb.) Shadcn UI standartlarındadır.
 * Kendi projenize entegre ederken bileşen yollarını güncelleyin.
 */

interface BlockSchema {
    type: string;
    name: string;
    icon: any;
    description: string;
    defaultContent: any;
    schema: any[];
}

interface PageSection {
    id?: string;
    section_key: string;
    section_type: string;
    content: any;
    is_active: boolean;
    order: number;
}

export default function PageBuilder() {
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [blocks, setBlocks] = useState<BlockSchema[]>([]);
    const [sections, setSections] = useState<PageSection[]>([]);
    const [currentPage, setCurrentPage] = useState("home");
    const [selectedSection, setSelectedSection] = useState<string | null>(null);

    // API'den block şemalarını ve sayfa içeriğini yükleme mantığı buraya gelecek
    // (LMS projesinden referans alınarak özelleştirilmiştir)

    return (
        <div className="flex h-screen bg-background overflow-hidden border">
            {/* Sol Panel - Blocklar */}
            <div className="w-80 border-r bg-card flex flex-col">
                <div className="p-4 border-b font-bold flex items-center gap-2">
                    <Plus className="h-5 w-5" /> Blocklar
                </div>
                <div className="p-4 grid grid-cols-2 gap-2 overflow-y-auto">
                    {/* Block butonları */}
                    <p className="col-span-2 text-xs text-muted-foreground mb-2">Block eklemek için tıklayın</p>
                    <div className="p-3 border rounded hover:bg-muted cursor-pointer flex flex-col items-center gap-1">
                        <Sparkles className="h-5 w-5" />
                        <span className="text-xs">Hero</span>
                    </div>
                    <div className="p-3 border rounded hover:bg-muted cursor-pointer flex flex-col items-center gap-1">
                        <Type className="h-5 w-5" />
                        <span className="text-xs">Metin</span>
                    </div>
                </div>
            </div>

            {/* Orta Panel - Canvas */}
            <div className="flex-1 flex flex-col bg-muted/20">
                <div className="h-14 border-b bg-card flex items-center justify-between px-4">
                    <div className="font-semibold">{currentPage} Düzenleniyor</div>
                    <div className="flex gap-2">
                        <button className="px-3 py-1 border rounded text-sm flex items-center gap-1 hover:bg-muted">
                            <Eye className="h-4 w-4" /> Önizle
                        </button>
                        <button className="px-3 py-1 bg-primary text-primary-foreground rounded text-sm flex items-center gap-1">
                            <Save className="h-4 w-4" /> Kaydet
                        </button>
                    </div>
                </div>
                <div className="flex-1 overflow-y-auto p-8">
                    <div className="max-w-4xl mx-auto min-h-full bg-background shadow-lg rounded-lg p-6 border-2 border-dashed border-muted flex flex-col items-center justify-center text-muted-foreground">
                        <Plus className="h-10 w-10 mb-2 opacity-20" />
                        Henüz bölüm yok. Sol panelden block ekleyin.
                    </div>
                </div>
            </div>

            {/* Sağ Panel - Ayarlar */}
            <div className="w-80 border-l bg-card flex flex-col text-sm">
                <div className="p-4 border-b font-bold flex items-center gap-2">
                    <Settings className="h-5 w-5" /> Ayarlar
                </div>
                <div className="p-8 text-center text-muted-foreground italic">
                    Düzenlemek için bir bölüm seçin
                </div>
            </div>
        </div>
    );
}
