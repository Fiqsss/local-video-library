@extends('layouts.app')
@section('title','Admin VidoHub')
@section('content')
<div class="heading"><div><h1>Manajemen Video</h1><p>Kelola detail dan sumber video koleksi VidoHub.</p></div><a class="btn primary" href="#video-form">＋ Tambah Video</a></div>
<div class="stats"><div class="stat"><small>Total video</small><strong>{{ $videos->count() }}</strong></div><div class="stat"><small>Kategori terdaftar</small><strong>{{ $categories->count() }}</strong></div><div class="stat"><small>Model terdaftar</small><strong>{{ $videos->pluck('model_name')->unique()->count() }}</strong></div></div>

<section class="panel"><h2>Manajemen Kategori</h2><p class="muted">Tambahkan kategori di sini. Kategori tersedia sebagai pilihan pada form video.</p>
<form action="{{ route('admin.categories.store') }}" method="POST" style="display:flex;gap:10px;flex-wrap:wrap;margin:15px 0">@csrf<input name="name" maxlength="80" required placeholder="Nama kategori baru…" style="flex:1;min-width:220px"><button class="btn primary">＋ Tambah kategori</button></form>
<div style="display:flex;gap:8px;flex-wrap:wrap">@forelse($categories as $category)<span class="pill" style="display:inline-flex;align-items:center;gap:8px">{{ $category->name }} <form method="POST" action="{{ route('admin.categories.destroy',$category) }}" onsubmit="return confirm('Hapus kategori ini?')" style="display:inline">@csrf @method('DELETE')<button class="danger" style="border:0;background:transparent;cursor:pointer" aria-label="Hapus kategori">×</button></form></span>@empty<span class="muted">Belum ada kategori.</span>@endforelse</div></section>

<section class="panel" id="video-form"><h2 id="form-heading">Tambah video baru</h2>
<form id="videoForm" action="{{ route('admin.videos.store') }}" method="POST">@csrf
<div class="form-grid">
<div class="field"><label>Nama video *</label><input name="title" id="title" required maxlength="180" placeholder="Contoh: Morning Vibes"></div>
<input type="hidden" name="duration" id="duration">
<div class="field"><label>Kategori *</label><select name="category_id" id="category_id" required><option value="">Pilih kategori…</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
<div class="field"><label>Model *</label><input name="model_name" id="model_name" required maxlength="120" list="models" placeholder="Nama model"><datalist id="models">@foreach($videos->pluck('model_name')->unique() as $model)<option value="{{ $model }}">@endforeach</datalist></div>
<div class="field full"><label>Sumber video *</label><div class="source-tabs">
<label><input type="radio" name="source_type" value="local" checked> Video dari folder komputer</label>
<label><input type="radio" name="source_type" value="url"> Link video eksternal</label></div>
<div id="localBox"><div style="display:flex;gap:10px"><input name="external_url" id="external_url" type="text" placeholder="D:\\Design\\folder\\video.mp4" required><button class="btn secondary" type="button" id="browseFilesBtn">Browse file</button></div><span class="help">Pilih file dari folder mana pun. File tidak diunggah dan tetap di lokasi asal.</span></div>
<div id="urlBox" style="display:none"><input name="external_url" id="external_url_url" type="text" placeholder="https://contoh.com/video.mp4"><span class="help">Masukkan URL file MP4/WebM langsung atau link YouTube biasa, Shorts, atau youtu.be.</span></div>
<div style="margin-top:12px"><label>URL thumbnail (opsional)</label><input name="thumbnail_url" id="thumbnail_url" type="url" placeholder="https://…"></div><span class="help" id="durationStatus">Durasi akan diambil otomatis dari file video.</span>
<video id="preview" controls playsinline style="display:none;width:100%;max-width:680px;max-height:360px;background:#111;margin-top:14px;border-radius:12px"></video>
</div></div>
<div style="display:flex;gap:10px;margin-top:18px"><button class="btn primary" id="submitBtn">Simpan video</button><button class="btn ghost" type="reset" id="resetBtn">Kosongkan form</button></div>
<p class="notice">Video lokal tidak disalin atau diunggah. Path asli file disimpan, lalu Laravel menyajikannya saat diputar. Komputer tempat Laravel berjalan harus tetap bisa mengakses file tersebut.</p>
</form></section>

<div id="fileBrowser" hidden style="position:fixed;inset:0;background:#0008;z-index:20;padding:5vh 15px"><section class="panel" style="max-width:760px;margin:auto;max-height:90vh;overflow:auto"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px"><h2 style="margin:0">Pilih file video</h2><button class="btn ghost" type="button" id="closeFileBrowser">Tutup</button></div><p class="help" id="browserPath"></p><div id="browserEntries" style="display:grid;gap:8px"></div></section></div>

