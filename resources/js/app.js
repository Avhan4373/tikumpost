// Interaksi ringan untuk tampilan publik (tanpa dependensi).

document.addEventListener('DOMContentLoaded', () => {
    // Menu navigasi mobile.
    const toggle = document.querySelector('[data-mobile-nav-toggle]');
    const panel = document.querySelector('[data-mobile-nav]');

    if (toggle && panel) {
        toggle.addEventListener('click', () => {
            const isHidden = panel.classList.toggle('hidden');
            toggle.setAttribute('aria-expanded', String(!isHidden));
            toggle.classList.toggle('is-open', !isHidden);
        });
    }

    // Header mengecil saat scroll: logo & pencarian disembunyikan, menu tetap menempel.
    const siteHeader = document.querySelector('.site-header');

    if (siteHeader) {
        const syncHeader = () => {
            siteHeader.classList.toggle('is-header-collapsed', window.scrollY > 64);
        };

        syncHeader();
        document.addEventListener('scroll', syncHeader, { passive: true });
    }

    // Submenu: dibuka via hover (desktop) dan klik (desktop/mobile).
    const dropdowns = document.querySelectorAll('[data-menu-dropdown]');

    const closeDropdowns = (except = null) => {
        dropdowns.forEach((dropdown) => {
            if (dropdown === except) {
                return;
            }

            dropdown.removeAttribute('data-open');
            dropdown.querySelector('[data-menu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-menu-toggle]');

        if (!trigger) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = dropdown.toggleAttribute('data-open');
            trigger.setAttribute('aria-expanded', String(isOpen));

            if (isOpen) {
                closeDropdowns(dropdown);
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-menu-dropdown]')) {
            closeDropdowns();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdowns();
        }
    });

    // Indikator progres baca pada halaman artikel.
    const progress = document.querySelector('[data-reading-progress]');

    if (progress) {
        const update = () => {
            const el = document.documentElement;
            const max = el.scrollHeight - el.clientHeight;
            progress.style.width = max > 0 ? `${Math.min((el.scrollTop / max) * 100, 100)}%` : '0%';
        };

        update();
        document.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    }

    // Tombol salin tautan artikel.
    document.querySelectorAll('[data-copy-link]').forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.getAttribute('data-copy-link');

            try {
                await navigator.clipboard.writeText(url);
                const label = button.querySelector('[data-copy-label]');
                const original = label ? label.textContent : null;

                if (label) {
                    label.textContent = 'Tersalin!';
                    setTimeout(() => {
                        label.textContent = original;
                    }, 1800);
                }
            } catch (error) {
                // Clipboard tidak tersedia (mis. konteks non-HTTPS) — abaikan.
            }
        });
    });

    // Lightbox gambar (mis. infografis di sidebar beranda).
    const lightbox = document.querySelector('[data-lightbox]');

    if (lightbox) {
        const lightboxImage = lightbox.querySelector('[data-lightbox-image]');
        const isOpen = () => !lightbox.classList.contains('hidden');

        const openLightbox = (src, alt) => {
            lightboxImage.src = src;
            lightboxImage.alt = alt;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeLightbox = () => {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            lightboxImage.src = '';
        };

        document.querySelectorAll('[data-lightbox-src]').forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                openLightbox(trigger.getAttribute('data-lightbox-src'), trigger.getAttribute('data-lightbox-alt') ?? '');
            });
        });

        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox || event.target.closest('[data-lightbox-close]')) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isOpen()) {
                closeLightbox();
            }
        });
    }

    // Scroll reveal: elemen muncul halus saat masuk viewport.
    const revealTargets = document.querySelectorAll('[data-reveal]');

    if (revealTargets.length) {
        if (!('IntersectionObserver' in window)) {
            // Tanpa IntersectionObserver: tampilkan semua tanpa animasi.
            revealTargets.forEach((el) => el.removeAttribute('data-reveal'));
        } else {
            document.documentElement.dataset.revealReady = '1';

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    const el = entry.target;
                    observer.unobserve(el);
                    el.classList.add('is-visible');

                    // Lepas atribut setelah animasi usai agar efek hover bawaan
                    // (mis. card-lift) tidak ikut tertimpa.
                    const styles = window.getComputedStyle(el);
                    const wait = ((parseFloat(styles.transitionDelay) || 0)
                        + (parseFloat(styles.transitionDuration) || 0)) * 1000;

                    window.setTimeout(() => {
                        el.removeAttribute('data-reveal');
                        el.style.removeProperty('--reveal-delay');
                    }, wait + 60);
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

            revealTargets.forEach((el) => observer.observe(el));
        }
    }

    // Media: shimmer saat memuat, lalu gambar memudar masuk setelah siap.
    document.querySelectorAll('img[data-fade]').forEach((img) => {
        const markLoaded = () => {
            img.classList.add('is-loaded');
            img.closest('.media-frame')?.classList.add('is-loaded');
        };

        if (img.complete) {
            markLoaded();
        } else {
            img.addEventListener('load', markLoaded, { once: true });
            img.addEventListener('error', markLoaded, { once: true });
        }
    });

    // Transisi antar halaman + progress bar tipis di atas.
    const navProgress = document.querySelector('[data-nav-progress]');
    let navigating = false;

    const startProgress = () => {
        document.body.classList.add('is-leaving');

        if (navProgress) {
            navProgress.style.opacity = '1';
            navProgress.style.transform = 'scaleX(0.75)';
        }
    };

    const finishProgress = () => {
        if (!navProgress) {
            return;
        }

        navProgress.style.transform = 'scaleX(1)';
        window.setTimeout(() => {
            navProgress.style.opacity = '0';
            navProgress.style.transform = 'scaleX(0)';
        }, 260);
    };

    // Saat kembali lewat cache browser, pastikan halaman tidak tetap memudar.
    window.addEventListener('pageshow', () => {
        navigating = false;
        document.body.classList.remove('is-leaving');
        finishProgress();
    });

    document.addEventListener('click', (event) => {
        if (navigating || event.defaultPrevented || event.button !== 0) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const link = event.target.closest('a[href]');

        if (!link || link.target === '_blank' || link.hasAttribute('download')
            || link.closest('[data-no-transition]')) {
            return;
        }

        const href = link.getAttribute('href');

        if (!href || href.startsWith('#')) {
            return;
        }

        const url = new URL(link.href, window.location.href);

        // Hanya tautan internal, bukan tautan ke halaman yang sama.
        if (url.origin !== window.location.origin
            || (url.pathname === window.location.pathname && url.search === window.location.search)) {
            return;
        }

        event.preventDefault();
        navigating = true;
        startProgress();

        window.setTimeout(() => {
            window.location.href = url.href;
        }, 180);
    });

    // Admin: urutan menu via drag & drop (Sortable dimuat hanya saat dibutuhkan).
    const menuSortable = document.querySelector('[data-menu-sortable]');

    if (menuSortable) {
        import('sortablejs').then(({ default: Sortable }) => {
            const status = document.querySelector('[data-menu-save-status]');
            const rootList = menuSortable.querySelector('[data-menu-list]');

            const serialize = (list, parentId) => [...list.children].flatMap((li, index) => {
                const id = Number(li.dataset.id);
                const rows = [{ id, parent_id: parentId, sort_order: index }];
                const childList = li.querySelector(':scope > [data-menu-children]');

                if (childList) {
                    rows.push(...serialize(childList, id));
                }

                return rows;
            });

            const save = async () => {
                if (status) {
                    status.textContent = 'Menyimpan...';
                    status.classList.remove('opacity-0');
                }

                try {
                    const response = await fetch(menuSortable.dataset.reorderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                        body: JSON.stringify({ items: serialize(rootList, null) }),
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    if (status) {
                        status.textContent = 'Tersimpan';
                        status.classList.add('text-emerald-600');
                        setTimeout(() => status.classList.add('opacity-0'), 1500);
                    }
                } catch (error) {
                    if (status) {
                        status.textContent = 'Gagal menyimpan — coba lagi';
                        status.classList.remove('opacity-0');
                        status.classList.replace('text-emerald-600', 'text-red-600');
                    }
                }
            };

            menuSortable.querySelectorAll('[data-menu-list]').forEach((list) => {
                Sortable.create(list, {
                    group: 'kabarkita-menu',
                    handle: '[data-drag-handle]',
                    animation: 160,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    emptyInsertThreshold: 12,
                    onEnd: save,
                });
            });
        });
    }
});
