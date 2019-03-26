<?php

//namespace App\Plug\OswsRss;

class TestDb
{

	public function init()
	{

		// 返す変数
		$ret = "<p>【テスト】ページのデータベースの読み込み</p><p>";

		// Page データ
		$pages = \App\Page::get();

		// データループ
		foreach ( $pages as $page ) {
			$ret .= $page->page_name . "<br />";
		}

		return $ret . "</p>";
	}
}

