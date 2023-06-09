@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/actor_classification/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.sort') ?? '排序'}}</label>
                                    <input type="text" class="form-control" name="sort" id="sort" placeholder="{{ trans('default.actor_classification_control.classification_sort_def') ?? '請輸入排序號碼'}}" value="{{$model->sort ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.actor_classification_control.classification_name') ?? '分類名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="{{ trans('default.actor_classification_control.classification_name_def') ?? '請輸入分類名稱'}}" value="{{$model->name ?? ''}}">
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