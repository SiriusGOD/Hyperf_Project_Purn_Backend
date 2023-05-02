@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <div class="col-sm-12 col-md-10 mb-1">
                                <form action="/admin/product/multipleEdit" method="get">
                                    <label for="exampleInputEmail1">{{trans('default.product_control.product_choose_type') ?? '選擇商品類型'}}</label>
                                    <select  class="form-control-sm" name="product_type" >
                                    <!-- @foreach(\App\Model\Product::TYPE_LIST as $type)
                                        <option value="{{$type}}" {{$product_type == $type ? 'selected' : ''}}>
                                            {{\App\Model\Product::TYPE_LIST_NAME[$type]}}
                                        </option>
                                    @endforeach -->
                                        <option value="{{\App\Model\Product::TYPE_LIST[0]}}" {{$product_type == \App\Model\Product::TYPE_LIST[0] ? 'selected' : ''}}>
                                            {{\App\Model\Product::TYPE_LIST_NAME['image']}}
                                        </option>
                                        <option value="{{\App\Model\Product::TYPE_LIST[1]}}" {{$product_type == \App\Model\Product::TYPE_LIST[1] ? 'selected' : ''}}>
                                            {{\App\Model\Product::TYPE_LIST_NAME['video']}}
                                        </option>
                                    </select>
                                    <label for="exampleInputEmail1">&nbsp;&nbsp; {{trans('default.product_control.product_name') ?? '名稱'}}: </label>
                                    <input type="text" name="product_name" id="product_name" value="" placeholder="請輸入影片或圖片名稱">

                                    <label for="exampleInputEmail1">&nbsp;&nbsp;{{trans('default.product_control.product_id') ?? '商品序號'}} (多筆請用逗號隔開): </label>
                                    <input type="text" name="product_id" id="product_id" value="" placeholder="請輸入ID">
                                    
                                    <button type="submit" class="btn btn-primary">查詢</button>
                                </form>
                            </div>
                            <button type="submit" class="btn btn-primary col-md-1 mb-1" onclick="multipleUpdate()">{{trans('default.product_control.multiple_edit') ?? '大批修改'}}</button>
                            <button type="submit" class="btn btn-danger col-md-1 mb-1" onclick="clearCache()">{{trans('default.product_control.product_clear_choose') ?? '清除選擇'}}</button>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="example2" class="table table-bordered table-hover dataTable dtr-inline"
                                       aria-describedby="example2_info">
                                    <thead>
                                    <tr>
                                    <th class="sorting sorting_asc" tabindex="0" aria-controls="example2"
                                            rowspan="1"
                                            colspan="1" aria-sort="ascending"
                                            aria-label="Rendering engine: activate to sort column descending">{{trans('default.id') ?? '序號'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.type') ?? '類型'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.name') ?? '名稱'}}
                                        </th>
                                        @if(! empty($product_type))
                                            @if($product_type == \App\Model\Product::TYPE_LIST[0] || $product_type == \App\Model\Product::TYPE_LIST[1])
                                            <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                                colspan="1"
                                                aria-label="Engine version: activate to sort column ascending">
                                                {{trans('default.image') ?? '圖片'}}
                                            </th>
                                            @endif
                                            @if($product_type == \App\Model\Product::TYPE_LIST[1])
                                            <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                                colspan="1"
                                                aria-label="Engine version: activate to sort column ascending">
                                                {{trans('default.preview') ?? '預覽'}}
                                            </th>
                                            @endif
                                            <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                                colspan="1"
                                                aria-label="Engine version: activate to sort column ascending">
                                                {{trans('default.click_num') ?? '點擊次數'}}
                                            </th>
                                        @endif
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.product_control.product_currency') ?? '商品幣別'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.product_control.product_price') ?? '商品價格'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.time')?? '時間'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.take_msg') ?? '上下架'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="CSS grade: activate to sort column ascending">
                                            <input type="checkbox" name="check_all" id="check_all" > 全選<br>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(!empty($datas))
                                    @foreach($datas as $model)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $model->id}}</td>
                                            <td>
                                                {{trans('default.product_control.product_type_msg')[$model->type]}}
                                            </td>
                                            <td>{{ $model->name}}</td>
                                            @if(! empty($product_type))
                                                @if($product_type == \App\Model\Product::TYPE_LIST[0] || $product_type == \App\Model\Product::TYPE_LIST[1])
                                                    <td>
                                                        <img src="{{ $model->img_thumb}}" alt="" style="width:100px">
                                                    </td>
                                                @endif
                                                @if($product_type == \App\Model\Product::TYPE_LIST[1])
                                                    <td>
                                                        <video controls>
                                                            <source src="{{$model->m3u8}}" type="application/x-mpegURL">
                                                        </video>
                                                    </td>
                                                @endif
                                                <td>
                                                    0次
                                                </td>
                                            @endif
                                            <td>
                                                {{trans('default.product_control.product_currency_msg')[$model->currency]}}
                                            </td>
                                            <td>{{ $model->selling_price}}</td>
                                            <td>
                                                {{trans('default.start') ?? '開始'}}: {{ $model->start_time}}<br>
                                                {{trans('default.end') ?? '結束'}}: {{ $model->end_time}}<br>
                                                {{trans('default.edit') ?? '編輯'}}: {{ $model->updated_at}}
                                            </td>
                                            <td>{{ $model->expire == 0 ? trans('default.take_up') : trans('default.take_down')}}</td>
                                            <td>
                                                <div class="row mb-1">
                                                    <input type="checkbox" name="Interest" id="checkbox{{$model->id}}" onclick="insertCache({{$model->id}})"> 新增<br>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1">{{trans('default.id') ?? '序號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.type') ?? '類型'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.name')?? '名稱'}}</th>
                                        @if(! empty($product_type))
                                            @if($product_type == \App\Model\Product::TYPE_LIST[0] || $product_type == \App\Model\Product::TYPE_LIST[1])
                                                <th rowspan="1" colspan="1">{{trans('default.image') ?? '圖片'}}</th>
                                            @endif
                                            @if($product_type == \App\Model\Product::TYPE_LIST[1])
                                                <th rowspan="1" colspan="1">{{trans('default.preview') ?? '預覽'}}</th>
                                            @endif
                                            <th rowspan="1" colspan="1">{{trans('default.click_num') ?? '點擊次數'}}</th>
                                        @endif
                                        <th rowspan="1" colspan="1">{{trans('default.product_control.product_currency') ?? '商品幣別'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.product_control.product_price') ?? '商品價格'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.time') ?? '時間'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.take_msg')?? '上下架'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.action') ?? '動作'}}</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="example2_info" role="status" aria-live="polite">
                                {{trans('default.table_page_info',[
                                        'page' => $page,
                                        'total' => $total,
                                        'last_page' => $last_page,
                                        'step' => $step,
                                    ]) ?? '顯示第 $page 頁
                                    共 $total 筆
                                    共 $last_page 頁
                                    每頁顯示 $step 筆'}}
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous {{$page <= 1 ? 'disabled' : ''}}" id="example2_previous">
                                            <a href="{{$prev}}"
                                               aria-controls="example2" data-dt-idx="0" tabindex="0"
                                               class="page-link">{{trans('default.pre_page') ?? '上一頁'}}</a>
                                        </li>
                                        <li class="paginate_button page-item next {{$last_page <= $page ? 'disabled' : ''}}" id="example2_next">
                                            <a href="{{$next}}"
                                               aria-controls="example2"
                                               data-dt-idx="7"
                                               tabindex="0"
                                               class="page-link">{{trans('default.next_page') ?? '下一頁'}}</a>
                                        </li>
                                    </ul>



                                </div>
                            </div>
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
            let products = JSON.parse(localStorage.getItem('multipleProducts'));
            products.forEach(id => {
                $("#checkbox" + id).prop('checked', true);
            });
        });
        function insertCache(id){
            let products = JSON.parse(localStorage.getItem('multipleProducts'));
            if(products){
                // 確認是否有重複 有的話刪除
                check = products.indexOf(id);
                if(check != -1){
                    products.splice(check,1);
                }else{
                    products.push(id);
                }
                
                localStorage.setItem('multipleProducts', JSON.stringify(products));
            }else{
                products = [];
                products.push(id);
                localStorage.setItem('multipleProducts', JSON.stringify(products));
            }
            console.log(localStorage.getItem('multipleProducts'));
        }

        function clearCache(){
            let products = JSON.parse(localStorage.getItem('multipleProducts'));;
            
            products.forEach(id => {
                $("#checkbox" + id).prop('checked', false);
                $("#check_all").prop('checked', false);
                // document.getElementById("checkbox" + id).checked = false;
            });

            // Clear items
            localStorage.removeItem('multipleProducts');
            // Clear all items
            localStorage.clear();
            console.log(localStorage.getItem('multipleProducts'));
        }

        function multipleUpdate(){
            console.log('{{ urlencode($product_type) }}');
            let data = localStorage.getItem('multipleProducts');
            let type = '{{ urlencode($product_type) }}';
            if(data){
                clearCache();
                window.location.href = "/admin/product/multipleUpdate?data=" + data + "&type=" + type;
            }else{
                alert('請勾選欲建立的商品');
            }
        }

        $(document).ready(function() {
            // 點擊全選 checkbox
            $("#check_all").change(function() {
                if (this.checked) {
                    // 全選
                    $('input[name="Interest"]').prop("checked", true);
                    $('input[name="Interest"]').each(function() {
                        var id = $(this).attr('id');
                        insertCache(id.replace('checkbox',''));
                    });
                } else {
                    // 取消全選
                    $('input[name="Interest"]').prop("checked", false);
                    clearCache();
                }
            });
        });
    </script>
@endsection