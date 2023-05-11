@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="padding: 25px;">
                <form action='{{ route("$main.update", $obj->id) }}' enctype="multipart/form-data" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <input type="hidden" name="id" value="{{ $obj->id }}">
                        @foreach ($fieldsSetting as $name => $setting)
                        @if ($name != 'id' && (strpos($setting['type'],"system") == 0) )
                                @include('backend.form.edit', [
                                    'name' => $name,
                                    'setting' => $setting,
                                    'main' => $main,
                                    'obj' => $obj,
                                ])
                            @endif
                        @endforeach

                        <div class="col-xs-12 col-sm-12 col-md-12 ">
                            <button type="submit" class="btn btn-primary">{{ __('default.update') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
