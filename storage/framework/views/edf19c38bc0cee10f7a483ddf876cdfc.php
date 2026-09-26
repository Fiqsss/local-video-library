<?php $__env->startSection('title','Admin VidoHub'); ?>
<?php $__env->startSection('content'); ?>
<div class="heading"><div><h1 style="font-size: 32px; font-weight: 800; color: #172033;">Manajemen Video</h1><p style="font-size: 14px; color: var(--muted);">Kelola sumber, model, dan kategori koleksi VidoHub.</p></div><a class="btn primary" href="#video-form">+ Tambah Video</a></div>
<div class="stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px;">
    <div class="panel" style="margin-bottom: 0;">
        <small style="text-transform: uppercase; font-size: 11px; color: var(--muted); font-weight: 600;">Total video</small>
        <strong style="display: block; font-size: 28px; margin-top: 5px; color: var(--text);"><?php echo e($totalVideos); ?></strong>
    </div>
    <div class="panel" style="margin-bottom: 0;">
        <small style="text-transform: uppercase; font-size: 11px; color: var(--muted); font-weight: 600;">Kategori terdaftar</small>
        <strong style="display: block; font-size: 28px; margin-top: 5px; color: var(--text);"><?php echo e($categories->count()); ?></strong>
    </div>
    <div class="panel" style="margin-bottom: 0;">
        <small style="text-transform: uppercase; font-size: 11px; color: var(--muted); font-weight: 600;">Model terdaftar</small>
        <strong style="display: block; font-size: 28px; margin-top: 5px; color: var(--text);"><?php echo e($models->count()); ?></strong>
    </div>
</div>
<div class="panel" style="display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--pink); background: var(--pink-soft);">
    <div>
        <h2 style="font-size: 15px; margin: 0; color: var(--pink);">Duplicate Videos</h2>
        <p style="font-size: 13px; color: var(--muted); margin: 4px 0 0;">Cari video dengan judul yang sama sebelum melakukan pembersihan.</p>
    </div>
    <a href="<?php echo e(route('admin.videos.duplicates')); ?>" class="btn" style="border: 1px solid var(--pink); color: var(--pink); background: transparent;">Buka Duplicate Checker</a>
</div>
<div class="admin-columns">
<section class="panel">
    <h2 style="font-size: 18px; margin-bottom: 16px;">Manajemen Kategori</h2>
    <form action="<?php echo e(route('admin.categories.store')); ?>" method="POST" style="display: flex; gap: 10px; margin-bottom: 16px;">
        <?php echo csrf_field(); ?>
        <input name="name" maxlength="80" required placeholder="Nama kategori baru" style="flex: 1; height: 40px;">
        <select name="page_key" style="max-width: 140px; height: 40px; border-radius: 8px;">
            <option value="">Kategori biasa</option>
            <option value="fav">Fav</option>
            <option value="edukasi">Edukasi</option>
            <option value="tutorial">Tutorial</option>
            <option value="music">Music</option>
            <option value="lucu">Lucu</option>
        </select>
        <button class="btn primary" style="height: 40px; display: flex; align-items: center; padding: 0 16px;">Tambah</button>
    </form>
    <div style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 120px; overflow-y: auto;">
        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <span class="pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 6px; background: var(--pink-soft); color: #d83d70;">
                <?php echo e($category->name); ?>

                <form method="POST" action="<?php echo e(route('admin.categories.destroy',$category)); ?>" onsubmit="return confirm('Hapus kategori ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button style="border: 0; background: transparent; cursor: pointer; color: #d83d70; font-weight: bold;">×</button>
                </form>
            </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <span class="muted" style="font-size: 13px;">Belum ada kategori.</span>
        <?php endif; ?>
    </div>
</section>
<section class="panel">
    <h2 style="font-size: 18px; margin-bottom: 16px;">Manajemen Model</h2>
    <form action="<?php echo e(route('admin.models.store')); ?>" method="POST" style="display: flex; gap: 10px; margin-bottom: 16px;">
        <?php echo csrf_field(); ?>
        <input name="name" maxlength="120" required placeholder="Nama model baru" style="flex: 1; height: 40px;">
        <button class="btn primary" style="height: 40px; display: flex; align-items: center; padding: 0 16px;">Tambah</button>
    </form>
    <div style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 120px; overflow-y: auto;">
        <?php $__empty_1 = true; $__currentLoopData = $models; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <span class="pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 6px; background: var(--blue); color: #3474c5;">
                <?php echo e($model->name); ?>

                <form method="POST" action="<?php echo e(route('admin.models.destroy',$model)); ?>" onsubmit="return confirm('Hapus model ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button style="border: 0; background: transparent; cursor: pointer; color: #3474c5; font-weight: bold;">×</button>
                </form>
            </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <span class="muted" style="font-size: 13px;">Belum ada model.</span>
        <?php endif; ?>
    </div>
