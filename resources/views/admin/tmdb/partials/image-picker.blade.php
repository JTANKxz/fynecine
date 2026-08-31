@php
    $pickerId = 'tmdb-image-picker-' . $type . '-' . $tmdbId;
    $imageEndpoint = route('admin.tmdb.images', ['type' => $type, 'tmdbId' => $tmdbId]);
@endphp

<section id="{{ $pickerId }}"
         data-tmdb-image-picker
         data-endpoint="{{ $imageEndpoint }}"
         class="md:col-span-2 overflow-hidden rounded-2xl border border-neutral-700 bg-neutral-950/70">
    <div class="flex flex-col gap-3 border-b border-neutral-800 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="flex items-center gap-2 font-bold text-white">
                <i class="fa-regular fa-images text-purple-400"></i>
                Galeria de artes do TMDb
            </h3>
            <p class="mt-1 text-xs text-neutral-400">Escolha uma arte e o campo correspondente será preenchido automaticamente.</p>
        </div>
        <button type="button" data-tmdb-image-reload
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-700 bg-neutral-800 px-4 py-2 text-sm font-semibold text-white transition hover:border-purple-500 hover:bg-neutral-700 disabled:cursor-wait disabled:opacity-60">
            <i class="fa-solid fa-rotate"></i>
            Atualizar opções
        </button>
    </div>

    <div data-tmdb-image-status class="border-b border-neutral-800 px-4 py-3 text-sm text-neutral-400">
        <i class="fa-solid fa-circle-notch fa-spin mr-2 text-purple-400"></i>Buscando imagens no TMDb...
    </div>

    @foreach([
        ['key' => 'logos', 'field' => 'logo_path', 'title' => 'Clear logos', 'description' => 'Logos transparentes do título', 'shape' => 'logo'],
        ['key' => 'backdrops', 'field' => 'backdrop_path', 'title' => 'Fotos de fundo', 'description' => 'Imagens horizontais para destaques e detalhes', 'shape' => 'backdrop'],
        ['key' => 'posters', 'field' => 'poster_path', 'title' => 'Pôsteres', 'description' => 'Capas verticais para cards e listagens', 'shape' => 'poster'],
    ] as $gallery)
        <div data-tmdb-image-gallery="{{ $gallery['key'] }}"
             data-target-field="{{ $gallery['field'] }}"
             data-image-shape="{{ $gallery['shape'] }}"
             class="border-b border-neutral-800 p-4 last:border-b-0">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-white">{{ $gallery['title'] }} <span data-gallery-count class="ml-1 text-xs font-medium text-neutral-500"></span></h4>
                    <p class="text-xs text-neutral-500">{{ $gallery['description'] }}</p>
                </div>
                <div class="hidden items-center gap-2 sm:flex">
                    <button type="button" data-gallery-scroll="-1" aria-label="Rolar para a esquerda"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-neutral-700 bg-neutral-800 text-neutral-300 transition hover:border-purple-500 hover:text-white">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" data-gallery-scroll="1" aria-label="Rolar para a direita"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-neutral-700 bg-neutral-800 text-neutral-300 transition hover:border-purple-500 hover:text-white">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div data-gallery-track class="flex min-h-28 snap-x snap-mandatory gap-3 overflow-x-auto overscroll-x-contain pb-3 scrollbar-thin scrollbar-track-neutral-900 scrollbar-thumb-neutral-700">
                <div class="flex min-h-28 w-full items-center justify-center rounded-xl border border-dashed border-neutral-800 text-sm text-neutral-600">Carregando...</div>
            </div>
        </div>
    @endforeach
</section>

