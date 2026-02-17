
@foreach($node['children'] as $child)

    @if($child['type'] === 'folder')
        <li>
            📁 <a href="{{ $child['url'] }}">
                {{ $child['name'] }}
            </a>
        </li>
    @else
        <li>
            📄 <a href="{{ $child['url'] }}" target="_blank">
                {{ $child['name'] }}
            </a>
        </li>
    @endif

@endforeach

