@extends("manual.common.layout_base")

@section('section_main')
    <div class="container-fluid">
        <div class="row mt-3">

            <aside class="col-lg-2 order-1">
                <nav class="list-group d-none d-lg-block">
                    <div class="list-group">
                        @foreach($methods->where('category', $current_method->category)->where('method_name', 'index') as $method)
                            <a href="{{$base_path}}{{$method->category}}/{{$method->plugin_name}}/index.html"
                                class="list-group-item
                                    @if(isset($current_method) &&
                                        $current_method->category == $method->category &&
                                        $current_method->plugin_name == $method->plugin_name &&
                                        $current_method->method_name == $method->method_name &&
                                        $level == 'plugin')
                                        active
                                    @endif
                            ">
                                {{$method->plugin_title}}
                            </a>
                            @if ($method->hasChildren() && (isset($current_method) && $current_method->plugin_name == $method->plugin_name) && ($level == 'plugin' || $level == 'method'))
                                {{-- index アクションは、Children ではなく、親になっているので、個別にリンク生成 --}}
                                <a href="{{$base_path}}{{$method->html_path}}"
                                    class="list-group-item
                                        @if(isset($current_method) &&
                                            $current_method->category == $method->category &&
                                            $current_method->plugin_name == $method->plugin_name &&
                                            $current_method->method_name == $method->method_name &&
                                            $level == 'method')
                                            active
                                        @endif
                                ">
                                    <i class="fas fa-chevron-right"></i>
                                    {{$method->method_title}}
                                </a>

                                {{-- プラグインの各アクション --}}
                                @foreach($method->children as $children)
                                    <a href="{{$base_path}}{{$children->html_path}}"
                                        class="list-group-item
                                            @if(isset($current_method) &&
                                                $current_method->category == $children->category &&
                                                $current_method->plugin_name == $children->plugin_name &&
                                                $current_method->method_name == $children->method_name &&
                                                $level == 'method')
                                                active
                                            @endif
                                    ">
                                        <i class="fas fa-chevron-right"></i>
                                        {{$children->method_title}}
                                    </a>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </nav>
            </aside>

            <main class="col-lg-10 order-2" role="main">
                @yield('content_main')
            </main>
        </div>
    </div>
@endsection
