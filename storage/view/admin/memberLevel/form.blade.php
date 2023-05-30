@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/member_level/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.type') ?? '類型'}}</label>
                                    <select class="form-control form-control-lg" name="type">
                                        <option value="{{\App\Model\MemberLevel::TYPE_LIST[0]}}" {{($model->type ?? '') == \App\Model\MemberLevel::TYPE_LIST[0] ? 'selected' : ''}}>
                                            {{\App\Model\MemberLevel::TYPE_NAME['vip']}}
                                        </option>
                                        <option value="{{\App\Model\MemberLevel::TYPE_LIST[1]}}" {{($model->type ?? '') == \App\Model\MemberLevel::TYPE_LIST[1] ? 'selected' : ''}}>
                                            {{\App\Model\MemberLevel::TYPE_NAME['diamond']}}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="" value="{{$model->name ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.member_level_control.member_level_title') ?? '會員卡資訊'}}</label>
                                    <textarea id="title" name="title" class="form-control" value="{{$model->title ?? ''}}">{{$model->title ?? ''}}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.member_level_control.member_level_description') ?? '會員卡描述'}}</label>
                                    <input type="text" class="form-control" name="description" id="description" placeholder="" value="{{$model->description ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.member_level_control.member_level_remark') ?? '會員卡備註'}}</label>
                                    <input type="text" class="form-control" name="remark" id="remark" placeholder="" value="{{$model->remark ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.member_level_control.member_level_duration') ?? '持續天數'}}</label>
                                    <input type="text" class="form-control" name="duration" id="duration" placeholder="" value="{{$model->duration ?? ''}}">
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