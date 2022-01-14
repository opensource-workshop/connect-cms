@extends("manual.common.layout_base")

@section('section_main')
    <div class="container-fluid">
        <div class="row mt-3">

            <aside class="col-lg-2 order-1">
                <nav class="list-group d-none d-lg-block">
                    <div class="list-group">
                        @foreach($plugins->where('method_name', 'index') as $plugin)
                            <a href="{{$base_path}}{{$plugin->html_path}}" class="list-group-item">{{$plugin->plugin_title}}</a>
                            @if ($plugin->hasChildren())
                                @foreach($plugin->children as $children)
                                    <a href="{{$base_path}}{{$children->html_path}}" class="list-group-item"><i class="fas fa-chevron-right"></i> {{$children->method_title}}</a>
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
