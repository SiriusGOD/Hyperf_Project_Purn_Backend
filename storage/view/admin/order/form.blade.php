@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/order/changeStatus" method="post" class="col-md-12" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{$model->id ?? null}}">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.order_control.order_num') ?? '訂單編號'}}</label>
                                    <p>{{$model->order_number}}</p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.buyer') ?? '購買人'}}</label>
                                    <p>{{$model->name}}</p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.order_control.order_buyer_email') ?? 'email'}}</label>
                                    <p>{{$model->email}}</p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.order_control.order_buyer_telephone') ?? '手機'}}</label>
                                    <p>{{$model->telephone}}</p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.order_control.order_status') ?? '訂單狀態'}}</label>
                                    <select name="order_status" class="form-control form-control-lg">
                                        <option value="{{\App\Model\Order::ORDER_STATUS['create']}}" {{($model->status ?? '') == \App\Model\Order::ORDER_STATUS['create'] ? 'selected' : ''}}>
                                            {{trans('default.order_control.order_status_create') ?? '訂單成立'}}
                                        </option>
                                        <option value="{{\App\Model\Order::ORDER_STATUS['delete']}}" {{($model->status ?? '') == \App\Model\Order::ORDER_STATUS['delete'] ? 'selected' : ''}}>
                                            {{trans('default.order_control.order_status_delete') ?? '訂單取消'}}
                                        </option>
                                        <option value="{{\App\Model\Order::ORDER_STATUS['finish']}}" {{($model->status ?? '') == \App\Model\Order::ORDER_STATUS['finish'] ? 'selected' : ''}}>
                                            {{trans('default.order_control.order_status_finish') ?? '訂單完成'}}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.order_control.order_price') ?? '訂單金額'}}</label>
                                    <p>{{$model->total_price}} 元 ({{$model->currency}})</p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.order_control.order_create_time') ?? '訂單成立時間'}}</label>
                                    <p>{{$model->created_at}}</p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.order_control.order_details') ?? '訂單明細'}}</label>
                                    <table id="example2" class="table table-bordered table-hover dataTable dtr-inline"
                                       aria-describedby="example2_info">
                                        <thead>
                                            <th>商品名稱</th>
                                            <th>商品金額</th>
                                        </thead>
                                        <tbody>
                                        @foreach($model_details as $product)
                                            <tr class="odd">
                                                <td class="sorting_1 dtr-control">{{ $product->product_name}}</td>
                                                <td>{{ $product->product_selling_price}} 元 ({{$product->product_currency}})</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
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