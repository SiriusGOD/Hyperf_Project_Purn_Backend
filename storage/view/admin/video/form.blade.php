@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/video/store" method="post" class="col-md-12">

                                @if($video->id)
                                    <input type="hidden" name="id" value="{{$video->id}}">
                                @endif

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.title') ?? '影片名稱'}}</label>
                                    <input type="text" class="form-control" name="title" id="title"
                                           placeholder="{{trans('default.video.title') ?? 'title'}}"
                                           value="{{$video->title}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.fan_id') ?? '番号'}}</label>
                                    <input type="text" class="form-control" name="fan_id" id="fan_id"
                                           placeholder="{{trans('default.video.fan_id') ?? 'title'}}"
                                           value="{{$video->fan_id}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.m3u8') ?? 'M3U8'}}</label>
                                    <input type="text" class="form-control" name="m3u8" id="m3u8"
                                           disabled="true" 
                                           placeholder="{{trans('default.video.m3u8') ?? 'm3u8'}}"
                                           value="{{$video->m3u8}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.video.category') ?? '分類'}}</label>
                                    <select class="form-control form-control-lg" name="category">
                                        @foreach($const::CATEGORY as $key => $value)
                                            <option value="{{$key}}" @if($key == $video->category) selected=true @endif >{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit"
                                        class="btn btn-primary">{{trans('default.submit') ?? '送出'}}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->


        </div>
        <!-- /.col -->
    </div>

@endsection
