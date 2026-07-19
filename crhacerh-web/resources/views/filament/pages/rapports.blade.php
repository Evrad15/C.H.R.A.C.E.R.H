<x-filament-panels::page>
<style>
    @keyframes rFadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    @keyframes rSlideIn { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }
    .r-page    { animation:rFadeIn .3s ease-out; }
    .r-detail  { animation:rSlideIn .3s ease-out; }

    .r-folder-card { transition:transform .2s,box-shadow .2s; cursor:pointer; }
    .r-folder-card:hover { transform:translateY(-4px); box-shadow:0 10px 28px rgba(0,0,0,.12) !important; }

    .r-report-card { transition:transform .22s,box-shadow .22s; }
    .r-report-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,.11) !important; }
    .r-report-card:hover .r-doc-icon { transform:scale(1.08) rotate(2deg); }
    .r-doc-icon { transition:transform .22s; }

    .r-btn { display:inline-flex;align-items:center;gap:.3rem;padding:.38rem .75rem;border-radius:8px;
              font-size:.7rem;font-weight:600;text-decoration:none;border:none;cursor:pointer;
              transition:background .15s,transform .1s; white-space:nowrap; }
    .r-btn:hover { transform:scale(1.03); }
    .r-btn-open { background:#EEF2FF;color:#4338CA; }
    .r-btn-open:hover { background:#E0E7FF; }
    .r-btn-dl   { background:#ECFDF3;color:#065F46; }
    .r-btn-dl:hover { background:#D1FAE5; }
    .r-btn-del  { background:#FEF2F2;color:#B91C1C; }
    .r-btn-del:hover { background:#FEE2E2; }

    .r-back-btn { display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:10px;
                  background:#f3f4f6;border:none;cursor:pointer;font-size:.78rem;font-weight:600;color:#374151;
                  transition:background .15s; }
    .r-back-btn:hover { background:#e5e7eb; }

    .r-avatar { display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;
                border-radius:9999px;font-size:.58rem;font-weight:800;border:2px solid #fff;margin-left:-5px; }
    .r-avatar:first-child { margin-left:0; }
</style>

@php
    $folderPalettes = [
        '01'  => ['bg'=>'#eef2ff','border'=>'#c7d2fe','tab'=>'#e0e7ff','tabBorder'=>'#c7d2fe','text'=>'#1e1b4b','sub'=>'#6366f1','avatarBg'=>'#c7d2fe','avatarText'=>'#3730a3','accent'=>'#4F46E5'],
        '02'  => ['bg'=>'#fdf4ff','border'=>'#e9d5ff','tab'=>'#f3e8ff','tabBorder'=>'#e9d5ff','text'=>'#3b0764','sub'=>'#a855f7','avatarBg'=>'#e9d5ff','avatarText'=>'#6b21a8','accent'=>'#9333EA'],
        '03'  => ['bg'=>'#eff6ff','border'=>'#bfdbfe','tab'=>'#dbeafe','tabBorder'=>'#bfdbfe','text'=>'#1e3a5f','sub'=>'#3b82f6','avatarBg'=>'#bfdbfe','avatarText'=>'#1d4ed8','accent'=>'#3B82F6'],
        '04'  => ['bg'=>'#f0fdf4','border'=>'#bbf7d0','tab'=>'#dcfce7','tabBorder'=>'#bbf7d0','text'=>'#052e16','sub'=>'#22c55e','avatarBg'=>'#bbf7d0','avatarText'=>'#15803d','accent'=>'#16A34A'],
        '05'  => ['bg'=>'#fafafa','border'=>'#e5e7eb','tab'=>'#f3f4f6','tabBorder'=>'#e5e7eb','text'=>'#111827','sub'=>'#9ca3af','avatarBg'=>'#e5e7eb','avatarText'=>'#374151','accent'=>'#6B7280'],
        '06'  => ['bg'=>'#ecfdf5','border'=>'#a7f3d0','tab'=>'#d1fae5','tabBorder'=>'#a7f3d0','text'=>'#022c22','sub'=>'#10b981','avatarBg'=>'#a7f3d0','avatarText'=>'#065f46','accent'=>'#059669'],
        '07'  => ['bg'=>'#fff7ed','border'=>'#fed7aa','tab'=>'#ffedd5','tabBorder'=>'#fed7aa','text'=>'#431407','sub'=>'#f97316','avatarBg'=>'#fed7aa','avatarText'=>'#c2410c','accent'=>'#EA580C'],
        '08'  => ['bg'=>'#fff1f2','border'=>'#fecdd3','tab'=>'#ffe4e6','tabBorder'=>'#fecdd3','text'=>'#4c0519','sub'=>'#f43f5e','avatarBg'=>'#fecdd3','avatarText'=>'#be123c','accent'=>'#E11D48'],
        '09'  => ['bg'=>'#fefce8','border'=>'#fef08a','tab'=>'#fef9c3','tabBorder'=>'#fef08a','text'=>'#422006','sub'=>'#eab308','avatarBg'=>'#fef08a','avatarText'=>'#a16207','accent'=>'#CA8A04'],
        '10'  => ['bg'=>'#f0f9ff','border'=>'#bae6fd','tab'=>'#e0f2fe','tabBorder'=>'#bae6fd','text'=>'#082f49','sub'=>'#0ea5e9','avatarBg'=>'#bae6fd','avatarText'=>'#0369a1','accent'=>'#0284C7'],
        '11'  => ['bg'=>'#fff7f0','border'=>'#fcd9b6','tab'=>'#ffedd5','tabBorder'=>'#fcd9b6','text'=>'#431407','sub'=>'#f97316','avatarBg'=>'#fcd9b6','avatarText'=>'#c2410c','accent'=>'#EA580C'],
        '12'  => ['bg'=>'#fef2f2','border'=>'#fecaca','tab'=>'#fee2e2','tabBorder'=>'#fecaca','text'=>'#450a0a','sub'=>'#ef4444','avatarBg'=>'#fecaca','avatarText'=>'#b91c1c','accent'=>'#DC2626'],
        'all' => ['bg'=>'#f4f5f7','border'=>'#e4e6ea','tab'=>'#e4e6ea','tabBorder'=>'#d4d6da','text'=>'#1f2937','sub'=>'#6b7280','avatarBg'=>'#d1d5db','avatarText'=>'#374151','accent'=>'#374151'],
    ];
@endphp

{{-- ═══════════════════════ VUE DÉTAIL DOSSIER (drill-down) ═══════════════════════ --}}
@if($openedFolder !== null)
<div class="r-detail" style="display:flex;flex-direction:column;gap:1.75rem;">

    {{-- En-tête dossier ouvert --}}
    @php
        $monthNum = substr($openedFolder, 5, 2);
        $p = $folderPalettes[$monthNum] ?? $folderPalettes['05'];
    @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;
                background:{{ $p['bg'] }};border:1px solid {{ $p['border'] }};border-radius:18px;padding:1.25rem 1.5rem;">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <button class="r-back-btn" wire:click="closeFolder" type="button">
                <i class="fa-solid fa-arrow-left" style="font-size:.7rem;"></i> Retour
            </button>
            <div style="width:1px;height:28px;background:{{ $p['border'] }};"></div>
            <div>
                <div style="font-size:1.1rem;font-weight:800;color:{{ $p['text'] }};">
                    📁 {{ $openedFolderName }}
                </div>
                <div style="font-size:.7rem;color:{{ $p['sub'] }};margin-top:.1rem;">
                    {{ count($files) }} rapport(s) — Rapports hebdomadaires du mois
                </div>
            </div>
        </div>
        {{-- Search inside folder --}}
        <div style="position:relative;">
            <span style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.65rem;">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filtrer..."
                style="padding:.4rem .7rem .4rem 1.9rem;border:1px solid {{ $p['border'] }};border-radius:9px;
                       font-size:.72rem;background:#fff;outline:none;width:200px;color:#111827;" />
        </div>
    </div>

    {{-- Rapports de ce mois --}}
    @if(empty($files))
        <div style="display:flex;flex-direction:column;align-items:center;padding:4rem;background:#fff;
                    border:1px solid #f1f5f9;border-radius:18px;text-align:center;">
            <div style="font-size:2rem;margin-bottom:.75rem;">📭</div>
            <div style="font-size:.85rem;font-weight:700;color:#111827;">Aucun rapport ce mois</div>
            <div style="font-size:.72rem;color:#6b7280;margin-top:.3rem;">
                Générez des rapports automatiques via le bouton en en-tête.
            </div>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.25rem;">
            @foreach($files as $file)
            @php
                /* Parse le nom de fichier pour l'afficher proprement */
                $rawName = pathinfo($file['name'], PATHINFO_FILENAME);
                // Format: Du-08-au-12_Juin-2026 → "Du 08 au 12 · Juin 2026"
                if (preg_match('/^Du-(\d+)-au-(\d+)_(.+)-(\d{4})$/i', $rawName, $m)) {
                    $displayName  = "Du {$m[1]} au {$m[2]}";
                    $displaySub   = "{$m[3]} {$m[4]}";
                    $isMensuel    = false;
                } elseif (preg_match('/^Mensuel_(.+)-(\d{4})$/i', $rawName, $m)) {
                    $displayName = "Rapport Mensuel";
                    $displaySub  = "{$m[1]} {$m[2]}";
                    $isMensuel   = true;
                } else {
                    $parts       = preg_split('/[_\-]+/', $rawName);
                    $displayName = implode(' ', array_map('ucfirst', $parts));
                    $displaySub  = $file['modified']->safeTranslatedFormat('F Y');
                    $isMensuel   = false;
                }
            @endphp
            <div class="r-report-card" style="display:flex;flex-direction:column;background:#fff;
                border:1px solid #f1f5f9;border-radius:18px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);">

                {{-- XLSX icon --}}
                <div style="height:110px;background:linear-gradient(145deg,#f0fdf4,#ecfdf5);
                            display:flex;align-items:center;justify-content:center;border-bottom:1px solid #f1f5f9;">
                    <div class="r-doc-icon" style="position:relative;width:62px;height:78px;">
                        <div style="position:absolute;inset:0;background:rgba(0,0,0,.09);border-radius:4px 4px 5px 5px;
                            transform:translateY(3px) scale(.96);filter:blur(3px);"></div>
                        <div style="position:absolute;inset:0;background:#22c55e;border-radius:5px 0 5px 5px;
                            clip-path:polygon(0 0,70% 0,100% 20%,100% 100%,0 100%);">
                            <div style="margin:22px 0 0 9px;display:flex;flex-direction:column;gap:4px;">
                                <div style="height:4px;width:28px;background:rgba(255,255,255,.45);border-radius:3px;"></div>
                                <div style="height:4px;width:36px;background:rgba(255,255,255,.45);border-radius:3px;"></div>
                                <div style="height:4px;width:32px;background:rgba(255,255,255,.45);border-radius:3px;"></div>
                            </div>
                        </div>
                        <div style="position:absolute;top:0;right:0;width:30%;height:20%;
                            background:#a7f3d0;clip-path:polygon(0 0,0 100%,100% 100%);"></div>
                        <div style="position:absolute;top:0;right:0;width:30%;height:20%;
                            background:rgba(0,0,0,.12);clip-path:polygon(0 0,100% 0,0 100%);"></div>
                        <div style="position:absolute;top:50%;left:-4px;width:8px;height:22px;transform:translateY(-50%);
                            background:#16a34a;clip-path:polygon(0 50%,100% 0,100% 100%);"></div>
                        <div style="position:absolute;top:50%;right:-4px;width:8px;height:22px;transform:translateY(-50%);
                            background:#16a34a;clip-path:polygon(100% 50%,0 0,0 100%);"></div>
                        <div style="position:absolute;top:calc(50% - 11px);left:-3px;right:-3px;height:22px;
                            background:#16a34a;display:flex;align-items:center;justify-content:center;
                            box-shadow:0 2px 6px rgba(0,0,0,.2);">
                            <span style="color:#fff;font-size:10px;font-weight:900;letter-spacing:.12em;">XLSX</span>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div style="padding:.9rem;display:flex;flex-direction:column;gap:.45rem;flex:1;">
                    {{-- Type badge --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        @if($isMensuel)
                            <span style="background:#F3E8FF;color:#7C3AED;font-size:.6rem;font-weight:700;border-radius:5px;padding:2px 7px;text-transform:uppercase;">Mensuel</span>
                        @else
                            <span style="background:#ECFDF3;color:#065F46;font-size:.6rem;font-weight:700;border-radius:5px;padding:2px 7px;text-transform:uppercase;">Hebdomadaire</span>
                        @endif
                        <span style="font-size:.62rem;color:#9ca3af;">{{ $file['modified']->format('d/m/Y') }}</span>
                    </div>

                    {{-- Nom principal --}}
                    <div style="font-size:.95rem;font-weight:800;color:#111827;line-height:1.2;">
                        {{ $displayName }}
                    </div>
                    <div style="font-size:.7rem;color:#6b7280;font-weight:500;">{{ $displaySub }}</div>
                    <div style="font-size:.62rem;color:#9ca3af;">{{ number_format($file['size'] / 1024, 1) }} KB</div>

                    {{-- Actions --}}
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.4rem;padding-top:.65rem;border-top:1px solid #f3f4f6;">
                        <a href="{{ $file['url_open'] }}" target="_blank" class="r-btn r-btn-open">
                            <i class="fa-regular fa-eye"></i> Ouvrir
                        </a>
                        <a href="{{ $file['url_download'] }}" class="r-btn r-btn-dl">
                            <i class="fa-solid fa-download"></i> Télécharger
                        </a>
                        <button type="button"
                            wire:click="deleteFile('{{ $file['path'] }}')"
                            wire:confirm="Supprimer définitivement ce rapport ?"
                            class="r-btn r-btn-del">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>

{{-- ═══════════════════════ VUE GRILLE DES DOSSIERS ═══════════════════════ --}}
@else
<div class="r-page" style="display:flex;flex-direction:column;gap:2rem;">

    {{-- Toolbar --}}
    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;
                background:#fff;border:1px solid #f1f5f9;border-radius:20px;padding:1.25rem 1.5rem;
                box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <div>
            <div style="font-size:1.1rem;font-weight:800;color:#111827;">Rapports XLSX</div>
            <div style="font-size:.72rem;color:#6b7280;margin-top:.15rem;">
                Cliquez sur un mois pour consulter ses rapports hebdomadaires.
            </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;">
            <div style="background:#ECFDF3;color:#065F46;border-radius:9999px;padding:.3rem .85rem;
                        font-size:.7rem;font-weight:700;display:flex;align-items:center;gap:.35rem;">
                <i class="fa-solid fa-file-excel"></i> {{ $counts['all'] }} fichiers XLSX
            </div>
        </div>
    </div>

    {{-- Dossiers --}}
    <div>
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:#6b7280;margin-bottom:1.1rem;">
            📅 Mois disponibles — cliquez pour ouvrir
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:1.25rem;">
            @foreach($folders as $folderItem)
                @php
                    $monthNum = substr($folderItem['key'], 5, 2);
                    $p = $folderPalettes[$monthNum] ?? $folderPalettes['05'];
                @endphp
                <div class="r-folder-card" wire:click="openFolder('{{ $folderItem['key'] }}', '{{ $folderItem['name'] }}')"
                    style="position:relative;width:210px;padding-top:18px;">
                    {{-- Tab --}}
                    <div style="position:absolute;top:0;left:0;width:44%;height:20px;border-radius:8px 8px 0 0;
                        background:{{ $p['tab'] }};border:1px solid {{ $p['tabBorder'] }};border-bottom:none;z-index:1;"></div>
                    {{-- Body --}}
                    <div style="position:relative;z-index:0;background:{{ $p['bg'] }};
                        border:1px solid {{ $p['border'] }};border-radius:0 10px 10px 10px;padding:.9rem 1rem;
                        box-shadow:0 1px 4px rgba(0,0,0,.04);">
                        {{-- Indicator --}}
                        <div style="position:absolute;top:.7rem;right:.7rem;width:24px;height:24px;border-radius:50%;
                            background:{{ $p['accent'] }}20;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-chevron-right" style="font-size:.55rem;color:{{ $p['accent'] }};"></i>
                        </div>
                        <div style="font-size:.82rem;font-weight:800;color:{{ $p['text'] }};padding-right:1.5rem;">
                            {{ $folderItem['name'] }}
                        </div>
                        <div style="font-size:.65rem;color:{{ $p['sub'] }};margin-top:.25rem;">
                            {{ $folderItem['count'] }} rapport(s) hebdomadaire(s)
                        </div>
                        {{-- Footer --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;
                            margin-top:1.1rem;padding-top:.6rem;border-top:1px solid rgba(0,0,0,.06);">
                            <div style="display:flex;">
                                @foreach($employees as $emp)
                                    <div class="r-avatar" style="background:{{ $p['avatarBg'] }};color:{{ $p['avatarText'] }};">
                                        {{ $emp['initials'] }}
                                    </div>
                                @endforeach
                            </div>
                            <span style="font-size:.62rem;background:{{ $p['avatarBg'] }};color:{{ $p['avatarText'] }};
                                border-radius:9999px;padding:2px 8px;font-weight:700;">
                                Voir →
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach

            @if(empty($folders))
                <div style="padding:3rem;background:#fff;border:1px solid #f1f5f9;border-radius:18px;text-align:center;width:100%;">
                    <div style="font-size:2rem;margin-bottom:.5rem;">📁</div>
                    <div style="font-size:.85rem;font-weight:700;color:#111827;">Aucun dossier</div>
                    <div style="font-size:.72rem;color:#6b7280;margin-top:.25rem;">
                        Générez votre premier rapport via le bouton en en-tête.
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endif
</x-filament-panels::page>