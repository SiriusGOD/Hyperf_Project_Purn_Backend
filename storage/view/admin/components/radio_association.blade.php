<?php
$forms = htmlCheckBoxAssociation($setting['association'], $name);
?>

@foreach ($forms as $row)
    <label style="margin-right:10px;">
        {!! Form::radio("{$name}", $row['id'], isset($obj) ? $obj->$name == $row['id'] : null, [
            'class' => $name,
            'id' => $name,
        ]) !!}
        {{ $row['title'] }}
    </label>
@endforeach


@section('businesses_js')
    <script type="text/javascript">
        $("document").ready(function() {
            $(".{{ $name }}").bind('click', function() {
                $(".business_design_id_2").next().html("");
                $(".business_design_id_2").html("");
                $(".aj_business_design_id").html("");
                $(".business_design_id").html("");
                 
                if ($(this).prop('checked')) {
                    $(".{{ $setting['association']['jquery_id'] }}").html("")
                    let clickVal = $(this).val();
                    $(".{{ $name }}").each(function() {
                        if ($(this).val() !== clickVal) {
                            $(this).prop("checked", false);
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        url: '{{ isset($isFrontend) ? route("{$setting['association']['frontendRroute']}") : route("{$setting['association']['route']}") }}',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "value": $(this).val(),
                            "association": '{{ $setting['association']['hasOne'] }}',
                            'name': '{{ $setting['association']['jquery_id'] }}',
                        },
                        success: function(data) {
                            if (data) {
                                
                                $(".{{ $setting['association']['jquery_id'] }}").html(data);

                                $("input.business_category_2_id").bind("click", function() {
                                    ///------------------------------- 
                                    $(".business_design_id_2").html("");
                                    $.ajax({
                                        type: 'POST',
                                        url: '{{ isset($isFrontend) ? route("ftontBusCate2Form") : route("getBusinessCate2Form") }}',
                                        data: {
                                            "_token": "{{ csrf_token() }}",
                                            "busCate2": $(this).val()
                                        },
                                        success: function(data) {
                                            $(".business_design_id").html(data);
                                            $(".business_design_id_2").html("");
                                            $("input.aj_business_design_id").bind("click", function() {
                                                $.ajax({
                                                    type: 'POST',
                                                    url: '{{ isset($isFrontend) ? route("frontendBusDesign") : route("getBusinessDesign") }}',
                                                    data: {
                                                        "_token": "{{ csrf_token() }}",
                                                        "id": $(this).val()
                                                    },
                                                    success: function(data) {   
                                                        console.log(data);                                                     
                                                        $(".business_design_id_2").html(data);
                                                        
                                                    }
                                                });
                                            });
                                        }
                                    });

                                });
                            }
                        }
                    });
                }
            });

        })
    </script>
@endsection