@once
    @push('scripts')
        <script>
            (() => {
                const normalizeTmdbPath = (value) => String(value || '')
                    .trim()
                    .replace(/^https:\/\/image\.tmdb\.org\/t\/p\/[^/]+/i, '');

                const matchesSelection = (currentValue, image) => {
                    const current = normalizeTmdbPath(currentValue);
                    return current !== '' && (current === image.path || current === normalizeTmdbPath(image.url));
                };

                const languageLabel = (language) => {
                    if (language === 'pt') return 'PT';
                    if (language === 'en') return 'EN';
                    return 'Sem idioma';
                };

                const setStatus = (root, message, state = 'loading') => {
                    const status = root.querySelector('[data-tmdb-image-status]');
                    const icon = state === 'error'
                        ? 'fa-solid fa-circle-exclamation text-red-400'
                        : state === 'success'
                            ? 'fa-solid fa-circle-check text-emerald-400'
                            : 'fa-solid fa-circle-notch fa-spin text-purple-400';
                    status.className = `border-b border-neutral-800 px-4 py-3 text-sm ${state === 'error' ? 'text-red-300' : 'text-neutral-400'}`;
                    status.innerHTML = `<i class="${icon} mr-2"></i><span></span>`;
                    status.querySelector('span').textContent = message;
                };

                const updateSelectedCards = (gallery, input) => {
                    gallery.querySelectorAll('[data-image-option]').forEach((button) => {
                        const selected = normalizeTmdbPath(input.value) === normalizeTmdbPath(button.dataset.imageValue);
                        button.classList.toggle('ring-2', selected);
                        button.classList.toggle('ring-purple-500', selected);
                        button.classList.toggle('border-purple-500', selected);
                        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                        const badge = button.querySelector('[data-selected-badge]');
                        if (badge) badge.classList.toggle('hidden', !selected);
                    });
                };

                const createImageCard = (image, shape, input, gallery) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.imageOption = '';
                    button.dataset.imageValue = image.url;
                    button.className = `group relative flex-none snap-start overflow-hidden rounded-xl border border-neutral-700 bg-neutral-900 text-left transition hover:-translate-y-0.5 hover:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 ${shape === 'poster' ? 'w-36' : shape === 'logo' ? 'w-64' : 'w-72'}`;
                    button.setAttribute('aria-label', 'Selecionar esta imagem');
                    button.setAttribute('aria-pressed', matchesSelection(input.value, image) ? 'true' : 'false');

                    const imageElement = document.createElement('img');
                    imageElement.src = image.preview_url;
                    imageElement.alt = '';
                    imageElement.loading = 'lazy';
                    imageElement.className = shape === 'poster'
                        ? 'aspect-[2/3] w-full bg-neutral-800 object-cover'
                        : shape === 'logo'
                            ? 'h-32 w-full bg-[linear-gradient(135deg,#171717_25%,#202020_25%,#202020_50%,#171717_50%,#171717_75%,#202020_75%)] bg-[length:18px_18px] object-contain p-4'
                            : 'aspect-video w-full bg-neutral-800 object-cover';
                    imageElement.addEventListener('error', () => button.remove());

                    const details = document.createElement('div');
                    details.className = 'flex items-center justify-between gap-2 border-t border-neutral-800 px-2.5 py-2 text-[10px] text-neutral-400';
                    const resolution = image.width && image.height ? `${image.width}×${image.height}` : 'TMDb';
                    details.innerHTML = '<span data-language></span><span data-resolution></span>';
                    details.querySelector('[data-language]').textContent = languageLabel(image.language);
                    details.querySelector('[data-resolution]').textContent = resolution;

                    const selectedBadge = document.createElement('span');
                    selectedBadge.dataset.selectedBadge = '';
                    selectedBadge.className = 'absolute right-2 top-2 rounded-full bg-purple-600 px-2 py-1 text-[10px] font-bold text-white shadow-lg hidden';
                    selectedBadge.textContent = 'Selecionada';

                    button.append(imageElement, details, selectedBadge);
                    button.addEventListener('click', () => {
                        input.value = image.url;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        updateSelectedCards(gallery, input);
                    });

                    if (matchesSelection(input.value, image)) {
                        button.classList.add('ring-2', 'ring-purple-500', 'border-purple-500');
                        selectedBadge.classList.remove('hidden');
                    }

                    return button;
                };

                const renderGallery = (root, key, images) => {
                    const gallery = root.querySelector(`[data-tmdb-image-gallery="${key}"]`);
                    const track = gallery.querySelector('[data-gallery-track]');
                    const count = gallery.querySelector('[data-gallery-count]');
                    const input = document.querySelector(`input[name="${gallery.dataset.targetField}"]`);
                    track.replaceChildren();
                    count.textContent = `(${images.length})`;

                    if (!images.length) {
                        const empty = document.createElement('div');
                        empty.className = 'flex min-h-28 w-full items-center justify-center rounded-xl border border-dashed border-neutral-800 px-4 text-center text-sm text-neutral-600';
                        empty.textContent = 'Nenhuma opção disponível no TMDb para este conteúdo.';
                        track.append(empty);
                        return;
                    }

                    let rendered = 0;
                    const appendBatch = () => {
                        track.querySelector('[data-load-more-images]')?.remove();
                        images.slice(rendered, rendered + 50).forEach((image) => {
                            track.append(createImageCard(image, gallery.dataset.imageShape, input, gallery));
                        });
                        rendered = Math.min(rendered + 50, images.length);

                        if (rendered < images.length) {
                            const loadMore = document.createElement('button');
                            loadMore.type = 'button';
                            loadMore.dataset.loadMoreImages = '';
                            loadMore.className = 'flex min-h-28 w-44 flex-none snap-start flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-neutral-700 bg-neutral-900 px-4 text-center text-sm font-semibold text-neutral-300 transition hover:border-purple-500 hover:text-white';
                            loadMore.innerHTML = '<i class="fa-solid fa-plus text-purple-400"></i><span></span>';
                            loadMore.querySelector('span').textContent = `Carregar mais (${images.length - rendered})`;
                            loadMore.addEventListener('click', appendBatch);
                            track.append(loadMore);
                        }
                    };

                    appendBatch();
                };

                const loadImages = async (root) => {
                    const reloadButton = root.querySelector('[data-tmdb-image-reload]');
                    reloadButton.disabled = true;
                    setStatus(root, 'Buscando imagens no TMDb...');

                    try {
                        const response = await fetch(root.dataset.endpoint, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(payload.message || 'Não foi possível carregar as imagens.');

                        renderGallery(root, 'logos', payload.logos || []);
                        renderGallery(root, 'backdrops', payload.backdrops || []);
                        renderGallery(root, 'posters', payload.posters || []);
                        const total = (payload.logos?.length || 0) + (payload.backdrops?.length || 0) + (payload.posters?.length || 0);
                        setStatus(root, `${total} opções encontradas. Clique em uma imagem para selecioná-la.`, 'success');
                    } catch (error) {
                        setStatus(root, error.message || 'Falha ao carregar as imagens do TMDb.', 'error');
                    } finally {
                        reloadButton.disabled = false;
                    }
                };

                const initialize = (root) => {
                    if (root.dataset.initialized === 'true') return;
                    root.dataset.initialized = 'true';
                    root.querySelector('[data-tmdb-image-reload]').addEventListener('click', () => loadImages(root));
                    root.querySelectorAll('[data-tmdb-image-gallery]').forEach((gallery) => {
                        gallery.querySelectorAll('[data-gallery-scroll]').forEach((button) => {
                            button.addEventListener('click', () => {
                                gallery.querySelector('[data-gallery-track]').scrollBy({
                                    left: Number(button.dataset.galleryScroll) * 520,
                                    behavior: 'smooth',
                                });
                            });
                        });
                    });
                    loadImages(root);
                };

                const boot = () => document.querySelectorAll('[data-tmdb-image-picker]').forEach(initialize);
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
                else boot();
            })();
        </script>
    @endpush
@endonce
