@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/tag_group/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.tag_control.tag_name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="{{ trans('default.web_name_def') ?? '請輸入名稱'}}" value="{{$model->name ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.tag_group_control.tag_group_hide') ?? '是否隱藏'}}</label>
                                    <select class="form-control form-control-lg" name="is_hide">
                                        <option value="{{\App\Model\TagGroup::HIDE['not_hide']}}" {{($model->is_hide ?? '') == \App\Model\TagGroup::HIDE['not_hide'] ? 'selected' : ''}}>
                                            {{\App\Model\TagGroup::HIDE_LIST[0]}}
                                        </option>
                                        <option value="{{\App\Model\TagGroup::HIDE['hide']}}" {{($model->currency ?? '') == \App\Model\TagGroup::HIDE['hide'] ? 'selected' : ''}}>
                                            {{\App\Model\TagGroup::HIDE_LIST[1]}}
                                        </option>
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

@endsection