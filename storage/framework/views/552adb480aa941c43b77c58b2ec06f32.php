<?php $__env->startSection('title', $video->title . ' | VidoHub'); ?>
<?php $__env->startSection('content'); ?>
<style>
    .detail-page { --detail-pink:#ff7f96; --detail-navy:#172033; --detail-heading:#68736f; --detail-body:#a5adaa; --detail-line:#e2e6e4; max-width: none; margin: -28px -20px 0; color: var(--detail-heading); }
    .detail-hero { position: relative; min-height: 112px; display: flex; align-items: center; overflow: hidden; color: #fff; background: var(--detail-navy); isolation: isolate; }
    .detail-hero::before { content: ''; position: absolute; inset: -20px; z-index: -2; background: linear-gradient(90deg, rgba(0,0,0,.62), rgba(0,0,0,.5)), url('<?php echo e($video->thumbnail_url ?: ($video->source_type === 'local' ? route('videos.thumbnail', $video) : '')); ?>') center/cover; filter: blur(2px); }
    .detail-hero::after { content: ''; position: absolute; inset: 0; z-index: -1; background: rgba(0,0,0,.55); }
    .detail-hero-inner { width: min(1240px, 100%); margin: auto; padding: 22px 20px; display: flex; align-items: center; justify-content: space-between; gap: 30px; }
    .detail-brand { flex: 0 0 auto; color: #fff; font-weight: 850; letter-spacing: -.04em; font-size: 24px; line-height: 1; }
    .detail-brand span { color: var(--detail-pink); }
    .detail-kicker { margin: 7px 0 0; color: #dce1e1; font-size: 10px; letter-spacing: .14em; text-transform: uppercase; }
    .detail-search { display: flex; flex-wrap: wrap; align-items: center; width: min(650px, 64%); border: 1px solid rgba(255,255,255,.35); border-radius: 7px; background: rgba(255,255,255,.16); backdrop-filter: blur(8px); }
    .detail-search-row { display: flex; width: 100%; height: 44px; }
    .detail-search input { flex: 1; min-width: 0; height: 100%; padding: 0 14px; border: 0; outline: 0; color: #fff; background: transparent; box-shadow: none; }
    .detail-search input::placeholder { color: #f1f3f2; }
    .detail-search button { width: 43px; height: 100%; border: 0; color: #fff; background: transparent; font-size: 20px; cursor: pointer; }
    .detail-stats { width: 100%; padding: 0 13px 7px; color: #dce1e1; font-size: 10px; letter-spacing: .02em; }
    .detail-divider { height: 12px; background: var(--detail-pink); }
    .detail-inner { width: min(1240px, 100%); margin: auto; padding: 38px 20px 64px; }
    .detail-main { display: grid; grid-template-columns: minmax(0, 7fr) minmax(245px, 3fr); gap: 42px; align-items: start; }
    .detail-player { overflow: hidden; aspect-ratio: 16/9; background: #000; }
    .detail-player video, .detail-player iframe { display: block; width: 100%; height: 100%; border: 0; object-fit: contain; }
    .recent-heading, .detail-section-heading { display: flex; align-items: center; gap: 12px; margin: 0 0 18px; color: var(--detail-heading); font-size: 19px; line-height: 1; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .recent-heading::after { content: ''; flex: 1; height: 1px; background: var(--detail-line); }
    .recent-list { list-style: none; margin: 0; padding: 0; }
    .recent-list li { padding: 0 0 13px; margin: 0 0 13px; border-bottom: 1px solid var(--detail-line); }
    .recent-list a { display: block; color: var(--detail-heading); font-size: 13px; font-weight: 750; line-height: 1.3; }
    .recent-meta, .detail-meta { color: var(--detail-body); font-size: 11px; }
    .recent-meta { margin-top: 5px; }
    .recent-more { display: inline-block; margin-top: 2px; padding: 8px 12px; border-radius: 7px; color: var(--detail-heading); background: #eef1f0; font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .detail-title-row { display: flex; align-items: start; justify-content: space-between; gap: 18px; margin-top: 27px; }
    .detail-title { max-width: 850px; margin: 0; color: var(--detail-heading); font-size: clamp(29px, 4vw, 38px); line-height: 1.07; font-weight: 850; letter-spacing: -.025em; text-transform: uppercase; }
    .detail-edit { flex: 0 0 auto; padding: 8px 14px; border-radius: 6px; color: #fff; background: var(--detail-pink); font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .detail-meta { display: flex; flex-wrap: wrap; gap: 9px; align-items: center; margin-top: 11px; font-size: 12px; }
    .detail-meta a { color: inherit; }
    .detail-meta .favorite { color: var(--detail-pink); }
    .detail-categories { margin-top: 60px; }
    .detail-section-heading { margin-bottom: 12px; font-size: 17px; letter-spacing: .02em; }
    .category-links { color: var(--detail-body); font-size: 13px; line-height: 1.8; }
    .category-links a { color: inherit; }
    .similar-heading { display: flex; align-items: center; gap: 15px; margin: 39px 0 21px; color: var(--detail-heading); font-size: 19px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .similar-heading::before, .similar-heading::after { content: ''; flex: 1; height: 1px; background: var(--detail-line); }
    .similar-heading span { white-space: nowrap; }
    .detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 25px 22px; }
    .detail-card { display: block; min-width: 0; color: inherit; }
    .detail-thumb { position: relative; overflow: hidden; aspect-ratio: 16/9; border-radius: 7px; background: #edf0ef; }
    .detail-thumb img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .detail-thumb-fallback { display: grid; place-items: center; height: 100%; color: var(--detail-body); font-size: 11px; text-transform: uppercase; }
    .detail-duration { position: absolute; right: 7px; bottom: 7px; padding: 3px 5px; border-radius: 3px; color: #fff; background: rgba(0,0,0,.75); font-size: 11px; line-height: 1; }
    .detail-card-title { display: -webkit-box; overflow: hidden; margin-top: 9px; color: var(--detail-heading); font-size: 13px; font-weight: 700; line-height: 1.3; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .detail-card-meta { margin-top: 4px; color: var(--detail-body); font-size: 11px; }
    .detail-load-more-wrap { display: flex; justify-content: center; margin-top: 34px; }
    .detail-load-more { border: 0; border-radius: 6px; padding: 12px 28px; color: #fff; background: var(--detail-pink); font-size: 12px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; cursor: pointer; transition: background .18s ease, transform .18s ease; }
    .detail-load-more:hover { background: #ef6d86; transform: translateY(-1px); }
    .detail-load-more:disabled { cursor: wait; opacity: .72; transform: none; }
    .detail-load-error, .detail-load-end { margin: 14px 0 0; color: var(--detail-body); font-size: 12px; text-align: center; }
    .detail-load-error { color: #b74d61; }
    @media (max-width: 850px) { .detail-main { grid-template-columns: 1fr; gap: 30px; } .detail-search { width: 60%; } }
    @media (max-width: 600px) { .detail-page { margin-left: -13px; margin-right: -13px; } .detail-hero-inner { align-items: stretch; flex-direction: column; gap: 14px; padding: 19px 13px; } .detail-search { width: 100%; } .detail-inner { padding: 28px 13px 45px; } .detail-title-row { flex-direction: column; gap: 12px; } .detail-title { font-size: 29px; } .detail-grid { grid-template-columns: 1fr; gap: 24px; } .similar-heading { font-size: 17px; } }
</style>

<div class="detail-page">
    <section class="detail-hero">
        <div class="detail-hero-inner">
            <div><a class="detail-brand" href="<?php echo e(route('videos.index')); ?>">Vido<span>Hub</span></a><p class="detail-kicker">Editorial video catalogue</p></div>
            <form class="detail-search" method="GET" action="<?php echo e(route('videos.index')); ?>"><div class="detail-search-row"><input name="q" placeholder="Cari video, model, atau kategori..." autocomplete="off" aria-label="Cari video"><button type="submit" aria-label="Cari">⌕</button></div><div class="detail-stats"><?php echo e(number_format($totalVideos, 0, ',', '.')); ?> videos &nbsp; · &nbsp; +<?php echo e($todayVideos); ?> today &nbsp; · &nbsp; Trending</div></form>
        </div>
    </section>
    <div class="detail-divider"></div>
    <div class="detail-inner">
        <div class="detail-main">
            <div class="detail-player" id="playerShell">
                <?php if($video->youtube_embed_url): ?>
                    <iframe id="videoPlayer" src="<?php echo e($video->youtube_embed_url); ?>" title="<?php echo e($video->title); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                <?php else: ?>
                    <video id="videoPlayer" controls playsinline preload="metadata" poster="<?php echo e($video->thumbnail_url ?: ($video->source_type === 'local' ? route('videos.thumbnail', $video) : '')); ?>"><source src="<?php echo e($video->playback_url); ?>">Browser kamu tidak mendukung pemutar video.</video>
                <?php endif; ?>
            </div>
            <aside class="detail-recent"><h2 class="recent-heading">Recent videos</h2><ul class="recent-list"><?php $__empty_1 = true; $__currentLoopData = $recentVideos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><li><a href="<?php echo e(route('videos.show', $recent)); ?>"><?php echo e($recent->title); ?></a><div class="recent-meta"><?php echo e($recent->category?->name ?: 'Uncategorized'); ?> · <?php echo e($recent->model_name ?: 'Model unavailable'); ?> · <?php echo e($recent->duration ?: '--:--'); ?></div></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><li class="recent-meta">Belum ada video terbaru.</li><?php endif; ?></ul><a class="recent-more" href="<?php echo e(route('videos.index')); ?>">See all recent videos</a></aside>
        </div>
        <section aria-labelledby="video-title">
            <div class="detail-title-row"><h1 class="detail-title" id="video-title"><?php echo e($video->title); ?></h1><a class="detail-edit" href="<?php echo e(route('admin.videos.index', ['edit' => $video->id])); ?>#video-form">Edit video</a></div>
            <div class="detail-meta"><span><?php echo e(optional($video->created_at)->format('d M Y')); ?></span><span>·</span><span><?php echo e($video->duration ?: '--:--'); ?></span><span>·</span><span><?php $__empty_1 = true; $__currentLoopData = $video->models; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a href="<?php echo e(route('videos.index', ['model' => $model->id])); ?>"><?php echo e($model->name); ?></a><?php echo e(!$loop->last ? ', ' : ''); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php echo e($video->model_name ?: 'Model unavailable'); ?><?php endif; ?></span><span>·</span><span class="favorite">♡ Favorite</span></div>
        </section>
        <section class="detail-categories"><h2 class="detail-section-heading">This video belongs to the following categories</h2><div class="category-links"><?php $__empty_1 = true; $__currentLoopData = $video->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a href="<?php echo e(route('videos.index', ['category' => $category->slug])); ?>"><?php echo e($category->name); ?></a><?php echo e(!$loop->last ? ', ' : ''); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> Uncategorized <?php endif; ?></div></section>
        <h2 class="similar-heading"><span>Similar videos</span></h2>
        <div class="detail-grid" id="relatedGrid"><?php $__empty_1 = true; $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a class="detail-card" href="<?php echo e(route('videos.show', $item)); ?>"><div class="detail-thumb"><?php ($thumb = $item->thumbnail_url ?: ($item->source_type === 'local' ? route('videos.thumbnail', $item) : null)); ?> <?php if($thumb): ?><img src="<?php echo e($thumb); ?>" alt="<?php echo e($item->title); ?>" loading="lazy" onerror="this.remove();this.nextElementSibling.hidden=false"><?php endif; ?><span class="detail-thumb-fallback" <?php if($thumb): ?> hidden <?php endif; ?>>Preview unavailable</span><span class="detail-duration"><?php echo e($item->duration ?: '--:--'); ?></span></div><strong class="detail-card-title"><?php echo e($item->title); ?></strong><div class="detail-card-meta">◷ <?php echo e($item->duration ?: '--:--'); ?></div></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="detail-meta">Belum ada video serupa.</p><?php endif; ?></div>
        <?php if($recommendations->hasMorePages()): ?><div class="detail-load-more-wrap"><button class="detail-load-more" id="loadMoreRelated" type="button" data-url="<?php echo e(route('videos.related', $video)); ?>" data-page="1">Lihat lebih banyak...</button></div><p class="detail-load-error" id="relatedLoadError" hidden>Gagal memuat video. Coba lagi.</p><p class="detail-load-end" id="relatedLoadEnd" hidden>Semua video telah ditampilkan.</p><?php else: ?><p class="detail-load-end">Semua video telah ditampilkan.</p><?php endif; ?>
    </div>
</div>
<script>
(() => {
    const button = document.getElementById('loadMoreRelated');
    const grid = document.getElementById('relatedGrid');
    if (!button || !grid) return;
    const error = document.getElementById('relatedLoadError');
    const end = document.getElementById('relatedLoadEnd');
    let loading = false;

    const createCard = item => {
        const link = document.createElement('a');
        link.className = 'detail-card';
        link.href = item.url;
        const thumb = document.createElement('div');
        thumb.className = 'detail-thumb';
        if (item.thumbnail_src) {
            const image = document.createElement('img');
            image.src = item.thumbnail_src;
            image.alt = item.title;
            image.loading = 'lazy';
            const fallback = document.createElement('span');
            fallback.className = 'detail-thumb-fallback';
            fallback.textContent = 'Preview unavailable';
            fallback.hidden = true;
            image.addEventListener('error', () => { image.remove(); fallback.hidden = false; });
            thumb.append(image, fallback);
        } else {
            const fallback = document.createElement('span');
            fallback.className = 'detail-thumb-fallback';
            fallback.textContent = 'Preview unavailable';
            thumb.append(fallback);
        }
        const duration = document.createElement('span');
        duration.className = 'detail-duration';
        duration.textContent = item.duration || '--:--';
        thumb.append(duration);
        const title = document.createElement('strong');
        title.className = 'detail-card-title';
        title.textContent = item.title;
        const meta = document.createElement('div');
        meta.className = 'detail-card-meta';
        meta.textContent = `◷ ${item.duration || '--:--'}`;
        link.append(thumb, title, meta);
        return link;
    };

    button.addEventListener('click', async () => {
        if (loading) return;
        loading = true;
        button.disabled = true;
        button.textContent = 'Memuat...';
        error.hidden = true;
        try {
            const nextPage = Number(button.dataset.page) + 1;
            const response = await fetch(`${button.dataset.url}?page=${nextPage}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('related request failed');
            const payload = await response.json();
            payload.data.forEach(item => grid.append(createCard(item)));
            button.dataset.page = String(payload.current_page);
            if (!payload.has_more) {
                button.remove();
                end.hidden = false;
            } else {
                button.disabled = false;
                button.textContent = 'Lihat lebih banyak...';
            }
        } catch {
            button.disabled = false;
            button.textContent = 'Coba lagi';
            error.hidden = false;
        } finally {
            loading = false;
        }
    });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\videohub\videohub\resources\views/videos/show.blade.php ENDPATH**/ ?>