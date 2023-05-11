@extends('layouts.backend')



@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-right">
                <a class="btn btn-primary" href='{{ route("$main.index") }}'> Back</a>
            </div>
        </div>
    </div>



    <div class="row">
        @if ($main == 'businessDesigns')
            <?php print_r($fieldsSetting); ?>
        @endif


        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>

                @foreach (json_decode($obj->html_attrs) as $no => $attrs)
                    @foreach ($attrs as $type => $attr)
                        @include("backend.components.$type", ['attrs' => $attr])
                    @endforeach
                @endforeach

            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Details:</strong>
                {{ $obj->detail }}
            </div>
        </div>

    </div>
@endsection