</section>
</div>
<section class="panel" id="video-form">
    <h2 id="form-heading" style="font-size: 18px; margin-bottom: 20px;">Tambah video baru</h2>
    <form id="videoForm" action="<?php echo e(route('admin.videos.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="duration" id="duration">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="field">
                <label>Nama video *</label>
                <input name="title" id="title" maxlength="180" placeholder="Nama otomatis dari file">
            </div>
            <div class="field">
                <label>Kategori *</label>
                <input class="tag-search" data-target="category-options" placeholder="Cari kategori…">
                <div class="check-list" id="category-options" style="max-height: 150px; overflow-y: auto;">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label data-name="<?php echo e(strtolower($category->name)); ?>" style="display: block; padding: 4px 8px;">
                            <input type="checkbox" name="category_ids[]" value="<?php echo e($category->id); ?>"> <?php echo e($category->name); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="field">
                <label>Model *</label>
                <input class="tag-search" data-target="model-options" placeholder="Cari model…">
                <div class="check-list" id="model-options" style="max-height: 150px; overflow-y: auto;">
                    <?php $__currentLoopData = $models; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label data-name="<?php echo e(strtolower($model->name)); ?>" style="display: block; padding: 4px 8px;">
                            <input type="checkbox" name="model_ids[]" value="<?php echo e($model->id); ?>"> <?php echo e($model->name); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="field">
                <label>URL thumbnail (opsional)</label>
                <input name="thumbnail_url" id="thumbnail_url" type="url" placeholder="https://…">
            </div>
        </div>

        <div class="field full" style="margin-top: 20px;">
            <label style="display: block; margin-bottom: 10px;">SUMBER VIDEO *</label>
            <div class="source-tabs" style="display: flex; gap: 15px; margin-bottom: 10px;">
                <label style="cursor: pointer;"><input type="radio" name="source_type" value="local" checked> File dari folder komputer</label>
                <label style="cursor: pointer;"><input type="radio" name="source_type" value="url"> Link video eksternal</label>
            </div>
            <div id="localBox">
                <div style="display: flex; gap: 10px;">
                    <input name="external_url" id="external_url" type="text" placeholder="D:\Design\folder\video.mp4" required style="flex: 1;">
                    <button class="btn secondary" type="button" id="browseFilesBtn">Browse file</button>
                </div>
                <div id="selectedFiles" class="selected-files"></div>
            </div>
            <div id="urlBox" style="display:none">
                <input name="external_url" id="external_url_url" type="text" placeholder="https://contoh.com/video.mp4">
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button class="btn primary" id="submitBtn">Simpan video</button>
            <button class="btn ghost" type="reset" id="resetBtn">Kosongkan form</button>
        </div>
    </form>
