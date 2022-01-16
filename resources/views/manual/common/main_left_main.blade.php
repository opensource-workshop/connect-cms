@extends("manual.common.layout_base")

@section('section_main')
    <div class="container-fluid">
        <div class="row mt-3">

            <aside class="col-lg-2 order-1">
                <nav class="list-group d-none d-lg-block">
                    <div class="list-group">
                        @foreach($plugins->where('method_name', 'index') as $plugin)
                            <a href="{{$base_path}}{{$plugin->html_path}}"
                                class="list-group-item
                                    @if(isset($method) &&
                                        $method->plugin_name == $plugin->plugin_name &&
                                        $method->method_name == $plugin->method_name)
                                        active
                                    @endif
                            ">
                                {{$plugin->plugin_title}}
                                @if ($plugin->hasChildren() && count($plugin->children) > 0)
                                    <i class="fas fa-plus"></i>
                                @endif
                            </a>
                            @if ($plugin->hasChildren() && (isset($method) && $method->plugin_name == $plugin->plugin_name))
                                @foreach($plugin->children as $children)
                                    <a href="{{$base_path}}{{$children->html_path}}"
                                        class="list-group-item
                                            @if(isset($method) &&
                                                $method->plugin_name == $children->plugin_name &&
                                                $method->method_name == $children->method_name)
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
