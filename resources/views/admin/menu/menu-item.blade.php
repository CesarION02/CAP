@if ($item["submenu"] == [])
<li class="dd-item dd3-item" data-id="{{$item["id"]}}">
    <span class="dd-handle dd3-handle"></span>
    <div class="dd3-content {{$item["url"] == "javascript:;" ? "font-weight-bold" : ""}}" style="height: auto;">
        <a href="{{route("editar_menu", ['id' => $item["id"]])}}">{{$item["name"] . " | Url -> " . $item["url"]}} Icono -> <i style="font-size:20px;" class="{{isset($item["icono"]) ? (strlen($item["icono"]) > 0 ? "fa fa-fw ".$item["icono"] : "") : ""}}"></i></a>
        <a href="{{route('eliminar_menu', ['id' => $item["id"]])}}" class="eliminar-menu tooltipsC" title="Eliminar este menĂº"><i class="text-danger fa fa-trash-o" style="float: right;"></i></a>
    </div>
</li>
@else
<li class="dd-item dd3-item" data-id="{{$item["id"]}}">
    <span class="dd-handle dd3-handle"></span>
    <div class="dd3-content {{$item["url"] == "javascript:;" ? "font-weight-bold" : ""}}" style="height: auto;">
        <a href="{{route("editar_menu", ['id' => $item["id"]])}}">{{ $item["name"] . " | Url -> " . $item["url"]}} Icono -> <i style="font-size:20px;" class="{{isset($item["icono"]) ? (strlen($item["icono"]) > 0 ? "fa fa-fw ".$item["icono"] : "") : ""}}"></i></a>
        <a href="{{route('eliminar_menu', ['id' => $item["id"]])}}" class="eliminar-menu tooltipsC" title="Eliminar este menĂº"><i class="text-danger fa fa-trash-o" style="float: right;"></i></a>
    </div>
    <ol class="dd-list">
        @foreach ($item["submenu"] as $submenu)
        @include("admin.menu.menu-item",[ "item" => $submenu ])
        @endforeach
    </ol>
</li>
@endif