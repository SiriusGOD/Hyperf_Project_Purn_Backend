@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/announcement/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.announcement_control.announcement_title') ?? '公告標題'}}</label>
                                    <input type="text" class="form-control" name="title" id="title" placeholder="" value="{{$model->title ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.announcement_control.announcement_content') ?? '公告內容'}}</label>
                                    <textarea class="form-control" name="content" id="content" placeholder="{{ trans('default.description_msg_def') ?? '請輸入描述'}}">{{$model->content ?? ''}}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.announcement_control.announcement_start_time') ?? '公告上架時間'}}</label>
                                    <input type="text" class="form-control" name="start_time" placeholder="start_time" value="{{$model->start_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.announcement_control.announcement_end_time') ?? '公告下架時間'}}</label>
                                    <input type="text" class="form-control" name="end_time" placeholder="end_time" value="{{$model->end_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.announcement_control.announcement_status') ?? '公告狀態'}}</label>
                                    <select class="form-control form-control-lg" name="status">
                                        @foreach(trans('default.announcement_control.announcement_status_type') as $key => $value)
                                            <option value="{{$key}}" @if($key == ($model->status ?? '')) selected @endif>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">{{ trans('default.submit') ?? '送出'}}</button>
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

    <script>
        $(function() {
            $('input[name="start_time"]').daterangepicker({
                singleDatePicker: true,
                timePicker:true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-M-DD HH:mm:00'
                }
            });
            $('input[name="end_time"]').daterangepicker({
                singleDatePicker: true,
                timePicker:true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-M-DD HH:mm:00'
                }
            });
        });
    </script>

@endsection