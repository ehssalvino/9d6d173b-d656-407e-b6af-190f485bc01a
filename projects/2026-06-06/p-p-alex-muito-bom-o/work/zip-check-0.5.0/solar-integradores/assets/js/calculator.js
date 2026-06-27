(function () {
    'use strict';

    function request(form, action) {
        var data = new FormData(form);
        data.append('action', action);
        data.append('nonce', SI_CONFIG.nonce);
        return fetch(SI_CONFIG.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (response) {
            return response.json();
        });
    }

    function setBusy(form, busy) {
        var button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = busy;
            button.classList.toggle('is-loading', busy);
        }
    }

    function setupCitySearch(app) {
        var input = app.querySelector('[data-si-city-search]');
        var suggestions = app.querySelector('[data-si-city-suggestions]');
        if (!input || !suggestions) {
            return;
        }
        var timer;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            suggestions.hidden = true;
            if (input.value.trim().length < 2) {
                return;
            }
            timer = window.setTimeout(function () {
                var url = SI_CONFIG.ajaxUrl + '?action=si_search_municipalities&nonce=' +
                    encodeURIComponent(SI_CONFIG.nonce) + '&query=' + encodeURIComponent(input.value.trim());
                fetch(url, { credentials: 'same-origin' }).then(function (response) {
                    return response.json();
                }).then(function (payload) {
                    suggestions.innerHTML = '';
                    (payload.data || []).forEach(function (city) {
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = city.municipality + ' - ' + city.state;
                        button.addEventListener('click', function () {
                            input.value = button.textContent;
                            app.querySelector('[data-si-city-name]').value = city.municipality;
                            app.querySelector('[data-si-city-state]').value = city.state;
                            if (city.hsp && app.querySelector('[name="hsp"]')) {
                                app.querySelector('[name="hsp"]').value = city.hsp;
                            }
                            var info = app.querySelector('[data-si-location-info]');
                            if (info) {
                                info.textContent = city.distributor
                                    ? 'Distribuidora identificada: ' + city.distributor + '. Tarifa estimada automaticamente no cálculo.'
                                    : 'Localização selecionada. Será usada a tarifa padrão configurada.';
                                info.hidden = false;
                            }
                            suggestions.hidden = true;
                        });
                        suggestions.appendChild(button);
                    });
                    suggestions.hidden = !suggestions.children.length;
                });
            }, 220);
        });
        document.addEventListener('click', function (event) {
            if (!suggestions.contains(event.target) && event.target !== input) {
                suggestions.hidden = true;
            }
        });
    }

    document.querySelectorAll('[data-si-calculator]').forEach(function (app) {
        setupCitySearch(app);
        var form = app.querySelector('[data-si-form]');
        var mode = form.querySelector('[data-si-mode]');
        var type = form.querySelector('[data-si-customer-type]');
        var billField = form.querySelector('[data-si-bill-field]');
        var kwhField = form.querySelector('[data-si-kwh-field]');
        var standard = form.querySelector('[data-si-standard-consumption]');
        var demand = form.querySelector('[data-si-demand-fields]');
        var results = app.querySelector('[data-si-results]');
        var message = form.querySelector('[data-si-message]');
        var quoteModal = app.querySelector('[data-si-quote-modal]');
        var quoteForm = app.querySelector('[data-si-quote-form]');
        var latestQuoteToken = '';

        function showQuoteStep(step) {
            quoteModal.querySelectorAll('[data-si-quote-step]').forEach(function (element) {
                element.hidden = element.getAttribute('data-si-quote-step') !== step;
            });
        }

        function openQuoteModal() {
            showQuoteStep('confirm');
            quoteModal.hidden = false;
            document.body.classList.add('si-modal-open');
        }

        function closeQuoteModal() {
            quoteModal.hidden = true;
            document.body.classList.remove('si-modal-open');
        }

        quoteModal.querySelectorAll('[data-si-quote-close]').forEach(function (button) {
            button.addEventListener('click', closeQuoteModal);
        });
        quoteModal.querySelector('[data-si-quote-yes]').addEventListener('click', function () {
            quoteForm.querySelector('[data-si-quote-token]').value = latestQuoteToken;
            showQuoteStep('form');
            quoteForm.querySelector('[name="full_name"]').focus();
        });

        function refresh() {
            var isDemand = type.value === 'commercial_demand';
            demand.hidden = !isDemand;
            standard.hidden = isDemand;
            billField.hidden = mode.value !== 'bill';
            kwhField.hidden = mode.value === 'bill';
        }

        mode.addEventListener('change', refresh);
        type.addEventListener('change', refresh);
        refresh();

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            setBusy(form, true);
            message.textContent = '';

            request(form, 'si_calculate').then(function (payload) {
                if (!payload.success) {
                    throw new Error(payload.data && payload.data.message ? payload.data.message : 'Não foi possível calcular.');
                }
                Object.keys(payload.data).forEach(function (key) {
                    var output = results.querySelector('[data-si-result="' + key + '"]');
                    if (output) {
                        var value = payload.data[key];
                        output.textContent = value === null ? '-' : Number(value).toLocaleString('pt-BR', { maximumFractionDigits: 2 });
                    }
                });
                results.querySelectorAll('[data-si-currency]').forEach(function (output) {
                    var key = output.getAttribute('data-si-currency');
                    var decimals = 0;
                    if (key === 'estimated_price_per_wp' || key === 'estimated_public_lighting_fee') {
                        decimals = 2;
                    } else if (key === 'estimated_energy_tariff') {
                        decimals = 4;
                    }
                    output.textContent = Number(payload.data[key] || 0).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                });
                results.querySelectorAll('[data-si-text]').forEach(function (output) {
                    output.textContent = payload.data[output.getAttribute('data-si-text')] || '-';
                });
                var warnings = results.querySelector('[data-si-warnings]');
                warnings.innerHTML = '';
                (payload.data.warnings || []).forEach(function (warning) {
                    var paragraph = document.createElement('p');
                    paragraph.textContent = warning;
                    warnings.appendChild(paragraph);
                });
                warnings.hidden = !payload.data.warnings || !payload.data.warnings.length;
                results.hidden = false;
                results.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                latestQuoteToken = payload.data.quote_token || '';
                if (latestQuoteToken) {
                    window.setTimeout(openQuoteModal, 500);
                }
            }).catch(function (error) {
                message.textContent = error.message;
                message.className = 'si-message is-error';
            }).finally(function () {
                setBusy(form, false);
            });
        });

        quoteForm.addEventListener('submit', function (event) {
            event.preventDefault();
            setBusy(quoteForm, true);
            var quoteMessage = quoteForm.querySelector('[data-si-quote-message]');
            quoteMessage.textContent = '';

            request(quoteForm, 'si_request_quote').then(function (payload) {
                if (!payload.success) {
                    throw new Error(payload.data && payload.data.message ? payload.data.message : 'Não foi possível gerar o orçamento.');
                }
                quoteModal.querySelector('[data-si-quote-success-message]').textContent = payload.data.message;
                quoteModal.querySelector('[data-si-quote-url]').href = payload.data.quote_url;
                quoteModal.querySelector('[data-si-whatsapp-url]').href = payload.data.whatsapp_share_url;
                showQuoteStep('success');
            }).catch(function (error) {
                quoteMessage.textContent = error.message;
                quoteMessage.className = 'si-message is-error';
            }).finally(function () {
                setBusy(quoteForm, false);
            });
        });
    });

    document.querySelectorAll('[data-si-registration]').forEach(function (form) {
        var message = form.querySelector('[data-si-message]');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            setBusy(form, true);
            message.textContent = '';
            request(form, 'si_register_integrator').then(function (payload) {
                if (!payload.success) {
                    throw new Error(payload.data && payload.data.message ? payload.data.message : 'Não foi possível concluir o cadastro.');
                }
                message.textContent = payload.data.message;
                message.className = 'si-message is-success';
                window.setTimeout(function () { window.location.reload(); }, 700);
            }).catch(function (error) {
                message.textContent = error.message;
                message.className = 'si-message is-error';
            }).finally(function () {
                setBusy(form, false);
            });
        });
    });
}());