</section>
<div id="fileBrowser" class="modal" style="display:none"><div class="file-browser-modal" role="dialog" aria-modal="true" aria-labelledby="fb-title"><header class="file-browser-header"><div class="title-group"><h2 id="fb-title">Pilih file video</h2><p>Centang file untuk memilih lebih dari satu.</p></div><button type="button" class="btn-close" id="closeFileBrowser" aria-label="Tutup">✕</button></header><div class="file-browser-toolbar"><label class="select-all-label"><input type="checkbox" id="selectAllFiles"> Pilih semua</label><span class="selection-count" id="fbSelectionCount">0 file dipilih</span></div><div class="file-browser-location"><label><input type="text" id="folderLocation" placeholder="D:\Video" aria-label="Path folder"><button type="button" class="btn secondary btn-open-location" id="openLocation">Buka lokasi</button></label></div><div class="file-browser-search"><input type="search" id="browserSearch" placeholder="Cari nama file…" aria-label="Cari nama file"></div><div class="file-browser-pinned empty" id="pinnedFoldersSection"><div class="pinned-header"><span>Folder tersimpan</span></div><div class="pinned-empty"><span>Belum ada folder tersimpan</span><button type="button" class="btn secondary btn-pin-current" id="pinFolder">☆ Pin folder saat ini</button></div></div><div class="file-browser-current"><div class="label">LOKASI SAAT INI</div><div class="path" id="browserPath"></div></div><div class="file-browser-list" id="browserEntries" role="listbox" aria-label="Daftar file dan folder"></div><footer class="file-browser-footer"><span class="selection-count" id="fbFooterSelectionCount">0 file dipilih</span><button type="button" class="btn primary btn-use-selected" id="useSelected" disabled>Gunakan file terpilih</button></footer></div></div>
<section class="panel">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 18px; margin: 0;">Daftar video</h2>
            <p style="font-size: 13px; color: var(--muted); margin: 4px 0 0;">Pilih video yang ingin dikelola.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button class="btn ghost" type="button" id="selectAllVideos">Pilih semua</button>
            <button class="btn danger" type="submit" form="bulkDeleteForm" onclick="return confirm('Hapus semua video yang dipilih?')">Hapus terpilih</button>
            <input id="tableSearch" placeholder="Cari..." style="width: 250px; height: 38px;">
        </div>
    </div>
    <form id="bulkDeleteForm" method="POST" action="<?php echo e(route('admin.videos.bulk-destroy')); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <div class="table-wrap">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; font-size: 12px; color: var(--muted); text-transform: uppercase;">
                        <th style="padding: 12px; border-bottom: 2px solid var(--line);"><input type="checkbox" id="selectAllCheckbox"></th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--line);">Video</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--line);">Durasi</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--line);">Kategori</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--line);">Model</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--line);">Sumber</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--line);">Aksi</th>
                    </tr>
                </thead>
                <tbody id="videoRows">
                    <?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr data-search="<?php echo e(strtolower($video->title.' '.$video->category->name.' '.$video->model_name)); ?>">
                            <td style="padding: 12px; border-bottom: 1px solid var(--line);"><input type="checkbox" name="video_ids[]" value="<?php echo e($video->id); ?>" class="video-checkbox"></td>
                            <td style="padding: 12px; border-bottom: 1px solid var(--line); font-weight: 600;"><?php echo e($video->title); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid var(--line); color: var(--muted);"><?php echo e($video->duration); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid var(--line);">
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    <?php $__currentLoopData = $video->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="pill"><?php echo e($category->name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid var(--line);">
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    <?php $__currentLoopData = $video->models; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="pill" style="background: var(--blue); color: #3474c5;"><?php echo e($model->name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid var(--line);"><a href="<?php echo e($video->playback_url); ?>" target="_blank" rel="noopener">↗</a></td>
                            <td style="padding: 12px; border-bottom: 1px solid var(--line); white-space: nowrap;">
                                <button class="btn secondary edit-btn" type="button" data-id="<?php echo e($video->id); ?>" data-title="<?php echo e($video->title); ?>" data-duration="<?php echo e($video->duration); ?>" data-categories="<?php echo e($video->categories->pluck('id')->implode(',')); ?>" data-models="<?php echo e($video->models->pluck('id')->implode(',')); ?>" data-type="<?php echo e($video->source_type); ?>" data-source="<?php echo e($video->source_path); ?>" data-thumb="<?php echo e($video->thumbnail_url); ?>">Edit</button>
                                <form method="POST" action="<?php echo e(route('admin.videos.destroy', $video)); ?>" style="display: inline;" onsubmit="return confirm('Hapus video ini?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn danger">Hapus</button></form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </form>
    <div style="margin-top: 20px;">
        <?php echo e($videos->links()); ?>

    </div>
</section>
<script>
const form=document.getElementById('videoForm'),localInput=document.getElementById('external_url'),urlInput=document.getElementById('external_url_url'),preview=document.getElementById('preview'),durationInput=document.getElementById('duration'),status=document.getElementById('durationStatus'),browser=document.getElementById('fileBrowser'),entries=document.getElementById('browserEntries'),locationInput=document.getElementById('folderLocation'),searchInput=document.getElementById('browserSearch'),pinnedSection=document.getElementById('pinnedFoldersSection'),fbSelectionCount=document.getElementById('fbSelectionCount'),fbFooterSelectionCount=document.getElementById('fbFooterSelectionCount'),selected=new Map(),selectedTitles=new Map();let currentFolder='';

function updateSelectionCount(){const count=selected.size;fbSelectionCount.textContent=`${count} file dipilih`;fbFooterSelectionCount.textContent=`${count} file dipilih`;document.getElementById('useSelected').disabled=count===0;}
function sourceChanged(){const type=form.querySelector('[name=source_type]:checked').value;document.getElementById('localBox').style.display=type==='local'?'block':'none';document.getElementById('urlBox').style.display=type==='url'?'block':'none';localInput.disabled=type!=='local';urlInput.disabled=type!=='url';localInput.required=type==='local'&&selected.size===0;urlInput.required=type==='url'}form.querySelectorAll('[name=source_type]').forEach(x=>x.addEventListener('change',sourceChanged));sourceChanged();
document.querySelectorAll('.tag-search').forEach(input=>input.oninput=()=>document.querySelectorAll('#'+input.dataset.target+' label').forEach(label=>label.hidden=!label.dataset.name.includes(input.value.toLowerCase())));
preview.addEventListener('loadedmetadata',()=>{const seconds=Math.round(preview.duration);durationInput.value=`${Math.floor(seconds/60)}:${String(seconds%60).padStart(2,'0')}`;status.textContent='Durasi otomatis: '+durationInput.value});
function previewLocal(path=localInput.value){if(!path)return;preview.src='<?php echo e(route('admin.videos.preview')); ?>?path='+encodeURIComponent(path);preview.style.display='block';if(!document.getElementById('title').value)document.getElementById('title').value=path.split(/[\\/]/).pop().replace(/\.[^.]+$/,'')}
function drawSelected(){const box=document.getElementById('selectedFiles');box.innerHTML='';selected.forEach((name,path)=>{const row=document.createElement('div');row.className='selected-file-row';const label=document.createElement('label');label.textContent=name;const input=document.createElement('input');input.type='text';input.name='file_titles[]';input.required=true;input.maxLength=180;input.value=selectedTitles.get(path)||name.replace(/\.[^.]+$/,'');input.oninput=()=>selectedTitles.set(path,input.value);const remove=document.createElement('button');remove.type='button';remove.className='btn danger';remove.textContent='Hapus';remove.onclick=()=>{selected.delete(path);selectedTitles.delete(path);drawSelected();sourceChanged()};row.append(label,input,remove);box.append(row)});sourceChanged();updateSelectionCount();}

function folderIcon(){return '<svg class="entry-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-9.5a2 2 0 0 1-1.68-.89L9.33 2.42A2 2 0 0 0 7.68 2H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h16z"></path></svg>';}
function fileIcon(){return '<svg class="entry-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="2" y="4" width="20" height="16" rx="2"></rect></svg>';}

async function openFolder(path=currentFolder,q=''){const params=new URLSearchParams();if(path)params.set('path',path);if(q)params.set('q',q);const response=await fetch('<?php echo e(route('admin.videos.files')); ?>?'+params);if(!response.ok){alert('Folder tidak bisa dibaca. Pastikan path berada di VIDEO_BROWSE_ROOT.');return}const data=await response.json();currentFolder=data.current;locationInput.value=data.current;document.getElementById('browserPath').textContent=data.current;entries.innerHTML='';if(data.parent){const back=document.createElement('div');back.className='file-entry';back.dataset.path=data.parent;back.innerHTML=folderIcon()+'<span class="entry-name">.. Kembali</span>';back.onclick=()=>openFolder(data.parent,searchInput.value);entries.append(back)}data.entries.forEach(item=>{const row=document.createElement('div');row.className='file-entry '+(item.type==='file'?'is-file':'');if(item.type==='file'){row.dataset.path=item.path;row.innerHTML='<input type="checkbox">'+fileIcon()+'<span class="entry-name">'+item.name+'</span>';const checkbox=row.querySelector('input[type="checkbox"]');checkbox.checked=selected.has(item.path);checkbox.onchange=()=>{checkbox.checked?selected.set(item.path,item.name):selected.delete(item.path);drawSelected()};}else{row.innerHTML=folderIcon()+'<span class="entry-name">'+item.name+'</span>';row.dataset.path=item.path;row.onclick=()=>openFolder(item.path,searchInput.value);}entries.append(row)});updateSelectAllCheckbox();}

function updateSelectAllCheckbox(){const allCheckboxes=entries.querySelectorAll('.is-file input[type=checkbox]');const checked=entries.querySelectorAll('.is-file input[type=checkbox]:checked').length;const total=allCheckboxes.length;const selectAll=document.getElementById('selectAllFiles');if(selectAll){selectAll.checked=total>0&&checked===total;selectAll.indeterminate=checked>0&&checked<total}}

document.getElementById('selectAllFiles').onclick=()=>{const boxes=entries.querySelectorAll('.is-file input[type=checkbox]');const allChecked=[...boxes].every(b=>b.checked);boxes.forEach(b=>{b.checked=!allChecked;b.dispatchEvent(new Event('change'))});drawSelected();}

function renderPins(){const pins=JSON.parse(localStorage.getItem('videohub-pinned-folders')||'[]');const section=pinnedSection;if(!pins.length){section.classList.add('empty');const emptyEl=section.querySelector('.pinned-empty');if(emptyEl)emptyEl.style.display='flex';return}section.classList.remove('empty');const emptyEl=section.querySelector('.pinned-empty');if(emptyEl)emptyEl.style.display='none';let container=section.querySelector('.pinned-container');if(!container){container=document.createElement('div');container.className='pinned-container';section.querySelector('.pinned-header').after(container);}container.innerHTML='';pins.forEach(path=>{const wrap=document.createElement('div');wrap.className='pinned-folder-item';wrap.innerHTML='<span class="pinned-path" title="'+path+'">'+path+'</span><button type="button" class="btn-pin-remove" title="Hapus pin">×</button>';wrap.querySelector('.pinned-path').onclick=()=>openFolder(path);wrap.querySelector('.btn-pin-remove').onclick=()=>{localStorage.setItem('videohub-pinned-folders',JSON.stringify(pins.filter(item=>item!==path)));renderPins()};container.append(wrap)});}
document.getElementById('browseFilesBtn').onclick=()=>{browser.style.display='flex';renderPins();openFolder()};// Close modal when clicking the close button
document.getElementById('closeFileBrowser').onclick = () => { browser.style.display = 'none'; };

// Close modal when clicking outside the modal content
window.addEventListener('click', (event) => {
    if (event.target === browser) {
        browser.style.display = 'none';
    }
});document.getElementById('openLocation').onclick=()=>openFolder(locationInput.value.trim(),searchInput.value);searchInput.oninput=()=>openFolder(currentFolder,searchInput.value);document.getElementById('pinFolder').onclick=()=>{if(!currentFolder)return;const pins=JSON.parse(localStorage.getItem('videohub-pinned-folders')||'[]');if(!pins.includes(currentFolder))pins.push(currentFolder);localStorage.setItem('videohub-pinned-folders',JSON.stringify(pins));renderPins()};document.getElementById('useSelected').onclick=()=>{if(!selected.size){alert('Pilih minimal satu file.');return}const first=[...selected.entries()][0];localInput.value=first[1];previewLocal(first[1]);browser.style.display='none';drawSelected()};
form.addEventListener('submit',()=>{document.querySelectorAll('.selected-file-hidden').forEach(input=>input.remove());selected.forEach((name,path)=>{const input=document.createElement('input');input.type='hidden';input.name='selected_files[]';input.value=path;input.className='selected-file-hidden';form.append(input)})});
document.querySelectorAll('.edit-btn').forEach(button=>button.onclick=()=>{form.action='<?php echo e(url('/admin/videos')); ?>/'+button.dataset.id;form.querySelector('[name=_method]')?.remove();const method=document.createElement('input');method.type='hidden';method.name='_method';method.value='PUT';form.append(method);document.getElementById('title').value=button.dataset.title;document.getElementById('duration').value=button.dataset.duration||'';document.querySelectorAll('[name="category_ids[]"],[name="model_ids[]"]').forEach(input=>input.checked=(button.dataset.categories+','+button.dataset.models).split(',').includes(input.value));document.getElementById('thumbnail_url').value=button.dataset.thumb||'';form.querySelector('[name=source_type][value="'+button.dataset.type+'"]').checked=true;if(button.dataset.type==='url')urlInput.value=button.dataset.source;else localInput.value=button.dataset.source;sourceChanged();document.getElementById('form-heading').textContent='Edit video';document.getElementById('submitBtn').textContent='Simpan perubahan';document.getElementById('video-form').scrollIntoView({behavior:'smooth'})});
document.getElementById('resetBtn').onclick=()=>setTimeout(()=>{form.action='<?php echo e(route('admin.videos.store')); ?>';form.querySelector('[name=_method]')?.remove();selected.clear();selectedTitles.clear();document.getElementById('selectedFiles').innerHTML='';document.getElementById('form-heading').textContent='Tambah video baru';document.getElementById('submitBtn').textContent='Simpan video';sourceChanged()},0);
document.getElementById('tableSearch').oninput=e=>document.querySelectorAll('#videoRows tr').forEach(row=>row.hidden=!row.dataset.search.includes(e.target.value.toLowerCase()));
const videoChecks=[...document.querySelectorAll('.video-checkbox')],allCheck=document.getElementById('selectAllCheckbox');function setAllVideos(checked){videoChecks.forEach(input=>input.checked=checked);allCheck.checked=checked}document.getElementById('selectAllVideos').onclick=()=>setAllVideos(!videoChecks.every(input=>input.checked));allCheck.onchange=()=>setAllVideos(allCheck.checked);videoChecks.forEach(input=>input.onchange=()=>{allCheck.checked=videoChecks.length>0&&videoChecks.every(item=>item.checked)});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\videohub\videohub\resources\views/admin/index.blade.php ENDPATH**/ ?>