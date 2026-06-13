<x-filament-panels::page>
<style>
    @keyframes rFadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    .r-page { animation:rFadeIn .3s ease-out; }
    .r-card {
        background:#fff; border:1px solid #f1f5f9; border-radius:20px;
        box-shadow:0 1px 4px rgba(0,0,0,.04); transition:transform .22s,box-shadow .22s;
        overflow:hidden; position:relative;
    }
    .r-card:hover { box-shadow:0 12px 32px rgba(0,0,0,.08) !important; }
    .dark .r-card { background:#1f2937; border-color:#374151; box-shadow:none; }
</style>

<div class="r-page">
    <div class="r-card p-1">
        {{ $this->table }}
    </div>
</div>
</x-filament-panels::page>