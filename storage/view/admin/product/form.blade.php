@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/product/store" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <input type="hidden" name="product_id" value="{{$model->product_id ?? null}}">
                                <input type="hidden" name="correspond_id" value="{{$model->correspond_id ?? null}}">
                                <input type="hidden" name="product_type" value="{{$product_type ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.name') ?? '名稱'}}</label>
                                    <input type="text" class="form-control" name="product_name" id="product_name" value="@if(!empty($model->name)){{$model->name}}@elseif(!empty($model->title)){{$model->title}}@endif">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.product_control.product_type') ?? '商品類型'}}</label>
                                    <p>
                                    {{trans('default.product_control.product_type_name')[$product_type]}}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.product_control.product_currency') ?? '商品幣別'}}</label>
                                    <select class="form-control form-control-lg" name="product_currency">
                                        @if($currency == \App\Model\Product::CURRENCY[0])
                                            <option value="{{\App\Model\Product::CURRENCY[0]}}" {{($model->currency ?? '') == \App\Model\Product::CURRENCY[0] ? 'selected' : ''}}>
                                                {{\App\Model\Product::CURRENCY_NAME['CNY']}}
                                            </option>
                                            <option value="{{\App\Model\Product::CURRENCY[3]}}" {{($model->currency ?? '') == \App\Model\Product::CURRENCY[3] ? 'selected' : ''}}>
                                                {{\App\Model\Product::CURRENCY_NAME['TWD']}}
                                            </option>
                                        @elseif($currency == \App\Model\Product::CURRENCY[1])
                                            <option value="{{\App\Model\Product::CURRENCY[1]}}" {{($model->currency ?? '') == \App\Model\Product::CURRENCY[1] ? 'selected' : ''}}>
                                                {{\App\Model\Product::CURRENCY_NAME['COIN']}}
                                            </option>
                                        @endif
                                        <!-- <option value="{{\App\Model\Product::CURRENCY[0]}}" {{($model->currency ?? '') == \App\Model\Product::CURRENCY[0] ? 'selected' : ''}}>
                                            {{\App\Model\Product::CURRENCY_NAME['CNY']}}
                                        </option>
                                        <option value="{{\App\Model\Product::CURRENCY[1]}}" {{($model->currency ?? '') == \App\Model\Product::CURRENCY[1] ? 'selected' : ''}}>
                                            {{\App\Model\Product::CURRENCY_NAME['COIN']}}
                                        </option> -->
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.product_control.product_price') ?? '商品價格'}}</label>
                                    <input type="text" class="form-control" name="product_price" id="product_price" placeholder="" value="{{$model->selling_price ?? ''}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.start_time') ?? '開始時間'}}</label>
                                    <input type="text" class="form-control" name="start_time" placeholder="name" value="{{$model->start_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.end_time') ?? '結束時間'}}</label>
                                    <input type="text" class="form-control" name="end_time" id="end_time" placeholder="name" value="{{$model->end_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.take_up_down_info') ?? '上下架情況(任意時間均可下架，上架需在結束時間以前)'}}</label>
                                    <select class="form-control form-control-lg" name="expire">
                                        <option value="{{\App\Model\Product::EXPIRE['no']}}" {{$model->expire == \App\Model\Product::EXPIRE['no'] ? 'selected' : ''}}>
                                            {{trans('default.take_up') ?? '上架'}}
                                        </option>
                                        <option value="{{\App\Model\Product::EXPIRE['yes']}}" {{$model->expire == \App\Model\Product::EXPIRE['yes'] ? 'selected' : ''}}>
                                            {{trans('default.take_down') ?? '下架'}}
                                        </option>
                                    </select>
                                </div>
                                @if($product_type == 'member' || $product_type == 'points')
                                    @include('partial.paySelect')
                                @endif
                                <button type="submit" class="btn btn-primary">{{trans('default.submit') ?? '送出'}}</button>
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

        var selDiv = "";
        document.addEventListener("DOMContentLoaded", init, false);
        function init() {
            document.querySelector('#customFile').addEventListener('change', handleFileSelect, false);
            selDiv = document.querySelector("#selectedFiles");
        }
        function handleFileSelect(e) {
            var files = e.target.files;
            for(var i=0; i<files.length; i++) {
                var f = files[i];
                if(!f.type.match("image.*")) {
                    continue;
                }
                var reader = new FileReader();
                reader.onload = function (e) {
                    var html = "<img src=\"" + e.target.result + "\" style='width:100px;'  >" ;
                    selDiv.innerHTML = html;
                }
                $('#modelImage').hide();
                reader.readAsDataURL(f);
            }
        }
    </script>

@endsection