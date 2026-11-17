'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const preview = document.querySelector('[data-preview-device]');
    if (!preview) {
        return;
    }

    const stage = preview;
    const canvas = preview.querySelector('.cc-preview-canvas');
    const frame = preview.querySelector('.cc-preview-frame');
    const sizeLabel = document.querySelector('.cc-preview-size-label');
    const deviceButtons = Array.from(document.querySelectorAll('[data-preview-device-option]'));
    const scaleButtons = Array.from(document.querySelectorAll('[data-preview-scale-option]'));
    let selectedDevice = preview.dataset.selectedDevice || 'current';
    let fitToScreen = true;
    let resizeFrame = null;
    let stageResizeObserver = null;

    /** 現在の画面または固定プリセットの論理寸法を返す。 */
    function getDimensions() {
        const selectedButton = deviceButtons.find(function (button) {
            return button.dataset.previewDeviceOption === selectedDevice;
        });

        if (!selectedButton || selectedDevice === 'current') {
            return {
                width: document.documentElement.clientWidth,
                height: window.innerHeight,
                label: selectedButton ? selectedButton.dataset.previewDeviceLabel : '現在の画面',
            };
        }

        return {
            width: Number(selectedButton.dataset.previewWidth),
            height: Number(selectedButton.dataset.previewHeight),
            label: selectedButton.dataset.previewDeviceLabel,
        };
    }

    /** 選択状態をURLへ反映し、再読み込み時に固定プリセットを復元できるようにする。 */
    function updateUrl() {
        const url = new URL(window.location.href);
        if (selectedDevice === 'current') {
            url.searchParams.delete('preview_device');
        } else {
            url.searchParams.set('preview_device', selectedDevice);
        }
        window.history.replaceState({}, '', url.toString());
    }

    /** ボタンの見た目と支援技術向けの選択状態を更新する。 */
    function updateButtons() {
        deviceButtons.forEach(function (button) {
            const selected = button.dataset.previewDeviceOption === selectedDevice;
            button.classList.toggle('btn-primary', selected);
            button.classList.toggle('btn-light', !selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });

        scaleButtons.forEach(function (button) {
            const selected = button.dataset.previewScaleOption === (fitToScreen ? 'fit' : 'actual');
            button.classList.toggle('btn-primary', selected);
            button.classList.toggle('btn-light', !selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
    }

    /** iframeの論理寸法を保ったまま、必要に応じて表示だけを縮小する。 */
    function applyPreviewSize(updateHistory) {
        const dimensions = getDimensions();
        const availableWidth = stage.clientWidth;
        const scale = fitToScreen ? Math.min(1, availableWidth / dimensions.width) : 1;

        frame.style.width = dimensions.width + 'px';
        frame.style.height = dimensions.height + 'px';
        frame.style.transform = 'scale(' + scale + ')';
        frame.title = dimensions.label + 'のページプレビュー';
        sizeLabel.textContent = dimensions.width + ' × ' + dimensions.height;

        canvas.style.width = Math.round(dimensions.width * scale) + 'px';
        canvas.style.height = Math.round(dimensions.height * scale) + 'px';

        updateButtons();
        preview.classList.add('cc-preview-stage--ready');

        if (updateHistory) {
            updateUrl();
        }
    }

    deviceButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            selectedDevice = button.dataset.previewDeviceOption;
            applyPreviewSize(true);
        });
    });

    scaleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            fitToScreen = button.dataset.previewScaleOption === 'fit';
            applyPreviewSize(false);
        });
    });

    window.addEventListener('resize', function () {
        if (resizeFrame !== null) {
            window.cancelAnimationFrame(resizeFrame);
        }
        resizeFrame = window.requestAnimationFrame(function () {
            applyPreviewSize(false);
            resizeFrame = null;
        });
    });

    if (window.ResizeObserver) {
        stageResizeObserver = new window.ResizeObserver(function () {
            applyPreviewSize(false);
        });
        stageResizeObserver.observe(stage);
    }

    applyPreviewSize(false);
});
