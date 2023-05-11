{!! Form::select($name, modelsAssociation($setting['association']), $checkName, ['class' => 'form-control']) !!}

@if (isset($setting['show_jquery']))
    <div class="{{ $name }}">{!! $business_design !!}</div>
    <p class="first">{!! $business_design !!}</p>
    @section('js2')
        <script type="text/javascript">
            $("document").ready(function() {

                $(".{{ $name }}").html('');
                $("select[name='business_design_id']").bind('change', function() {
                    $("p.first").html('');
                    $.ajax({
                        type: 'POST',
                        url: '{{ isset($isFrontend)?route("{$setting['association']['frontendRroute']}") : route('getBusinessDesign')  }}',                        
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "id": $(this).val()
                        },
                        success: function(data) {
                            $(".{{ $name }}").html(data);
                        }
                    });
                });

            });
        </script>
    @endsection
@endif
