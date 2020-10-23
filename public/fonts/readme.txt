・Bootstrap 標準のapp.cssでは、glyphicons は public\fonts\vendor\bootstrap-sass\bootstrap に必要だった。
・Bootstrap 3.4.1 にしたことで、glyphicons のインクルード先が変わった。
・Bootstrap 4 を利用するようになり、public\fonts\vendor\bootstrap-sass\bootstrap\glyphicons-halflings-regular.svg, public\fonts\glyphicons-halflings-regular.svgは参照されなくなった。
　glyphicons-halflings-regular を 全ソースGrep検索 して public\css\app.css.Laravel標準 からしか参照されない事を確認したため、下記fontsを削除する。
　- public\fonts\vendor\bootstrap-sass\bootstrap\glyphicons-halflings-regular.*
　- public\fonts\glyphicons-halflings-regular.*
