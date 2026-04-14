(function () {
    // ページ一覧ごとに折り畳み状態を分けて保持する。
    const storageKey = 'connect.manage.page.treeState:' + window.location.pathname;
    // 初期描画時のちらつきを抑える一時スタイルの識別子。
    const preloadStyleId = 'connect-page-manage-tree-preload-style';
    let initialized = false;

    /**
     * sessionStorage から保存済みの折り畳み状態を取得する。
     *
     * @returns {string[]} 折り畳まれている親ページID一覧
     */
    function loadState() {
        try {
            const savedState = sessionStorage.getItem(storageKey);
            if (!savedState) {
                return [];
            }

            const parsedState = JSON.parse(savedState);
            if (!parsedState || !Array.isArray(parsedState.collapsed_ids)) {
                return [];
            }

            return parsedState.collapsed_ids.map(String);
        } catch (error) {
            return [];
        }
    }

    /**
     * ページ一覧テーブルのラッパー要素を取得する。
     *
     * @returns {Element|null} ツリー一覧のコンテナ
     */
    function getContainer() {
        return document.querySelector('[data-page-manage-tree]');
    }

    /**
     * 一覧内のページ行を配列で取得する。
     *
     * @param {Element|null} container ツリー一覧のコンテナ
     * @returns {HTMLElement[]} ページ行の配列
     */
    function getTreeRows(container) {
        if (!container) {
            return [];
        }

        return Array.from(container.querySelectorAll('tbody tr[data-page-id]'));
    }

    /**
     * 折り畳まれている親ページID一覧を sessionStorage に保存する。
     *
     * @param {string[]} collapsedIds 折り畳まれている親ページID一覧
     * @returns {void}
     */
    function persistCollapsedIds(collapsedIds) {
        try {
            if (!collapsedIds.length) {
                sessionStorage.removeItem(storageKey);
                return;
            }

            sessionStorage.setItem(storageKey, JSON.stringify({
                collapsed_ids: collapsedIds,
            }));
        } catch (error) {
            // sessionStorage が使えない環境では保持を諦める。
        }
    }

    /**
     * 初期描画前に挿入した一時スタイルを削除する。
     *
     * @returns {void}
     */
    function removePreloadStyle() {
        const preloadStyle = document.getElementById(preloadStyleId);
        if (preloadStyle) {
            preloadStyle.remove();
        }
    }

    /**
     * 保存済みの折り畳み状態に応じて、初期描画前の一時スタイルを適用する。
     *
     * @returns {void}
     */
    function applyPreloadStyle() {
        const collapsedIds = loadState();
        if (!collapsedIds.length) {
            removePreloadStyle();
            return;
        }

        // 保存済みの親IDを ancestor セレクタへ変換し、行描画時点から子孫を隠す。
        const selectors = collapsedIds.map(function (pageId) {
            return '[data-page-manage-tree] tbody tr[data-ancestor-ids~="' + pageId + '"]';
        });

        if (!selectors.length) {
            removePreloadStyle();
            return;
        }

        let preloadStyle = document.getElementById(preloadStyleId);
        if (!preloadStyle) {
            preloadStyle = document.createElement('style');
            preloadStyle.id = preloadStyleId;
            document.head.appendChild(preloadStyle);
        }

        preloadStyle.textContent = selectors.join(',\n') + ' { display: none; }';
    }

    /**
     * 現在のツリー表示状態を走査して sessionStorage に保存する。
     *
     * @returns {void}
     */
    function saveState() {
        const container = getContainer();
        const rows = getTreeRows(container);
        if (!rows.length) {
            return;
        }

        // 全展開を既定にして、閉じている親ページIDだけを保存する。
        const collapsedIds = rows
            .filter(function (row) {
                return row.dataset.hasChildren === '1' && row.dataset.treeExpanded === '0';
            })
            .map(function (row) {
                return row.dataset.pageId;
            });

        persistCollapsedIds(collapsedIds);
    }

    /**
     * 行の展開状態とトグルの aria 属性を更新する。
     *
     * @param {HTMLElement} row 対象の行
     * @param {boolean} expanded 展開状態
     * @returns {void}
     */
    function setExpandedState(row, expanded) {
        row.dataset.treeExpanded = expanded ? '1' : '0';

        const toggle = row.querySelector('[data-page-tree-toggle]');
        if (!toggle) {
            return;
        }

        const label = expanded ? '子ページを折り畳む' : '子ページを展開する';
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
    }

    /**
     * 祖先の展開状態をたどり、対象行を表示すべきか判定する。
     *
     * @param {HTMLElement} row 対象の行
     * @param {Map<string, HTMLElement>} rowMap ページIDと行要素の対応
     * @returns {boolean} 表示対象なら true
     */
    function isRowVisible(row, rowMap) {
        let parentId = row.dataset.parentId;

        while (parentId) {
            const parentRow = rowMap.get(parentId);
            if (!parentRow || parentRow.dataset.treeExpanded !== '1') {
                return false;
            }
            parentId = parentRow.dataset.parentId;
        }

        return true;
    }

    /**
     * すべての行に対して表示・非表示を再計算する。
     *
     * @param {HTMLElement[]} rows ページ行の配列
     * @param {Map<string, HTMLElement>} rowMap ページIDと行要素の対応
     * @returns {void}
     */
    function updateVisibility(rows, rowMap) {
        rows.forEach(function (row) {
            row.hidden = !isRowVisible(row, rowMap);
        });
    }

    /**
     * ページ一覧の折り畳み UI を初期化し、保存済み状態を復元する。
     *
     * @returns {void}
     */
    function init() {
        if (initialized) {
            return;
        }

        const container = getContainer();
        const rows = getTreeRows(container);
        if (!rows.length) {
            return;
        }

        initialized = true;

        const rowMap = new Map(rows.map(function (row) {
            return [row.dataset.pageId, row];
        }));
        const treeRows = rows.filter(function (row) {
            return row.dataset.hasChildren === '1';
        });
        const savedCollapsedIds = loadState();

        // 先にイベントを張ってから、保存済みの親だけ閉じた状態へ戻す。
        treeRows.forEach(function (row) {
            setExpandedState(row, true);

            const toggle = row.querySelector('[data-page-tree-toggle]');
            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', function () {
                const expanded = row.dataset.treeExpanded === '1';
                setExpandedState(row, !expanded);
                updateVisibility(rows, rowMap);
                saveState();
            });
        });

        const validCollapsedIds = [];
        savedCollapsedIds.forEach(function (pageId) {
            const row = rowMap.get(pageId);
            if (!row || row.dataset.hasChildren !== '1') {
                return;
            }

            setExpandedState(row, false);
            validCollapsedIds.push(pageId);
        });

        updateVisibility(rows, rowMap);
        persistCollapsedIds(validCollapsedIds);
        removePreloadStyle();
    }

    applyPreloadStyle();

    window.connectPageManageTree = {
        init: init,
        saveState: saveState,
    };
}());
