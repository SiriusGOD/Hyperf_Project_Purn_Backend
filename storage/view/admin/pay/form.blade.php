@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/pay/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="" value="{{$model->name ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.pay_control.pay_pronoun') ?? '代稱'}}</label>
                                    <input type="text" class="form-control" name="pronoun" id="pronoun" placeholder="" value="{{$model->pronoun ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.expire') ?? '狀態'}}</label>
                                    <select class="form-control form-control-lg" name="expire">
                                        <option value="{{\App\Model\Product::EXPIRE['no']}}" {{$model->expire == \App\Model\Product::EXPIRE['no'] ? 'selected' : ''}}>
                                            {{trans('default.pay_control.pay_open') ?? '開啟'}}
                                        </option>
                                        <option value="{{\App\Model\Product::EXPIRE['yes']}}" {{$model->expire == \App\Model\Product::EXPIRE['yes'] ? 'selected' : ''}}>
                                            {{trans('default.pay_control.pay_close') ?? '關閉'}}
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