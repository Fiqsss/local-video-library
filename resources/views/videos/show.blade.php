@extends('layouts.app')
@section('title',$video->title.' | VidoHub')
@section('content')
<p class="muted"><a href="{{ route('videos.index') }}">Home</a> / Detail Video</p>
<div class="player-shell detail-player-shell" id="playerShell"><div class="player">
@if($video->youtube_embed_url)
<iframe id="videoPlayer" src="{{ $video->youtube_embed_url }}" title="{{ $video->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="width:100%;aspect-ratio:16/9;border:0"></iframe>
@else
<video id="videoPlayer" controls playsinline preload="metadata" poster="{{ $video->thumbnail_url }}"><source src="{{ $video->playback_url }}">Browser kamu tidak mendukung pemutar video.</video>
@endif
<div class="overlay-tools"><button type="button" id="back5" title="Mundur 5 detik">↶ 5s</button><button type="button" id="forward5" title="Maju 5 detik">5s ↷</button><button type="button" id="soundToggle">🔊</button><button type="button" id="fullscreenBtn" title="Fullscreen">⛶</button></div>
</div></div>
<section class="info"><h1>{{ $video->title }}</h1><div class="metadata"><span>Kategori: @foreach($video->categories as $category)<a href="{{ route('videos.index',['category'=>$category->slug]) }}">{{ $category->name }}</a>@if(!$loop->last), @endif @endforeach</span><span>Model: @foreach($video->models as $model)<a href="{{ route('videos.index', ['model' => $model->id]) }}"><b>{{ $model->name }}</b></a>@if(!$loop->last), @endif @endforeach</span></div></section>
<section style="margin-top:28px"><h2>Video Rekomendasi</h2><div class="cards">@foreach($recommendations as $item)<a class="card" href="{{ route('videos.show',$item) }}"><div class="thumb">@if($item->thumbnail_url)<img loading="lazy" src="{{ $item->thumbnail_url }}" alt="">@else<video class="thumb-video" muted preload="metadata" playsinline style="width:100%;height:100%;object-fit:cover;display:block" src="{{ $item->playback_url }}"></video>@endif<span class="duration">{{ $item->duration }}</span></div><div class="copy"><strong>{{ $item->title }}</strong><div class="tags"><span class="pill">{{ $item->category->name }}</span><span class="pill" style="background:var(--blue);color:#3474c5">{{ $item->model_name }}</span></div></div></a>@endforeach</div></section>
<script>
const player=document.getElementById('videoPlayer'),shell=document.getElementById('playerShell');let timer;
document.querySelectorAll('.thumb-video').forEach(video => { const thumb = video.closest('.thumb'); video.addEventListener('loadedmetadata', () => { video.currentTime = 0; }, { once: true }); thumb.addEventListener('pointermove', event => { if (!Number.isFinite(video.duration)) return; const bounds = thumb.getBoundingClientRect(); const progress = Math.max(0, Math.min(1, (event.clientX - bounds.left) / bounds.width)); video.currentTime = progress * video.duration; }); thumb.addEventListener('pointerleave', () => { video.pause(); video.currentTime = 0; }); });
if(player.tagName==='IFRAME'){document.querySelector('.overlay-tools').style.display='none';}
if(player.tagName==='IFRAME'){/* YouTube manages its own playback controls. */} else {
function showControls(){shell.classList.remove('controls-hidden');clearTimeout(timer);if(!player.paused)timer=setTimeout(()=>shell.classList.add('controls-hidden'),2500)}
shell.addEventListener('mousemove',showControls);shell.addEventListener('pointerdown',showControls);player.addEventListener('play',showControls);player.addEventListener('pause',()=>{clearTimeout(timer);shell.classList.remove('controls-hidden')});
function seek(n){player.currentTime=Math.max(0,Math.min(Number.isFinite(player.duration)?player.duration:Infinity,player.currentTime+n))}
document.getElementById('back5').onclick=()=>seek(-5);document.getElementById('forward5').onclick=()=>seek(5);
document.getElementById('soundToggle').onclick=e=>{player.muted=!player.muted;e.currentTarget.textContent=player.muted?'🔇':'🔊'};
document.getElementById('fullscreenBtn').onclick=()=>document.fullscreenElement?document.exitFullscreen():shell.requestFullscreen?.();
document.addEventListener('fullscreenchange',()=>shell.classList.remove('controls-hidden'));
player.addEventListener('error',()=>{const message=document.createElement('p');message.className='muted';message.textContent='Video tidak ditemukan. Pastikan file berada di public/videos dan alamatnya benar.';shell.after(message)});
document.addEventListener('keydown',e=>{if(['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)||e.target.isContentEditable)return;if(e.key==='ArrowLeft'){e.preventDefault();seek(-5)}if(e.key==='ArrowRight'){e.preventDefault();seek(5)}});
}
</script>
@endsection
