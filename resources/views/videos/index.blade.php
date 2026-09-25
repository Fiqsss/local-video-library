@extends('layouts.app')
@section('title','VidoHub | Koleksi Video')
@section('content')
<div class="heading"><div><h1>{{ $pageCategory?->name ?? 'Koleksi Video' }}</h1><p>Jelajahi koleksi berdasarkan kategori, nama video, atau model.</p></div></div>
<form id="liveSearch" method="GET" action="{{ route('videos.index') }}" class="panel live-search"><input name="q" value="{{ request('q') }}" placeholder="Ketik untuk mencari video, model, atau kategori…" autocomplete="off" aria-label="Cari video">
<div class="filter-bar" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
<select name="category" onchange="this.form.submit()" style="padding:8px;border-radius:8px;border:1px solid var(--line)">
<option value="">Semua Kategori</option>
@foreach($categories as $cat)
<option value="{{ $cat->slug }}" {{ request('category')==$cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
@endforeach
</select>
<select name="model" onchange="this.form.submit()" style="padding:8px;border-radius:8px;border:1px solid var(--line)">
<option value="">Semua Model</option>
@foreach($models as $mod)
<option value="{{ $mod->id }}" {{ request('model')==$mod->id ? 'selected' : '' }}>{{ $mod->name }}</option>
@endforeach
</select>
</div>
</form>
<div class="cards">@forelse($videos as $video)
<a class="card" href="{{ route('videos.show',$video) }}"><div class="thumb"><video class="thumb-video" muted preload="metadata" playsinline src="{{ $video->playback_url }}"></video>@if($video->thumbnail_url)<img class="thumb-fallback" loading="lazy" src="{{ $video->thumbnail_url }}" alt="">@endif<span class="duration">{{ $video->duration ?: '--:--' }}</span></div><div class="copy"><strong>{{ $video->title }}</strong><div class="tags">@foreach($video->categories as $category)<span class="pill">{{ $category->name }}</span>@endforeach @foreach($video->models as $model)<span class="pill" style="background:var(--blue);color:#3474c5" onclick="event.preventDefault();event.stopPropagation();window.location='{{ route('videos.index', ['model' => $model->id]) }}'">{{ $model->name }}</span>@endforeach</div></div></a>
@empty<p class="muted">Belum ada video untuk ditampilkan.</p>@endforelse</div>
<div style="margin-top:24px">{{ $videos->links('pagination.custom') }}</div>
<script>
const search=document.querySelector('#liveSearch input');let searchTimer;search.addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>{const url=new URL(search.form.action);if(search.value.trim())url.searchParams.set('q',search.value.trim());window.location=url},350)});
document.querySelectorAll('.thumb-video').forEach(video=>{const thumb=video.closest('.thumb');const fallback=thumb.querySelector('.thumb-fallback');const duration=thumb.querySelector('.duration');video.addEventListener('loadedmetadata',()=>{if(video.duration>0){const seconds=Math.round(video.duration);duration.textContent=`${Math.floor(seconds/60)}:${String(seconds%60).padStart(2,'0')}`;video.currentTime=Math.random()*video.duration*.85}},{once:true});video.addEventListener('error',()=>{video.hidden=true});thumb.addEventListener('pointermove',event=>{if(!Number.isFinite(video.duration))return;video.hidden=false;if(fallback)fallback.hidden=true;const bounds=thumb.getBoundingClientRect();video.currentTime=Math.max(0,Math.min(1,(event.clientX-bounds.left)/bounds.width))*video.duration});thumb.addEventListener('pointerleave',()=>{if(fallback)fallback.hidden=false;video.pause()})});
</script>
@endsection
