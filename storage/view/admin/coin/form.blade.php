@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/coin/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.type') ?? '類型'}}</label>
                                    <select class="form-control form-control-lg" name="type">
                                        <option value="{{\App\Model\Coin::TYPE_LIST[0]}}" {{($model->type ?? '') == \App\Model\Coin::TYPE_LIST[0] ? 'selected' : ''}}>
                                            {{\App\Model\Coin::TYPE_NAME['cash']}}
                                        </option>
                                        <option value="{{\App\Model\Coin::TYPE_LIST[1]}}" {{($model->type ?? '') == \App\Model\Coin::TYPE_LIST[1] ? 'selected' : ''}}>
                                            {{\App\Model\Coin::TYPE_NAME['diamond']}}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{ trans('default.name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="" value="{{$model->name ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.coin_control.points_msg') ?? '點數'}}</label>
                                    <input type="text" class="form-control" name="points" id="points" placeholder="" value="{{$model->points ?? 0}}">
                                </div>
                                @if(empty($model->type))
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.coin_control.bonus_msg') ?? '贈與點數'}}</label>
                                    <input type="text" class="form-control" name="bonus" id="bonus" placeholder="" value="{{$model->bonus ?? 0}}">
                                </div>
                                @elseif(!empty($model->type))
                                @if($model->type == \App\Model\Coin::TYPE_LIST[1])
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.coin_control.bonus_msg') ?? '贈與點數'}}</label>
                                    <input type="text" class="form-control" name="bonus" id="bonus" placeholder="" value="{{$model->bonus ?? 0}}">
                                </div>
                                @endif
                                @endif
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