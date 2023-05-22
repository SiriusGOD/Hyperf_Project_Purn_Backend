@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/drive_class/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.drive_class_control.drive_class_name') ?? '車群類別名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="{{ trans('default.name_msg_def') ?? '請輸入名稱'}}" value="{{$model->name ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.drive_class_control.drive_class_description') ?? '車群類別描述'}}</label>
                                    <input type="text" class="form-control" name="description" id="description" placeholder="{{ trans('default.description_msg_def') ?? '請輸入描述'}}" value="{{$model->description ?? ''}}">
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