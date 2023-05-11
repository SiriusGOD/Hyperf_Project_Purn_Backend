@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="padding: 25px;">
                <form action='{{ route("$main.store") }}' enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="row">
                        @foreach ($fieldsSetting as $name => $setting)
                                                    
                            @if ($name != 'id' && (strpos($setting['type'],"system") == 0) )
                                @include('backend.form.create', [
                                    'name' => $name,
                                    'setting' => $setting,
                                    'main' => $main,
                                ])
                            @endif
                        @endforeach

                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <button type="submit" class="btn btn-primary">{{ __('default.submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
