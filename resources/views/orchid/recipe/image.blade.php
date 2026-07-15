<div class="bg-white rounded p-3 mb-3">
    <h5 class="mb-3">Image</h5>

    @if (! empty($recipe['image_url']))
        <div class="mb-2">
            <img src="{{ $recipe['image_url'] }}" class="rounded" style="height:96px;">
        </div>
    @endif

    <input type="file" name="image" accept="image/*" class="form-control">
    <div class="form-text">Upload to replace the current image. Leave empty to keep it.</div>
</div>
