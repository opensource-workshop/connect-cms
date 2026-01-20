{{--
 * FAQリンクコピー用共通スクリプト
 * @category FAQプラグイン
--}}
@once
    <script>
        if (typeof window.connectFaqCopyLink !== 'function') {
            window.connectFaqCopyLink = function (button, messageId) {
                var url = button.getAttribute('data-url');
                var messageElement = document.getElementById(messageId);

                if (!url) {
                    return;
                }

                var showMessage = function (text, isError) {
                    // スクリーンリーダー向けにメッセージを書き換え
                    if (messageElement) {
                        messageElement.textContent = text;
                        messageElement.style.display = 'none';
                    }

                    if (typeof window.connectFaqShowToast === 'function') {
                        window.connectFaqShowToast(text, isError);
                    }
                };

                var copyWithTextarea = function () {
                    var textarea = document.createElement('textarea');
                    textarea.value = url;
                    textarea.style.position = 'fixed';
                    textarea.style.top = '-100px';
                    document.body.appendChild(textarea);
                    textarea.focus();
                    textarea.select();

                    try {
                        var successful = document.execCommand('copy');
                        showMessage(successful ? 'リンクをコピーしました' : 'コピーできませんでした', !successful);
                    } catch (err) {
                        showMessage('コピーできませんでした', true);
                    }

                    document.body.removeChild(textarea);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url)
                        .then(function () {
                            showMessage('リンクをコピーしました', false);
                        })
                        .catch(function () {
                            copyWithTextarea();
                        });
                } else {
                    copyWithTextarea();
                }
            };
        }

        if (typeof window.connectFaqShowToast !== 'function') {
            window.connectFaqShowToast = function (text, isError) {
                // Bootstrap4 toast が使える場合は利用
                if (window.jQuery && typeof jQuery.fn.toast === 'function') {
                    var containerId = 'connect-faq-toast-container';
                    var container = document.getElementById(containerId);
                    if (!container) {
                        container = document.createElement('div');
                        container.id = containerId;
                        container.className = 'position-fixed';
                        container.style.top = '1rem';
                        container.style.right = '1rem';
                        container.style.zIndex = '1080';
                        container.style.pointerEvents = 'none';
                        document.body.appendChild(container);
                    }

                    var toast = document.createElement('div');
                    toast.className = 'toast text-white ' + (isError ? 'bg-danger' : 'bg-success') + ' border-0';
                    toast.setAttribute('role', 'status');
                    toast.setAttribute('aria-live', 'polite');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.setAttribute('data-delay', '2000');
                    toast.style.minWidth = '240px';
                    toast.style.pointerEvents = 'auto';

                    var toastBody = document.createElement('div');
                    toastBody.className = 'toast-body';
                    toastBody.textContent = text;
                    toast.appendChild(toastBody);

                    container.appendChild(toast);

                    jQuery(toast).toast({delay: 2000});
                    jQuery(toast).on('hidden.bs.toast', function () {
                        if (toast.parentNode === container) {
                            container.removeChild(toast);
                        }
                        if (container.childNodes.length === 0 && container.parentNode) {
                            container.parentNode.removeChild(container);
                        }
                    });
                    jQuery(toast).toast('show');
                    return;
                }

                // フォールバック（自前トースト）
                var fallbackContainerId = 'connect-faq-toast-container';
                var fallbackContainer = document.getElementById(fallbackContainerId);
                if (!fallbackContainer) {
                    fallbackContainer = document.createElement('div');
                    fallbackContainer.id = fallbackContainerId;
                    fallbackContainer.style.position = 'fixed';
                    fallbackContainer.style.top = '1rem';
                    fallbackContainer.style.right = '1rem';
                    fallbackContainer.style.zIndex = '1080';
                    fallbackContainer.style.pointerEvents = 'none';
                    fallbackContainer.style.minWidth = '240px';
                    document.body.appendChild(fallbackContainer);
                }

                var box = document.createElement('div');
                box.className = 'shadow-sm mb-2 rounded text-white';
                box.style.backgroundColor = isError ? '#dc3545' : '#28a745';
                box.style.padding = '0.75rem 1rem';
                box.style.opacity = '0.95';
                box.style.pointerEvents = 'auto';
                box.textContent = text;

                fallbackContainer.appendChild(box);

                setTimeout(function () {
                    box.style.opacity = '0';
                }, 2000);

                setTimeout(function () {
                    if (box.parentNode === fallbackContainer) {
                        fallbackContainer.removeChild(box);
                    }
                    if (fallbackContainer.childNodes.length === 0 && fallbackContainer.parentNode) {
                        fallbackContainer.parentNode.removeChild(fallbackContainer);
                    }
                }, 2600);
            };
        }

        if (typeof window.connectFaqTooltipInitialized === 'undefined') {
            window.connectFaqTooltipInitialized = true;
            if (window.jQuery && typeof jQuery.fn.tooltip === 'function') {
                jQuery(function () {
                    jQuery('[data-toggle="tooltip"]').tooltip();
                });
            }
        }
    </script>
@endonce
