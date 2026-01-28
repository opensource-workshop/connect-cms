/**
 * 一覧テーブルの横スクロール支援（上部スクロールバー＆固定ヘッダー複製）を行うモジュール。
 * 対象: `.js-cc-table-scroll` を持つコンテナ。
 */
document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.js-cc-table-scroll');
    if (!containers.length) {
        return;
    }

    // 1コンテナ分のヘッダー複製・横スクロール同期・列幅/高さ同期を設定する。
    const setupContainer = (container) => {
        const sticky = container.querySelector('.cc-table-scroll__sticky');
        const top = container.querySelector('.cc-table-scroll__top');
        const topInner = container.querySelector('.cc-table-scroll__top-inner');
        const header = container.querySelector('.cc-table-scroll__header');
        const body = container.querySelector('.cc-table-scroll__body');
        if (!top || !topInner || !body) {
            return;
        }

        const table = body.querySelector('table');
        const thead = table ? table.querySelector('thead') : null;
        let headerInner = null;
        let headerTable = null;

        const initializeHeaderTooltips = () => {
            if (!headerTable) {
                return;
            }
            const jquery = window.jQuery;
            if (!jquery || typeof jquery.fn.tooltip !== 'function') {
                return;
            }
            const targets = jquery(headerTable).find('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]');
            if (!targets.length) {
                return;
            }
            targets.tooltip();
        };

        // ヘッダーを複製して固定表示用のヘッダー領域を作る。
        const ensureHeaderClone = () => {
            if (!header || !table || !thead) {
                return;
            }
            headerInner = header.querySelector('.cc-table-scroll__header-inner');
            if (!headerInner) {
                headerInner = document.createElement('div');
                headerInner.className = 'cc-table-scroll__header-inner';
                header.appendChild(headerInner);
            }
            headerTable = headerInner.querySelector('table');
            if (!headerTable) {
                headerTable = document.createElement('table');
                headerInner.appendChild(headerTable);
            }
            headerTable.className = table.className;
            headerTable.classList.add('cc-table-scroll__header-table');
            Array.from(headerTable.classList).forEach((cls) => {
                if (/^m[trblxy]?-(?:n?\d+|auto)$/.test(cls)) {
                    headerTable.classList.remove(cls);
                }
            });
            headerTable.innerHTML = '';
            headerTable.appendChild(thead.cloneNode(true));
            header.setAttribute('aria-hidden', 'true');
            container.classList.add('is-cc-header-cloned');
            initializeHeaderTooltips();
        };

        // 列幅計測に使うセル配列を取得する（tbody先頭行→thead最終行の順）。
        const getColumnCells = () => {
            if (!table) {
                return [];
            }
            const firstBodyRow = table.tBodies && table.tBodies[0] ? table.tBodies[0].rows[0] : null;
            if (firstBodyRow && firstBodyRow.cells.length) {
                return Array.from(firstBodyRow.cells);
            }
            if (thead) {
                const headerRows = Array.from(thead.rows || []);
                const lastHeaderRow = headerRows[headerRows.length - 1];
                if (lastHeaderRow && lastHeaderRow.cells.length) {
                    return Array.from(lastHeaderRow.cells);
                }
            }
            return [];
        };

        // 本体テーブルの列幅をヘッダー複製テーブルに反映する。
        const updateHeaderColumns = () => {
            if (!headerTable || !body) {
                return;
            }
            const cells = getColumnCells();
            if (!cells.length) {
                return;
            }
            const widths = cells.map((cell) => Math.round(cell.getBoundingClientRect().width));
            const colgroup = document.createElement('colgroup');
            widths.forEach((width) => {
                const col = document.createElement('col');
                col.style.width = `${width}px`;
                colgroup.appendChild(col);
            });
            const existingColgroup = headerTable.querySelector('colgroup');
            if (existingColgroup) {
                existingColgroup.remove();
            }
            headerTable.insertBefore(colgroup, headerTable.firstChild);
            headerTable.style.width = `${body.scrollWidth}px`;
        };

        // ヘッダー高さを計測して余白補正用のCSS変数に反映する。
        const updateHeaderHeight = () => {
            if (headerTable) {
                const height = headerTable.getBoundingClientRect().height;
                if (height > 0) {
                    container.style.setProperty('--cc-table-header-height', `${height}px`);
                    return;
                }
            }
            if (!thead) {
                return;
            }
            const height = thead.getBoundingClientRect().height;
            container.style.setProperty('--cc-table-header-height', `${height}px`);
        };

        // 2段ヘッダー時のオフセット量を計測してCSS変数に反映する。
        const updateStickyOffset = () => {
            const stickyTable = container.querySelector('table.cc-table-sticky-header-2');
            if (!stickyTable) {
                return;
            }
            const headerRow = stickyTable.querySelector('thead tr:first-child');
            if (!headerRow) {
                return;
            }
            const height = headerRow.getBoundingClientRect().height;
            container.style.setProperty('--cc-table-sticky-offset', `${height}px`);
        };

        // 固定ヘッダー（navbar）の高さを取得し、stickyの開始位置を調整する。
        const updateViewportOffset = () => {
            if (!sticky) {
                return;
            }
            const stickyHeader = document.querySelector('.navbar.sticky-top');
            const offset = stickyHeader ? stickyHeader.getBoundingClientRect().height : 0;
            container.style.setProperty('--cc-table-sticky-viewport-offset', `${offset}px`);
        };

        // 上部スクロールバー幅とスクロール位置を同期する。
        const updateScrollWidth = () => {
            const scrollWidth = body.scrollWidth;
            topInner.style.width = `${scrollWidth}px`;
            container.classList.toggle('is-cc-scrollable', scrollWidth > body.clientWidth);
            top.scrollLeft = body.scrollLeft;
            if (headerInner) {
                headerInner.scrollLeft = body.scrollLeft;
            }
        };

        // スクロール同期での無限ループ防止用フラグ。
        let syncing = false;
        top.addEventListener('scroll', () => {
            if (syncing) {
                return;
            }
            syncing = true;
            body.scrollLeft = top.scrollLeft;
            if (headerInner) {
                headerInner.scrollLeft = top.scrollLeft;
            }
            requestAnimationFrame(() => {
                syncing = false;
            });
        });

        // 本体スクロール→上部スクロール/ヘッダーへ同期。
        body.addEventListener('scroll', () => {
            if (syncing) {
                return;
            }
            syncing = true;
            top.scrollLeft = body.scrollLeft;
            if (headerInner) {
                headerInner.scrollLeft = body.scrollLeft;
            }
            requestAnimationFrame(() => {
                syncing = false;
            });
        });

        // 計測と同期処理をまとめて実行する。
        const updateAll = () => {
            ensureHeaderClone();
            updateHeaderColumns();
            updateHeaderHeight();
            updateScrollWidth();
            updateStickyOffset();
            updateViewportOffset();
        };

        updateAll();

        // サイズ変化に追随して列幅/高さを再計算する。
        if (typeof ResizeObserver !== 'undefined') {
            const resizeObserver = new ResizeObserver(() => {
                updateAll();
            });
            resizeObserver.observe(body);
            const resizeTable = body.querySelector('table');
            if (resizeTable) {
                resizeObserver.observe(resizeTable);
            }
            const resizeThead = body.querySelector('thead');
            if (resizeThead) {
                resizeObserver.observe(resizeThead);
            }
        }

        // 画面リサイズ時も再計算する。
        window.addEventListener('resize', updateAll);
    };

    containers.forEach((container) => {
        setupContainer(container);
    });
});
