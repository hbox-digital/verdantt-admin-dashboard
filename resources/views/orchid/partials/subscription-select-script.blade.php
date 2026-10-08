{{--
    Every row dropdown lives inside Orchid's single `#post-form`, so it cannot
    own a <form> of its own — nested forms are invalid HTML and the browser
    silently drops everything after the inner </form> (see search-box.blade.php).

    Instead we submit the shared form through a throw-away submit button whose
    `formaction` mirrors exactly what Orchid's own action buttons generate:

        {current-url}/{method}?{parameters}

    Orchid's form controller reads the submitter's `formaction` and posts there,
    so the screen method receives `id` and `subscribed` as query parameters.
--}}
<script>
(function () {
    document.addEventListener('change', function (event) {
        const select = event.target.closest('[data-role="subscription-select"]');

        if (!select) {
            return;
        }

        const form = document.getElementById('post-form');

        if (!form) {
            return;
        }

        const url = new URL(window.location.href);
        url.search = '';
        url.pathname = url.pathname.replace(/\/+$/, '') + '/updateSubscription';
        url.searchParams.set('id', select.dataset.userId);
        url.searchParams.set('subscribed', select.value === '1' ? '1' : '0');

        const button = document.createElement('button');
        button.type = 'submit';
        button.setAttribute('formaction', url.pathname + url.search);
        button.style.display = 'none';

        form.appendChild(button);
        button.click();

        setTimeout(function () {
            button.remove();
        }, 0);
    });
})();
</script>
