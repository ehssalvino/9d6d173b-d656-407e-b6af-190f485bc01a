(function () {
    function updateStatus(select) {
        var taskId = select.getAttribute('data-task-id');
        var status = select.value;

        fetch(HTW_ADMIN.restUrl + '/tasks/' + taskId + '/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': HTW_ADMIN.nonce
            },
            body: JSON.stringify({ status: status })
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Falha ao atualizar status');
            }
            window.location.reload();
        }).catch(function (error) {
            alert(error.message);
        });
    }

    function copyFromButton(button) {
        var input = button.parentElement.querySelector('input');
        if (!input) {
            return;
        }

        input.focus();
        input.select();
        navigator.clipboard.writeText(input.value).then(function () {
            var original = button.textContent;
            button.textContent = 'Copiado';
            setTimeout(function () {
                button.textContent = original;
            }, 1200);
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('htw-status')) {
            updateStatus(event.target);
        }
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('htw-copy')) {
            copyFromButton(event.target);
        }
    });
})();
