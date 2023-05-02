@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/navigation/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.navigation_control.navigation_name') ?? '名稱'}}</label>
                                    <input readonly type="text" class="form-control" name="name" id="name" placeholder="{{ trans('default.web_name_def') ?? '請輸入名稱'}}" value="{{$model->name ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.navigation_control.navigation_hot_order') ?? '排序'}}</label>
                                    <input type="text" class="form-control" name="hot_order" id="hot_order" placeholder="{{ trans('default.web_name_def') ?? '請輸入名稱'}}" value="{{$model->hot_order ?? 0}}">
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
@endsection