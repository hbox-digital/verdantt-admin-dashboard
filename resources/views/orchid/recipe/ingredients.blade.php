@php
    $existingIngredients = $recipe['ingredients'] ?? [];
    $options = $ingredientOptions ?? [];
@endphp

<div class="bg-white rounded p-3 mb-3" id="ingredients-repeater">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Ingredients</h5>
        <button type="button" class="btn btn-outline-primary btn-sm" id="ingredients-add-row">Add ingredient</button>
    </div>

    <div id="ingredients-rows"></div>

    <template id="ingredient-row-template">
        <div class="row g-2 align-items-center border-top pt-2 mt-2 ingredient-row">
            <div class="col-md-3">
                <select class="form-select form-select-sm" data-field="ingredient_id" required>
                    <option value="">Select ingredient</option>
                    @foreach ($options as $option)
                        <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <input type="number" step="any" class="form-control form-control-sm" data-field="quantity" placeholder="Qty">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" data-field="quantity_label" placeholder="Quantity label">
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control form-control-sm" data-field="unit" placeholder="Unit">
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control form-control-sm" data-field="prefix" placeholder="Prefix">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" data-field="notes" placeholder="Notes">
            </div>
            <div class="col-md-1 form-check">
                <input type="checkbox" class="form-check-input" data-field="is_optional">
                <label class="form-check-label small">Optional</label>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm ingredient-remove-row">&times;</button>
            </div>
        </div>
    </template>
</div>

<script>
(function () {
    const container = document.getElementById('ingredients-rows');
    const template = document.getElementById('ingredient-row-template');
    let index = 0;

    function addRow(data) {
        data = data || {};
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.ingredient-row');
        const i = index++;

        row.querySelectorAll('[data-field]').forEach((field) => {
            const name = field.dataset.field;
            field.name = `ingredients[${i}][${name}]`;

            if (field.type === 'checkbox') {
                field.checked = !!data[name];
            } else if (data[name] !== undefined && data[name] !== null) {
                field.value = data[name];
            }
        });

        row.querySelector('.ingredient-remove-row').addEventListener('click', () => row.remove());

        container.appendChild(row);
    }

    document.getElementById('ingredients-add-row').addEventListener('click', () => addRow());

    const existing = @json($existingIngredients);
    existing.forEach((row) => addRow(row));
})();
</script>
