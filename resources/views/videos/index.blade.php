@extends('layouts.app')
@section('title','VidoHub | Koleksi Video')
@section('content')
<div class="heading"><div><h1>Koleksi Video</h1><p>Jelajahi koleksi berdasarkan kategori, nama video, atau model.</p></div></div>
<form method="GET" action="{{ route('videos.index') }}" class="panel" style="display:flex;gap:10px;flex-wrap:wrap">
<input name="q" value="{{ request('q') }}" placeholder="Cari video atau model…" style="flex:1;min-width:200px">
<select name="category" style="max-width:260px"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category')===$category->slug)>{{ $category->name }} ({{ $category->videos_count }})</option>@endforeach</select>
<button class="btn primary">Cari</button><a class="btn ghost" href="{{ route('videos.index') }}">Reset</a>
</form>
<div class="cards">@forelse($videos as $video)
<a class="card" href="{{ route('videos.show',$video) }}"><div class="thumb">@if($video->thumbnail_url)<img loading="lazy" src="{{ $video->thumbnail_url }}" alt="">@else<video class="thumb-video" muted preload="metadata" playsinline style="width:100%;height:100%;object-fit:cover;display:block" src="{{ $video->playback_url }}"></video>@endif<span class="duration">{{ $video->duration }}</span></div><div class="copy"><strong>{{ $video->title }}</strong><div class="tags"><span class="pill">{{ $video->category->name }}</span><span class="pill" style="background:var(--blue);color:#3474c5">{{ $video->model_name }}</span></div></div></a>
@empty<p class="muted">Belum ada video untuk ditampilkan.</p>@endforelse</div>
<div style="margin-top:24px">{{ $videos->links() }}</div>
<script>
document.querySelectorAll('.thumb-video').forEach(video => {
	const thumb = video.closest('.thumb');
	video.addEventListener('loadedmetadata', () => { video.currentTime = 0; }, { once: true });
	thumb.addEventListener('pointermove', event => {
		if (!Number.isFinite(video.duration)) return;
		const bounds = thumb.getBoundingClientRect();
		const progress = Math.max(0, Math.min(1, (event.clientX - bounds.left) / bounds.width));
		video.currentTime = progress * video.duration;
	});
	thumb.addEventListener('pointerleave', () => { video.pause(); video.currentTime = 0; });
});
</script>
@endsection
