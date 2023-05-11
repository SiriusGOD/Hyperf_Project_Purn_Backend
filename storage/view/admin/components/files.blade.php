 <input type="file" name="{{ $name }}[]" multiple>

 @if (isset($obj->id))
     <div class="zoom-gallery">
         @if (show_pics($setting, $obj))
             @foreach (show_pics($setting, $obj) as $k => $img)
                 <div>
                     <a class="img-del" href="#del" data-id="{{ $k }}">
                         <i class="ri-delete-bin-line"></i>
                     </a>
                     <img src="{{ $img }}" alt="img-{{ $k }}" width="120">
                 </div>
             @endforeach
         @endif
     </div>
 @endif


 @section('js')
     <script type="text/javascript">
         $("document").ready(function() {
             $(".img-del").bind('click', function() {

                 var yes = confirm('{{ __('default.confirm_delete') }}');
                 if (yes) {
                     $.ajax({
                         type: 'POST',
                         url: '{{ route('delimage') }}',
                         data: {
                             "_token": "{{ csrf_token() }}",
                             "id": $(this).data('id'),
                             "main": '{{ $main }}'
                         },
                         success: function(data) {
                             if (parseInt(data) == 1) {
                                 alert('{{ __('default.deleted_successfully') }}');
                                 window.location.reload();
                             } else {
                                 return false;
                             }
                         }
                     });
                 }
             });

         })
     </script>
 @endsection

 @section('css')
     <style>
         .zoom-gallery {
             display: flex;
         }
     </style>
 @endsection