<section class="panel"><div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap"><h2>Daftar video</h2><input id="tableSearch" placeholder="Cari nama, kategori, model…" style="max-width:360px"></div>
<div class="table-wrap"><table><thead><tr><th>Video</th><th>Durasi</th><th>Kategori</th><th>Model</th><th>Sumber</th><th>Aksi</th></tr></thead><tbody id="videoRows">
@foreach($videos as $video)<tr data-search="{{ strtolower($video->title.' '.$video->category->name.' '.$video->model_name) }}"><td><strong>{{ $video->title }}</strong><span class="help" style="display:block">URL / lokasi video</span></td><td>{{ $video->duration }}</td><td><span class="pill">{{ $video->category->name }}</span></td><td>{{ $video->model_name }}</td><td><a href="{{ $video->playback_url }}" target="_blank" rel="noopener">Buka link ↗</a></td><td style="white-space:nowrap"><button class="btn secondary edit-btn" type="button" data-id="{{ $video->id }}" data-title="{{ $video->title }}" data-duration="{{ $video->duration }}" data-category="{{ $video->category_id }}" data-model="{{ $video->model_name }}" data-type="{{ $video->source_type }}" data-source="{{ $video->source_path }}" data-thumb="{{ $video->thumbnail_url }}">Edit</button><form method="POST" action="{{ route('admin.videos.destroy',$video) }}" style="display:inline" onsubmit="return confirm('Hapus video ini?')">@csrf @method('DELETE')<button class="btn danger">Hapus</button></form></td></tr>@endforeach
</tbody></table>@if($videos->isEmpty())<p class="muted" style="text-align:center;padding:24px">Belum ada video.</p>@endif</div></section>
<script>
const form=document.getElementById('videoForm'),urlInput=document.getElementById('external_url'),urlExternal=document.getElementById('external_url_url'),preview=document.getElementById('preview'),durationInput=document.getElementById('duration'),durationStatus=document.getElementById('durationStatus');
const localBox=document.getElementById('localBox'),urlBox=document.getElementById('urlBox');
function sourceChanged(){const type=form.querySelector('[name=source_type]:checked').value;localBox.style.display=type==='local'?'block':'none';urlBox.style.display=type==='url'?'block':'none';urlInput.required=type==='local';urlExternal.required=type==='url';urlInput.disabled=type!=='local';urlExternal.disabled=type!=='url';preview.style.display='none';preview.removeAttribute('src');}
form.querySelectorAll('[name=source_type]').forEach(x=>x.addEventListener('change',sourceChanged));sourceChanged();
function updateDuration(){if(!Number.isFinite(preview.duration))return;const seconds=Math.round(preview.duration);durationInput.value=`${Math.floor(seconds/60)}:${String(seconds%60).padStart(2,'0')}`;durationStatus.textContent=`Durasi otomatis: ${durationInput.value}`;}
preview.addEventListener('loadedmetadata',updateDuration);
function setLocalPreview(){if(!urlInput.value.trim())return;preview.src='{{ route('admin.videos.preview') }}?path='+encodeURIComponent(urlInput.value.trim());preview.style.display='block';}
urlInput.addEventListener('input',setLocalPreview);
urlExternal.addEventListener('input',()=>{if(urlExternal.value.trim() && !/youtu(?:\.be|be\.com)/i.test(urlExternal.value)){preview.src=urlExternal.value.trim();preview.style.display='block'}else {preview.style.display='none';durationInput.value='';durationStatus.textContent='Durasi akan diambil otomatis dari file video.'}});
const fileBrowser=document.getElementById('fileBrowser'),browserPath=document.getElementById('browserPath'),browserEntries=document.getElementById('browserEntries');
async function openFileBrowser(path=''){const response=await fetch('{{ route('admin.videos.files') }}'+(path?'?path='+encodeURIComponent(path):''));if(!response.ok){alert('Folder tidak bisa dibaca.');return}const data=await response.json();fileBrowser.hidden=false;browserPath.textContent=data.current;browserEntries.innerHTML='';if(data.parent){const parent=document.createElement('button');parent.className='btn ghost';parent.type='button';parent.textContent='.. Kembali';parent.onclick=()=>openFileBrowser(data.parent);browserEntries.append(parent)}data.entries.forEach(entry=>{const button=document.createElement('button');button.className='btn '+(entry.type==='file'?'secondary':'ghost');button.type='button';button.textContent=(entry.type==='file'?'▶ ':'📁 ')+entry.name;button.style.textAlign='left';button.onclick=()=>{if(entry.type==='file'){urlInput.value=entry.path;fileBrowser.hidden=true;setLocalPreview()}else openFileBrowser(entry.path)};browserEntries.append(button)})}
document.getElementById('browseFilesBtn').onclick=()=>openFileBrowser();document.getElementById('closeFileBrowser').onclick=()=>fileBrowser.hidden=true;
document.querySelectorAll('.edit-btn').forEach(btn=>btn.addEventListener('click',()=>{
 form.action='{{ url('/admin/videos') }}/'+btn.dataset.id;form.querySelector('input[name=_method]')?.remove();let method=document.createElement('input');method.type='hidden';method.name='_method';method.value='PUT';form.appendChild(method);form.dataset.editing='1';
 document.getElementById('title').value=btn.dataset.title;document.getElementById('duration').value=btn.dataset.duration||'';document.getElementById('category_id').value=btn.dataset.category;document.getElementById('model_name').value=btn.dataset.model;document.getElementById('thumbnail_url').value=btn.dataset.thumb||'';
 form.querySelector(`[name=source_type][value="${btn.dataset.type}"]`).checked=true;if(btn.dataset.type==='url')urlExternal.value=btn.dataset.source||'';else urlInput.value=btn.dataset.source||'';sourceChanged();document.getElementById('form-heading').textContent='Edit video';document.getElementById('submitBtn').textContent='Simpan perubahan';document.getElementById('video-form').scrollIntoView({behavior:'smooth'});
}));
document.getElementById('resetBtn').addEventListener('click',()=>{setTimeout(()=>{form.action='{{ route('admin.videos.store') }}';form.querySelector('input[name=_method]')?.remove();delete form.dataset.editing;document.getElementById('form-heading').textContent='Tambah video baru';document.getElementById('submitBtn').textContent='Simpan video';sourceChanged()},0)});
document.getElementById('tableSearch').addEventListener('input',e=>{let q=e.target.value.toLowerCase();document.querySelectorAll('#videoRows tr').forEach(r=>r.hidden=!r.dataset.search.includes(q))});
</script>
@endsection
