{{--
    Deliberately NOT a <form> — Orchid already wraps every screen's layouts in
    its own outer <form id="post-form">, and nested <form> elements are invalid
    HTML: the browser's parser closes the outer form as soon as it hits this
    partial's closing </form> tag, silently detaching everything rendered
    after it (the table, footer, etc.) from the form's DOM subtree. Plain
    inputs + JS-built navigation avoid that entirely.
--}}
<div class="bg-white rounded p-3 mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-auto flex-grow-1">
            <input
                type="text"
                data-role="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="{{ $placeholder ?? 'Search...' }}"
            >
        </div>

        @foreach ($filters ?? [] as $filter)
            <div class="col-auto">
                <select data-role="filter" data-name="{{ $filter['name'] }}" class="form-select">
                    <option value="">{{ $filter['label'] }}</option>
                    @foreach ($filter['options'] as $value => $label)
                        <option value="{{ $value }}" @selected(request($filter['name']) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div class="col-auto">
            <button type="button" data-role="apply" class="btn btn-outline-primary">Filter</button>
        </div>

        @if (request('search') || collect($filters ?? [])->contains(fn ($f) => request($f['name'])))
            <div class="col-auto">
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    const container = document.currentScript.previousElementSibling;

    // Opt-in: pass `debounce` => 300 to auto-apply the search after the user
    // stops typing for that many milliseconds. Defaults to 0 (manual apply).
    const debounceMs = Number({{ (int) ($debounce ?? 0) }}) || 0;

    // Opt-in: pass `focus` => true to put the caret back into the search field
    // once the page has loaded. Required whenever `debounce` is used, because
    // every auto-apply is a full page load and would otherwise drop focus.
    const focusOnLoad = {{ ($focus ?? false) ? 'true' : 'false' }};

    let debounceTimer = null;

    const searchInput = container.querySelector('[data-role="search"]');

    function apply() {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }

        const url = new URL(window.location.href);

        const search = searchInput.value;
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }

        container.querySelectorAll('[data-role="filter"]').forEach((select) => {
            const name = select.dataset.name;
            if (select.value) {
                url.searchParams.set(name, select.value);
            } else {
                url.searchParams.delete(name);
            }
        });

        url.searchParams.delete('page');

        window.location.href = url.toString();
    }

    container.querySelector('[data-role="apply"]').addEventListener('click', apply);
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            apply();
        }
    });

    if (debounceMs > 0) {
        searchInput.addEventListener('input', () => {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            debounceTimer = setTimeout(apply, debounceMs);
        });
    }

    if (focusOnLoad) {
        // Each debounced apply() above navigates with a full page load, which
        // drops focus and the caret position. Claim them back once the page is
        // ready — but never steal focus from a control that already has it
        // (for example an Orchid modal opened on page load).
        const refocus = () => {
            const active = document.activeElement;

            if (active === null || active === document.body || active === document.documentElement) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', refocus, { once: true });
        } else {
            // Give Turbo a frame to finish swapping the page in first.
            window.requestAnimationFrame(refocus);
        }
    }
})();
</script>
