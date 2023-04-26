@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/redeem/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.title') ?? '標題'}}</label>
                                    <input type="text" class="form-control" name="title" id="title" placeholder="" value="{{$model->title ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.code') ?? '兌換代碼'}}</label>
                                    <input type="text" class="form-control" name="code" id="code" placeholder="" value="{{$model->code ?? $code}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.content') ?? '公告內容'}}</label>
                                    <textarea class="form-control" name="content" id="content" placeholder="{{ trans('default.description_msg_def') ?? '請輸入描述'}}">{{$model->content ?? ''}}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.count') ?? '可以次數'}}</label>
                                    <input type="text" class="form-control" name="count" id="title" placeholder="" value="{{$model->count ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.start_time') ?? '上架時間'}}</label>
                                    <input type="text" class="form-control" name="start" placeholder="start" value="{{$model->start_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.end_time') ?? '下架時間'}}</label>
                                    <input type="text" class="form-control" name="end" placeholder="end" value="{{$model->end_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.category_name') ?? '分類名稱'}}</label>
                                    <select class="form-control form-control-lg" name="category_id">
                                        @foreach(trans('default.redeem.categories') as $key => $value)
                                            <option value="{{$key}}" @if($key == ($model->category_id ?? '')) selected @endif>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.redeem.status') ?? '狀態'}}</label>
                                    <select class="form-control form-control-lg" name="status">
                                        @foreach(trans('default.redeem.status_type') as $key => $value)
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
            $('input[name="start"]').daterangepicker({
                singleDatePicker: true,
                timePicker:true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-M-DD HH:mm:00'
                }
            });
            $('input[name="end"]').daterangepicker({
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
