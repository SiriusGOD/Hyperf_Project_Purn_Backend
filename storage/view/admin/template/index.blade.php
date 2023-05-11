@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-10">
                            </div>
                            <div class="col-sm-12 col-md-2">
                                <div class="pull-left">
                                            <!-- {{ trans('default.create') }}
                                            {{ trans("default.$main.title") }} -->
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <table id="example2" class="table table-bordered table-hover dataTable dtr-inline"
                                       aria-describedby="example2_info">
                                <thead class="something">
                                    <tr>
                                        @foreach ($fieldsSetting as $title => $attrs)
                                            @if (!isset($attrs['index_show']))
                                                <th style="max-width: 50px;word-wrap: break-word; overflow-wrap: break-word;" >
                                                  {{trans("default.$main.$title")}}
                                                </th>
                                            @endif
                                        @endforeach
                                        <th>{{ __('default.option') }}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <? $i = 0; ?>
                                    @foreach ($objs as $obj)
                                        <tr @if ($i % 2 == 1) class="odd" @else class="even" @endif>

                                            @foreach ($fieldsSetting as $title => $attrs)
                                                @if (!isset($attrs['index_show']))
                                                    @if ($attrs['type'] == 'file')
                                                        <th>
                                                            <img src="/storage/{{ $obj->$title }}" class="avatar-md">
                                                        </th>
                                                    @elseif ($attrs['type'] == 'checkbox')
                                                        <th>
                                                          @if($obj->$title==1)
                                                            是
                                                          @else
                                                            否
                                                          @endif
                                                        </th>
                                                        @elseif(isset($attrs['association']))
                                                            <th>
                                                                {{ indexAssociaction($attrs['association'], $obj->$title) }}
                                                            </th>
                                                        @else

                                                    <th style="max-width: 150px;word-wrap: break-word !impoerant;"  >

                                                            @if ($title == 'crud')
                                                                {!! __("default.{$obj->$title}") !!}
                                                            @else
                                                                {{ $obj->$title }}
                                                            @endif

                                                            

                                                        </th>
                                                    @endif
                                                @endif
                                            @endforeach
                                            <td>
                                                <form action='route("$main.destroy", $obj->id) ' method="POST">

                                                    <a class="btn btn-sm btn-primary" href=' route("$main.edit", $obj->id) '>{{ __('default.edit') }}</a>


                                                    @if(authPermission("$main-create"))<button type="submit"
                                                            class="btn btn-sm  btn-danger">{{ __('default.delete') }}</button>
                                                    @endif

                                                </form>
                                            </td>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>
        </div> <!-- end col -->
    </div>
@endsection
