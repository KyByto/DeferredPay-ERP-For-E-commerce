<div class="flex items-center justify-center h-full">
    @if($getState())
        <img src="{{ $getState() }}" class="w-16 h-16 rounded-lg object-cover shadow-sm border border-gray-200" alt="Produit">
    @else
        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs text-center border border-gray-200">
            No Img
        </div>
    @endif
</div>
