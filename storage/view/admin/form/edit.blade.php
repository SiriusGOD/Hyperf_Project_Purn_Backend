<link
	rel="stylesheet"
	href="https://unpkg.com/jodit@3.23.2/build/jodit.es2018.min.css"
/>
<script src="https://unpkg.com/jodit@3.23.2/build/jodit.es2018.min.js"></script>


<div class="row mb-3"            @if(isset($setting['isHidden']) )  style="display:none;"  @endif>
    @if ($setting['type'] != 'system')
        <label for="example-text-input" class="col-sm-2 col-form-label" >{{ __("$main.titles.$name") }}</label>
    @endif

    <div class="col-sm-10">
        @if ($setting['type'] == 'text' || $setting['type'] == 'number')
        @if(isset($setting['isHidden']) )
            <input type="hidden" name="{{ $name }}" value='{{ $obj->$name }}' class="form-control"
                placeholder='{{ __("$main.titles.$name") }}'>
        @else
            <input type="{{ $setting['type'] }}" name="{{ $name }}" value='{{ $obj->$name }}' class="form-control"
                placeholder='{{ __("$main.titles.$name") }}'>
        @endif
        @elseif($setting['type'] == 'checkbox')
            <input type="{{ $setting['type'] }}" name="{{ $name }}" value='1'
                placeholder='{{ __("$main.titles.$name") }}' @if ($obj->$name == 1) checked="checked" @endif>
        @elseif($setting['type'] == 'textarea')
            {!! Form::textarea($name, $obj->$name, ['class' => 'form-control']) !!}
        @elseif($setting['type'] == 'text|model')
            @include('backend.components.model')
        @elseif($setting['type'] == 'ckeditor')
            <textarea id="editor{{ $name }}" name="{{ $name }}" cols="200" rows="60" name="update_context">{{ $obj->$name }}</textarea>
            <script>
              var editor = Jodit.make('#editor{{ $name }}', {
                              uploader: {
                                url: '/uploadimgs'
                },
                  height: "500px"});
            </script>
        @elseif($setting['type'] == 'file')
            @include('backend.components.file')
        @elseif($setting['type'] == 'files')
            @include('backend.components.files')
        @elseif($setting['type'] == 'select' && isset($setting['association']['bool']))
            {!! Form::select($name, modelsAssociation($setting['association']), $obj->$name, ['class' => 'form-control']) !!}
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
                                    url: '{{ route('getBusinessDesign') }}',
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
        @elseif($setting['type'] == 'select' && isset($setting['isData']))
            {!! Form::select($name, $setting['data'], $obj->$name, ['class' => 'form-control']) !!}
        @elseif($setting['type'] == 'number_by_select')
            {!! Form::select($name, [1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9], $obj->$name, ['class' => 'form-control']) !!}
        @elseif($setting['type'] == 'self')
            @foreach (allSems() as $row)
                <label  style="margin-right:20px;">
                    <input type="checkbox" class="ckbox" value="{{ $row->id }}">{{ $row->title }}
                </label>
            @endforeach

            <div class="form-show">{!!getChkBoxs($obj->reftext)!!}</div>

            @section('js2')
                <script type="text/javascript">
                    $("document").ready(function() {
                        $(".ckbox").bind('click', function() {
                            let empty = '';
                            $(".ckbox").each(function(){
                                if($(this).is(":checked")){
                                    empty+='_'+$(this).val()
                                }
                            });
                            getSems(empty);
                        });
                    });

                    function getSems(empty) {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('getSems') }}',
                            data: {
                                "_token": "{{ csrf_token() }}",
                                "val": empty
                            },
                            success: function(data) {
                                $(".form-show").html(data)
                            }
                        });
                    }
                </script>
            @endsection
        @elseif($setting['type'] == 'radio|area')
            <div class="{{ $name }}">
                <?php $models = getRadios($setting, $obj); ?>
                @foreach ($models as $mData)
                    <label style="margin-right:10px;">
                        {!! Form::radio($name, $mData->id, $obj->$name == $mData->id) !!}
                        {{ $mData->title }}
                    </label>
                @endforeach
            </div>

            @if ($name == 'business_design_id')
                <span class="{{ $name }}_2"></span>
                <div>{!! $business_design !!}</div>
            @endif
        @elseif($setting['type'] == 'system')
            @if ($name === 'user_id')
                {!! Form::hidden($name, auth()->user()->id) !!}
            @else
                {!! Form::hidden($name, auth()->user()->$name) !!}
            @endif

        @endif
    </div>
</div>
