@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/image/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.image_control.image_name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="title" id="title" placeholder="{{ trans('default.name_msg_def') ?? '請輸入名稱'}}" value="{{$model->title ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.image_profile_dec') ?? '圖片(不上傳就不更新，只接受圖片檔案(png jpeg gif))'}}</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="customFile" accept="image/png, image/gif, image/jpeg">
                                        <label class="custom-file-label" for="customFile">{{trans('default.choose_file') ?? '選擇檔案'}}</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.image_control.image_group_id') ?? '套圖id'}}</label>
                                    <input type="text" class="form-control" name="group_id" id="group_id" placeholder="{{ trans('default.id_msg_def') ?? '請輸入id'}}" value="{{$model->group_id ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.image_control.image_description') ?? '圖片描述'}}</label>
                                    <textarea class="form-control" name="description" id="description" placeholder="{{ trans('default.description_msg_def') ?? '請輸入描述'}}">{{$model->description ?? ''}}</textarea>
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