<div class="bg-neutral-900 border border-neutral-800 rounded overflow-hidden group relative content-card" data-id="{{ $item->id }}">
    <img src="{{ $item->poster_path ? 'https://image.tmdb.org/t/p/w200'.$item->poster_path : 'https://via.placeholder.com/200x300' }}"
         class="w-full aspect-[2/3] object-cover">
    <div class="p-1.5">
        <p class="text-[10px] font-bold truncate">{{ $type === 'movie' ? $item->title : $item->name }}</p>
    </div>
    <button onclick="removeContent({{ $item->id }}, '{{ $type }}', this)"
        class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition bg-red-600 text-white w-5 h-5 rounded-full text-[10px] flex items-center justify-center hover:bg-red-700">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
