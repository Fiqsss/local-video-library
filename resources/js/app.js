import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';

function VideoHome() {
    const videos = window.videoHomeData || [];
    const pagination = window.videoHomePagination || {};
    return React.createElement(React.Fragment, null,
        React.createElement('div', { className: 'cards' }, videos.length ? videos.map(video =>
            React.createElement('a', { className: 'card', href: video.url, key: video.id },
                React.createElement('div', { className: 'thumb' },
                    video.thumbnail_src ? React.createElement('img', { className: 'lazy-thumbnail', loading: 'lazy', 'data-src': video.thumbnail_src, alt: video.title }) : React.createElement('div', { className: 'thumb-placeholder' }, 'Preview belum tersedia'),
                React.createElement('span', { className: 'duration' }, video.duration || '--:--')),
                React.createElement('div', { className: 'copy' },
                    React.createElement('strong', null, video.title),
                    React.createElement('div', { className: 'tags' },
                        video.categories.map(category => React.createElement('span', { className: 'pill', key: category }, category)),
                        video.models.map(model => React.createElement('span', { className: 'pill', key: model.id, style: { background: 'var(--blue)', color: '#3474c5' } }, model.name))))))
            : React.createElement('p', { className: 'muted' }, 'Belum ada video untuk ditampilkan.')),
        pagination.last > 1 && React.createElement('div', { className: 'pagination-custom' },
            React.createElement('div', { className: 'pagination-inner' },
                pagination.prev ? React.createElement('a', { className: 'page-link', href: pagination.prev }, 'Sebelum') : React.createElement('span', { className: 'page-link disabled' }, 'Sebelum'),
                React.createElement('span', { className: 'page-link active' }, pagination.current),
                pagination.next ? React.createElement('a', { className: 'page-link', href: pagination.next }, 'Sesudah') : React.createElement('span', { className: 'page-link disabled' }, 'Sesudah'))));
}

const videoHome = document.getElementById('video-home-react');
if (videoHome) createRoot(videoHome).render(React.createElement(VideoHome));
