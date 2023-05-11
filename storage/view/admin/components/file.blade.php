@if(!file_exists(public_path()."/". $obj->$name))
    <input type="hidden" name="{{$name}}" value='{{$obj->$name}}' placeholder='{{__("$main.titles.$name")}}'>
    <img src="/storage/{{$obj->$name}}" style="width:120px;">
        <i class="ri-delete-bin-line delete_img"  id="{{$name}}" data-path="public/{{$obj->$name}}"></i>
@else

    <input type="{{$setting['type']}}" name="{{$name}}" value='' placeholder='{{__("$main.titles.$name")}}'>
@endif
@section('js')
<script type="text/javascript">
    $("document").ready(function() {
        $(".delete_img").bind('click', function() {
            var yes = confirm('{{__("default.confirm_delete")}}');
            if (yes) {
                $.ajax({
                    type: 'POST',
                    url: '{{route("destroy_image")}}',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "name": $(this).attr('id'),
                        "id": '{{ isset($setting['mainIsKey']) ? $obj->key : $obj->id}}',
                        "type":'{{ isset($setting['mainIsKey']) ? 'key' : 'id'}}',
                        "main": '{{traTable($main)}}',
                        "path": $(this).data('path')
                    },
                    success: function(data) {
                        if (parseInt(data) == 1) {
                            alert('{{__("default.deleted_successfully")}}');
                            window.location.reload();
                        }
                    }
                });
            }
        });

    })
</script>
@endsection
