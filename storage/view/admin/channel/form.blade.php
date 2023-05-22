@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->

                 
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/channel/detail?id={{$model->id}}" method="get" class="col-md-12">
                                    <div class="form-row">
                                         
                                        <div class="form-group col-md-5">
                                            <label>{{trans('default.channels.duration') ?? '日期區間'}}</label>
                                            <input type="duration"  
                                             class="form-control" name="duration" id="daterange" aria-describedby="title" value="{{$start_duration ?? ''}}">
                                        </div>
                                         
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">{{trans('default.channels.search') ?? '搜尋'}}</button>
                                    </div>
                            </form>
                        </div>

                        <div class="row">
                             
                                 
                                @if(isset($calcs))
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.channels.total_amount') ?? '使用者統計'}}</label>
                                    <input type="text" class="form-control" name="account" id="account" value="{{$calcs['ach_total']}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.channels.register_count') ?? '使用者統計'}}</label>
                                    <input type="text" class="form-control" name="account" id="account" value="{{$calcs['register_total']}}">
                                </div>
                                @endif

                                 
                              
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
$(document).ready(function() {
  $('#daterange').daterangepicker({
    startDate: moment().startOf('day'),
    endDate: moment().endOf('day'),
    opens: 'left',
    // 其他选项和回调函数可以根据您的需求进行设置
    "locale": {
      "format": "YYYY-MM-DD HH:00:00", // 设置日期格式
      // 可以根据需要设置其他本地化选项
    },
    timePicker: true, // 启用时间选择器
    timePicker24Hour: true, // 使用24小时制显示时间
    timePickerIncrement: 1 // 时间间隔的分钟数
  });
});



    </script>

@endsection
