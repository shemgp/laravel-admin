<li class="treeview">
    <a href="#">
        <i class="fa fa-gear"></i>
            <span>{{ $title ?? 'Menu' }}</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
    @if (trim(view('layouts.menu')) != '')
        <ul class="treeview-menu">
            @include('layouts.menu')
        </ul>
    @endif
</li>
