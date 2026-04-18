@php
    $items = $items ?? [];
@endphp

@if(! empty($items))
<ul class="breadcrumbs workspace-breadcrumbs mb-2">
    @foreach($items as $item)
        <li>
            @if(! empty($item['url']) && ! $loop->last)
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @elseif(! empty($item['url']) && empty($item['active']))
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @else
                <span>{{ $item['label'] }}</span>
            @endif
        </li>
    @endforeach
</ul>
@else
<ul class="breadcrumbs float-left workspace-breadcrumbs mb-2">
    @php
        $route = request()->route();
        $routeArray = $route ? $route->getAction() : [];
        $controllerAction = $routeArray['controller'] ?? null;
        $controller = null;

        if ($controllerAction && str_contains($controllerAction, '@')) {
            [$controller] = explode('@', $controllerAction);
        }

        $segments = '';
        $requestSegments = Request::segments();
        $tenantSlug = app()->bound('tenant') ? app('tenant')->slug : null;
    @endphp

    @foreach($requestSegments as $segment)
        @if ($segment === 'dashboard' || ($tenantSlug && $segment === $tenantSlug))
            @php continue; @endphp
        @endif

        @php $segments .= '/'.$segment; @endphp

        @if(is_numeric($segment))
            @if ($controller && method_exists($controller, 'show'))
                @php $segment = 'View'; @endphp
            @else
                @php continue; @endphp
            @endif
        @endif

        @if(! ignoreRoutes($segments))
            @php continue; @endphp
        @endif

        @if(! $loop->last)
            <li>
                <a href="{{ url($segments) }}">{{ ucwords(str_replace('_',' ',$segment)) }}</a>
            </li>
        @else
            <li>
                <span>{{ ucwords(str_replace('_',' ',$segment)) }}</span>
            </li>
        @endif
    @endforeach
</ul>
@endif
