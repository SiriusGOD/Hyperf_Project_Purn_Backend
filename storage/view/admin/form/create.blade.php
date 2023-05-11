<link
	rel="stylesheet"
	href="https://unpkg.com/jodit@3.23.2/build/jodit.es2018.min.css"
/>
<script src="https://unpkg.com/jodit@3.23.2/build/jodit.es2018.min.js"></script>


<div class="row mb-3">
    @if ($setting['type'] != 'system')
        <label for="example-text-input" class="col-sm-2 col-form-label">{{ __("$main.titles.$name") }}</label>
    @endif
    <div class="col-sm-10">
        @if ($setting['type'] == 'text' || $setting['type'] == 'number')
            <input type="{{ $setting['type'] }}" name="{{ $name }}" value='{{ old("$name") }}' class="form-control"
                placeholder='{{ __("$main.titles.$name") }}'>
        @elseif($setting['type'] == 'radio' && isset($setting['association']['bool']))
            @include('backend.components.radio_association')
        @elseif($setting['type'] == 'checkbox')
            <input type="{{ $setting['type'] }}" name="{{ $name }}" value="1"
                placeholder='{{ __("$main.titles.$name") }}'>
        @elseif($setting['type'] == 'textarea')
            {!! Form::textarea($name, null, ['class' => 'form-control']) !!}
        @elseif($setting['type'] == 'text|model')
            @include('backend.components.model')
        @elseif($setting['type'] == 'ckeditor')
            <textarea id="editor{{ $name }}" name="{{ $name }}" cols="200" rows="60" name="update_context"></textarea>
            <script>
              var editor = Jodit.make('#editor{{ $name }}', {
                              uploader: {
                                url: '/uploadimgs'
                },
                  height: "500px"});
              editor.value = '';
            </script>
        @elseif($setting['type'] == 'file')
            <input type="{{ $setting['type'] }}" name="{{ $name }}" value='{{ old("$name") }}'
                placeholder='{{ __("$main.titles.$name") }}'>
        @elseif($setting['type'] == 'files')
            @include('backend.components.files')
        @elseif($setting['type'] == 'select' && isset($setting['association']['bool']))
            @include('backend.components.select')
            @if (isset($setting['show_jquery']))
                <div class="{{ $name }}"></div>

                @section('js2')
                    <script type="text/javascript">
                        $("document").ready(function() {
                            $(".{{ $name }}").html('');
                            $("select[name='business_design_id']").bind('change', function() {
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
        @elseif($setting['type'] == 'number_by_select')
            {!! Form::select($name, [1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9], null, ['class' => 'form-control']) !!}
        @elseif($setting['type'] == 'select' && isset($setting['isData']))
            {!! Form::select($name, $setting['data'], null, ['class' => 'form-control']) !!}
        @elseif($setting['type'] == 'self')
        @foreach (allSems() as $row)
        <label  style="margin-right:20px;">
            <input type="checkbox" class="ckbox" value="{{ $row->id }}">{{ $row->title }}
        </label>
    @endforeach

    <div class="form-show"> </div>

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
        @elseif(strpos($setting['type'], 'area') > 1)
            <div class="{{ $name }}"></div>
            <span class="{{ $name }}_2"></span>
        @elseif($setting['type'] == 'system')
            @if ($name === 'user_id')
                {!! Form::hidden($name, auth()->user()->id) !!}
            @else
                {!! Form::hidden($name, auth()->user()->$name) !!}
            @endif

        @endif
    </div>
</div>
